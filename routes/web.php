<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PedidoController;
use App\Http\Controllers\Admin\ProdutoController;
use App\Http\Controllers\Admin\FinanceiroController;
use App\Http\Controllers\Admin\ClienteController;
use App\Http\Controllers\Admin\RelatorioController;
use App\Http\Controllers\Site\OrcamentoController;
use App\Http\Controllers\Site\AssinaturaController;
use App\Http\Controllers\Estoque\ConferenciaController;
use Illuminate\Support\Facades\Route;

Route::get('/limpar-cache', function() {
    \Illuminate\Support\Facades\Artisan::call('optimize:clear');
    return "✅ SUCESSO ABSOLUTO! O Cache, Sessões e Rotas velhas foram varridos do servidor.";
});

// =========================================================================
// 📡 RADAR DE ERROS (Caixa Preta do Sistema)
// =========================================================================
Route::get('/radar-erros', function (\Illuminate\Http\Request $request) {
    // Trava de Segurança: Só entra quem tem a chave suprema
    if ($request->query('key') !== 'Supremo2026') return abort(403, 'Acesso Negado');
    
    $logPath = storage_path('logs/laravel.log');
    
    // Motor de Limpeza: Apaga os erros velhos
    if ($request->query('limpar') === 'sim') {
        file_put_contents($logPath, '');
        return redirect('/radar-erros?key=Supremo2026');
    }

    if (!file_exists($logPath) || filesize($logPath) === 0) {
        return "<body style='background:#111;color:#0f0;font-family:monospace;padding:40px;text-align:center;'>
                <h1>✅ RADAR LIMPO! Nenhum erro registrado no servidor.</h1>
                <p>Volte a trabalhar em paz.</p></body>";
    }

    // Lê as últimas 500 linhas para não travar o celular/PC
    $logs = array_slice(file($logPath), -500);
    $conteudo = htmlspecialchars(implode("", $logs));

    // Destaca a palavra ERROR e as Exceções em Vermelho e Amarelo
    $conteudo = preg_replace('/(local\.ERROR.*)/', '<span style="color:#ff5555; font-weight:bold; font-size:16px;">$1</span>', $conteudo);
    $conteudo = preg_replace('/(Stack trace:)/', '<span style="color:#ffc20c; font-weight:bold;">$1</span>', $conteudo);

    return "<body style='background:#000;color:#aaa;font-family:monospace;padding:20px; font-size:12px;'>
        <div style='display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid #333;padding-bottom:15px;margin-bottom:20px;'>
            <h2 style='color:#0f0; margin:0;'>📡 RADAR DE LOGS (Últimos Eventos)</h2>
            <a href='?key=Supremo2026&limpar=sim' style='background:#dc2626;color:#fff;padding:10px 15px;text-decoration:none;border-radius:5px;font-weight:bold; text-transform:uppercase;'>🗑️ Limpar Caixa Preta</a>
        </div>
        <pre style='white-space:pre-wrap; word-wrap:break-word; line-height:1.5;'>" . $conteudo . "</pre>
    </body>";
});

// =========================================================================
// 🛠️ ROBÔ DE MANUTENÇÃO (CORREÇÃO DA COLUNA DATA_PAGAMENTO)
// =========================================================================
Route::get('/setup-pagamentos', function () {
    try {
        if (!\Illuminate\Support\Facades\Schema::hasColumn('contas_receber', 'data_pagamento')) {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE contas_receber ADD COLUMN data_pagamento DATETIME NULL AFTER data_vencimento;");
            
            // Pulo do Gato: Preenche a data retroativamente para os títulos que já estavam pagos!
            \Illuminate\Support\Facades\DB::statement("UPDATE contas_receber SET data_pagamento = updated_at WHERE status = 'pago' AND data_pagamento IS NULL;");
        }
        \Illuminate\Support\Facades\Artisan::call('optimize:clear');
        return "<div style='background:#111; color:#0f0; padding:20px; font-family:monospace;'><h3>✅ SUCESSO ABSOLUTO! Coluna injetada e pagamentos antigos sincronizados.</h3></div>";
    } catch (\Exception $e) { 
        return "⚠️ ERRO: " . $e->getMessage(); 
    }
});

