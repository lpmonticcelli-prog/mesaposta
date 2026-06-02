<x-app-layout>
    <x-slot name="header">Montagem e Edição: OS #{{ str_pad($pedido->id, 5, '0', STR_PAD_LEFT) }}</x-slot>

    <div class="max-w-7xl mx-auto space-y-6">

        @if(session('success')) 
            <div class="p-4 bg-green-100 border-l-4 border-green-500 text-green-800 font-bold shadow-sm">
                {{ session('success') }}
            </div> 
        @endif
        @if(session('error')) 
            <div class="p-4 bg-red-100 border-l-4 border-red-500 text-red-800 font-bold shadow-sm">
                {{ session('error') }}
            </div> 
        @endif
        
        @if($pedido->tipo === 'cobranca' && $pedido->pedidoOriginal)
            <div class="bg-red-50 border border-red-200 shadow-sm rounded-xl p-4 flex flex-col sm:flex-row justify-between items-center animate-pulse">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <p class="text-sm text-red-800 font-bold">Esta é uma OS de Cobrança vinculada a um evento passado.</p>
                </div>
                <a href="{{ route('admin.pedidos.show', $pedido->pedido_original_id) }}" class="mt-3 sm:mt-0 px-5 py-2 bg-red-600 text-white text-xs font-black rounded-md hover:bg-red-700 uppercase tracking-widest shadow-md transition-colors text-center">
                    &larr; Voltar para a OS Original #{{ str_pad($pedido->pedido_original_id, 5, '0', STR_PAD_LEFT) }}
                </a>
            </div>
        @endif

        <div class="bg-white shadow-md sm:rounded-xl p-6 md:p-8 flex flex-col md:flex-row justify-between items-center border-t-4 border-brand-gold gap-4">
            <div>
                <h3 class="text-lg font-black text-brand-dark uppercase tracking-wide flex items-center">
                    Status Atual: 
                    <span class="ml-3 px-4 py-1 text-sm rounded font-black tracking-widest {{ $pedido->status === 'orcamento' ? 'bg-gray-200 text-gray-700' : 'bg-green-100 text-green-700' }}">
                        {{ strtoupper(str_replace('_', ' ', $pedido->status)) }}
                    </span>
                    
                    @if($pedido->tipo === 'cobranca')
                        <span class="ml-3 px-4 py-1 text-[10px] rounded bg-red-600 text-white font-black tracking-widest uppercase shadow-sm">
                            ⚠️ OS DE COBRANÇA (AVARIA)
                        </span>
                    @endif
                </h3>
                <p class="text-sm text-gray-500 mt-2 font-medium">Cliente: <strong class="text-brand-dark">{{ $pedido->cliente->nome ?? 'N/A' }}</strong> | Evento: <strong class="text-brand-dark">{{ \Carbon\Carbon::parse($pedido->data_evento)->format('d/m/Y') }}</strong></p>
            </div>
            
            <div class="flex space-x-3">
                <a href="{{ route('admin.pedidos.imprimir', $pedido->id) }}" target="_blank" class="px-6 py-3 bg-gray-800 text-brand-gold text-xs font-black rounded shadow-md hover:bg-black transition-colors uppercase tracking-widest flex items-center">
                    Imprimir Documento
                </a>

                @if($pedido->status === 'orcamento')
                    <form action="{{ route('admin.pedidos.aprovar', $pedido->id) }}" method="POST" onsubmit="return confirm('Deseja aprovar e bloquear os materiais no estoque sob regras concorrentes?');">
                        @csrf
                        <button type="submit" class="px-6 py-3 bg-brand-gold text-brand-black text-xs font-black rounded shadow-md hover:bg-brand-hover transition-colors uppercase tracking-widest h-full">
                            Aprovar e Bloquear Peças
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <div class="bg-white shadow-md sm:rounded-xl overflow-hidden border border-gray-200">
            <div class="p-6 md:p-8 bg-brand-black flex flex-col sm:flex-row justify-between items-center gap-4">
                <h4 class="text-sm font-black text-brand-gold uppercase tracking-widest">Itens Alocados</h4>
                <h4 class="text-2xl font-black text-white tracking-widest">
                    Total: R$ <span class="text-brand-gold">{{ number_format($pedido->valor_total, 2, ',', '.') }}</span>
                </h4>
            </div>

            @if($pedido->status === 'orcamento')
                <div class="p-6 bg-brand-light border-b border-gray-200">
                    <form action="{{ route('admin.pedidos.itens.store', $pedido->id) }}" method="POST" class="flex flex-col sm:flex-row gap-4 items-end">
                        @csrf
                        <div class="w-full sm:w-1/2">
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2">Adicionar Material do Acervo</label>
                            <select name="produto_id" required class="w-full rounded border-gray-300 focus:border-brand-gold focus:ring focus:ring-brand-gold font-bold text-brand-dark">
                                <option value="">-- Escolha um material --</option>
                                @foreach($produtos as $prod)
                                    @php 
                                        $livre = $prod->estoqueLivreNoPeriodo($pedido->data_entrega ?? $pedido->data_evento, $pedido->data_devolucao ?? $pedido->data_evento);
                                    @endphp
                                    <option value="{{ $prod->id }}" {{ $livre === 0 ? 'disabled class=text-gray-300' : '' }}>
                                        {{ $prod->nome }} (Livre no Período: {{ $livre }}) - R$ {{ number_format($prod->valor_locacao, 2, ',', '.') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="w-full sm:w-1/4">
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2">Quantidade</label>
                            <input type="number" name="whitespace_fix_qtd" required min="1" value="1" class="w-full rounded border-gray-300 focus:border-brand-gold font-black text-center text-lg">
                        </div>
                        <div class="w-full sm:w-1/4">
                            <button type="submit" class="w-full px-5 py-3 bg-brand-dark text-brand-gold text-xs font-black rounded hover:bg-black uppercase tracking-widest transition-colors shadow-sm">
                                + Adicionar
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            <div class="overflow-x-auto p-2">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-white">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">Produto</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-gray-400 uppercase tracking-widest">Qtd</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-400 uppercase tracking-widest">Subtotal</th>
                            @if($pedido->status === 'orcamento') <th class="px-6 py-4"></th> @endif
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-50">
                        @forelse ($pedido->itens as $item)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 text-sm font-black text-brand-dark uppercase">
                                    {{ $item->produto->nome ?? 'Excluído do Acervo' }}
                                    @if($item->foto_avaria)
                                        <div class="mt-2">
                                            <a href="{{ asset('storage/' . $item->foto_avaria) }}" target="_blank" class="inline-flex items-center text-[10px] bg-red-100 text-red-700 hover:bg-red-200 border border-red-300 px-3 py-1.5 rounded transition-colors tracking-widest font-black shadow-sm">
                                                VER FOTO DA AVARIA
                                            </a>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center text-sm font-black bg-gray-100 rounded">{{ $item->quantidade_pedida }}</td>
                                <td class="px-6 py-4 text-right text-sm font-black text-brand-dark">R$ {{ number_format($item->quantidade_pedida * $item->valor_unitario, 2, ',', '.') }}</td>
                                @if($pedido->status === 'orcamento')
                                    <td class="px-6 py-4 text-center">
                                        <form action="{{ route('admin.pedidos.itens.destroy', [$pedido->id, $item->id]) }}" method="POST">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700 font-black text-[10px] uppercase tracking-widest">Remover</button>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-6 py-8 text-center text-gray-400 font-bold text-xs uppercase tracking-widest">O documento está vazio. Adicione materiais acima.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>