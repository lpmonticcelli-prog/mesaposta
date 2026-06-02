<x-app-layout>
    <x-slot name="header">
        <h2 class="font-extrabold text-2xl text-brand-dark leading-tight tracking-tight uppercase">
            {{ __('Acervo e Inventário') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen" x-data="estoqueApp()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="mb-6 flex justify-between items-center">
                <p class="text-sm text-gray-500 font-bold uppercase tracking-widest hidden sm:block">Controle Físico do Galpão</p>
                <button @click="abrirNovo()" class="w-full sm:w-auto px-5 py-3 bg-brand-gold text-brand-dark text-sm font-extrabold rounded-md shadow-md hover:bg-brand-gold-hover transition-colors uppercase tracking-wide flex justify-center items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Cadastrar Nova Peça
                </button>
            </div>

            @if(session('success'))
                <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg font-bold shadow-sm">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg font-bold shadow-sm">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-xl border border-gray-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-brand-dark">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-brand-gold uppercase tracking-widest">Produto</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-brand-gold uppercase tracking-widest">Estoque Físico</th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-brand-gold uppercase tracking-widest">Diária (R$)</th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-brand-gold uppercase tracking-widest">Multa Reposição (R$)</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-brand-gold uppercase tracking-widest">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($produtos as $p)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-5 whitespace-nowrap">
                                        <div class="text-sm font-extrabold text-brand-dark uppercase">{{ $p->nome }}</div>
                                        <div class="text-xs text-gray-500 font-bold uppercase tracking-wider mt-1">{{ $p->categoria ?? 'GERAL' }}</div>
                                    </td>
                                    <td class="px-6 py-5 whitespace-nowrap text-center">
                                        <span class="px-4 py-1.5 bg-gray-100 border border-gray-300 text-brand-dark font-extrabold rounded-md shadow-inner">{{ $p->quantidade_estoque }}</span>
                                    </td>
                                    <td class="px-6 py-5 whitespace-nowrap text-right text-sm font-bold text-green-700">
                                        R$ {{ number_format($p->valor_locacao, 2, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-5 whitespace-nowrap text-right text-sm font-bold text-red-600">
                                        R$ {{ number_format($p->valor_reposicao, 2, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-5 whitespace-nowrap text-center text-sm font-medium">
                                        <button @click="abrirEdicao({{ $p->toJson() }})" class="text-brand-dark hover:text-brand-gold font-extrabold uppercase tracking-widest transition-colors mr-4">
                                            Editar
                                        </button>
                                        <form action="{{ route('admin.produtos.destroy', $p->id) }}" method="POST" class="inline-block" onsubmit="return confirm('ATENÇÃO: Deseja apagar este material do catálogo permanentemente?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 font-extrabold uppercase tracking-widest transition-colors">Apagar</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500 text-sm font-medium">Nenhuma peça cadastrada no acervo. Adicione produtos para gerar orçamentos.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($produtos->hasPages())
                    <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                        {{ $produtos->links() }}
                    </div>
                @endif
            </div>
        </div>

        <div x-show="aberto" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-70 backdrop-blur-sm transition-opacity" style="display: none;" x-transition>
            <div @click.away="aberto = false" class="bg-white rounded-xl shadow-2xl w-full max-w-2xl overflow-hidden mx-4 transform transition-all border-t-4 border-brand-gold">
                <div class="bg-brand-dark px-6 py-4 flex justify-between items-center">
                    <h3 class="text-brand-gold font-extrabold uppercase tracking-widest" x-text="modoEdicao ? 'Editar Peça' : 'Nova Peça no Acervo'"></h3>
                    <button @click="aberto = false" type="button" class="text-gray-400 hover:text-white text-2xl font-bold">&times;</button>
                </div>
                
                <form :action="formAction()" method="POST" class="p-6 space-y-5">
                    @csrf
                    <template x-if="modoEdicao">
                        <input type="hidden" name="_method" value="PUT">
                    </template>
                    
                    <div class="grid grid-cols-2 gap-5">
                        <div class="col-span-2">
                            <label class="block text-xs font-extrabold text-brand-dark uppercase tracking-wider mb-1">Nome da Peça (Ex: Cadeira Dior Ouro)</label>
                            <input type="text" name="nome" x-model="form.nome" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-gold focus:ring focus:ring-brand-gold font-medium">
                        </div>
                        <div class="col-span-2 sm:col-span-1">
                            <label class="block text-xs font-extrabold text-brand-dark uppercase tracking-wider mb-1">Categoria</label>
                            <input type="text" name="categoria" x-model="form.categoria" placeholder="Ex: Móveis, Louças" class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-gold focus:ring focus:ring-brand-gold font-medium">
                        </div>
                        <div class="col-span-2 sm:col-span-1">
                            <label class="block text-xs font-extrabold text-brand-dark uppercase tracking-wider mb-1">Estoque Físico Total</label>
                            <input type="number" name="quantidade_estoque" x-model="form.quantidade_estoque" required min="0" class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-gold focus:ring focus:ring-brand-gold font-bold bg-gray-50 text-lg">
                        </div>
                        <div class="col-span-2 sm:col-span-1">
                            <label class="block text-xs font-extrabold text-green-700 uppercase tracking-wider mb-1">Diária de Locação (R$)</label>
                            <input type="number" step="0.01" name="valor_locacao" x-model="form.valor_locacao" required min="0" class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 font-bold text-green-700">
                        </div>
                        <div class="col-span-2 sm:col-span-1">
                            <label class="block text-xs font-extrabold text-red-600 uppercase tracking-wider mb-1">Multa por Quebra (R$)</label>
                            <input type="number" step="0.01" name="valor_reposicao" x-model="form.valor_reposicao" required min="0" class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 font-bold text-red-600">
                        </div>
                    </div>
                    
                    <div class="mt-8 flex justify-end space-x-3 border-t border-gray-100 pt-5">
                        <button type="button" @click="aberto = false" class="px-6 py-3 bg-gray-100 text-gray-700 font-extrabold rounded-md hover:bg-gray-200 uppercase tracking-widest transition-colors text-sm">
                            Cancelar
                        </button>
                        <button type="submit" class="px-6 py-3 bg-brand-gold text-brand-dark font-extrabold rounded-md hover:bg-brand-gold-hover shadow-md uppercase tracking-widest transition-colors text-sm">
                            Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('estoqueApp', () => ({
                aberto: false,
                modoEdicao: false,
                form: {
                    id: '', nome: '', categoria: '', quantidade_estoque: '', valor_locacao: '', valor_reposicao: ''
                },
                abrirNovo() {
                    this.modoEdicao = false;
                    this.form = { id: '', nome: '', categoria: '', quantidade_estoque: '', valor_locacao: '', valor_reposicao: '' };
                    this.aberto = true;
                },
                abrirEdicao(produto) {
                    this.modoEdicao = true;
                    this.form = { ...produto };
                    this.aberto = true;
                },
                formAction() {
                    return this.modoEdicao ? `/admin/produtos/${this.form.id}` : '{{ route("admin.produtos.store") }}';
                }
            }));
        });
    </script>
</x-app-layout>