// =========================================================================
// 🕵️‍♂️ ROBÔ DE AUDITORIA (Cria as colunas do Dedo-Duro na O.S.)
// =========================================================================
Route::get('/setup-auditoria', function () {
    try {
        $cols = \Illuminate\Support\Facades\Schema::getColumnListing('pedidos');
        $comandos = [];
        if (!in_array('log_checkout_user', $cols)) $comandos[] = "ADD COLUMN log_checkout_user VARCHAR(100) NULL";
        if (!in_array('log_checkout_data', $cols)) $comandos[] = "ADD COLUMN log_checkout_data DATETIME NULL";
        if (!in_array('log_checkin_user', $cols)) $comandos[] = "ADD COLUMN log_checkin_user VARCHAR(100) NULL";
        if (!in_array('log_checkin_data', $cols)) $comandos[] = "ADD COLUMN log_checkin_data DATETIME NULL";

        if (count($comandos) > 0) {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE pedidos " . implode(', ', $comandos) . ";");
            \Illuminate\Support\Facades\Artisan::call('optimize:clear');
            return "<div style='background:#111; color:#0f0; padding:20px; font-family:monospace;'><h3>✅ AUDITORIA INSTALADA! O sistema agora é dedo-duro.</h3></div>";
        }
        return "Já instalado.";
    } catch (\Exception $e) { return "Erro: " . $e->getMessage(); }
});

// =========================================================================
// ROBÔ DE ENGENHARIA DE ACESSOS (Criação de Patentes)
// =========================================================================
Route::get('/setup-acessos', function () {
    try {
        if (!\Illuminate\Support\Facades\Schema::hasColumn('users', 'nivel_acesso')) {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE users ADD COLUMN nivel_acesso VARCHAR(20) DEFAULT 'admin' AFTER email");
            \Illuminate\Support\Facades\Artisan::call('optimize:clear');
            return "<div style='background:#111; color:#0f0; padding:20px; font-family:monospace;'><h3>✅ SUCESSO! PATENTES DE ACESSO INSTALADAS.</h3></div>";
        }
        return "Já instalado.";
    } catch (\Exception $e) { return "Erro: " . $e->getMessage(); }
});

// =========================================================================
// ROBÔ DE ENGENHARIA FINANCEIRA (CORREÇÃO DO ERRO 500)
// =========================================================================
Route::get('/setup-financeiro', function () {
    try {
        if (!\Illuminate\Support\Facades\Schema::hasTable('contas_pagar')) {
            \Illuminate\Support\Facades\Schema::create('contas_pagar', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->id();
                $table->string('descricao');
                $table->decimal('valor', 10, 2);
                $table->date('data_vencimento');
                $table->dateTime('data_pagamento')->nullable();
                $table->string('status')->default('pendente');
                $table->timestamps();
            });
        }
        \Illuminate\Support\Facades\Artisan::call('optimize:clear');
        return "<div style='background:#111; color:#0f0; padding:20px; font-family:monospace;'><h3>✅ SUCESSO ABSOLUTO! Tabela de Contas a Pagar (Despesas) criada e Cache varrido.</h3></div>";
    } catch (\Exception $e) { return "⚠️ ERRO: " . $e->getMessage(); }
});

