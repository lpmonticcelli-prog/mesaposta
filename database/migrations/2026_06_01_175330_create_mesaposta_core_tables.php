<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabela de Clientes
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('telefone')->nullable();
            $table->string('documento')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });

        // 2. Tabela do Inventário
        Schema::create('produtos', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('categoria')->nullable();
            $table->integer('quantidade_estoque')->default(0);
            $table->decimal('valor_locacao', 10, 2)->default(0);
            $table->decimal('valor_reposicao', 10, 2)->default(0);
            $table->timestamps();
        });

        // 3. Tabela de Ordem de Serviço
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            $table->enum('status', ['orcamento', 'confirmado', 'em_separacao', 'entregue', 'devolvido'])->default('orcamento');
            $table->date('data_evento')->nullable();
            $table->date('data_entrega')->nullable();
            $table->date('data_devolucao')->nullable();
            $table->decimal('valor_total', 10, 2)->default(0);
            $table->text('observacoes')->nullable();
            $table->timestamps();
            
            // ÍNDICE DE PERFORMANCE: Crucial para o Algoritmo Temporal Anti-Furo
            $table->index(['status', 'data_evento']); 
        });

        // 4. Tabela Pivot
        Schema::create('pedido_itens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->constrained('pedidos')->onDelete('cascade');
            $table->foreignId('produto_id')->nullable()->constrained('produtos')->onDelete('set null');
            $table->integer('quantidade_pedida');
            $table->integer('quantidade_separada')->default(0);
            $table->decimal('valor_unitario', 10, 2)->default(0);
            $table->timestamps();
            
            // ÍNDICE DE PERFORMANCE: Impede Full Table Scan no MySQL na hora de calcular estoque livre
            $table->index(['produto_id', 'pedido_id']);
        });
        
        // 5. Tabela de Logística
        Schema::create('avarias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->constrained('pedidos')->onDelete('cascade');
            $table->string('foto_path'); 
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('avarias');
        Schema::dropIfExists('pedido_itens');
        Schema::dropIfExists('pedidos');
        Schema::dropIfExists('produtos');
        Schema::dropIfExists('clientes');
    }
};