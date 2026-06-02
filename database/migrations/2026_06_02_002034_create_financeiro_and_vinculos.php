<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. O Cordão Umbilical (Amarra a OS Nova com a OS Velha)
        Schema::table('pedidos', function (Blueprint $table) {
            if (!Schema::hasColumn('pedidos', 'pedido_original_id')) {
                $table->unsignedBigInteger('pedido_original_id')->nullable()->after('id');
            }
        });

        // 2. A Criação do Cofre Financeiro (Contas a Receber)
        if (!Schema::hasTable('contas_receber')) {
            Schema::create('contas_receber', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pedido_id')->nullable()->constrained('pedidos')->nullOnDelete();
                $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
                $table->string('descricao');
                $table->decimal('valor', 10, 2);
                $table->date('data_vencimento');
                $table->string('status')->default('pendente'); // pendente, pago, cancelado
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        // Vazio para segurança estrutural dos dados em produção
    }
};