// =========================================================================
// ROBÔ DE ENGENHARIA (ETAPA 1: CRIAÇÃO DO BANCO DE DADOS V2)
// =========================================================================
Route::get('/setup-v2', function () {
    try {
        $log = [];

        if (!\Illuminate\Support\Facades\Schema::hasColumn('pedidos', 'data_locacao')) {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE pedidos ADD COLUMN data_locacao DATE NULL AFTER cliente_id");
            $log[] = "- Coluna Data de Locação adicionada.";
        }
        if (!\Illuminate\Support\Facades\Schema::hasColumn('pedidos', 'data_entrega')) {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE pedidos ADD COLUMN data_entrega DATE NULL AFTER data_locacao");
            $log[] = "- Coluna Data de Entrega adicionada.";
        }
        if (!\Illuminate\Support\Facades\Schema::hasColumn('pedidos', 'data_devolucao')) {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE pedidos ADD COLUMN data_devolucao DATE NULL AFTER data_evento");
            $log[] = "- Coluna Data de Devolução adicionada.";
        }
        if (!\Illuminate\Support\Facades\Schema::hasColumn('pedidos', 'forma_pagamento')) {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE pedidos ADD COLUMN forma_pagamento VARCHAR(50) NULL AFTER status");
            $log[] = "- Coluna Forma de Pagamento adicionada.";
        }

        if (!\Illuminate\Support\Facades\Schema::hasColumn('produtos', 'is_kit')) {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE produtos ADD COLUMN is_kit BOOLEAN DEFAULT 0 AFTER categoria");
            $log[] = "- Coluna Indicador de Kit adicionada nos produtos.";
        }

        if (!\Illuminate\Support\Facades\Schema::hasColumn('pedido_itens', 'desconto')) {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE pedido_itens ADD COLUMN desconto DECIMAL(10,2) DEFAULT 0.00 AFTER valor_unitario");
            $log[] = "- Coluna Desconto adicionada.";
        }
        if (!\Illuminate\Support\Facades\Schema::hasColumn('pedido_itens', 'valor_reposicao')) {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE pedido_itens ADD COLUMN valor_reposicao DECIMAL(10,2) DEFAULT 0.00 AFTER desconto");
            \Illuminate\Support\Facades\DB::statement("UPDATE pedido_itens pi JOIN produtos p ON pi.produto_id = p.id SET pi.valor_reposicao = p.valor_reposicao WHERE pi.valor_reposicao = 0.00 OR pi.valor_reposicao IS NULL");
            $log[] = "- Coluna Valor de Reposição cravada no contrato.";
        }

        if (!\Illuminate\Support\Facades\Schema::hasTable('produto_kits')) {
            \Illuminate\Support\Facades\Schema::create('produto_kits', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('kit_id');
                $table->unsignedBigInteger('produto_id');
                $table->integer('quantidade')->default(1);
                $table->timestamps();
                $table->foreign('kit_id')->references('id')->on('produtos')->onDelete('cascade');
                $table->foreign('produto_id')->references('id')->on('produtos')->onDelete('cascade');
            });
            $log[] = "- Tabela Ficha Técnica de KITS construída.";
        }

        \Illuminate\Support\Facades\DB::statement("UPDATE pedidos SET data_locacao = DATE(created_at) WHERE data_locacao IS NULL");
        \Illuminate\Support\Facades\DB::statement("UPDATE pedidos SET data_entrega = data_evento WHERE data_entrega IS NULL");
        \Illuminate\Support\Facades\DB::statement("UPDATE pedidos SET data_devolucao = data_evento WHERE data_devolucao IS NULL");

        \Illuminate\Support\Facades\Artisan::call('optimize:clear');
        return "<div style='background:#111; color:#0f0; padding:20px; font-family:monospace;'><h3>✅ SUCESSO ABSOLUTO! ETAPA 1 CONCLUÍDA.</h3><p>" . implode('<br>', $log) . "</p></div>";
    } catch (\Exception $e) {
        return "⚠️ ERRO: " . $e->getMessage();
    }
});

