<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Models\Pedido;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class OrcamentoController extends Controller implements HasMiddleware
{
    /**
     * WAF INTERNO (Laravel 11 Middleware Pipeline)
     * Proteção contra DDoS e exaustão de Entry Processes no cPanel.
     */
    public static function middleware(): array
    {
        return [
            // Throttling: Máximo de 3 tentativas de orçamento por minuto por IP.
            // Se exceder, o Laravel bloqueia na borda (HTTP 429) antes de instanciar o banco.
            new Middleware('throttle:3,1'),
        ];
    }

    /**
     * Recebe os dados da Vitrine Pública e aplica a triagem Anti-Bot
     */
    public function store(Request $request)
    {
        // 1. CAMADA ZERO TRUST: Honeypot Invisível (Fail Fast)
        // O campo 'url_website_fake' DEVE existir no HTML do formulário com "display: none;"
        if ($request->filled('url_website_fake')) {
            // ENGANANDO O BOT: Retornamos HTTP 200 OK imediato em JSON.
            // O processamento morre aqui. Sem redirect, sem carregar views. Consumo de RAM = ~0.
            return response()->json(['success' => true, 'message' => 'Payload accepted.'], 200);
        }

        // 2. BARREIRA DE MEMÓRIA (OOM Protection) & Validação Estrita
        // Limites rígidos de caracteres impedem que payloads gigantes saturem a RAM do PHP 
        $validated = $request->validate([
            'name'        => 'required|string|max:100',
            'telefone'    => 'required|string|max:20',
            'data_evento' => 'required|date|after_or_equal:today', // Impede orçamentos de datas passadas
            'message'     => 'required|string|max:1000', // Bloqueio contra textos massivos (Buffer Overflow)
        ]);

        // 3. TRANSAÇÃO ATÔMICA E SANITIZAÇÃO (Anti-XSS)
        DB::beginTransaction();
        try {
            // Removemos tags HTML para impedir que um atacante injete scripts no Painel do ERP (Stored XSS)
            $nomeLimpo = mb_strtoupper(trim(strip_tags($validated['name'])));
            $telefoneLimpo = preg_replace('/[^0-9]/', '', $validated['telefone']);
            $mensagemLimpa = strip_tags($validated['message']);
            
            // 4. CRM INTELIGENTE: Busca em Cascata para evitar duplicidades
            $cliente = Cliente::where('nome', $nomeLimpo)
                ->orWhere(function($q) use ($telefoneLimpo) {
                    if (!empty($telefoneLimpo)) {
                        $q->where('telefone', 'like', "%{$telefoneLimpo}%");
                    }
                })->first();

            // Se não encontrou, cria um novo
            if (!$cliente) {
                $cliente = Cliente::create([
                    'nome'     => $nomeLimpo,
                    'telefone' => $telefoneLimpo
                ]);
            } else {
                // Se achou o cliente, mas estava sem telefone, atualiza o cadastro dele silenciosamente
                if (empty($cliente->telefone) && !empty($telefoneLimpo)) {
                    $cliente->update(['telefone' => $telefoneLimpo]);
                }
            }

            // 5. CRIA A OS INICIAL NA MESA DE OPERAÇÕES
            Pedido::create([
                'cliente_id'       => $cliente->id,
                'status'           => 'orcamento',
                'data_evento'      => $validated['data_evento'],
                'data_locacao'     => now(),
                'valor_total'      => 0.00,
                'observacoes'      => "SOLICITAÇÃO VIA SITE:\n" . $mensagemLimpa,
                'token_assinatura' => \Illuminate\Support\Str::random(40)
            ]);

            DB::commit();

            // SUCESSO REAL (HUMANO): Redireciona com feedback
            return redirect('/#contato')->with('success', 'Orçamento solicitado com sucesso! Nossa equipe chamará no WhatsApp.');

        } catch (\Exception $e) {
            DB::rollBack();
            // Fail Fast silencioso. Log de erro interno omitido do frontend por segurança (Information Disclosure)
            return redirect('/#contato')->with('error', 'Erro interno ao processar o pedido. Tente novamente mais tarde.');
        }
    }
}