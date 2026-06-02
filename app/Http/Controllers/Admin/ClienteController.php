<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    /**
     * Exibe a listagem completa da Carteira de Clientes
     */
    public function index()
    {
        // Puxa todos os clientes ordenados pelo nome e separa de 20 em 20 por página
        $clientes = Cliente::orderBy('nome', 'asc')->paginate(20);
        
        return view('admin.clientes.index', compact('clientes'));
    }
}