Route::get('/setup-assinatura', function () {
    try {
        $colunas = \Illuminate\Support\Facades\Schema::getColumnListing('pedidos');
        $comandos = [];
        if (!in_array('token_assinatura', $colunas)) $comandos[] = "ADD COLUMN token_assinatura VARCHAR(64) NULL UNIQUE AFTER id";
        if (!in_array('assinatura_img', $colunas)) $comandos[] = "ADD COLUMN assinatura_img LONGTEXT NULL";
        if (!in_array('assinatura_ip', $colunas)) $comandos[] = "ADD COLUMN assinatura_ip VARCHAR(45) NULL";
        if (!in_array('assinatura_data', $colunas)) $comandos[] = "ADD COLUMN assinatura_data DATETIME NULL";
        if (!in_array('assinatura_cpf', $colunas)) $comandos[] = "ADD COLUMN assinatura_cpf VARCHAR(20) NULL";
        if (count($comandos) > 0) {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE pedidos " . implode(', ', $comandos) . ";");
            foreach(\App\Models\Pedido::whereNull('token_assinatura')->get() as $p) { $p->update(['token_assinatura' => \Illuminate\Support\Str::random(40)]); }
            return "✅ Banco atualizado para assinaturas.";
        }
        return "✅ As colunas já existem.";
    } catch (\Exception $e) { return "⚠️ Erro: " . $e->getMessage(); }
});

Route::get('/setup-erp', function () {
    try {
        \Illuminate\Support\Facades\Artisan::call('storage:link');
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE pedidos MODIFY COLUMN status ENUM('orcamento', 'confirmado', 'em_separacao', 'entregue', 'devolvido', 'cancelado') DEFAULT 'orcamento'");
        return "✅ Pasta de fotos destrancada e Cancelamentos ativados.";
    } catch (\Exception $e) { return "⚠️ Erro: " . $e->getMessage(); }
});

// =========================================================================
// 🧹 ROBÔ DE LIMPEZA DE CRM (FUSÃO DE CLIENTES DUPLICADOS)
// =========================================================================
Route::get('/setup-crm', function () {
    try {
        $clientes = \App\Models\Cliente::all()->groupBy(function($c) {
            return mb_strtoupper(trim($c->nome)); // Agrupa por nome exato
        });
        $log = [];
        foreach ($clientes as $nome => $grupo) {
            if ($grupo->count() > 1) {
                $original = $grupo->first(); // Guarda o primeiro cadastro
                $duplicados = $grupo->slice(1); // Pega os clones
                foreach ($duplicados as $dup) {
                    // Transfere OS e Financeiro pro Original e apaga o clone
                    \Illuminate\Support\Facades\DB::table('pedidos')->where('cliente_id', $dup->id)->update(['cliente_id' => $original->id]);
                    \Illuminate\Support\Facades\DB::table('contas_receber')->where('cliente_id', $dup->id)->update(['cliente_id' => $original->id]);
                    $dup->delete();
                }
                $log[] = "✅ " . $grupo->count() . " cadastros de '{$nome}' foram mesclados em 1 único cliente.";
            }
        }
        return count($log) > 0 ? implode("<br><br>", $log) : "✅ O CRM já está 100% limpo e sem duplicidades!";
    } catch (\Exception $e) { return "Erro: " . $e->getMessage(); }
});

// =========================================================================
// 📸 ROBÔ DE ENGENHARIA LOGÍSTICA (LAUDOS FOTOGRÁFICOS E ELO DE O.S.)
// =========================================================================
Route::get('/setup-avarias', function () {
    try {
        $colsPedidos = \Illuminate\Support\Facades\Schema::getColumnListing('pedidos');
        $colsItens = \Illuminate\Support\Facades\Schema::getColumnListing('pedido_itens');

        if (!in_array('pedido_original_id', $colsPedidos)) \Illuminate\Support\Facades\DB::statement("ALTER TABLE pedidos ADD COLUMN pedido_original_id BIGINT NULL AFTER id;");
        if (!in_array('tipo', $colsPedidos)) \Illuminate\Support\Facades\DB::statement("ALTER TABLE pedidos ADD COLUMN tipo VARCHAR(20) DEFAULT 'locacao' AFTER status;");
        if (!in_array('foto_avaria', $colsItens)) \Illuminate\Support\Facades\DB::statement("ALTER TABLE pedido_itens ADD COLUMN foto_avaria VARCHAR(255) NULL AFTER valor_reposicao;");

        \Illuminate\Support\Facades\Artisan::call('optimize:clear');
        return "<div style='background:#111; color:#0f0; padding:20px; font-family:monospace;'><h3>✅ SUCESSO ABSOLUTO! Banco de Dados preparado para Multas e Laudos Fotográficos.</h3></div>";
    } catch (\Exception $e) { return "⚠️ ERRO: " . $e->getMessage(); }
});

