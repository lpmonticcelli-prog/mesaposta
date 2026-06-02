<x-app-layout>
    <x-slot name="header">Carteira de Clientes</x-slot>

    <div class="max-w-7xl mx-auto space-y-6">
        <div class="bg-white overflow-hidden shadow-md sm:rounded-xl border border-gray-200 border-t-4 border-brand-dark">
            <div class="p-6 bg-brand-black flex justify-between items-center">
                <h4 class="text-sm font-black text-brand-gold uppercase tracking-widest">Base de Contatos Cadastrados</h4>
                <a href="{{ route('admin.pedidos.create') }}" class="px-5 py-2.5 bg-brand-gold text-brand-black text-xs font-black rounded-md hover:bg-brand-hover uppercase tracking-widest shadow-md transition-colors">
                    + Novo Evento/Cliente
                </a>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-widest">Tipo</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Nome / Razão Social</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">CPF / CNPJ</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-widest">WhatsApp</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-widest">Cidade/UF</th>
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
                                <td class="px-6 py-4 text-sm font-black text-brand-dark">{{ $cli->nome }}</td>
                                <td class="px-6 py-4 text-sm font-bold text-gray-600">{{ $cli->cpf_cnpj ?? 'Não informado' }}</td>
                                <td class="px-6 py-4 text-center text-sm font-bold text-gray-800">{{ $cli->telefone }}</td>
                                <td class="px-6 py-4 text-right text-sm font-bold text-gray-500">
                                    @if($cli->cidade && $cli->estado)
                                        {{ $cli->cidade }} - {{ $cli->estado }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-12 text-center text-xs text-gray-400 font-bold uppercase tracking-widest">Sua carteira de clientes está vazia.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($clientes->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $clientes->links() }}
                </div>
            @endif

        </div>
    </div>
</x-app-layout>