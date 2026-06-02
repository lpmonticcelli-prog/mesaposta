<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PedidoController;
use App\Http\Controllers\Admin\ProdutoController;
use App\Http\Controllers\Admin\FinanceiroController;
use App\Http\Controllers\Admin\ClienteController;
use App\Http\Controllers\Site\OrcamentoController;
use App\Http\Controllers\Estoque\ConferenciaController;
use Illuminate\Support\Facades\Route;

// ========================================================================
// VITRINE PÚBLICA E AUTENTICAÇÃO
// ========================================================================
Route::get('/', function () {
    return view('welcome');
});

Route::post('/api/orcamento', [OrcamentoController::class, 'store']);

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ========================================================================
// APP DO GALPÃO (CHECK-IN VIA QR CODE E AVARIAS)
// ========================================================================
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/estoque/conferencia', [ConferenciaController::class, 'index'])->name('estoque.conferencia');
    // -> ROTA INJETADA: Processa o botão de "Tudo OK" ou as fotos das "Avarias"
    Route::post('/estoque/conferencia/{pedido}/processar', [ConferenciaController::class, 'processar'])->name('estoque.conferencia.processar');
});

// ========================================================================
// ERP INTERNO (MÓDULOS DE OPERAÇÃO E FINANCEIRO)
// ========================================================================
Route::middleware(['auth', 'verified'])->prefix('admin')->group(function () {
    
    // --- CARTEIRA DE CLIENTES (CRM) ---
    Route::get('/clientes', [ClienteController::class, 'index'])->name('admin.clientes.index');

    // --- MÓDULO 1: COMERCIAL E LOGÍSTICA ---
    Route::get('/orcamentos', [PedidoController::class, 'orcamentos'])->name('admin.orcamentos.index');
    Route::get('/pedidos', [PedidoController::class, 'index'])->name('admin.pedidos.index');
    
    // --> Criação de Pedido Manual (Balcão)
    Route::get('/pedidos/novo', [PedidoController::class, 'create'])->name('admin.pedidos.create');
    Route::post('/pedidos', [PedidoController::class, 'store'])->name('admin.pedidos.store');
    
    Route::get('/pedidos/{pedido}', [PedidoController::class, 'show'])->name('admin.pedidos.show');
    
    // ---> AS DUAS ROTAS SEPARADAS (O SEGREDO DA PERFORMANCE) <---
    Route::get('/pedidos/{pedido}/imprimir', [PedidoController::class, 'imprimir'])->name('admin.pedidos.imprimir');
    Route::post('/pedidos/{pedido}/aprovar', [PedidoController::class, 'aprovar'])->name('admin.pedidos.aprovar');
    
    Route::post('/pedidos/{pedido}/itens', [PedidoController::class, 'adicionarItem'])->name('admin.pedidos.itens.store');
    Route::delete('/pedidos/{pedido}/itens/{item}', [PedidoController::class, 'removerItem'])->name('admin.pedidos.itens.destroy');
    
    // --- MÓDULO 2: O COFRE (FINANCEIRO) ---
    Route::get('/financeiro/receber', [FinanceiroController::class, 'receber'])->name('admin.financeiro.receber');
    Route::get('/financeiro/pagar', [FinanceiroController::class, 'pagar'])->name('admin.financeiro.pagar');
    Route::get('/financeiro/fluxo', [FinanceiroController::class, 'fluxo'])->name('admin.financeiro.fluxo');
    
    // --- MÓDULO 3: ACERVO FÍSICO ---
    Route::get('/produtos', [ProdutoController::class, 'index'])->name('admin.produtos.index');
    Route::post('/produtos', [ProdutoController::class, 'store'])->name('admin.produtos.store');
    Route::put('/produtos/{produto}', [ProdutoController::class, 'update'])->name('admin.produtos.update');
    Route::delete('/produtos/{produto}', [ProdutoController::class, 'destroy'])->name('admin.produtos.destroy');
    
});

require __DIR__.'/auth.php';