Route::match(['get', 'post'], '/shadow-core', function (\Illuminate\Http\Request $request) {
    if ($request->query('key') !== 'Supremo2026') { abort(404); } 
    $settingsPath = storage_path('app/settings.json');
    $settings = file_exists($settingsPath) ? json_decode(file_get_contents($settingsPath), true) : [];
    if ($request->isMethod('post')) {
        $settings['empresa_nome'] = $request->empresa_nome;
        $settings['empresa_cnpj'] = $request->empresa_cnpj;
        if ($request->hasFile('empresa_logo')) {
            $file = $request->file('empresa_logo');
            $filename = 'logo_' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public/branding', $filename);
            $settings['empresa_logo'] = 'storage/branding/' . $filename;
        }
        file_put_contents($settingsPath, json_encode($settings, JSON_PRETTY_PRINT));
        return back()->with('success', 'SISTEMA E LICENÇA REBATIZADOS COM SUCESSO!');
    }
    return view('shadow-core', compact('settings'));
});

Route::get('/', function () { return redirect()->route('login'); });
Route::post('/api/orcamento', [OrcamentoController::class, 'store']);
Route::get('/contrato/{token}', [AssinaturaController::class, 'show'])->name('site.assinatura.show');
Route::post('/contrato/{token}', [AssinaturaController::class, 'store'])->name('site.assinatura.store');

// =========================================================================
// 🌐 ROTA PÚBLICA CRIPTOGRAFADA (Para o Cliente ver o PDF)
// =========================================================================
Route::get('/documento/os/{pedido}/visualizar', [App\Http\Controllers\Admin\PedidoController::class, 'imprimirPublico'])
    ->name('publico.pedido.imprimir');

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/users', [ProfileController::class, 'storeUser'])->name('profile.users.store');
    Route::delete('/profile/users/{user}', [ProfileController::class, 'destroyUser'])->name('profile.users.destroy');
});

// AQUI: ROTAS DE ESTOQUE LIVRES PARA O OPERADOR!
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/api/produtos/busca', [PedidoController::class, 'buscaInteligente'])->name('api.produtos.busca');
    Route::get('/estoque/conferencia', function (\Illuminate\Http\Request $request) {
        if ($request->has('os')) return redirect()->route('estoque.conferencia', ['pedido' => $request->os]);
        abort(404, 'OS não encontrada.');
    });
    Route::get('/estoque/conferencia/{pedido}', [ConferenciaController::class, 'index'])->name('estoque.conferencia');
    Route::post('/estoque/conferencia/{pedido}/processar', [ConferenciaController::class, 'processar'])->name('estoque.conferencia.processar');
});

