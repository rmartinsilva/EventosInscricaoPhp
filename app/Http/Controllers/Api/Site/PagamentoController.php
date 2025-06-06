<?php

namespace App\Http\Controllers\Api\Site;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePagamentoCartaoRequest;
use App\Http\Requests\StorePagamentoPixRequest;
use App\Services\MercadoPagoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Models\PagamentoPendente;

class PagamentoController extends Controller
{
    protected MercadoPagoService $mercadoPagoService;

    public function __construct(MercadoPagoService $mercadoPagoService)
    {
        $this->mercadoPagoService = $mercadoPagoService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Processa um pagamento com cartão de crédito.
     *
     * @param StorePagamentoCartaoRequest $request
     * @return JsonResponse
     */
    public function processarCartao(StorePagamentoCartaoRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            
            $paymentResponse = $this->mercadoPagoService->createCardPayment($validatedData);

            // Log da resposta completa do serviço do Mercado Pago
            Log::info('Resposta do MercadoPagoService@createCardPayment:', $paymentResponse);

            // Verificar se o pagamento foi rejeitado por ser duplicado na resposta SÍNCRONA
            if (isset($paymentResponse['status']) && $paymentResponse['status'] === 'rejected' && 
                isset($paymentResponse['status_detail']) && $paymentResponse['status_detail'] === 'cc_rejected_duplicated_payment') {
                
                Log::info('Pagamento duplicado identificado na resposta síncrona.', [
                    'payment_id_mp' => $paymentResponse['id'] ?? null,
                    'status' => $paymentResponse['status'],
                    'status_detail' => $paymentResponse['status_detail']
                ]);
                return response()->json([
                    'message' => 'Parece que este pagamento já foi processado ou está duplicado. Verifique seus pagamentos anteriores ou tente novamente em alguns instantes.', 
                    'error_code' => 'duplicated_payment',
                    'mercadopago_response' => $paymentResponse // Opcional: enviar a resposta completa do MP para o front se necessário para depuração no front
                ], 400); // 400 Bad Request
            }

            // A resposta do Mercado Pago pode variar.
            // Se chegou aqui e não é duplicado, mas pode ser outro tipo de rejeição ou sucesso
            // O frontend precisará lidar com diferentes status ('approved', 'in_process', 'rejected' por outros motivos).
            return response()->json($paymentResponse, 201); // Ou 200 OK, dependendo da semântica e se o pagamento foi realmente criado/aprovado

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Erro de validação no PagamentoController@processarCartao', [
                'errors' => $e->errors(), 
                'request_data' => $request->all()
            ]);
            return response()->json(['message' => 'Erro de validação.', 'errors' => $e->errors()], 422);
        } catch (\Illuminate\Http\Client\RequestException $e) {
            $responseBody = $e->response ? $e->response->json() : null;
            $mpErrorCode = null;
            if (isset($responseBody['cause']) && is_array($responseBody['cause']) && count($responseBody['cause']) > 0) {
                $mpErrorCode = $responseBody['cause'][0]['code'] ?? null;
            } elseif (isset($responseBody['status']) && isset($responseBody['error'])){
                 // Lógica anterior para outros erros de API
            }

            $exceptionFullMessage = $e->getMessage();
            $errorRawBody = $e->response ? $e->response->body() : '';

            Log::error('Erro na API do Mercado Pago ao processar cartão (RequestException)', [
                'exception_full_message' => $exceptionFullMessage,
                'status_code' => $e->response ? $e->response->status() : null,
                'response_body_array' => $responseBody,
                'response_body_raw' => $errorRawBody,
                'request_data' => $request->validated()
            ]);

            $foundInExceptionMessage = str_contains($exceptionFullMessage, 'cc_rejected_duplicated_payment');
            $foundInErrorBody = str_contains($errorRawBody, 'cc_rejected_duplicated_payment');

            Log::debug('Verificando por cc_rejected_duplicated_payment em RequestException', [
                'string_to_find' => 'cc_rejected_duplicated_payment',
                'exception_message_content' => $exceptionFullMessage,
                'response_body_raw_content' => $errorRawBody,
                'found_in_exception_message' => $foundInExceptionMessage,
                'found_in_error_body' => $foundInErrorBody
            ]);

            if ($foundInExceptionMessage || $foundInErrorBody) {
                Log::info('Identificado cc_rejected_duplicated_payment em RequestException. Retornando mensagem amigável.');
                return response()->json([
                    'message' => 'Parece que este pagamento já foi processado ou está duplicado. Verifique seus pagamentos anteriores ou tente novamente em alguns instantes.', 
                    'error_code' => 'duplicated_payment'
                ], 400);
            }

            Log::info('Não identificado cc_rejected_duplicated_payment em RequestException. Retornando mensagem genérica.');
            return response()->json(['message' => 'Erro ao processar pagamento com o provedor. Tente novamente mais tarde.', 'details' => $exceptionFullMessage], 502);
        } catch (Exception $e) {
            Log::error('Erro inesperado no PagamentoController@processarCartao', [
                'message' => $e->getMessage(), 
                'request_data' => $request->validated(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['message' => 'Ocorreu um erro inesperado no servidor. Tente novamente mais tarde.'], 500);
        }
    }

    /**
     * Processa um pagamento com PIX.
     *
     * @param StorePagamentoPixRequest $request
     * @return JsonResponse
     */
    public function processarPix(StorePagamentoPixRequest $request): JsonResponse
    {
        try {

            $validatedData = $request->validated();
            $paymentResponse = $this->mercadoPagoService->createPixPayment($validatedData);

            Log::info('Resposta do MercadoPagoService@createPixPayment:', $paymentResponse);

            // A resposta do Mercado Pago para PIX incluirá dados para QR Code.
            // O frontend usará esses dados para exibir o QR Code e/ou o código Copia e Cola.
            return response()->json($paymentResponse, 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Erro de validação no PagamentoController@processarPix', [
                'errors' => $e->errors(), 
                'request_data' => $request->all()
            ]);
            return response()->json(['message' => 'Erro de validação.', 'errors' => $e->errors()], 422);
        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('Erro na API do Mercado Pago ao processar PIX', [
                'message' => $e->getMessage(),
                'status_code' => $e->response ? $e->response->status() : null,
                'response_body' => $e->response ? $e->response->body() : null,
                'request_data' => $request->validated()
            ]);
            return response()->json(['message' => 'Erro ao processar pagamento PIX com o provedor. Tente novamente mais tarde.', 'details' => $e->getMessage()], 502);
        } catch (Exception $e) {
            Log::error('Erro inesperado no PagamentoController@processarPix', [
                'message' => $e->getMessage(), 
                'request_data' => $request->validated(),
                'trace' => $e->getTraceAsString()
            ]);
            // dd($e); // Removido dd()
            return response()->json(['message' => 'Ocorreu um erro inesperado no servidor. Tente novamente mais tarde.'], 500);
        }
    }

    /**
     * Recebe e processa notificações de Webhook do Mercado Pago.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function handleMercadoPagoWebhook(Request $request): JsonResponse
    {
        $notificationData = $request->all(); 
        $requestBody = $request->getContent(); 
        $signatureHeader = $request->header('X-Signature');
        $xRequestIdHeader = $request->header('X-Request-Id'); 
        $dataIdFromQuery = $request->query('data_id'); 

        Log::info('Controlador: Webhook do Mercado Pago recebido', [
            'data_body' => $notificationData, 
            'query_params' => $request->query(), 
            'headers' => $request->headers->all(),
            'body_raw' => $requestBody
        ]);
        
        if (empty($signatureHeader)) {
            Log::warning('Webhook Mercado Pago: Header X-Signature ausente. Recusando.');
            return response()->json(['message' => 'Header X-Signature ausente.'], 403);
        }
        
        if (empty($xRequestIdHeader) || empty($dataIdFromQuery)) {
            Log::warning('Webhook Mercado Pago: Header X-Request-Id ou query param data.id ausente.', [
                'x_request_id' => $xRequestIdHeader,
                'data_id_query' => $dataIdFromQuery
            ]);
        }

        $isValidSignature = $this->mercadoPagoService->validateWebhookSignature(
            $signatureHeader, 
            $requestBody, 
            $dataIdFromQuery, 
            $xRequestIdHeader
        );
        if (!$isValidSignature) {
            Log::warning('Webhook Mercado Pago: Assinatura inválida. Recusando.');
            return response()->json(['message' => 'Assinatura inválida.'], 403);
        }

        $processed = $this->mercadoPagoService->handleWebhookNotification($notificationData);

        if ($processed) {
            return response()->json(['status' => 'success', 'message' => 'Webhook processado.'], 200);
        } else {
            Log::error('Controlador: Erro ao processar webhook do Mercado Pago. Service retornou false.', $notificationData);
            return response()->json(['status' => 'error', 'message' => 'Erro ao processar webhook.'], 500);
        }
    }

    /**
     * Verifica o status de um pagamento utilizando o ID do Mercado Pago.
     *
     * @param string $id_pagamento_mp O ID do pagamento fornecido pelo Mercado Pago.
     * @return \Illuminate\Http\JsonResponse
     */
    public function verificarStatusPagamentoMp(string $id_pagamento_mp): JsonResponse
    {
        try {
            // Log::info("Api\PagamentoController: Iniciando consulta de status para id_pagamento_mp: {$id_pagamento_mp}");

            $pagamento = PagamentoPendente::where('id_pagamento_mp', $id_pagamento_mp)->first();

            if (!$pagamento) {
                // Log::warning("Api\PagamentoController: Pagamento não encontrado para id_pagamento_mp: {$id_pagamento_mp}");
                return response()->json(['mensagem' => 'Pagamento não encontrado.'], 404);
            }
            
            // Opcional: Consultar diretamente o MP para atualizar o status local se necessário.
            // Esta lógica pode ser útil se o webhook falhar ou para ter uma confirmação mais imediata.
            // if (in_array($pagamento->status_pagamento_mp, ['pending', 'in_process', null])) {
            //     try {
            //         Log::info("Api\PagamentoController: Status local é {$pagamento->status_pagamento_mp}. Consultando MP para {$id_pagamento_mp}");
            //         $detalhesMP = $this->mercadoPagoService->getPaymentDetails($id_pagamento_mp);
            //         if (isset($detalhesMP['status']) && $detalhesMP['status'] !== $pagamento->status_pagamento_mp) {
            //             Log::info("Api\PagamentoController: Status do MP ({$detalhesMP['status']}) difere do local ({$pagamento->status_pagamento_mp}) para {$id_pagamento_mp}. Atualizando.");
            //             $pagamento->status_pagamento_mp = $detalhesMP['status'];
            //             $pagamento->dados_webhook_mp_json = array_merge($pagamento->dados_webhook_mp_json ?? [], $detalhesMP); // Atualiza com os dados mais recentes
            //             
            //             // Se o pagamento foi aprovado ('approved') e a inscrição ainda não foi efetivada, 
            //             // podemos tentar processá-lo aqui. Isso pode ser um fallback para webhooks.
            //             if ($detalhesMP['status'] === 'approved' && !$pagamento->inscricao_efetivada) {
            //                 Log::info("Api\PagamentoController: Pagamento {$id_pagamento_mp} aprovado no MP (via consulta direta), tentando processar aprovação.");
            //                 // O método processarPagamentoAprovado espera o objeto PagamentoPendente e os detalhes do pagamento.
            //                 // Precisamos garantir que a transação seja tratada corretamente, possivelmente movendo a lógica de transação para o serviço.
            //                 // Por simplicidade, chamaremos um método dedicado que lida com isso, ou adaptamos o existente.
            //                 // $this->mercadoPagoService->processarPagamentoAprovado($pagamento, $detalhesMP); // Pode precisar de ajustes para contexto transacional
            //                 // Atualizar `inscricao_efetivada` aqui se o processamento acima for síncrono e bem-sucedido.
            //             }
            //             $pagamento->save();
            //         }
            //     } catch (\Exception $mpError) {
            //         Log::error("Api\PagamentoController: Erro ao buscar detalhes do pagamento {$id_pagamento_mp} do MP: " . $mpError->getMessage());
            //         // Continua com o status local em caso de erro na consulta ao MP.
            //     }
            // }

            // Log::info("Api\PagamentoController: Retornando status para id_pagamento_mp: {$id_pagamento_mp}", [
            //     'uuid_interno' => $pagamento->uuid,
            //     'status_pagamento_mp' => $pagamento->status_pagamento_mp,
            //     'inscricao_efetivada' => $pagamento->inscricao_efetivada
            // ]);

            return response()->json([
                'uuid_interno' => $pagamento->uuid,
                'id_pagamento_mp' => $pagamento->id_pagamento_mp,
                'status_pagamento_mp' => $pagamento->status_pagamento_mp,
                'inscricao_efetivada' => $pagamento->inscricao_efetivada,
                'forma_pagamento_solicitada' => $pagamento->forma_pagamento_solicitada,
                'valor' => $pagamento->valor,
                'data_criacao_registro' => $pagamento->created_at ? $pagamento->created_at->toIso8601String() : null,
                'data_ultima_atualizacao_registro' => $pagamento->updated_at ? $pagamento->updated_at->toIso8601String() : null,
            ]);

        } catch (\Exception $e) {
            Log::error("Api\PagamentoController: Erro crítico ao verificar status do pagamento por id_pagamento_mp ({$id_pagamento_mp}): " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['mensagem' => 'Erro ao consultar status do pagamento.'], 500);
        }
    }

    // Aqui virão os métodos para processar Pix, e o webhook handler
}
