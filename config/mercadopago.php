<?php

return [
    'public_key' => env('MERCADOPAGO_PUBLIC_KEY'),
    'access_token' => env('MERCADOPAGO_ACCESS_TOKEN'),
    'webhook_secret' => env('MERCADOPAGO_WEBHOOK_SECRET'), // Para validação de webhooks
    'urls' => [
        'base' => 'https://api.mercadopago.com',
        'payments' => '/v1/payments',
        'payment_methods' => '/v1/payment_methods',
        // Adicionar outros endpoints conforme necessário
    ],
    'notification_url' => env('MERCADOPAGO_NOTIFICATION_URL', env('APP_URL') . '/api/webhooks/mercadopago'),
]; 