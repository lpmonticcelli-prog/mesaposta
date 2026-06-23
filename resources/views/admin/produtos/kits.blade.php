<x-app-layout>
    <x-slot name="header">Ficha Técnica do Conjunto</x-slot>

    <div class="max-w-4xl mx-auto space-y-6 py-6">
        @if(session('success')) <div class="p-4 bg-green-100 border-l-4 border-green-500 text-green-800 font-bold shadow-sm rounded mb-4">{{ session('success') }}</div> @endif
        
        <div class="bg-gray-900 rounded-xl p-6 flex flex-col sm:flex-row justify-between items-center shadow-lg border-t-4 border-purple-500 gap-4">
            <div>
                <h3 class="text-white font-black uppercase tracking-widest text-lg flex items-center gap-2">📦 KIT: {{ mb_strtoupper($produto->nome) }}</h3>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-1">Defina as quantidades exatas de cadeiras/mesas que formam 1 Conjunto.</p>
            </div>
            <a href="{{ route('admin.produtos.index') }}" class="px-6 py-2 bg-gray-700 text-white text-[10px] font-black uppercase tracking-widest rounded hover:bg-gray-600 transition-colors shadow-sm">Voltar ao Acervo</a>
        </div>

        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
            <div class="p-6 bg-purple-50 border-b border-purple-100">
                <form action="{{ route('admin.produtos.kits.store', $produto->id) }}" method="POST" class="flex flex-col sm:flex-row gap-4 items-end">
                    @csrf
                    <div class="w-full sm:w-2/3">
                        <label class="block text-[10px] font-black text-purple-800 uppercase tracking-widest mb-1">Localizar Peça Avulsa Física</label>
                        <select name="produto_id" required class="w-full bg-white border border-purple-300 px-4 py-3 rounded-md shadow-sm font-bold text-sm focus:border-purple-500 focus:ring-1 focus:ring-purple-500 text-gray-800">
                            <option value="">-- Escolha um material avulso --</option>
                            @foreach($avulsos as $avulso)
                                <option value="{{ $avulso->id }}">{{ mb_strtoupper($avulso->nome) }} (Físico Real Total: {{ $avulso->quantidade_estoque }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-full sm:w-1/6">
                        <label class="block text-[10px] font-black text-purple-800 uppercase tracking-widest mb-1">Exigidos por Kit</label>
                        <input type="number" name="quantidade" required min="1" value="1" class="w-full bg-white border border-purple-300 px-4 py-3 rounded-md shadow-sm font-black text-center text-lg focus:border-purple-500 focus:ring-1 focus:ring-purple-500">
                    </div>
                    <div class="w-full sm:w-1/6">
                        <button type="submit" class="w-full px-5 py-3 bg-purple-700 text-white text-[10px] font-black rounded-md hover:bg-purple-800 uppercase tracking-widest transition-colors shadow-sm h-[46px]">+ Vincular Ficha</button>
                    </div>
                </form>
            </div>

            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-white">
                    <tr>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Material Oculto amarrado neste Kit</th>
                        <th class="px-6 py-4 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest">Qtd Multiplicadora</th>
                        <th class="px-6 py-4 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-50">
                    @forelse ($componentes as $comp)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 text-sm font-black text-brand-dark uppercase">{{ $comp->produtoAvulso->nome }}</td>
                            <td class="px-6 py-4 text-center text-sm font-black"><span class="bg-gray-100 border border-gray-300 shadow-sm px-3 py-1 rounded-md">{{ $comp->quantidade }}x por conjunto</span></td>
                            <td class="px-6 py-4 text-right">
                                <form action="{{ route('admin.produtos.kits.destroy', $comp->id) }}" method="POST" onsubmit="return confirm('Remover item da ficha?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 font-black text-[10px] uppercase tracking-widest underline">Desvincular</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-6 py-12 text-center text-red-500 font-bold text-xs uppercase tracking-widest">Ficha Técnica vazia. Se você alugar este Conjunto agora, o estoque físico não sofrerá baixas! Amarre os itens.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>