<x-app-layout>
    <x-slot name="header">Acervo, Inventário Físico e Kits</x-slot>

    <div class="py-6 bg-gray-50 min-h-screen" x-data="estoqueApp()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success')) <div class="p-4 bg-green-100 border-l-4 border-green-500 text-green-800 font-bold shadow-sm rounded-md">{{ session('success') }}</div> @endif
            @if(session('error')) <div class="p-4 bg-red-100 border-l-4 border-red-500 text-red-800 font-bold shadow-sm rounded-md">{{ session('error') }}</div> @endif

            {{-- A SMART TOOLBAR DE BUSCA --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-200 border-t-4 border-brand-dark">
                <div class="p-6 bg-brand-black flex flex-col md:flex-row justify-between items-center gap-4">
                    <form action="{{ route('admin.produtos.index') }}" method="GET" class="flex flex-col sm:flex-row w-full md:w-2/3 gap-3 items-end">
                        <div class="w-full">
                            <label class="block text-[10px] font-black text-brand-gold uppercase tracking-widest mb-1">Buscar Material</label>
                            <input type="text" name="busca" value="{{ request('busca') }}" placeholder="🔍 Buscar nome ou categoria..." class="w-full bg-white border border-gray-300 font-bold text-sm rounded-md px-4 py-2 focus:ring-brand-gold text-brand-dark shadow-sm">
                        </div>
                        <div class="w-full sm:w-1/2">
                            <label class="block text-[10px] font-black text-brand-gold uppercase tracking-widest mb-1">Estrutura (Filtro)</label>
                            <select name="tipo" class="w-full bg-white border border-gray-300 font-bold text-sm rounded-md px-3 py-2 shadow-sm text-gray-700">
                                <option value="">Todo o Acervo (Misturado)</option>
                                <option value="avulso" {{ request('tipo') == 'avulso' ? 'selected' : '' }}>Somente Peças Avulsas (Físicas)</option>
                                <option value="kit" {{ request('tipo') == 'kit' ? 'selected' : '' }}>Somente Conjuntos / Kits (Virtuais)</option>
                            </select>
                        </div>
                        <button type="submit" class="bg-brand-gold text-brand-black px-6 py-2.5 font-black uppercase text-[10px] tracking-widest rounded-md hover:bg-brand-hover shadow-sm h-[38px]">Filtrar</button>
                    </form>
                    <button @click="abrirNovo()" class="w-full md:w-auto px-6 py-3 bg-white text-brand-dark text-[10px] font-black rounded-md shadow-md hover:bg-gray-100 uppercase tracking-widest whitespace-nowrap">+ Novo Cadastro</button>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-500 uppercase tracking-widest">Produto e Estrutura</th>
                                <th class="px-6 py-4 text-center text-[10px] font-black text-gray-500 uppercase tracking-widest">Estoque Real</th>
                                <th class="px-6 py-4 text-right text-[10px] font-black text-gray-500 uppercase tracking-widest">Preços e Multas</th>
                                <th class="px-6 py-4 text-center text-[10px] font-black text-gray-500 uppercase tracking-widest">Ações e Fichas</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse ($produtos as $p)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-5">
                                        <div class="text-sm font-black text-brand-dark uppercase">{{ $p->nome }}</div>
                                        <div class="text-[9px] text-gray-500 font-bold uppercase tracking-widest mt-1">{{ $p->categoria ?? 'GERAL' }}</div>
                                    </td>
                                    <td class="px-6 py-5 text-center">
                                        @if($p->is_kit)
                                            <span class="px-3 py-1 bg-purple-100 text-purple-800 text-[9px] font-black rounded uppercase tracking-widest border border-purple-200">📦 CONJUNTO</span>
                                        @else
                                            <span class="px-4 py-1.5 bg-white border border-gray-300 text-brand-dark font-black rounded-md shadow-sm">{{ $p->quantidade_estoque }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-5 text-right text-sm">
                                        <span class="font-black text-green-700 block">R$ {{ number_format($p->valor_locacao, 2, ',', '.') }}</span>
                                        <span class="text-[9px] font-bold text-red-500 uppercase tracking-widest mt-1 block">Multa Rep: R$ {{ number_format($p->valor_reposicao, 2, ',', '.') }}</span>
                                    </td>
                                    <td class="px-6 py-5 text-center flex justify-center gap-2">
                                        @if($p->is_kit)
                                            <a href="{{ route('admin.produtos.kits', $p->id) }}" class="px-3 py-1.5 bg-purple-600 text-white text-[9px] font-black rounded uppercase shadow-sm hover:bg-purple-700 transition-colors border border-purple-700">Ficha Técnica</a>
                                        @endif
                                        <button @click="abrirEdicao({{ $p->toJson() }})" class="px-3 py-1.5 bg-gray-800 text-brand-gold text-[9px] font-black rounded uppercase shadow-sm hover:bg-black transition-colors border border-gray-900">Editar</button>
                                        <form action="{{ route('admin.produtos.destroy', $p->id) }}" method="POST" onsubmit="return confirm('ATENÇÃO: Deseja apagar este material do catálogo?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="px-3 py-1.5 bg-red-50 text-red-600 text-[9px] font-black rounded uppercase shadow-sm hover:bg-red-600 hover:text-white transition-colors border border-red-200">Apagar</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-6 py-12 text-center text-gray-500 text-xs font-bold uppercase tracking-widest">Nenhuma peça atende a este filtro.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($produtos->hasPages()) <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">{{ $produtos->links() }}</div> @endif
            </div>
        </div>

        {{-- O MODAL PADRÃO DE CADASTRO/EDIÇÃO --}}
        <div x-cloak x-show="aberto" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-70 backdrop-blur-sm px-4">
            <div @click.away="aberto = false" class="bg-white rounded-xl shadow-2xl w-full max-w-2xl overflow-hidden border-t-4 border-brand-gold">
                <div class="bg-brand-dark px-6 py-4 flex justify-between items-center">
                    <h3 class="text-brand-gold font-extrabold uppercase tracking-widest text-sm" x-text="modoEdicao ? 'Editar Registro' : 'Nova Peça ou Conjunto'"></h3>
                    <button @click="aberto = false" type="button" class="text-gray-400 hover:text-white text-2xl font-bold">&times;</button>
                </div>
                <div class="p-6 bg-gray-50">
                    <form :action="modoEdicao ? `{{ url('admin/produtos') }}/${form.id}` : '{{ route('admin.produtos.store') }}'" method="POST" class="space-y-5 bg-white p-6 rounded-lg border border-gray-200 shadow-sm">
                        @csrf <template x-if="modoEdicao"><input type="hidden" name="_method" value="PUT"></template>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div class="col-span-2">
                                <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Comportamento Estrutural</label>
                                <select name="is_kit" x-model="form.is_kit" class="w-full bg-yellow-50 border border-yellow-400 px-4 py-3 rounded-md shadow-sm text-sm font-black focus:border-brand-gold text-yellow-900">
                                    <option value="0">Peça Avulsa Simples (Possui Estoque Físico Próprio)</option>
                                    <option value="1">KIT/Conjunto (Estoque Dinâmico baseado em Ficha Técnica)</option>
                                </select>
                            </div>
                            <div class="col-span-2">
                                <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Nome Oficial da Peça ou Conjunto</label>
                                <input type="text" name="nome" x-model="form.nome" required class="w-full bg-white border border-gray-300 px-4 py-2 rounded-md shadow-sm text-sm font-bold focus:border-brand-gold">
                            </div>
                            <div class="col-span-2 sm:col-span-1" x-show="form.is_kit == 0">
                                <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Estoque Físico Inicial no Galpão</label>
                                <input type="number" name="quantidade_estoque" x-model="form.quantidade_estoque" class="w-full bg-white border border-gray-300 px-4 py-2 rounded-md shadow-sm text-lg font-black text-center focus:border-brand-gold">
                            </div>
                            <div class="col-span-2 sm:col-span-1 border-t border-gray-100 pt-4 mt-2">
                                <label class="block text-[10px] font-black text-green-600 uppercase tracking-widest mb-1">Preço Cobrado (Diária R$)</label>
                                <input type="number" step="0.01" name="valor_locacao" x-model="form.valor_locacao" required class="w-full bg-white border border-green-300 px-4 py-2 rounded-md shadow-sm text-sm font-bold text-green-700 focus:border-green-500">
                            </div>
                            <div class="col-span-2 sm:col-span-1 border-t border-gray-100 pt-4 mt-2">
                                <label class="block text-[10px] font-black text-red-600 uppercase tracking-widest mb-1">Multa Jurídica por Avaria (R$)</label>
                                <input type="number" step="0.01" name="valor_reposicao" x-model="form.valor_reposicao" required class="w-full bg-white border border-red-300 px-4 py-2 rounded-md shadow-sm text-sm font-bold text-red-600 focus:border-red-500">
                            </div>
                        </div>
                        <div class="mt-6 flex justify-end gap-3 border-t border-gray-100 pt-4">
                            <button type="button" @click="aberto = false" class="px-6 py-3 bg-gray-200 text-gray-700 font-black rounded-md uppercase tracking-widest text-[10px] transition-colors hover:bg-gray-300 shadow-sm border border-gray-300">Cancelar</button>
                            <button type="submit" class="px-6 py-3 bg-brand-gold text-brand-dark font-black rounded-md shadow-md hover:bg-brand-hover uppercase tracking-widest text-[10px] transition-colors">Salvar Cadastro</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('estoqueApp', () => ({
                aberto: false, modoEdicao: false,
                form: { id: '', nome: '', is_kit: '0', quantidade_estoque: '', valor_locacao: '', valor_reposicao: '' },
                abrirNovo() { this.modoEdicao = false; this.form = { id: '', nome: '', is_kit: '0', quantidade_estoque: '', valor_locacao: '', valor_reposicao: '' }; this.aberto = true; },
                abrirEdicao(produto) { this.modoEdicao = true; this.form = { ...produto, is_kit: produto.is_kit ? '1' : '0' }; this.aberto = true; }
            }));
        });
    </script>
</x-app-layout>