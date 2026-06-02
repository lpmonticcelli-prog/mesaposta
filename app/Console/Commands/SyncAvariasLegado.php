<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pedido;
use App\Models\PedidoItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncAvariasLegado extends Command
{
    protected $signature = 'erp:sync-avarias-legado';
    protected $description = 'Resgata fotos de avarias órfãs e gera OS de Cobrança para revisão manual do valor';

    public function handle()
    {
        $this->info('Iniciando varredura das Avarias antigas na estrutura real do banco...');
        
        // Teto de memória para garantir estabilidade no ambiente local e na HostGator
        ini_set('memory_limit', '256M');

        // Lendo diretamente pelo Query Builder para ignorar os Models quebrados do sistema antigo
        $avariasAntigas = DB::table('avarias')->get();

        if ($avariasAntigas->isEmpty()) {
            $this->info('Nenhuma avaria antiga encontrada. Seu banco está limpo!');
            return;
        }

        $avariasProcessadas = 0;

        foreach ($avariasAntigas as $avaria) {
            DB::beginTransaction();
            try {
                $pedidoOriginal = Pedido::find($avaria->pedido_id);
                
                // Se a OS original nem existir mais, limpa o lixo do banco
                if (!$pedidoOriginal) {
                    DB::table('avarias')->where('id', $avaria->id)->delete();
                    DB::commit();
                    continue;
                }

                // Verifica se já criamos uma OS de cobrança para não duplicar
                $jaExiste = Pedido::where('pedido_original_id', $pedidoOriginal->id)
                                  ->where('tipo', 'cobranca')
                                  ->exists();

                if ($jaExiste) {
                    DB::table('avarias')->where('id', $avaria->id)->delete();
                    DB::commit();
                    continue;
                }

                // 1. CRIA A OS DE COBRANÇA EM MODO "ORÇAMENTO"
                // O valor ficará zerado até o administrador olhar a foto e precificar
                $novoPedidoVenda = Pedido::create([
                    'pedido_original_id' => $pedidoOriginal->id,
                    'cliente_id'         => $pedidoOriginal->cliente_id,
                    'status'             => 'orcamento', // <--- Requer intervenção humana
                    'tipo'               => 'cobranca',
                    'data_evento'        => now()->toDateString(), 
                    'valor_total'        => 0,
                    'observacoes'        => "RESGATE DO SISTEMA ANTIGO: Uma avaria foi registrada para a OS #{$pedidoOriginal->id}. Verifique a foto no item abaixo, adicione o material quebrado na lista e aprove o contrato para gerar o financeiro."
                ]);

                // 2. CRIA UM ITEM FANTASMA SÓ PARA SEGURAR A FOTO
                PedidoItem::create([
                    'pedido_id'         => $novoPedidoVenda->id,
                    'produto_id'        => null, // Deixamos nulo pois não sabemos qual item quebrou
                    'quantidade_pedida' => 1,
                    'valor_unitario'    => 0,
                    'foto_avaria'       => $avaria->foto_path
                ]);

                // 3. Apaga a avaria da tabela velha (A tabela avarias será abandonada na nova arquitetura)
                DB::table('avarias')->where('id', $avaria->id)->delete();

                DB::commit();
                $avariasProcessadas++;
                $this->line("<info>✓</info> OS de Revisão gerada para o pedido original #{$pedidoOriginal->id}.");

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("Falha ao processar avaria legado ID {$avaria->id}: " . $e->getMessage());
                $this->error("Falha ao resgatar a avaria ID {$avaria->id}. Pulando...");
            }
        }

        $this->info("Operação Concluída! {$avariasProcessadas} avarias antigas foram transformadas em OS de Cobrança.");
        $this->info("ATENÇÃO: Vá na Mesa de Operações do ERP, encontre os Orçamentos com a TAG de Avaria, veja a foto, adicione o item correto e aprove para gerar o boleto.");
    }
}