<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Produto;
use App\Models\ProdutoKit;
use Illuminate\Http\Request;

class ProdutoController extends Controller
{
    public function index(Request $request)
    {
        $query = Produto::query();

        if ($request->filled('busca')) {
            $b = $request->busca;
            $query->where(function($q) use ($b) {
                $q->where('nome', 'like', "%{$b}%")->orWhere('categoria', 'like', "%{$b}%");
            });
        }
        
        if ($request->filled('tipo')) {
            if ($request->tipo === 'kit') $query->where('is_kit', true);
            if ($request->tipo === 'avulso') $query->where('is_kit', false);
        }

        $produtos = $query->orderBy('nome')->paginate(30)->withQueryString();
        return view('admin.produtos.index', compact('produtos'));
    }

    public function store(Request $request)
    {
        try {
            $dados = $request->all();
            
            // PROTEÇÃO ANTI-ERRO 500: Se vier vazio, força o número zero
            $dados['quantidade_estoque'] = $dados['quantidade_estoque'] ?: 0;
            $dados['valor_locacao'] = $dados['valor_locacao'] ?: 0;
            $dados['valor_reposicao'] = $dados['valor_reposicao'] ?: 0;

            Produto::create($dados);
            return back()->with('success', 'Material cadastrado no Acervo.');
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao salvar produto: ' . $e->getMessage());
        }
    }

    public function update(Request $request, Produto $produto)
    {
        try {
            $dados = $request->all();
            
            // PROTEÇÃO ANTI-ERRO 500
            $dados['quantidade_estoque'] = $dados['quantidade_estoque'] ?: 0;
            $dados['valor_locacao'] = $dados['valor_locacao'] ?: 0;
            $dados['valor_reposicao'] = $dados['valor_reposicao'] ?: 0;

            $produto->update($dados);
            return back()->with('success', 'Ficha atualizada.');
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao atualizar produto: ' . $e->getMessage());
        }
    }

    public function destroy(Produto $produto)
    {
        if ($produto->itensPedido()->exists() || $produto->presenteEmKits()->exists()) {
            return back()->with('error', 'Bloqueado: Esta peça já possui histórico de locação ou faz parte da ficha técnica de um KIT.');
        }
        $produto->delete();
        return back()->with('success', 'Peça excluída do Acervo.');
    }

    // =========================================================================
    // GESTÃO DA FICHA TÉCNICA DE KITS
    // =========================================================================
    public function kits(Produto $produto)
    {
        if (!$produto->is_kit) {
            return redirect()->route('admin.produtos.index')->with('error', 'Bloqueio: Esta peça não é um Conjunto/KIT.');
        }

        $componentes = ProdutoKit::with('produtoAvulso')->where('kit_id', $produto->id)->get();
        $avulsos = Produto::where('is_kit', false)->orderBy('nome')->get();

        return view('admin.produtos.kits', compact('produto', 'componentes', 'avulsos'));
    }

    public function storeKit(Request $request, Produto $produto)
    {
        try {
            ProdutoKit::create([
                'kit_id' => $produto->id,
                'produto_id' => $request->produto_id,
                'quantidade' => $request->quantidade ?: 1
            ]);
            return back()->with('success', 'Item vinculado ao Conjunto!');
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao vincular ficha: ' . $e->getMessage());
        }
    }

    public function destroyKit($id)
    {
        ProdutoKit::findOrFail($id)->delete();
        return back()->with('success', 'Item desvinculado da ficha técnica.');
    }
}