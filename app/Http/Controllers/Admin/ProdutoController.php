<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Produto;
use Illuminate\Http\Request;

class ProdutoController extends Controller
{
    public function index()
    {
        $produtos = Produto::orderBy('nome')->paginate(15);
        return view('admin.produtos.index', compact('produtos'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome'               => 'required|string|max:255',
            'categoria'          => 'nullable|string|max:100',
            'quantidade_estoque' => 'required|integer|min:0',
            'valor_locacao'      => 'required|numeric|min:0',
            'valor_reposicao'    => 'required|numeric|min:0', // <-- BLINDAGEM: O novo preço real
        ]);

        Produto::create($validated);

        return back()->with('success', 'Peça cadastrada no acervo com sucesso!');
    }

    public function update(Request $request, Produto $produto)
    {
        $validated = $request->validate([
            'nome'               => 'required|string|max:255',
            'categoria'          => 'nullable|string|max:100',
            'quantidade_estoque' => 'required|integer|min:0',
            'valor_locacao'      => 'required|numeric|min:0',
            'valor_reposicao'    => 'required|numeric|min:0', // <-- BLINDAGEM: O novo preço real
        ]);

        $produto->update($validated);

        return back()->with('success', 'Dados da peça atualizados com sucesso!');
    }

    public function destroy(Produto $produto)
    {
        try {
            $produto->delete();
            return back()->with('success', 'Peça removida do catálogo.');
        } catch (\Illuminate\Database\QueryException $e) {
            // TRAVA DE SEGURANÇA: Se a peça já foi alugada alguma vez, o banco de dados proíbe a exclusão para não quebrar o histórico financeiro!
            return back()->with('error', 'Segurança: Não é possível apagar esta peça pois ela já pertence a um Histórico de Orçamento ou OS.');
        }
    }
}