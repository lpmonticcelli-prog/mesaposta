<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $query = Cliente::query();

        if ($request->filled('busca')) {
            $b = $request->busca;
            $query->where(function($q) use ($b) {
                $q->where('nome', 'like', "%{$b}%")->orWhere('cpf_cnpj', 'like', "%{$b}%")
                  ->orWhere('telefone', 'like', "%{$b}%")->orWhere('email', 'like', "%{$b}%");
            });
        }

        $clientes = $query->orderBy('nome')->paginate(20)->withQueryString();
        return view('admin.clientes.index', compact('clientes'));
    }

    public function store(Request $request)
    {
        $dados = $request->all();
        $dados['cpf_cnpj'] = preg_replace('/[^0-9]/', '', $dados['cpf_cnpj'] ?? '');
        Cliente::create($dados);
        return back()->with('success', 'Cliente registrado!');
    }

    public function update(Request $request, Cliente $cliente)
    {
        $dados = $request->all();
        $dados['cpf_cnpj'] = preg_replace('/[^0-9]/', '', $dados['cpf_cnpj'] ?? '');
        $cliente->update($dados);
        return back()->with('success', 'Ficha atualizada com sucesso!');
    }

    public function destroy(Cliente $cliente)
    {
        if ($cliente->pedidos()->exists() || $cliente->contasReceber()->exists()) {
            return back()->with('error', 'PROIBIDO: Cliente possui contratos ou boletos associados no histórico.');
        }
        $cliente->delete();
        return back()->with('success', 'Cliente expurgado.');
    }
}