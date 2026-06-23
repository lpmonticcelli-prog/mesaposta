<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PedidoItem extends Model
{
    protected $table = 'pedido_itens';

    protected $guarded = ['id'];

    // Força o sistema a tratar finanças e descontos como moeda (casas decimais)
    protected $casts = [
        'desconto' => 'decimal:2',
        'valor_unitario' => 'decimal:2',
        'valor_reposicao' => 'decimal:2',
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class, 'produto_id')->withDefault([
            'nome' => 'Peça Indisponível/Excluída',
            'valor_reposicao' => 0
        ]);
    }

    // A MATEMÁTICA FINANCEIRA: Subtotal = (Qtd x Valor) - Desconto
    public function getSubtotalAttribute()
    {
        $bruto = $this->quantidade_pedida * $this->valor_unitario;
        $desconto = $this->desconto ?? 0;
        return max(0, $bruto - $desconto); // Impede subtotal negativo
    }
}