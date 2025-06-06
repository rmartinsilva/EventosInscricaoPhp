<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/run-verificar-pagamentos', function () {
    // Adicione autenticação/autorização aqui para proteger este endpoint!
    // Exemplo: if (auth()->user()->isAdmin()) { ... }

    try {
        Artisan::call('mercadopago:verificar-pagamentos');
        $output = Artisan::output();
        return response()->json(['status' => 'success', 'output' => $output]);
    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
});
