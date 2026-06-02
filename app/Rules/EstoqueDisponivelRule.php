<?php

namespace App\Rules;

use App\Services\EstoqueService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class EstoqueDisponivelRule implements ValidationRule
{
    /**
     * Injeta as dependências necessárias para a viagem no tempo do Motor Logístico.
     */
    public function __construct(
        protected int $produtoId,
        protected string $dataEntrega,
        protected string $dataDevolucao
    ) {}

    /**
     * Roda a validação. O $value será a 'quantidade' que o usuário digitou.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $quantidadePedida = (int) $value;

        // Instancia o serviço gastando o mínimo de RAM
        $estoqueService = app(EstoqueService::class);
        
        $estoqueLivre = $estoqueService->calcularDisponibilidade(
            $this->produtoId,
            $this->dataEntrega,
            $this->dataDevolucao
        );

        // O Veto Algorítmico: Se pedir mais do que tem, corta a requisição com Erro 422.
        if ($quantidadePedida > $estoqueLivre) {
            $fail("Estoque insuficiente para este período. Restam apenas {$estoqueLivre} unidades disponíveis.");
        }
    }
}