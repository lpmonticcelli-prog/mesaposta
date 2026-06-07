<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Site\OrcamentoController;
// use App\Http\Controllers\Api\ChecklistController;

/*
|--------------------------------------------------------------------------
| API ROUTES - MESA POSTA LOCAÇÕES (Ambiente cPanel/HostGator)
|--------------------------------------------------------------------------
| Todas as rotas públicas ou de PWA devem operar com a filosofia Zero Trust.
| O uso do middleware de Rate Limiting (throttle) é OBRIGATÓRIO para evitar 
| a exaustão de conexões no Apache e a consequente queda do servidor (Erro 508).
|--------------------------------------------------------------------------
*/

// Rota de autenticação base do Laravel Sanctum (Criada nativamente na instalação da API)
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// ========================================================================
// 1. CAMADA PÚBLICA (VITRINE DO SITE)
// ========================================================================
// Protegida pela trava 'orcamento-site' configurada no AppServiceProvider
// Limite: 3 requisições por minuto por IP. Se atingir, retorna 429 Too Many Requests silencioso.
Route::post('/orcamento', [OrcamentoController::class, 'store'])
    ->middleware('throttle:orcamento-site');


// ========================================================================
// 2. CAMADA INTERNA (PWA OFFLINE DO ESTOQUE)
// ========================================================================
// Recebe a foto esmagada (Base64) e os dados da avaria enviados pelo celular do galpão.
// Limite Fixo: 30 envios por minuto. Protege a CPU contra celulares com loop de rede.
// Route::post('/estoque/avaria', [ChecklistController::class, 'registrarAvaria'])
//     ->middleware('throttle:30,1');