<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PagamentoPendente;
use App\Services\MercadoPagoService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Throwable;

class MercadoPagoVerificarPagamentos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mercadopago:verificar-pagamentos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica pagamentos pendentes no Mercado Pago e processa aqueles que foram aprovados.';

    protected MercadoPagoService $mercadoPagoService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(MercadoPagoService $mercadoPagoService)
    {
        parent::__construct();
        $this->mercadoPagoService = $mercadoPagoService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Log::info('[JOB] Iniciando verificação de pagamentos pendentes do Mercado Pago.');
        $this->info('[JOB] Iniciando verificação de pagamentos pendentes do Mercado Pago.');

        $statusNaoFinais = ['pending', 'in_process', 'authorized', null];

        $pagamentosParaVerificar = PagamentoPendente::where('inscricao_efetivada', false)
            ->whereNotNull('id_pagamento_mp')
            ->where(function ($query) use ($statusNaoFinais) {
                $query->whereNull('status_pagamento_mp')
                      ->orWhereIn('status_pagamento_mp', $statusNaoFinais);
            })
            ->get();

        if ($pagamentosParaVerificar->isEmpty()) {
            Log::info('[JOB] Nenhum pagamento pendente encontrado para verificação.');
            $this->info('[JOB] Nenhum pagamento pendente encontrado para verificação.');
            return Command::SUCCESS;
        }

        $countTotal = $pagamentosParaVerificar->count();
        Log::info(sprintf('[JOB] %d pagamentos pendentes encontrados para verificação.', $countTotal));
        $this->info(sprintf('[JOB] %d pagamentos pendentes encontrados para verificação.', $countTotal));

        $pagamentosProcessadosComSucesso = 0;
        $pagamentosAprovadosEInscritos = 0;
        $errosNaVerificacao = 0;

        foreach ($pagamentosParaVerificar as $pagamentoPendente) {
            $this->info(sprintf('Verificando pagamento MP ID: %s (Pendente UUID: %s)', $pagamentoPendente->id_pagamento_mp, $pagamentoPendente->uuid));
            
            $pagamentoPendenteProcessadoNestaIteracao = false;
            try {
                DB::beginTransaction();

                // O método verificarEProcessarPagamentoPendente agora lida com a lógica de buscar 
                // detalhes e chamar processarPagamentoAprovado. Ele retorna true se uma ação 
                // significativa foi tomada (status mudou, ou foi aprovado e processado).
                $acaoRealizada = $this->mercadoPagoService->verificarEProcessarPagamentoPendente($pagamentoPendente);

                if ($acaoRealizada) {
                    // Se foi aprovado e a inscrição efetivada, o log específico já ocorre dentro do serviço ou aqui abaixo.
                    if ($pagamentoPendente->inscricao_efetivada && $pagamentoPendente->status_pagamento_mp === 'approved') {
                        Log::info(sprintf('[JOB] Pagamento MP ID: %s (Pendente UUID: %s) foi APROVADO e inscrição EFETIVADA.', $pagamentoPendente->id_pagamento_mp, $pagamentoPendente->uuid));
                        $this->info(sprintf('Pagamento MP ID: %s APROVADO e inscrição EFETIVADA.', $pagamentoPendente->id_pagamento_mp));
                        $pagamentosAprovadosEInscritos++;
                    } else {
                        // Log apenas se status mudou mas não resultou em inscrição (ex: de pending para in_process)
                        Log::info(sprintf('[JOB] Pagamento MP ID: %s (Pendente UUID: %s) teve status atualizado para: %s.', $pagamentoPendente->id_pagamento_mp, $pagamentoPendente->uuid, $pagamentoPendente->status_pagamento_mp));
                    }
                    $pagamentoPendenteProcessadoNestaIteracao = true;
                }
                
                // Salva o pagamentoPendente independentemente se houve grande ação ou apenas atualização de dados_webhook_mp_json
                // A transação garante que, se processarPagamentoAprovado falhar (lançar exceção), o save não ocorre.
                $pagamentoPendente->save();
                DB::commit();
                
                if ($pagamentoPendenteProcessadoNestaIteracao) {
                    $pagamentosProcessadosComSucesso++;
                }

            } catch (Throwable $e) {
                DB::rollBack();
                $errosNaVerificacao++;
                Log::error(sprintf('[JOB] Erro ao verificar/processar pagamento MP ID: %s (Pendente UUID: %s)', $pagamentoPendente->id_pagamento_mp, $pagamentoPendente->uuid), [
                    'exception_class' => get_class($e),
                    'exception_message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    // 'trace' => $e->getTraceAsString() // Pode ser muito verboso para logs de rotina
                ]);
                $this->error(sprintf('Erro ao verificar/processar pagamento MP ID: %s. Detalhes no log.', $pagamentoPendente->id_pagamento_mp));
                // Continuar para o próximo pagamento
            }
        }

        Log::info(sprintf(
            '[JOB] Verificação de pagamentos concluída. Total verificados: %d. Processados com sucesso (status atualizado ou inscrição): %d. Aprovados e inscritos nesta execução: %d. Erros: %d.',
            $countTotal,
            $pagamentosProcessadosComSucesso,
            $pagamentosAprovadosEInscritos,
            $errosNaVerificacao
        ));
        $this->info(sprintf(
            '[JOB] Verificação concluída. Verificados: %d. Processados: %d. Aprovados/Inscritos agora: %d. Erros: %d.',
            $countTotal,
            $pagamentosProcessadosComSucesso,
            $pagamentosAprovadosEInscritos,
            $errosNaVerificacao
        ));
        
        return Command::SUCCESS;
    }
}
