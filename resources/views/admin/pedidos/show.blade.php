<x-app-layout>
    <x-slot name="header">Gestão do Contrato #{{ str_pad($pedido->id, 5, '0', STR_PAD_LEFT) }} • {{ mb_strtoupper($pedido->cliente->nome) }}</x-slot>

    <div class="max-w-7xl mx-auto space-y-6 pb-12">
        <div id="alertas-sistema">
            @if(session('success')) <div class="p-4 bg-green-100 border-l-4 border-green-500 text-green-800 font-bold shadow-sm rounded-md mb-4">{{ session('success') }}</div> @endif
            @if(session('error')) <div class="p-4 bg-red-100 border-l-4 border-red-500 text-red-800 font-bold shadow-sm rounded-md mb-4">{{ session('error') }}</div> @endif
        </div>

        {{-- ALARMES DE VÍNCULO LOGÍSTICO (MULTA E OS ORIGINAL) --}}
        @if($pedido->tipo === 'cobranca' && $pedido->pedido_original_id)
            <div class="bg-red-50 border-l-4 border-red-600 p-4 rounded-xl shadow-sm border border-red-200">
                <p class="text-red-800 font-bold text-sm uppercase tracking-widest flex items-center">
                    <span class="text-2xl mr-3">⚠️</span> 
                    <span>Este é um Laudo de Cobrança por Avaria.<br> 
                    <a href="{{ route('admin.pedidos.show', $pedido->pedido_original_id) }}" class="underline text-red-900 font-black hover:text-red-700">CLIQUE AQUI PARA VOLTAR À O.S. ORIGINAL #{{ str_pad($pedido->pedido_original_id, 5, '0', STR_PAD_LEFT) }}</a></span>
                </p>
            </div>
        @endif

        @php $filhoAvaria = \App\Models\Pedido::where('pedido_original_id', $pedido->id)->first(); @endphp
        @if($filhoAvaria)
            <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded-xl shadow-sm flex flex-col sm:flex-row justify-between sm:items-center gap-4 border border-yellow-200">
                <p class="text-yellow-800 font-bold text-sm uppercase tracking-widest flex items-center">
                    <span class="text-2xl mr-3">📸</span> 
                    Esta O.S. retornou do galpão com peças quebradas!
                </p>
                <a href="{{ route('admin.pedidos.show', $filhoAvaria->id) }}" class="bg-red-600 text-white px-5 py-3 rounded-lg font-black text-xs uppercase shadow-md hover:bg-red-700 hover:scale-105 transition-transform text-center shrink-0">
                    VER LAUDO FOTOGRÁFICO E MULTA
                </a>
            </div>
        @endif

        {{-- PAINEL SUPERIOR: RESUMO E BOTÕES --}}
        <div class="bg-white shadow-md sm:rounded-xl p-6 md:p-8 flex flex-col lg:flex-row justify-between items-center border-t-4 {{ $pedido->status === 'cancelado' ? 'border-red-500' : 'border-brand-gold' }} gap-4">
            <div class="w-full lg:w-auto text-center lg:text-left">
                <h3 class="text-lg font-black text-brand-dark uppercase tracking-wide flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-3">
                    @php
                        $corStatus = match($pedido->status) {
                            'orcamento' => 'bg-gray-200 text-gray-700',
                            'em_separacao' => 'bg-blue-100 text-blue-700',
                            'entregue' => 'bg-yellow-100 text-yellow-800',
                            'devolvido' => 'bg-green-100 text-green-700',
                            'cancelado' => 'bg-red-100 text-red-700',
                            default => 'bg-brand-gold text-brand-black'
                        };
                    @endphp
                    Status: <span class="px-4 py-1 text-sm rounded font-black tracking-widest {{ $corStatus }}">{{ strtoupper(str_replace('_', ' ', $pedido->status)) }}</span>
                    @if($pedido->assinatura_data || ($pedido->tipo === 'cobranca' && \App\Models\Pedido::find($pedido->pedido_original_id)?->assinatura_data)) 
                        <span class="px-3 py-1 text-[9px] rounded bg-green-600 text-white font-black shadow-sm uppercase">✍️ Assinado Eletronicamente</span> 
                    @endif
                </h3>
                <div class="text-[11px] text-gray-500 mt-3 font-bold uppercase tracking-widest grid grid-cols-2 md:grid-cols-4 gap-3 bg-gray-50 border border-gray-200 p-3 rounded">
                    <div><span class="block text-[9px] text-gray-400">Entrega</span><span class="text-blue-600">{{ $pedido->data_entrega ? $pedido->data_entrega->format('d/m/Y') : '-' }}</span></div>
                    <div><span class="block text-[9px] text-gray-400">Devolução</span><span class="text-red-600">{{ $pedido->data_devolucao ? $pedido->data_devolucao->format('d/m/Y') : '-' }}</span></div>
                    <div><span class="block text-[9px] text-gray-400">Pagamento</span><span class="text-green-700">{{ $pedido->forma_pagamento }}</span></div>
                    <div><span class="block text-[9px] text-gray-400">Total</span><span class="text-brand-dark font-black">R$ {{ number_format($pedido->valor_total, 2, ',', '.') }}</span></div>
                </div>
            </div>
            
            <div class="flex flex-wrap justify-center lg:justify-end gap-2 w-full lg:w-auto mt-4 lg:mt-0">
                @if($pedido->status !== 'cancelado' && $pedido->cliente->telefone)
                    @php 
                        $num = preg_replace('/[^0-9]/', '', $pedido->cliente->telefone); if(strlen($num) == 10 || strlen($num) == 11) $num = '55' . $num;
                        
                        // =========================================================================
                        // 💬 MOTOR DO WHATSAPP (MENSAGENS E LINKS SEGUROS)
                        // =========================================================================
                        $linkSeguroPdf = URL::signedRoute('publico.pedido.imprimir', ['pedido' => $pedido->id]);

                        if($pedido->tipo === 'cobranca') {
                            $msg = urlencode("Olá {$pedido->cliente->nome}! Segue o Laudo Técnico e a Cobrança referente às avarias do seu último evento (OS Original #" . str_pad($pedido->pedido_original_id, 5, '0', STR_PAD_LEFT) . "). Acesse o PDF com as fotos das peças pelo link seguro abaixo:\n\n" . $linkSeguroPdf);
                            $btnText = "💬 Enviar Laudo / Multa";
                        } else {
                            if ($pedido->assinatura_data) {
                                $msg = urlencode("Olá {$pedido->cliente->nome}! Segue a via do seu Contrato e os dados de pagamento (OS #" . str_pad($pedido->id, 5, '0', STR_PAD_LEFT) . "). Clique no link seguro abaixo para abrir o documento PDF:\n\n" . $linkSeguroPdf);
                            } else {
                                $msg = urlencode("Olá {$pedido->cliente->nome}! Seu orçamento está pronto. Por favor, acesse o link abaixo e assine digitalmente para confirmar a reserva do material na nossa agenda:\n\n" . route('site.assinatura.show', $pedido->token_assinatura ?? ''));
                            }
                            $btnText = $pedido->assinatura_data ? "💬 Enviar PDF e PIX" : "💬 Pedir Assinatura";
                        }
                    @endphp
                    <a href="https://api.whatsapp.com/send?phone={{ $num }}&text={{ $msg }}" target="_blank" class="px-4 py-2 bg-blue-600 text-white text-[10px] hover:bg-blue-700 font-black rounded shadow-sm uppercase tracking-widest flex items-center justify-center">{{ $btnText }}</a>
                @endif
                
                <a href="{{ route('admin.pedidos.imprimir', $pedido->id) }}?via=cliente" target="_blank" class="px-4 py-2 bg-gray-900 text-brand-gold text-[10px] font-black rounded shadow-sm hover:bg-black uppercase tracking-widest flex items-center justify-center">📄 Imprimir: Via Cliente</a>
                <a href="{{ route('admin.pedidos.imprimir', $pedido->id) }}?via=galpao" target="_blank" class="px-4 py-2 bg-white border border-gray-400 text-gray-800 text-[10px] font-black rounded shadow-sm hover:bg-gray-100 uppercase tracking-widest flex items-center justify-center">📦 Imprimir: Separação</a>

                @if($pedido->status === 'orcamento')
                    <form action="{{ route('admin.pedidos.aprovar', $pedido->id) }}" method="POST" onsubmit="return confirm('Aprovar Manualmente e Bloquear o Estoque Físico?');">@csrf <button type="submit" class="px-4 py-2 bg-brand-gold text-brand-black text-[10px] font-black rounded shadow-sm hover:bg-yellow-500 uppercase tracking-widest h-full">Aprovar / Bloquear</button></form>
                @endif
                @if(in_array($pedido->status, ['confirmado', 'orcamento']))
                    <form action="{{ route('admin.pedidos.cancelar', $pedido->id) }}" method="POST" onsubmit="return confirm('Cancelar OS e liberar peças?');">@csrf <button type="submit" class="px-4 py-2 bg-red-100 text-red-600 border border-red-200 text-[10px] font-black rounded hover:bg-red-600 hover:text-white uppercase tracking-widest transition-colors h-full">Cancelar</button></form>
                @endif
            </div>
        </div>

        {{-- SELO DE AUDITORIA LOGÍSTICA --}}
        @if($pedido->log_checkout_user || $pedido->log_checkin_user)
            <div class="bg-gray-50 border-2 border-dashed border-gray-300 p-4 rounded-xl flex flex-col md:flex-row gap-6 items-center shadow-inner">
                <div class="flex items-center gap-2">
                    <span class="text-2xl">🕵️‍♂️</span>
                    <div>
                        <h4 class="text-[10px] font-black uppercase tracking-widest text-gray-500">Rastreabilidade de Galpão</h4>
                        <p class="text-xs font-bold text-gray-400">Auditoria inalterável de sistema.</p>
                    </div>
                </div>
                <div class="flex-1 w-full grid grid-cols-1 md:grid-cols-2 gap-4">
                    @if($pedido->log_checkout_user)
                        <div class="bg-white p-3 rounded border border-blue-200 shadow-sm">
                            <span class="block text-[9px] font-black text-blue-600 uppercase tracking-widest">📦 Check-out (Expedição)</span>
                            <span class="block text-sm font-black text-brand-dark uppercase mt-1">{{ $pedido->log_checkout_user }}</span>
                            <span class="block text-[10px] font-bold text-gray-500 mt-1">Data: {{ \Carbon\Carbon::parse($pedido->log_checkout_data)->format('d/m/Y \à\s H:i') }}</span>
                        </div>
                    @endif
                    @if($pedido->log_checkin_user)
                        <div class="bg-white p-3 rounded border border-green-200 shadow-sm">
                            <span class="block text-[9px] font-black text-green-600 uppercase tracking-widest">🚚 Check-in (Retorno)</span>
                            <span class="block text-sm font-black text-brand-dark uppercase mt-1">{{ $pedido->log_checkin_user }}</span>
                            <span class="block text-[10px] font-bold text-gray-500 mt-1">Data: {{ \Carbon\Carbon::parse($pedido->log_checkin_data)->format('d/m/Y \à\s H:i') }}</span>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- BLOCO DO CARRINHO --}}
        <div class="bg-white shadow-md sm:rounded-xl overflow-hidden border border-gray-200">
            <div id="painel-totais" class="p-6 md:p-8 bg-brand-black flex justify-between items-center">
                <h4 class="text-sm font-black text-brand-gold uppercase tracking-widest">Carrinho Comercial</h4>
                <h4 class="text-2xl font-black text-white tracking-widest">Líquido: R$ <span class="text-brand-gold">{{ number_format($pedido->valor_total, 2, ',', '.') }}</span></h4>
            </div>

            @if($pedido->status === 'orcamento')
                <div class="p-6 bg-gray-50 border-b border-gray-200" x-data="carrinhoOS()">
                    <div class="flex flex-col md:flex-row gap-4 items-end relative">
                        <div class="w-full md:w-1/2 relative">
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">🔍 Busca Inteligente de Material</label>
                            <input type="text" x-model="busca" @input.debounce.300ms="pesquisar()" placeholder="Digite o nome da peça..." autocomplete="off" class="w-full bg-white border border-gray-300 px-4 py-3 rounded-md shadow-sm font-bold text-sm focus:border-brand-gold focus:ring-1 focus:ring-brand-gold text-brand-dark transition-all">
                            
                            <ul x-show="resultados.length > 0" @click.away="resultados = []" class="absolute z-50 w-full bg-white border border-brand-gold shadow-2xl rounded-md mt-1 max-h-60 overflow-y-auto">
                                <template x-for="res in resultados" :key="res.id">
                                    <li @click="selecionar(res)" class="p-3 hover:bg-brand-gold hover:text-black cursor-pointer border-b border-gray-100 flex justify-between items-center group">
                                        <div>
                                            <span class="font-black uppercase text-sm text-gray-800 group-hover:text-black block" x-text="res.nome"></span>
                                            <span x-show="res.is_kit" class="text-[9px] bg-black text-brand-gold px-1.5 py-0.5 rounded font-bold uppercase mt-1 inline-block">CONJUNTO / KIT</span>
                                        </div>
                                        <div class="text-right">
                                            <span class="block text-[10px] uppercase font-black" :class="res.estoque_livre > 0 ? 'text-green-600' : 'text-red-600'" x-text="`Livre p/ Data: ${res.estoque_livre}x`"></span>
                                            <span class="block text-xs font-bold text-gray-500 group-hover:text-gray-900" x-text="`Tabela: R$ ${res.valor_locacao}`"></span>
                                        </div>
                                    </li>
                                </template>
                            </ul>
                        </div>
                        
                        <div class="w-full sm:w-1/6">
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Quantidade</label>
                            <input type="number" x-model="quantidade" x-ref="qtd" min="1" class="w-full bg-white border border-gray-300 px-4 py-3 rounded-md shadow-sm font-black text-center text-lg focus:border-brand-gold text-gray-900">
                        </div>
                        <div class="w-full sm:w-1/6">
                            <label class="block text-[10px] font-black text-red-600 uppercase tracking-widest mb-1">Desc. Uni. (R$)</label>
                            <input type="number" x-model="desconto" step="0.01" min="0" placeholder="0.00" class="w-full bg-red-50 border border-red-300 px-4 py-3 rounded-md shadow-sm font-bold text-center text-sm focus:border-red-500 text-red-700">
                        </div>
                        <div class="w-full sm:w-1/6">
                            <button @click="adicionar()" x-ref="btn" :disabled="!produtoId || carregando" class="w-full px-5 py-3 bg-brand-dark text-brand-gold text-xs font-black rounded-md hover:bg-black uppercase tracking-widest transition-colors shadow-sm disabled:opacity-50 h-[46px] flex items-center justify-center">
                                + Inserir
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <div id="tabela-itens" class="overflow-x-auto p-2">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-6 py-4 text-left text-[10px] font-black text-gray-500 uppercase tracking-widest">Produto Solicitado</th>
                            <th class="px-6 py-4 text-center text-[10px] font-black text-gray-500 uppercase tracking-widest">Qtd</th>
                            <th class="px-6 py-4 text-right text-[10px] font-black text-gray-500 uppercase tracking-widest">Diária (R$)</th>
                            <th class="px-6 py-4 text-right text-[10px] font-black text-red-500 uppercase tracking-widest">Desc (R$)</th>
                            <th class="px-6 py-4 text-right text-[10px] font-black text-brand-dark uppercase tracking-widest">Subtotal Líquido</th>
                            @if($pedido->status === 'orcamento') <th class="px-6 py-4"></th> @endif
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-50">
                        @forelse ($pedido->itens as $item)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-black text-brand-dark uppercase">{{ $item->produto->nome ?? 'Excluído' }}</div>
                                    <div class="text-[9px] text-gray-400 font-bold uppercase tracking-widest mt-1">MULTA AVARIA: <span class="text-red-500">R$ {{ number_format($item->valor_reposicao, 2, ',', '.') }}/UN</span></div>
                                </td>
                                <td class="px-6 py-4 text-center text-sm font-black"><span class="bg-white border border-gray-300 shadow-sm px-3 py-1 rounded-md">{{ $item->quantidade_pedida }}</span></td>
                                <td class="px-6 py-4 text-right text-xs font-bold text-gray-600">R$ {{ number_format($item->valor_unitario, 2, ',', '.') }}</td>
                                <td class="px-6 py-4 text-right text-xs font-bold text-red-600">- R$ {{ number_format($item->desconto, 2, ',', '.') }}</td>
                                <td class="px-6 py-4 text-right text-sm font-black text-brand-dark">R$ {{ number_format($item->subtotal, 2, ',', '.') }}</td>
                                @if($pedido->status === 'orcamento')
                                    <td class="px-6 py-4 text-center">
                                        <button onclick="removerItemAjax('{{ route('admin.pedidos.itens.destroy', [$pedido->id, $item->id]) }}')" class="text-red-500 hover:text-red-700 font-black text-[10px] uppercase tracking-widest underline">Remover</button>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-8 text-center text-gray-400 font-bold text-xs uppercase tracking-widest">Carrinho vazio. Busque e adicione materiais.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <script>
                document.addEventListener('alpine:init', () => {
                    Alpine.data('carrinhoOS', () => ({
                        busca: '', resultados: [], carregando: false, produtoId: '', quantidade: 1, desconto: '',
                        
                        async pesquisar() {
                            if(this.busca.length < 2) { this.resultados = []; return; }
                            try {
                                let res = await fetch(`{{ route('api.produtos.busca') }}?q=${encodeURIComponent(this.busca)}&dt_entrega={{ $pedido->data_entrega ? $pedido->data_entrega->format('Y-m-d') : '' }}&dt_devolucao={{ $pedido->data_devolucao ? $pedido->data_devolucao->format('Y-m-d') : '' }}&pedido_id={{ $pedido->id }}`);
                                this.resultados = await res.json();
                            } catch(e) {}
                        },
                        selecionar(item) {
                            if(item.estoque_livre <= 0) { alert('BLOQUEIO: Não há unidades livres nas datas deste evento!'); return; }
                            this.busca = item.nome; this.produtoId = item.id; this.resultados = []; this.$refs.qtd.focus();
                        },
                        async adicionar() {
                            if(!this.produtoId) return;
                            this.carregando = true; let btn = this.$refs.btn; btn.innerHTML = 'Add...';
                            let fd = new FormData();
                            fd.append('_token', '{{ csrf_token() }}'); fd.append('produto_id', this.produtoId);
                            fd.append('quantidade', this.quantidade); fd.append('desconto', this.desconto || 0);

                            try {
                                let res = await fetch(`{{ route('admin.pedidos.itens.store', $pedido->id) }}`, { method: 'POST', body: fd, headers: {'X-Requested-With': 'XMLHttpRequest'} });
                                let json = await res.json();
                                if(!json.success) { alert(json.message); } 
                                else { document.dispatchEvent(new Event('reload-carrinho')); this.busca = ''; this.produtoId = ''; this.quantidade = 1; this.desconto = ''; }
                            } catch(e) { alert('Erro na comunicação.'); }
                            this.carregando = false; btn.innerHTML = '+ Inserir';
                        }
                    }));
                });

                document.addEventListener('reload-carrinho', () => {
                    fetch(window.location.href).then(r => r.text()).then(html => {
                        let doc = new DOMParser().parseFromString(html, 'text/html');
                        document.getElementById('tabela-itens').innerHTML = doc.getElementById('tabela-itens').innerHTML;
                        document.getElementById('painel-totais').innerHTML = doc.getElementById('painel-totais').innerHTML;
                    });
                });

                async function removerItemAjax(url) {
                    if(!confirm('Deseja remover essa peça?')) return;
                    let fd = new FormData(); fd.append('_token', '{{ csrf_token() }}'); fd.append('_method', 'DELETE');
                    try { await fetch(url, { method: 'POST', body: fd, headers: {'X-Requested-With': 'XMLHttpRequest'} }); document.dispatchEvent(new Event('reload-carrinho')); } catch(e) {}
                }
            </script>
        </div>
    </div>
</x-app-layout>