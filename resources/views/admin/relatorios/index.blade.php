<x-app-layout>
    <x-slot name="header">Inteligência de Negócio</x-slot>

    <div class="max-w-5xl mx-auto space-y-6">
        <div class="bg-white rounded-xl shadow-md border-t-4 border-brand-dark overflow-hidden">
            <div class="bg-brand-black px-6 py-4">
                <h3 class="text-brand-gold font-black uppercase tracking-widest text-sm">Demonstração do Resultado do Exercício (DRE)</h3>
            </div>
            
            <form action="{{ route('admin.relatorios.fechamento') }}" method="POST" target="_blank" class="p-6 md:p-8 space-y-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-black text-gray-500 uppercase tracking-widest mb-2">Data Inicial (Pagamento/Evento)</label>
                        <input type="date" name="data_inicio" value="{{ now()->startOfMonth()->toDateString() }}" required class="w-full rounded border-gray-300 focus:border-brand-gold font-bold text-gray-800">
                    </div>
                    <div>
                        <label class="block text-xs font-black text-gray-500 uppercase tracking-widest mb-2">Data Final (Pagamento/Evento)</label>
                        <input type="date" name="data_fim" value="{{ now()->endOfMonth()->toDateString() }}" required class="w-full rounded border-gray-300 focus:border-brand-gold font-bold text-gray-800">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-black text-gray-500 uppercase tracking-widest mb-2">Filtro por Cliente / Parceiro Comercial</label>
                    <select name="cliente_id" class="w-full rounded border-gray-300 focus:border-brand-gold font-bold text-brand-dark bg-gray-50 p-3">
                        <option value="">-- Todos os Clientes (Visão Global da Empresa) --</option>
                        @foreach($clientes as $cliente)
                            <option value="{{ $cliente->id }}">{{ $cliente->nome }} ({{ $cliente->cpf_cnpj ?? 'Sem doc' }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="pt-4 border-t border-gray-100 flex justify-end">
                    <button type="submit" class="w-full md:w-auto py-3 px-8 bg-brand-gold text-brand-black font-black uppercase tracking-widest rounded hover:bg-brand-hover transition-colors shadow-lg flex items-center justify-center">
                        🖨️ Processar e Gerar DRE Analítico
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>