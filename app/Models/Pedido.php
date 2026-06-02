<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pedido extends Model
{
    protected $table = 'pedidos';

    protected $fillable = [
        'pedido_original_id', // <-- CORDÃO UMBILICAL
        'cliente_id', 
        'status', 
        'tipo',               // <-- LOCAÇÃO OU COBRANÇA
        'data_evento', 
        'valor_total', 
        'observacoes',
        'cep_entrega', 
        'endereco_entrega', 
        'numero_entrega', 
        'complemento_entrega',
        'bairro_entrega', 
        'cidade_entrega', 
        'estado_entrega'
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function itens(): HasMany
    {
        return $this->hasMany(PedidoItem::class, 'pedido_id');
    }

    // --- RELACIONAMENTOS DE LOGÍSTICA REVERSA E FINANCEIRO ---

    public function pedidoOriginal(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_original_id');
    }

    public function osCobranca(): HasOne
    {
        return $this->hasOne(Pedido::class, 'pedido_original_id');
    }

    public function financeiro(): HasMany
    {
        return $this->hasMany(ContaReceber::class, 'pedido_id');
    }
}