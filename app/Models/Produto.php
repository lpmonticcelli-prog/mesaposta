<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Produto extends Model
{
    /**
     * Campos permitidos para inserção em massa (Mass Assignment Protection)
     */
    protected $fillable = [
        'nome', 
        'categoria', 
        'quantidade_estoque', 
        'valor_locacao', 
        'valor_reposicao'
    ];

    /**
     * RELAÇÃO: Um Produto possui vários Itens em Pedidos/OS
     */
    public function itens()
    {
        return $this->hasMany(PedidoItem::class);
    }

    /**
     * O MOTOR LOGÍSTICO ANTI-FURO (Cálculo Temporal sem Baixa de Estoque)
     * Delegado 100% ao MySQL (Database Layer) para prevenir estouro de RAM no cPanel (OOM).
     *
     * @param string|Carbon $dataInicio
     * @param string|Carbon $dataFim
     * @return int Quantidade livre no galpão para o período
     */
    public function estoqueLivreNoPeriodo($dataInicio, $dataFim): int
    {
        // Sanitização e normalização das datas recebidas
        $inicio = Carbon::parse($dataInicio)->startOfDay();
        $fim    = Carbon::parse($dataFim)->endOfDay();

        // Delega a soma da colisão temporal diretamente ao MySQL (Consumo RAM PHP = 0 bytes)
        $quantidadeOcupada = PedidoItem::where('produto_id', $this->id)
            ->whereHas('pedido', function ($query) use ($inicio, $fim) {
                // Apenas status que "trancam" o material no calendário físico
                $query->whereIn('status', ['confirmado', 'em_separacao', 'entregue'])
                      ->where('tipo', 'locacao') // <--- BLINDAGEM: Ignora "OS de Cobrança de Avaria" para não abater 2 vezes!
                      ->where(function ($q) use ($inicio, $fim) {
                          // Matemática Temporal de Colisão (Overlap Logístico)
                          // Lógica: Início do Pedido <= Fim Desejado E Fim do Pedido >= Início Desejado
                          $q->whereRaw("COALESCE(data_entrega, data_evento) <= ?", [$fim])
                            ->whereRaw("COALESCE(data_devolucao, data_evento) >= ?", [$inicio]);
                      });
            })
            ->sum('quantidade_pedida');

        // Calcula a sobra garantindo que nunca retorne valor negativo em caso de falha de concorrência
        $estoqueLivre = $this->quantidade_estoque - $quantidadeOcupada;

        return max(0, (int) $estoqueLivre);
    }
}