<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContaReceber;
use App\Models\ContaPagar;
use App\Models\Pedido;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RelatorioController extends Controller
{
    public function index()
    {
        $clientes = Cliente::orderBy('nome')->get();
        return view('admin.relatorios.index', compact('clientes'));
    }

    public function fechamento(Request $request)
    {
        // 🛡️ INÍCIO DO ESCUDO INTERCEPTADOR
        try {
            $request->validate([
                'data_inicio' => 'required|date', 
                'data_fim' => 'required|date|after_or_equal:data_inicio', 
                'cliente_id' => 'nullable|exists:clientes,id'
            ]);

            $dataInicio = $request->data_inicio;
            $dataFim = $request->data_fim;
            $clienteId = $request->cliente_id;

            $periodoFormatado = Carbon::parse($dataInicio)->format('d/m/Y') . ' a ' . Carbon::parse($dataFim)->format('d/m/Y');
            $clienteNome = $clienteId ? Cliente::find($clienteId)->nome : 'Todos os Clientes (Visão Global)';

            $queryReceitas = ContaReceber::with('pedido.cliente')->where('status', 'pago')->whereBetween('data_pagamento', [$dataInicio, $dataFim]);
            $queryDespesas = ContaPagar::where('status', 'pago')->whereBetween('data_pagamento', [$dataInicio, $dataFim]);
            
            $queryReceitasPendentes = ContaReceber::whereIn('status', ['pendente', 'atrasado'])->whereBetween('data_vencimento', [$dataInicio, $dataFim]);
            $queryDespesasPendentes = ContaPagar::whereIn('status', ['pendente', 'atrasado'])->whereBetween('data_vencimento', [$dataInicio, $dataFim]);

            if ($clienteId) {
                $queryReceitas->whereHas('pedido', function($q) use ($clienteId) { $q->where('cliente_id', $clienteId); });
                $queryReceitasPendentes->whereHas('pedido', function($q) use ($clienteId) { $q->where('cliente_id', $clienteId); });
                $queryDespesas->whereRaw('1 = 0');
                $queryDespesasPendentes->whereRaw('1 = 0');
            }

            $brutoReceitas = $queryReceitas->get();
            $brutoDespesas = $queryDespesas->get();

            $categoriasReceita = method_exists(ContaReceber::class, 'categorias') ? ContaReceber::categorias() : [
                'locacao' => 'Locação', 'multa_avaria' => 'Multas/Avarias', 'frete' => 'Frete/Logística', 'venda' => 'Venda', 'outros' => 'Outros'
            ];
            
            $categoriasDespesa = method_exists(ContaPagar::class, 'categorias') ? ContaPagar::categorias() : [
                'impostos' => 'Impostos', 'fornecedores' => 'Fornecedores', 'lavanderia' => 'Lavanderia', 'infraestrutura' => 'Infraestrutura', 'folha' => 'Folha', 'frete' => 'Frete', 'marketing' => 'Marketing', 'outros' => 'Outros'
            ];

            $listaReceitas = $brutoReceitas->map(function($item) {
                $desc = mb_strtoupper($item->descricao ?: '');
                if (str_contains($desc, 'MULTA') || str_contains($desc, 'AVARIA') || str_contains($desc, 'QUEBRA')) $item->categoria_chave = 'multa_avaria';
                elseif (str_contains($desc, 'FRETE') || str_contains($desc, 'ENTREGA')) $item->categoria_chave = 'frete';
                elseif (str_contains($desc, 'VENDA')) $item->categoria_chave = 'venda';
                else $item->categoria_chave = 'locacao';
                return $item;
            });

            $listaDespesas = $brutoDespesas->map(function($item) {
                $desc = mb_strtoupper($item->descricao ?: '');
                if (str_contains($desc, 'IMPOSTO') || str_contains($desc, 'TAXA') || str_contains($desc, 'DAS')) $item->categoria_chave = 'impostos';
                elseif (str_contains($desc, 'LAVANDERIA') || str_contains($desc, 'LIMPEZA')) $item->categoria_chave = 'lavanderia';
                elseif (str_contains($desc, 'ALUGUEL') || str_contains($desc, 'AGUA') || str_contains($desc, 'LUZ')) $item->categoria_chave = 'infraestrutura';
                elseif (str_contains($desc, 'SALARIO') || str_contains($desc, 'FOLHA')) $item->categoria_chave = 'folha';
                elseif (str_contains($desc, 'COMBUSTIVEL') || str_contains($desc, 'GASOLINA') || str_contains($desc, 'FRETE')) $item->categoria_chave = 'frete';
                elseif (str_contains($desc, 'MARKETING') || str_contains($desc, 'ANUNCIO')) $item->categoria_chave = 'marketing';
                elseif (str_contains($desc, 'COMPRA') || str_contains($desc, 'FORNECEDOR')) $item->categoria_chave = 'fornecedores';
                else $item->categoria_chave = 'outros';
                return $item;
            });

            $receitasPorCategoria = [];
            foreach ($categoriasReceita as $chave => $nome) {
                $receitasPorCategoria[$chave] = ['nome' => $nome, 'total' => $listaReceitas->where('categoria_chave', $chave)->sum('valor')];
            }

            $despesasPorCategoria = [];
            foreach ($categoriasDespesa as $chave => $nome) {
                $despesasPorCategoria[$chave] = ['nome' => $nome, 'total' => $listaDespesas->where('categoria_chave', $chave)->sum('valor')];
            }

            $receitasPagas = $listaReceitas->sum('valor') ?? 0;
            $despesasPagas = $listaDespesas->sum('valor') ?? 0;
            $lucroLiquido = $receitasPagas - $despesasPagas;

            $receitasPendentes = $queryReceitasPendentes->sum('valor') ?? 0;
            $despesasPendentes = $queryDespesasPendentes->sum('valor') ?? 0;

            $queryPedidos = Pedido::where('status', '!=', 'orcamento')->whereBetween('data_evento', [$dataInicio, $dataFim]);
            if ($clienteId) $queryPedidos->where('cliente_id', $clienteId);

            $totalOsConfirmadas = $queryPedidos->count();
            $valorOsConfirmadas = $queryPedidos->sum('valor_total') ?? 0;

            return view('admin.relatorios.fechamento_print', compact(
                'periodoFormatado', 'clienteNome', 'receitasPagas', 'despesasPagas', 'lucroLiquido', 
                'listaReceitas', 'listaDespesas', 'receitasPendentes', 'despesasPendentes', 
                'totalOsConfirmadas', 'valorOsConfirmadas', 'categoriasReceita', 'categoriasDespesa', 
                'receitasPorCategoria', 'despesasPorCategoria'
            ));

        } catch (\Throwable $e) {
            // 🛡️ SE OCORRER QUALQUER ERRO, A TELA PRETA MOSTRARÁ A RAIZ DO PROBLEMA
            return response("<div style='padding: 30px; background: #111; color: #ff5555; font-family: monospace; font-size: 16px; border-radius: 8px; margin: 20px;'>
                <h2 style='color: #ff3333;'>🚨 ERRO CRÍTICO INTERCEPTADO</h2>
                <p>O servidor travou, mas nosso escudo interceptou o problema. Copie os dados abaixo e mande para o Arquiteto:</p>
                <hr style='border-color: #333; margin: 20px 0;'>
                <p><strong>ERRO:</strong> " . $e->getMessage() . "</p>
                <p><strong>ARQUIVO:</strong> " . $e->getFile() . "</p>
                <p><strong>LINHA:</strong> " . $e->getLine() . "</p>
            </div>", 500);
        }
    }
}