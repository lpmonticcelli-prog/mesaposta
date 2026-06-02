<x-app-layout>
    <x-slot name="header">App do Galpão: Retorno de OS</x-slot>

    <div class="max-w-3xl mx-auto" x-data="{ modoAvaria: false }">
        
        <div class="bg-brand-black rounded-t-xl p-6 text-center border-b-4 border-brand-gold shadow-lg">
            <h2 class="text-brand-gold font-black text-xl uppercase tracking-widest">OS #{{ str_pad($pedido->id, 5, '0', STR_PAD_LEFT) }}</h2>
            <p class="text-gray-300 font-bold mt-2">{{ $pedido->cliente->nome }}</p>
            <p class="text-gray-500 text-xs mt-1 uppercase tracking-widest">Retorno de Materiais</p>
        </div>

        <div class="bg-white p-6 shadow-md rounded-b-xl border border-gray-200 space-y-6">
            
            <div class="space-y-2">
                @foreach($pedido->itens as $item)
                    <div class="flex justify-between items-center p-4 bg-gray-50 rounded border border-gray-100">
                        <div>
                            <span class="font-black text-brand-dark uppercase text-sm block">{{ $item->produto->nome }}</span>
                        </div>
                        <div class="bg-brand-dark text-brand-gold font-black px-4 py-2 rounded text-lg">
                            {{ $item->quantidade_pedida }}x
                        </div>
                    </div>
                @endforeach
            </div>

            <div x-show="!modoAvaria" class="space-y-4 pt-6 border-t border-gray-100" x-transition>
                
                <form action="{{ route('estoque.conferencia.processar', $pedido->id) }}" method="POST" onsubmit="return confirm('Confirma que tudo voltou perfeitamente? O estoque será liberado.');">
                    @csrf
                    <input type="hidden" name="tipo_retorno" value="intacto">
                    <button type="submit" class="w-full py-5 bg-green-600 text-white font-black rounded-lg shadow-md hover:bg-green-700 uppercase tracking-widest text-lg flex justify-center items-center">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Tudo OK - Retornar ao Estoque
                    </button>
                </form>

                <button @click="modoAvaria = true" type="button" class="w-full py-5 bg-red-600 text-white font-black rounded-lg shadow-md hover:bg-red-700 uppercase tracking-widest text-lg flex justify-center items-center">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    Registrar Avarias / Quebras
                </button>
            </div>

            <div x-cloak x-show="modoAvaria" class="pt-6 border-t-4 border-red-500" x-transition>
                <div class="bg-red-50 p-4 rounded mb-6 border border-red-200">
                    <h3 class="text-red-800 font-black uppercase text-sm mb-2">Modo de Avaria Ativado</h3>
                    <p class="text-xs text-red-600 font-bold">Informe apenas a quantidade que foi QUEBRADA/PERDIDA de cada item. Tire fotos para evidência.</p>
                </div>

                <form action="{{ route('estoque.conferencia.processar', $pedido->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    <input type="hidden" name="tipo_retorno" value="avaria">

                    @foreach($pedido->itens as $item)
                        <div class="bg-white p-4 rounded border border-gray-300 shadow-sm">
                            <p class="font-black text-brand-dark uppercase text-sm mb-3">{{ $item->produto->nome }} (Alugados: {{ $item->quantidade_pedida }})</p>
                            
                            <div class="flex gap-4 items-start">
                                <div class="w-1/3">
                                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Qtd Quebrada</label>
                                    <input type="number" name="avarias[{{ $item->id }}]" min="0" max="{{ $item->quantidade_pedida }}" value="0" class="w-full rounded border-gray-300 focus:border-red-500 font-black text-center text-lg text-red-600 bg-red-50">
                                </div>
                                <div class="w-2/3">
                                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Foto da Avaria</label>
                                    <input type="file" name="fotos[{{ $item->id }}]" accept="image/*" capture="environment" class="w-full text-xs file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-xs file:font-bold file:bg-gray-800 file:text-brand-gold hover:file:bg-black">
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <div class="pt-4 flex gap-4">
                        <button @click="modoAvaria = false" type="button" class="w-1/3 py-4 bg-gray-200 text-gray-700 font-black rounded hover:bg-gray-300 uppercase tracking-widest text-xs">Cancelar</button>
                        <button type="submit" class="w-2/3 py-4 bg-red-600 text-white font-black rounded hover:bg-red-700 shadow-lg uppercase tracking-widest text-xs">
                            Confirmar Quebras e Gerar Cobrança
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>