<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\PagamentoPendente;
use Illuminate\Support\Facades\DB;
use Throwable;
use App\Services\InscricaoService;
use App\DTO\CreateInscricaoDTO;
use Illuminate\Support\Str;
use Carbon\Carbon;

class MercadoPagoService
{
    protected string $accessToken;
    protected string $publicKey;
    protected string $baseUri;
    protected string $notificationUrl;

    public function __construct()
    {
        $this->accessToken = config('mercadopago.access_token');
        $this->publicKey = config('mercadopago.public_key');
        $this->baseUri = config('mercadopago.urls.base');
        $this->notificationUrl = config('mercadopago.notification_url');

        if (empty($this->accessToken) || empty($this->publicKey)) {
            // Log::error('Credenciais do Mercado Pago não configuradas. Verifique o arquivo .env e config/mercadopago.php');
            // Considerar lançar uma exceção aqui para impedir o funcionamento se as credenciais estiverem ausentes
        }
    }

    /**
     * Método para realizar uma chamada genérica à API do Mercado Pago.
     */
    protected function makeRequest(string $method, string $uri, array $data = [], array $headers = [])
    {
        $fullUri = $this->baseUri . $uri;

        $defaultHeaders = [
            'Authorization' => 'Bearer ' . $this->accessToken,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        // Adicionar X-Idempotency-Key para métodos POST, PUT, PATCH
        if (in_array(strtoupper($method), ['POST', 'PUT', 'PATCH'])) {
            $defaultHeaders['X-Idempotency-Key'] = Str::uuid()->toString();
        }

        $response = Http::withHeaders(array_merge($defaultHeaders, $headers))
            ->$method($fullUri, $data);

        if ($response->failed()) {
            /*Log::error('Erro na chamada à API do Mercado Pago', [
                'uri' => $fullUri,
                'method' => $method,
                'status' => $response->status(),
                'response_body' => $response->body(),
                'request_data' => $data
            ]);*/
            // Lançar uma exceção personalizada ou retornar um erro específico
            $response->throw(); // Lança uma Illuminate\Http\Client\RequestException
        }

        return $response->json();
    }

    // Métodos para criar pagamento com cartão, pix, etc., serão adicionados aqui.
    // Exemplo:
    // public function createCardPayment(array $paymentData, string $cardToken, array $preInscriptionData) { ... }
    // public function createPixPayment(array $paymentData, array $preInscriptionData) { ... }
    // public function handleWebhookNotification(array $notificationData) { ... }

    /**
     * Cria uma intenção de pagamento com cartão de crédito no Mercado Pago.
     *
     * @param array $data Contém: evento_codigo, participante_codigo, valor, descricao_pagamento,
     *                    card_token, installments, payment_method_id, payer (com email, identification.type, identification.number)
     * @return array Resposta da API do Mercado Pago.
     * @throws \Illuminate\Http\Client\RequestException Em caso de erro na API do MP.
     * @throws Throwable Em caso de outros erros.
     */
    public function createCardPayment(array $data): array
    {
        // Validação básica dos dados de entrada (pode ser mais robusta)
        // O ideal é que isso seja feito por um FormRequest antes de chegar ao Service
        if (empty($data['card_token']) || empty($data['valor']) || empty($data['installments']) || empty($data['payment_method_id']) || empty($data['payer']['email'])) {
            throw new \InvalidArgumentException('Dados incompletos para criar pagamento com cartão.');
        }

        $pagamentoPendente = null;

        try {
            DB::beginTransaction();

            // 1. Criar registro de pagamento pendente
            $pagamentoPendente = PagamentoPendente::create([
                'evento_codigo' => $data['evento_codigo'],
                'participante_codigo' => $data['participante_codigo'],
                'forma_pagamento_solicitada' => 'credit_card',
                'valor' => $data['valor'],
                // status_pagamento_mp e id_pagamento_mp serão preenchidos após a chamada à API
            ]);

            // 2. Preparar payload para a API do Mercado Pago
            $payload = [
                'transaction_amount' => (float) $data['valor'],
                'token' => $data['card_token'],
                'description' => $data['descricao_pagamento'] ?? 'Pagamento Inscrição Evento',
                'installments' => (int) $data['installments'],
                'payment_method_id' => $data['payment_method_id'],
                'payer' => [
                    'email' => $data['payer']['email'],
                    // Adicionar identificação se disponível e necessário para o MP
                    // 'identification' => [
                    //    'type' => $data['payer']['identification']['type'], 
                    //    'number' => $data['payer']['identification']['number']
                    // ], 
                ],
                'external_reference' => $pagamentoPendente->uuid,
                'notification_url' => $this->notificationUrl,
                //'capture' => false, // Para autorizar e capturar depois. Default é true (captura automática).
            ];
            
            // Adiciona dados de identificação do pagador se existirem
            if (!empty($data['payer']['identification']['type']) && !empty($data['payer']['identification']['number'])) {
                $payload['payer']['identification'] = [
                    'type' => $data['payer']['identification']['type'],
                    'number' => $data['payer']['identification']['number']
                ];
            }


            // 3. Chamar a API do Mercado Pago
            $paymentResponse = $this->makeRequest(
                'POST',
                config('mercadopago.urls.payments'),
                $payload
            );

            // 4. Atualizar o registro de pagamento pendente com a resposta do MP
            $pagamentoPendente->id_pagamento_mp = $paymentResponse['id'] ?? null;
            $pagamentoPendente->status_pagamento_mp = $paymentResponse['status'] ?? null;
            $pagamentoPendente->dados_criacao_mp_json = $paymentResponse;
            $pagamentoPendente->save();

            DB::commit();

            return $paymentResponse;

        } catch (Throwable $e) {
            DB::rollBack();
            /*Log::error('Erro ao criar pagamento com cartão no MercadoPagoService', [
                'exception_message' => $e->getMessage(),
                'pagamento_pendente_id' => $pagamentoPendente->id ?? null,
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);*/
            // Re-lançar a exceção para ser tratada pela camada superior (Controller)
            throw $e;
        }
    }

    /**
     * Cria uma intenção de pagamento com PIX no Mercado Pago.
     *
     * @param array $data Contém: evento_codigo, participante_codigo, valor, descricao_pagamento,
     *                    payer (com email, first_name, last_name, identification.type, identification.number)
     * @return array Resposta da API do Mercado Pago, incluindo dados para QR Code/Copia e Cola.
     * @throws \Illuminate\Http\Client\RequestException Em caso de erro na API do MP.
     * @throws Throwable Em caso de outros erros.
     */
    public function createPixPayment(array $data): array
    {
        // Validação básica (idealmente feita por um FormRequest)
        if (empty($data['valor']) || empty($data['payer']['email']) || 
            empty($data['payer']['first_name']) || empty($data['payer']['last_name']) ||
            empty($data['payer']['identification']['type']) || empty($data['payer']['identification']['number'])) {
            throw new \InvalidArgumentException('Dados incompletos para criar pagamento PIX. São necessários: valor, payer.email, payer.first_name, payer.last_name, payer.identification (type e number).');
        }

        $pagamentoPendente = null;

        try {
            DB::beginTransaction();

            // 1. Criar registro de pagamento pendente
            $pagamentoPendente = PagamentoPendente::create([
                'evento_codigo' => $data['evento_codigo'],
                'participante_codigo' => $data['participante_codigo'],
                'forma_pagamento_solicitada' => 'pix',
                'valor' => $data['valor'],
            ]);

            // 2. Preparar payload para a API do Mercado Pago
            $payload = [
                'transaction_amount' => (float) $data['valor'],
                'description' => $data['descricao_pagamento'] ?? 'Pagamento PIX Inscrição Evento',
                'payment_method_id' => 'pix',
                'payer' => [
                    'email' => $data['payer']['email'],
                    'first_name' => $data['payer']['first_name'],
                    'last_name' => $data['payer']['last_name'],
                    'identification' => [
                        'type' => $data['payer']['identification']['type'], // Ex: CPF ou CNPJ
                        'number' => $data['payer']['identification']['number']
                    ],
                    // Poderia adicionar endereço se necessário/disponível
                ],
                'external_reference' => $pagamentoPendente->uuid,
                'notification_url' => $this->notificationUrl,
                // 'date_of_expiration' => now()->addMinutes(30)->toIso8601String(), // Exemplo: Pix expira em 30 minutos
            ];
            
            if (!empty($data['date_of_expiration'])) {
                 $payload['date_of_expiration'] = $data['date_of_expiration'];
            }           

            // 3. Chamar a API do Mercado Pago
            $paymentResponse = $this->makeRequest(
                'POST',
                config('mercadopago.urls.payments'),
                $payload
            );

            // 4. Atualizar o registro de pagamento pendente com a resposta do MP
            $pagamentoPendente->id_pagamento_mp = $paymentResponse['id'] ?? null;
            $pagamentoPendente->status_pagamento_mp = $paymentResponse['status'] ?? null; // Geralmente 'pending'
            $pagamentoPendente->dados_criacao_mp_json = $paymentResponse;
            $pagamentoPendente->save();

            DB::commit();

            // A resposta conterá point_of_interaction.transaction_data.qr_code_base64 e .qr_code (copia e cola)
            return $paymentResponse;

        } catch (Throwable $e) {
            DB::rollBack();
            /*Log::error('Erro ao criar pagamento PIX no MercadoPagoService', [
                'exception_message' => $e->getMessage(),
                'pagamento_pendente_id' => $pagamentoPendente->id ?? null,
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);*/
            throw $e;
        }
    }

    /**
     * Processa uma notificação de webhook recebida do Mercado Pago.
     *
     * @param array $notificationData Dados da notificação (geralmente o corpo do request).
     * @return bool True se processado com sucesso, false caso contrário.
     */
    public function handleWebhookNotification(array $notificationData): bool
    {
        // Log::info('Webhook do Mercado Pago recebido:', $notificationData);

        $type = $notificationData['type'] ?? null;
        $paymentId = $notificationData['data']['id'] ?? null;

        if ($type === 'payment' && $paymentId) {
            $pagamentoPendente = null; // Definir para ter escopo no catch
            try {
                $paymentInfo = $this->getPaymentDetails($paymentId);

                if (empty($paymentInfo['external_reference'])) {
                    Log::warning('Webhook Mercado Pago: external_reference não encontrada para o pagamento.', [
                        'payment_id' => $paymentId,
                        'payment_info' => $paymentInfo
                    ]);
                    return true; 
                }

                // Usar transação para garantir atomicidade
                DB::beginTransaction();

                $pagamentoPendente = PagamentoPendente::where('uuid', $paymentInfo['external_reference'])->first();

                if (!$pagamentoPendente) {
                    Log::warning('Webhook Mercado Pago: PagamentoPendente não encontrado pela external_reference. Tentando buscar por id_pagamento_mp como fallback.', [
                        'external_reference' => $paymentInfo['external_reference'],
                        'payment_id_mp' => $paymentId
                    ]);
                    // Fallback: tentar encontrar pelo ID do pagamento do MP, caso external_reference não tenha sido gravada ou seja diferente
                    $pagamentoPendente = PagamentoPendente::where('id_pagamento_mp', $paymentId)->first();
                    if (!$pagamentoPendente) {
                        Log::error('Webhook Mercado Pago: PagamentoPendente não encontrado nem por external_reference nem por id_pagamento_mp.', [
                            'external_reference' => $paymentInfo['external_reference'],
                            'payment_id_mp' => $paymentId
                        ]);
                        DB::rollBack(); // Importante reverter se não encontrarmos o PagamentoPendente
                        return true; // Retorna true para MP não reenviar, mas logamos o erro.
                    }
                     Log::info('Webhook Mercado Pago: PagamentoPendente encontrado via fallback (id_pagamento_mp).', [
                        'pagamento_pendente_uuid' => $pagamentoPendente->uuid,
                        'payment_id_mp' => $paymentId
                    ]);
                }
                
                $pagamentoPendente->status_pagamento_mp = $paymentInfo['status'] ?? $pagamentoPendente->status_pagamento_mp;
                $pagamentoPendente->dados_webhook_mp_json = $paymentInfo;
                
                if ($paymentInfo['status'] === 'approved') {
                    // processarPagamentoAprovado lançará exceção em caso de erro,
                    // que será capturada pelo catch (Throwable $e) abaixo.
                    $this->processarPagamentoAprovado($pagamentoPendente, $paymentInfo);
                }
                // Outros status (rejected, cancelled, etc.) podem ser tratados aqui se necessário,
                // por exemplo, para enviar notificações ao usuário ou atualizar a UI.
                // Por ora, apenas atualizamos o status no PagamentoPendente.

                $pagamentoPendente->save();
                DB::commit();

                Log::info('Webhook do Mercado Pago processado com sucesso.', ['payment_id' => $paymentId, 'external_reference' => $paymentInfo['external_reference'], 'status_mp' => $paymentInfo['status']]);
                return true;

            } catch (Throwable $e) {
                DB::rollBack();
                Log::error('Erro ao processar webhook do Mercado Pago no Service', [
                    'exception_class' => get_class($e),
                    'exception_message' => $e->getMessage(),
                    'payment_id' => $paymentId,
                    'pagamento_pendente_uuid' => $pagamentoPendente->uuid ?? 'N/A',
                    'notification_data' => $notificationData,
                    'trace' => $e->getTraceAsString()
                ]);
                return false; // Sinaliza erro para o MP tentar reenviar.
            }
        } else {
            // Log::info('Webhook do Mercado Pago ignorado (tipo não é \'payment\' ou ID do pagamento ausente).', $notificationData);
            return true; 
        }
    }

    /**
     * Processa um pagamento que foi confirmado como aprovado.
     * Cria a inscrição correspondente se ainda não foi efetivada.
     *
     * @param PagamentoPendente $pagamentoPendente
     * @param array $paymentInfo Detalhes do pagamento obtidos da API do Mercado Pago.
     * @return bool True se a inscrição foi criada ou já existia, false em caso de erro na criação.
     * @throws Throwable
     */
    protected function processarPagamentoAprovado(PagamentoPendente $pagamentoPendente, array $paymentInfo): bool
    {
        if ($pagamentoPendente->inscricao_efetivada) {
            // Log::info('Inscrição já efetivada anteriormente para este pagamento.', [
            //     'pagamento_pendente_id' => $pagamentoPendente->id,
            //     'evento_codigo' => $pagamentoPendente->evento_codigo,
            // ]);
            return true; // Já foi processado
        }

        /* Log::info('Pagamento aprovado. Tentando criar inscrição...', [
            'pagamento_pendente_id' => $pagamentoPendente->id,
            'evento_codigo' => $pagamentoPendente->evento_codigo,
            'participante_codigo' => $pagamentoPendente->participante_codigo
        ]);*/

        // A transação DB é controlada pelo método chamador (handleWebhookNotification ou o novo comando)
        // try { // Este try-catch é para a lógica de inscrição em si.
        $inscricaoService = app(InscricaoService::class);

        // Determinar a data da inscrição
        $dataInscricao = now()->setTimezone('-03:00'); // Fallback para agora em UTC-3
        if (!empty($paymentInfo['date_approved'])) {
            try {
                $dataInscricao = Carbon::parse($paymentInfo['date_approved'])->setTimezone('-03:00');
            } catch (\Exception $dateException) {
                Log::warning('MercadoPagoService: Falha ao parsear date_approved do MP. Usando data atual.', [
                    'date_approved' => $paymentInfo['date_approved'],
                    'error' => $dateException->getMessage()
                ]);
            }
        }

        // Preparar dados para o DTO
        $formaPagamentoDetalhada = $pagamentoPendente->forma_pagamento_solicitada . ' (MP ' . ($paymentInfo['payment_method_id'] ?? '');
        if (!empty($paymentInfo['payment_type_id'])) {
            $formaPagamentoDetalhada .= ' - ' . $paymentInfo['payment_type_id'];
        }
        $formaPagamentoDetalhada .= ')';

        $dadosParaConstrutorDTO = [
            'evento_codigo' => $pagamentoPendente->evento_codigo,
            'participante_codigo' => $pagamentoPendente->participante_codigo,
            'data' => $dataInscricao->toDateTimeString(),
            'forma_pagamento' => $formaPagamentoDetalhada,
            'cortesia' => false,
            'status' => 'P', // Pago
        ];
        
        // Log::debug('MercadoPagoService: Dados preparados para CreateInscricaoDTO', $dadosParaConstrutorDTO);

        // Log::debug('MercadoPagoService: Tentando instanciar CreateInscricaoDTO.');
        $createInscricaoDTO = new CreateInscricaoDTO(...$dadosParaConstrutorDTO);
        // Log::debug('MercadoPagoService: CreateInscricaoDTO instanciado com sucesso.', (array) $createInscricaoDTO);

        // Log::debug('MercadoPagoService: Tentando chamar inscricaoService->new().');
        $novaInscricao = $inscricaoService->new($createInscricaoDTO);
        // Log::debug('MercadoPagoService: inscricaoService->new() chamado com sucesso.');

        $pagamentoPendente->inscricao_efetivada = true;
        // $pagamentoPendente->save(); // REMOVIDO: O save será feito pelo chamador, dentro da transação principal.

        /*
        Log::info('Inscrição efetivada via processamento de pagamento aprovado.', [
            'inscricao_id' => $novaInscricao->codigo, 
            'pagamento_pendente_id' => $pagamentoPendente->id
        ]);
        */
        return true; // Sucesso na criação da inscrição

        // } catch (Throwable $inscricaoException) { // Exceções serão capturadas pelo chamador
        //     // Log::error('MercadoPagoService: Erro ao tentar instanciar DTO ou chamar InscricaoService no processarPagamentoAprovado', [...]);
        //     throw $inscricaoException; 
        // }
    }

    /**
     * Busca os detalhes de um pagamento específico na API do Mercado Pago.
     *
     * @param string $paymentId
     * @return array
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function getPaymentDetails(string $paymentId): array
    {
        $uri = config('mercadopago.urls.payments') . '/' . $paymentId;
        return $this->makeRequest('GET', $uri);
    }

    /**
     * Valida a assinatura de uma notificação de webhook do Mercado Pago.
     *
     * @param string $signatureHeader O valor do header X-Signature (ex: "ts=...,v1=...").
     * @param string $requestBody O corpo raw da requisição (payload JSON) - usado como fallback.
     * @param string|null $dataIdFromQuery O valor de 'data.id' extraído da query string.
     * @param string|null $xRequestIdHeader O valor do header 'X-Request-Id'.
     * @return bool True se a assinatura for válida, false caso contrário.
     */
    public function validateWebhookSignature(string $signatureHeader, string $requestBody, ?string $dataIdFromQuery, ?string $xRequestIdHeader): bool
    {
        $secret = config('mercadopago.webhook_secret');

        if (empty($secret)) {
            // Log::warning('Webhook Mercado Pago: Secret para validação de assinatura não configurado. Pulando validação (perigoso!).');
            // Em produção, você deveria retornar false aqui se o secret é esperado.
            // Para desenvolvimento/teste sem secret, pode retornar true, mas CUIDADO.
            return false; // Ou false se quiser forçar a validação e configuração do secret.
        }

        $timestamp = null;
        $receivedSignatureV1 = null;

        // Parse o header X-Signature (ex: "ts=123456789,v1=blablabla")
        $parts = explode(',', $signatureHeader);
        foreach ($parts as $part) {
            list($key, $value) = explode('=', $part, 2);
            if ($key === 'ts') {
                $timestamp = $value;
            } elseif ($key === 'v1') {
                $receivedSignatureV1 = $value;
            }
        }

        if (is_null($timestamp) || is_null($receivedSignatureV1)) {
            // Log::error('Webhook Mercado Pago: Header X-Signature malformado ou componentes ts/v1 ausentes.', ['header' => $signatureHeader]);
            return false;
        }

        // Tenta a nova validação (com data.id da query e X-Request-ID)
        if ($dataIdFromQuery && $xRequestIdHeader) {
            // Formato do manifest: "id:{data.id};request-id:{x-request-id};ts:{timestamp};"
            $manifest = "id:{$dataIdFromQuery};request-id:{$xRequestIdHeader};ts:{$timestamp};";
            $calculatedSignature = hash_hmac('sha256', $manifest, $secret);

            if (hash_equals($calculatedSignature, $receivedSignatureV1)) {
                Log::info('Webhook Mercado Pago: Assinatura (nova) validada com sucesso.');
                return true;
            }
            /*Log::warning('Webhook Mercado Pago: Falha na validação da assinatura (nova).', [
                'received_signature' => $receivedSignatureV1,
                'calculated_signature' => $calculatedSignature,
                'manifest' => $manifest
            ]);*/
            // Não retorna false imediatamente, pode tentar o método antigo se este falhar.
        }

        // Fallback para o método de validação anterior (usando data.id do corpo do request)
        // Este bloco pode ser útil se nem todos webhooks do MP usarem o X-Request-ID ou se houver transição.
        $notificationData = json_decode($requestBody, true);
        $dataIdFromBody = $notificationData['data']['id'] ?? null;

        if (is_null($dataIdFromBody)) {
            // Log::error('Webhook Mercado Pago (fallback): data.id não encontrado no corpo da requisição para validação da assinatura.', ['body' => $requestBody]);
            // Se a nova validação falhou e o fallback também não tem os dados, então é erro.
            return false;
        }

        // Formato antigo/alternativo do signed_content: "data-id:{data.id};ts:{timestamp};"
        $signedContentFallback = "data-id:{$dataIdFromBody};ts:{$timestamp};";
        $calculatedSignatureFallback = hash_hmac('sha256', $signedContentFallback, $secret);

        if (hash_equals($calculatedSignatureFallback, $receivedSignatureV1)) {
            // Log::info('Webhook Mercado Pago: Assinatura (fallback) validada com sucesso.');
            return true;
        }

        /*Log::warning('Webhook Mercado Pago: Falha na validação da assinatura (fallback).', [
            'received_signature' => $receivedSignatureV1,
            'calculated_signature_fallback' => $calculatedSignatureFallback,
            'signed_content_fallback' => $signedContentFallback,
            'data_id_from_query' => $dataIdFromQuery, // Loga para saber se a nova tentativa ocorreu
            'x_request_id' => $xRequestIdHeader // Loga para saber se a nova tentativa ocorreu
        ]);*/
        
        return false;
    }

    /**
     * Verifica o status de um pagamento pendente no Mercado Pago e processa se aprovado.
     * Este método é projetado para ser chamado por um job/comando.
     *
     * @param PagamentoPendente $pagamentoPendente
     * @return bool True se alguma ação foi tomada (status atualizado, inscrição processada), false caso contrário.
     * @throws Throwable Em caso de erro na API do MP ou no processamento da inscrição.
     */
    public function verificarEProcessarPagamentoPendente(PagamentoPendente $pagamentoPendente): bool
    {
        if (empty($pagamentoPendente->id_pagamento_mp)) {
            Log::warning('MercadoPagoService: Tentativa de verificar pagamento pendente sem id_pagamento_mp.', [
                'pagamento_pendente_uuid' => $pagamentoPendente->uuid
            ]);
            return false;
        }

        // Log::info(sprintf('MercadoPagoService: Verificando pagamento MP ID: %s (Pendente UUID: %s)', $pagamentoPendente->id_pagamento_mp, $pagamentoPendente->uuid));

        $paymentInfo = $this->getPaymentDetails($pagamentoPendente->id_pagamento_mp);

        $statusAnterior = $pagamentoPendente->status_pagamento_mp;
        $pagamentoPendente->status_pagamento_mp = $paymentInfo['status'] ?? $statusAnterior;
        // Atualiza os dados do webhook/verificação com a informação mais recente.
        $pagamentoPendente->dados_webhook_mp_json = $paymentInfo;

        $statusAtual = $pagamentoPendente->status_pagamento_mp;
        $houveMudancaDeStatus = ($statusAnterior !== $statusAtual);

        if ($statusAtual === 'approved' && !$pagamentoPendente->inscricao_efetivada) {
            // Log::info(sprintf('MercadoPagoService: Pagamento MP ID: %s (UUID: %s) foi APROVADO via verificação. Processando inscrição.', $pagamentoPendente->id_pagamento_mp, $pagamentoPendente->uuid));
            $this->processarPagamentoAprovado($pagamentoPendente, $paymentInfo); 
            // processarPagamentoAprovado agora apenas marca inscricao_efetivada = true, não salva.
            // O save será feito pelo chamador deste método (o comando), dentro de sua transação.
            return true; // Indica que o processamento da aprovação foi iniciado.
        } elseif ($houveMudancaDeStatus) {
            // Log::info(sprintf('MercadoPagoService: Pagamento MP ID: %s (UUID: %s) teve status atualizado para: %s via verificação.', $pagamentoPendente->id_pagamento_mp, $pagamentoPendente->uuid, $statusAtual));
            return true; // Indica que o status foi atualizado.
        }

        // Se não houve mudança de status e não foi aprovado (ou já estava efetivado), não faz nada demais.
        return false; // Nenhuma ação significativa tomada (além de talvez atualizar dados_webhook_mp_json se já estava igual)
    }
} 