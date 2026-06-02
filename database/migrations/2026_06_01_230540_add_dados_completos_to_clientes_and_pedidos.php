<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Expandindo o cadastro do Cliente (Com checagem de existência)
        Schema::table('clientes', function (Blueprint $table) {
            if (!Schema::hasColumn('clientes', 'tipo_pessoa')) {
                $table->string('tipo_pessoa', 2)->default('PF')->after('nome');
            }
            if (!Schema::hasColumn('clientes', 'cpf_cnpj')) {
                $table->string('cpf_cnpj', 20)->nullable()->after('tipo_pessoa');
            }
            if (!Schema::hasColumn('clientes', 'rg_ie')) {
                $table->string('rg_ie', 30)->nullable()->after('cpf_cnpj');
            }
            
            // REMOVIDO: A coluna 'email', pois o seu banco de dados já possui ela!

            if (!Schema::hasColumn('clientes', 'cep')) {
                $table->string('cep', 10)->nullable();
                $table->string('endereco')->nullable();
                $table->string('numero', 20)->nullable();
                $table->string('complemento')->nullable();
                $table->string('bairro')->nullable();
                $table->string('cidade')->nullable();
                $table->string('estado', 2)->nullable();
            }
        });

        // 2. Expandindo a OS com Endereço de Entrega/Evento
        Schema::table('pedidos', function (Blueprint $table) {
            if (!Schema::hasColumn('pedidos', 'cep_entrega')) {
                $table->string('cep_entrega', 10)->nullable()->after('observacoes');
                $table->string('endereco_entrega')->nullable()->after('cep_entrega');
                $table->string('numero_entrega', 20)->nullable()->after('endereco_entrega');
                $table->string('complemento_entrega')->nullable()->after('numero_entrega');
                $table->string('bairro_entrega')->nullable()->after('complemento_entrega');
                $table->string('cidade_entrega')->nullable()->after('bairro_entrega');
                $table->string('estado_entrega', 2)->nullable()->after('cidade_entrega');
            }
        });
    }

    public function down(): void
    {
        // ... método down mantido inalterado
    }
};