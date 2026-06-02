<?php

namespace App\Jobs;

use App\Models\Pedido;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessarPedidoConfirmado implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Regra de Sobrevivência: Se o Job demorar mais de 30 segundos, ele falha e solta a CPU.
    public $timeout = 30;
    
    // Tenta no máximo 3 vezes antes de desistir e jogar para a tabela de failed_jobs
    public $tries = 3;

    public function __construct(
        public Pedido $pedido
    ) {}

    public function handle(): void
    {
        // Aqui o PHP está rodando em segundo plano, sem travar a tela do cliente.
        Log::info("Processando fundo de tela para o pedido: {$this->pedido->id}");
        
        // [TODO: Instanciar a lib DomPDF para gerar o contrato]
        // [TODO: Disparar e-mail via SMTP com o PDF anexado]
    }
}