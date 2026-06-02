<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lancamentos', function (Blueprint $table) {
            $table->id();
            $table->string('descricao'); // Ex: Conta de Luz, Reposição de Taças, Pagamento OS #05
            $table->enum('tipo', ['receita', 'despesa']); // Entrou ou Saiu
            $table->decimal('valor', 10, 2);
            $table->date('data_vencimento');
            $table->date('data_pagamento')->nullable();
            $table->enum('status', ['pendente', 'pago', 'atrasado', 'cancelado'])->default('pendente');
            
            // Se a receita/despesa vier de uma OS, fica vinculada aqui
            $table->foreignId('pedido_id')->nullable()->constrained('pedidos')->onDelete('set null'); 
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lancamentos');
    }
};