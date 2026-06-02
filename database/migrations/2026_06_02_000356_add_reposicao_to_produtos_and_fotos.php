<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Adiciona o Preço de Reposição na tabela de Produtos
        Schema::table('produtos', function (Blueprint $table) {
            if (!Schema::hasColumn('produtos', 'valor_reposicao')) {
                $table->decimal('valor_reposicao', 10, 2)->default(0)->after('valor_locacao');
            }
        });

        // 2. Adiciona a Gaveta de Foto na tabela de Itens da OS
        Schema::table('pedido_itens', function (Blueprint $table) {
            if (!Schema::hasColumn('pedido_itens', 'foto_avaria')) {
                $table->string('foto_avaria')->nullable()->after('valor_unitario');
            }
        });

        // 3. Adiciona o Tipo de OS (Locação vs Cobrança)
        Schema::table('pedidos', function (Blueprint $table) {
            if (!Schema::hasColumn('pedidos', 'tipo')) {
                $table->string('tipo', 20)->default('locacao')->after('status');
            }
        });
    }

    public function down(): void
    {
        // Mantido em branco para segurança estrutural
    }
};