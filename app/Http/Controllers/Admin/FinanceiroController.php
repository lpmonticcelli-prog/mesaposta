<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContaReceber;
use App\Models\ContaPagar;
use Illuminate\Http\Request;

class FinanceiroController extends Controller
{
    public function fluxo(Request $request)
    {
        $receitasQuery = ContaReceber::with('pedido.cliente');
        $despesasQuery = ContaPagar::query();
        
        if ($request->filled('busca')) {
            $b = $request->busca;
            $receitasQuery->where(function($q) use ($b) {
                $q->where('descricao', 'like', "%{$b}%")
                  ->orWhereHas('pedido.cliente', fn($c) => $c->where('nome', 'like', "%{$b}%"));
            });
            $despesasQuery->where('descricao', 'like', "%{$b}%");
        }
        
        if ($request->filled('status')) {
            $receitasQuery->where('status', $request->status);
            $despesasQuery->where('status', $request->status);
        }
        if ($request->filled('data_inicio') && $request->filled('data_fim')) {
            $receitasQuery->whereBetween('data_vencimento', [$request->data_inicio, $request->data_fim]);
            $despesasQuery->whereBetween('data_vencimento', [$request->data_inicio, $request->data_fim]);
        }

        $receitas = $receitasQuery->orderBy('data_vencimento', 'asc')->get();
        $despesas = $despesasQuery->orderBy('data_vencimento', 'asc')->get();

        return view('admin.financeiro.fluxo', compact('receitas', 'despesas'));
    }

    public function receber(Request $request)
    {
        $query = ContaReceber::with('pedido.cliente');

        // =========================================================================
        // 📊 MOTOR DE FILTRO AVANÇADO (Inadimplência de Avarias)
        // =========================================================================
        if ($request->filtro === 'avarias') {
            // Força a busca por Multas Pendentes
            $query->where('descricao', 'like', '%Multa de Avarias%')
                  ->where('status', 'pendente');
        } elseif ($request->filtro === 'locacoes') {
            // Oculta as multas e mostra apenas as locações normais
            $query->where('descricao', 'not like', '%Multa de Avarias%');
        }

        if ($request->filled('busca')) {
            $b = $request->busca;
            $query->where(function($q) use ($b) {
                $q->where('descricao', 'like', "%{$b}%")
                  ->orWhereHas('pedido.cliente', fn($c) => $c->where('nome', 'like', "%{$b}%"));
            });
        }
        
        // Só aplica o filtro de status normal se NÃO estiver na aba de avarias
        if ($request->filled('status') && $request->filtro !== 'avarias') {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('data_inicio')) $query->whereDate('data_vencimento', '>=', $request->data_inicio);
        if ($request->filled('data_fim')) $query->whereDate('data_vencimento', '<=', $request->data_fim);

        $lancamentos = $query->orderBy('data_vencimento', 'asc')->paginate(20)->withQueryString();
        return view('admin.financeiro.receber', compact('lancamentos'));
    }

    public function pagar(Request $request)
    {
        $query = ContaPagar::query();
        if ($request->filled('busca')) $query->where('descricao', 'like', "%{$request->busca}%");
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('data_inicio')) $query->whereDate('data_vencimento', '>=', $request->data_inicio);
        if ($request->filled('data_fim')) $query->whereDate('data_vencimento', '<=', $request->data_fim);

        $lancamentos = $query->orderBy('data_vencimento', 'asc')->paginate(20)->withQueryString();
        return view('admin.financeiro.pagar', compact('lancamentos'));
    }

    public function store(Request $request)
    {
        $request->validate(['tipo' => 'required', 'descricao' => 'required', 'valor' => 'required|numeric', 'data_vencimento' => 'required|date']);
        if ($request->tipo === 'receber') ContaReceber::create($request->except('tipo'));
        else ContaPagar::create($request->except('tipo'));
        return back()->with('success', 'Lançamento financeiro adicionado!');
    }

    public function baixar(Request $request, $id)
    {
        if ($request->tipo === 'receber') ContaReceber::findOrFail($id)->update(['status' => 'pago', 'data_pagamento' => now()]);
        else ContaPagar::findOrFail($id)->update(['status' => 'pago', 'data_pagamento' => now()]);
        return back()->with('success', 'Título liquidado com sucesso!');
    }

    public function destroy(Request $request, $id)
    {
        if ($request->tipo === 'receber') ContaReceber::findOrFail($id)->delete();
        else ContaPagar::findOrFail($id)->delete();
        return back()->with('success', 'Lançamento estornado e excluído.');
    }
}