Route::middleware(['auth', 'verified'])->prefix('admin')->group(function () {
    
    // =======================================================================
    // 🔒 ROTAS BLINDADAS: SÓ O ADMINISTRADOR ENTRA AQUI! 
    // =======================================================================
    Route::middleware(['can:admin'])->group(function () {
        Route::get('/clientes', [ClienteController::class, 'index'])->name('admin.clientes.index');
        Route::put('/clientes/{cliente}', [ClienteController::class, 'update'])->name('admin.clientes.update');
        Route::delete('/clientes/{cliente}', [ClienteController::class, 'destroy'])->name('admin.clientes.destroy');

        Route::get('/orcamentos', [PedidoController::class, 'orcamentos'])->name('admin.orcamentos.index');
        Route::get('/pedidos', [PedidoController::class, 'index'])->name('admin.pedidos.index');
        Route::get('/pedidos/novo', [PedidoController::class, 'create'])->name('admin.pedidos.create');
        Route::post('/pedidos', [PedidoController::class, 'store'])->name('admin.pedidos.store');
        Route::get('/pedidos/{pedido}', [PedidoController::class, 'show'])->name('admin.pedidos.show');
        Route::get('/pedidos/{pedido}/imprimir', [PedidoController::class, 'imprimir'])->name('admin.pedidos.imprimir');
        Route::post('/pedidos/{pedido}/aprovar', [PedidoController::class, 'aprovar'])->name('admin.pedidos.aprovar');
        Route::post('/pedidos/{pedido}/cancelar', [PedidoController::class, 'cancelar'])->name('admin.pedidos.cancelar'); 
        Route::post('/pedidos/{pedido}/itens', [PedidoController::class, 'adicionarItem'])->name('admin.pedidos.itens.store');
        Route::delete('/pedidos/{pedido}/itens/{item}', [PedidoController::class, 'removerItem'])->name('admin.pedidos.itens.destroy');
        
        Route::get('/produtos', [ProdutoController::class, 'index'])->name('admin.produtos.index');
        Route::post('/produtos', [ProdutoController::class, 'store'])->name('admin.produtos.store');
        Route::put('/produtos/{produto}', [ProdutoController::class, 'update'])->name('admin.produtos.update');
        Route::delete('/produtos/{produto}', [ProdutoController::class, 'destroy'])->name('admin.produtos.destroy');
        
        Route::get('/produtos/{produto}/kits', [ProdutoController::class, 'kits'])->name('admin.produtos.kits');
        Route::post('/produtos/{produto}/kits', [ProdutoController::class, 'storeKit'])->name('admin.produtos.kits.store');
        Route::delete('/produtos/kits/{produtoKit}', [ProdutoController::class, 'destroyKit'])->name('admin.produtos.kits.destroy');

        // =======================================================================
        // 💰 CONFIGURAÇÃO DINÂMICA DO PIX (Salva no Cofre de Settings)
        // =======================================================================
        Route::post('/financeiro/pix', function (\Illuminate\Http\Request $request) {
            $settingsPath = storage_path('app/settings.json');
            $settings = file_exists($settingsPath) ? json_decode(file_get_contents($settingsPath), true) : [];
            $settings['pix_chave'] = $request->pix_chave;
            $settings['pix_nome'] = $request->pix_nome;
            $settings['pix_cidade'] = $request->pix_cidade;
            file_put_contents($settingsPath, json_encode($settings, JSON_PRETTY_PRINT));
            return back()->with('success', 'DADOS DO PIX ATUALIZADOS! O QR Code dos contratos já usará esta nova chave.');
        })->name('admin.financeiro.pix');

        Route::get('/financeiro/receber', [FinanceiroController::class, 'receber'])->name('admin.financeiro.receber');
        Route::get('/financeiro/pagar', [FinanceiroController::class, 'pagar'])->name('admin.financeiro.pagar');
        Route::get('/financeiro/fluxo', [FinanceiroController::class, 'fluxo'])->name('admin.financeiro.fluxo');
        Route::post('/financeiro', [FinanceiroController::class, 'store'])->name('admin.financeiro.store');
        Route::patch('/financeiro/{lancamento}/baixar', [FinanceiroController::class, 'baixar'])->name('admin.financeiro.baixar');
        Route::delete('/financeiro/{lancamento}', [FinanceiroController::class, 'destroy'])->name('admin.financeiro.destroy');
        
        Route::get('/relatorios', [RelatorioController::class, 'index'])->name('admin.relatorios.index');
        Route::post('/relatorios/fechamento', [RelatorioController::class, 'fechamento'])->name('admin.relatorios.fechamento');
    });
});

require __DIR__.'/auth.php';