<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PedidoItem extends Model
{
    protected $table = 'pedido_itens';

    protected $fillable = [
        'pedido_id',
        'produto_id',
        'quantidade_pedida',
        'valor_unitario',
        'foto_avaria', // <-- AQUI: Permissão para salvar a foto da quebra ativada!
    ];

    /**
     * RELACIONAMENTO PAI: A qual OS este item pertence?
     */
    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    /**
     * RELACIONAMENTO MÃE: Qual é a peça original do acervo?
     */
    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class, 'produto_id');
    }
}