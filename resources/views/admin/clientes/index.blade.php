<x-app-layout>
    <x-slot name="header">Carteira de Clientes (CRM)</x-slot>

    <div class="max-w-7xl mx-auto space-y-6 pb-10" x-data="{ modalAberto: false, clienteEdit: {} }">
        
        @if(session('success')) <div class="p-4 bg-green-100 border-l-4 border-green-500 text-green-800 font-bold shadow-sm rounded-md">{{ session('success') }}</div> @endif
        @if(session('error')) <div class="p-4 bg-red-100 border-l-4 border-red-500 text-red-800 font-bold shadow-sm rounded-md">{{ session('error') }}</div> @endif

        {{-- SMART TOOLBAR HÍBRIDA DE BUSCA (Substituiu a barra antiga) --}}
        <div class="bg-white p-6 rounded-xl shadow-sm border-t-4 border-brand-gold flex flex-col md:flex-row gap-4 items-end justify-between">
            <form action="{{ route('admin.clientes.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3 w-full md:w-auto items-end">
                <div>
                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Busca Rápida Híbrida</label>
                    <input type="text" name="busca" value="{{ request('busca') }}" placeholder="Nome, CNPJ, Tel ou E-mail..." class="bg-gray-50 border border-gray-300 px-4 py-2.5 rounded-md font-bold text-sm focus:border-brand-gold w-full sm:w-80 shadow-sm">
                </div>
                <button type="submit" class="bg-brand-gold text-brand-dark px-6 py-2.5 font-black uppercase text-[10px] tracking-widest rounded-md hover:bg-brand-hover shadow-sm h-[42px] transition-colors">🔍 Pesquisar</button>
                <a href="{{ route('admin.clientes.index') }}" class="px-4 py-2.5 bg-gray-200 text-gray-700 font-black rounded-md uppercase text-[10px] shadow-sm flex items-center justify-center h-[42px] hover:bg-gray-300 transition-colors">Limpar</a>
            </form>
            <a href="{{ route('admin.pedidos.create') }}" class="px-6 py-2.5 bg-brand-dark text-brand-gold font-black uppercase text-[10px] tracking-widest rounded-md shadow-md hover:bg-black whitespace-nowrap h-[42px] flex items-center transition-colors">
                + Novo Evento
            </a>
        </div>

        <div class="bg-white overflow-hidden shadow-md sm:rounded-xl border border-gray-200">
            <div class="p-4 bg-brand-black">
                <h4 class="text-sm font-black text-brand-gold uppercase tracking-widest">Base de Clientes Ativos</h4>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-center text-[10px] font-black text-gray-500 uppercase tracking-widest">Tipo</th>
                            <th class="px-6 py-4 text-left text-[10px] font-black text-gray-500 uppercase tracking-widest">Nome / Razão Social</th>
                            <th class="px-6 py-4 text-left text-[10px] font-black text-gray-500 uppercase tracking-widest">Documento</th>
                            <th class="px-6 py-4 text-center text-[10px] font-black text-gray-500 uppercase tracking-widest">WhatsApp</th>
                            <th class="px-6 py-4 text-right text-[10px] font-black text-gray-500 uppercase tracking-widest">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse ($clientes as $cli)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 text-center">
                                    <span class="px-3 py-1 text-[10px] font-black rounded uppercase tracking-widest {{ $cli->tipo_pessoa === 'PJ' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-700' }}">
                                        {{ $cli->tipo_pessoa }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm font-black text-brand-dark uppercase">{{ $cli->nome }}</td>
                                <td class="px-6 py-4 text-sm font-bold text-gray-600">{{ $cli->cpf_cnpj ?? 'Não Informado' }}</td>
                                <td class="px-6 py-4 text-center text-sm font-bold text-gray-800">{{ $cli->telefone }}</td>
                                <td class="px-6 py-4 text-right flex justify-end gap-2">
                                    <button @click="clienteEdit = {{ json_encode($cli) }}; modalAberto = true" class="px-3 py-1.5 bg-gray-800 text-brand-gold text-[10px] font-black rounded uppercase hover:bg-black shadow-sm transition-colors border border-gray-900">Editar</button>
                                    <form action="{{ route('admin.clientes.destroy', $cli->id) }}" method="POST" onsubmit="return confirm('ATENÇÃO: Deseja excluir permanentemente este cliente?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="px-3 py-1.5 bg-red-50 text-red-600 text-[10px] font-black rounded uppercase hover:bg-red-600 hover:text-white shadow-sm transition-colors border border-red-200">Excluir</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-12 text-center text-xs text-gray-400 font-bold uppercase tracking-widest">Sua carteira de clientes está vazia ou a busca não encontrou resultados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($clientes->hasPages()) <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">{{ $clientes->links() }}</div> @endif
        </div>

        {{-- MODAL DE EDIÇÃO COM URL() ABSOLUTA --}}
        <div x-cloak x-show="modalAberto" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-70 backdrop-blur-sm transition-opacity px-4">
            <div @click.away="modalAberto = false" class="bg-white rounded-xl shadow-2xl w-full max-w-3xl overflow-hidden border-t-4 border-brand-gold flex flex-col max-h-[90vh]">
                <div class="bg-brand-dark px-6 py-4 flex justify-between items-center shrink-0">
                    <h3 class="text-brand-gold font-black uppercase tracking-widest text-sm">Atualizar Ficha Cadastral</h3>
                    <button @click="modalAberto = false" type="button" class="text-gray-400 hover:text-white text-2xl font-bold">&times;</button>
                </div>
                
                <div class="overflow-y-auto p-6 md:p-8 flex-1 bg-gray-50">
                    <form :action="`{{ url('admin/clientes') }}/${clienteEdit.id}`" method="POST" class="space-y-4">
                        @csrf @method('PUT')
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="md:col-span-2">
                                <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Nome / Razão Social</label>
                                <input type="text" name="nome" x-model="clienteEdit.nome" required class="w-full bg-white border border-gray-300 px-4 py-2 rounded-md shadow-sm text-sm font-bold text-gray-900 focus:border-brand-gold focus:ring-1 focus:ring-brand-gold">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Tipo de Pessoa</label>
                                <select name="tipo_pessoa" x-model="clienteEdit.tipo_pessoa" class="w-full bg-white border border-gray-300 px-4 py-2 rounded-md shadow-sm text-sm font-bold text-gray-900 focus:border-brand-gold focus:ring-1 focus:ring-brand-gold">
                                    <option value="PF">Pessoa Física (PF)</option><option value="PJ">Pessoa Jurídica (PJ)</option>
                                </select>
                            </div>
                            <div><label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">CPF / CNPJ</label><input type="text" name="cpf_cnpj" x-model="clienteEdit.cpf_cnpj" class="w-full bg-white border border-gray-300 px-4 py-2 rounded-md shadow-sm text-sm font-bold text-gray-900 focus:border-brand-gold focus:ring-1 focus:ring-brand-gold"></div>
                            <div><label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">RG / Insc. Estadual</label><input type="text" name="rg_ie" x-model="clienteEdit.rg_ie" class="w-full bg-white border border-gray-300 px-4 py-2 rounded-md shadow-sm text-sm font-bold text-gray-900 focus:border-brand-gold focus:ring-1 focus:ring-brand-gold"></div>
                            <div><label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">WhatsApp / Telefone</label><input type="text" name="telefone" x-model="clienteEdit.telefone" required class="w-full bg-white border border-gray-300 px-4 py-2 rounded-md shadow-sm text-sm font-bold text-gray-900 focus:border-brand-gold focus:ring-1 focus:ring-brand-gold"></div>
                            <div class="md:col-span-2"><label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">E-mail</label><input type="email" name="email" x-model="clienteEdit.email" class="w-full bg-white border border-gray-300 px-4 py-2 rounded-md shadow-sm text-sm font-bold text-gray-900 focus:border-brand-gold focus:ring-1 focus:ring-brand-gold"></div>
                            
                            <div class="md:col-span-2 border-t border-gray-100 pt-4 mt-2"><h4 class="text-xs font-black text-brand-dark uppercase tracking-widest mb-4">Dados de Endereço</h4></div>
                            
                            <div><label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">CEP</label><input type="text" name="cep" x-model="clienteEdit.cep" class="w-full bg-white border border-gray-300 px-4 py-2 rounded-md shadow-sm text-sm font-bold text-gray-900 focus:border-brand-gold focus:ring-1 focus:ring-brand-gold"></div>
                            <div><label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Bairro</label><input type="text" name="bairro" x-model="clienteEdit.bairro" class="w-full bg-white border border-gray-300 px-4 py-2 rounded-md shadow-sm text-sm font-bold text-gray-900 focus:border-brand-gold focus:ring-1 focus:ring-brand-gold"></div>
                            <div class="md:col-span-2"><label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Endereço (Rua, Av.)</label><input type="text" name="endereco" x-model="clienteEdit.endereco" class="w-full bg-white border border-gray-300 px-4 py-2 rounded-md shadow-sm text-sm font-bold text-gray-900 focus:border-brand-gold focus:ring-1 focus:ring-brand-gold"></div>
                            <div class="grid grid-cols-2 gap-4 md:col-span-2">
                                <div><label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Número</label><input type="text" name="numero" x-model="clienteEdit.numero" class="w-full bg-white border border-gray-300 px-4 py-2 rounded-md shadow-sm text-sm font-bold text-gray-900 focus:border-brand-gold focus:ring-1 focus:ring-brand-gold"></div>
                                <div><label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Estado (UF)</label><input type="text" name="estado" x-model="clienteEdit.estado" maxlength="2" class="w-full bg-white border border-gray-300 px-4 py-2 rounded-md shadow-sm text-sm font-bold text-gray-900 focus:border-brand-gold focus:ring-1 focus:ring-brand-gold uppercase"></div>
                            </div>
                            <div class="grid grid-cols-2 gap-4 md:col-span-2">
                                <div><label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Complemento</label><input type="text" name="complemento" x-model="clienteEdit.complemento" class="w-full bg-white border border-gray-300 px-4 py-2 rounded-md shadow-sm text-sm font-bold text-gray-900 focus:border-brand-gold focus:ring-1 focus:ring-brand-gold"></div>
                                <div><label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Cidade</label><input type="text" name="cidade" x-model="clienteEdit.cidade" class="w-full bg-white border border-gray-300 px-4 py-2 rounded-md shadow-sm text-sm font-bold text-gray-900 focus:border-brand-gold focus:ring-1 focus:ring-brand-gold"></div>
                            </div>
                        </div>
                        <div class="mt-8 flex justify-end gap-3 pt-5 shrink-0 border-t border-gray-200">
                            <button type="button" @click="modalAberto = false" class="px-6 py-3 bg-gray-200 text-gray-700 font-black rounded-md uppercase tracking-widest text-[10px] transition-colors hover:bg-gray-300 shadow-sm border border-gray-300">Cancelar</button>
                            <button type="submit" class="px-6 py-3 bg-brand-gold text-brand-dark font-black rounded-md shadow-md hover:bg-brand-hover uppercase tracking-widest text-[10px] transition-colors">Salvar Alterações</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>