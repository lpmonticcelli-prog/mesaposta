<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pedido extends Model
{
    protected $table = 'pedidos';

    protected $guarded = ['id'];

    // Convertendo as Strings do banco em Datas oficiais
    protected $casts = [
        'data_locacao'    => 'date',
        'data_entrega'    => 'date',
        'data_evento'     => 'date',
        'data_devolucao'  => 'date',
        'assinatura_data' => 'datetime',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function itens(): HasMany
    {
        return $this->hasMany(PedidoItem::class, 'pedido_id');
    }

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