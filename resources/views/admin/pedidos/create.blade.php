<x-app-layout>
    <x-slot name="header">Abertura de O.S. (Matriz Logística)</x-slot>

    <div class="max-w-6xl mx-auto space-y-6 py-6">
        @if(session('error')) <div class="p-4 bg-red-100 border-l-4 border-red-500 text-red-800 font-bold shadow-sm rounded mb-4">{{ session('error') }}</div> @endif
        
        <form action="{{ route('admin.pedidos.store') }}" method="POST" class="bg-white rounded-xl shadow-md border-t-4 border-brand-gold overflow-hidden">
            @csrf
            
            <div class="bg-brand-black px-6 py-4 flex justify-between items-center">
                <h3 class="text-brand-gold font-black uppercase tracking-widest text-sm">Geração de Contrato Comercial</h3>
                <a href="{{ route('admin.pedidos.index') }}" class="text-[10px] font-bold bg-gray-800 text-gray-300 px-3 py-1.5 rounded uppercase hover:bg-brand-gold hover:text-brand-dark transition-colors">Voltar ao Painel</a>
            </div>

            <div class="p-6 md:p-8 space-y-8 bg-gray-50">
                
                {{-- 1. IDENTIFICAÇÃO DO CLIENTE --}}
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                    <h4 class="text-xs font-black text-brand-dark uppercase tracking-widest border-b-2 border-brand-gold pb-2 mb-4">1. Dados do Contratante</h4>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div><label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Tipo</label><select name="tipo_pessoa" class="w-full bg-white border border-gray-300 rounded-md px-3 py-2 shadow-sm text-sm focus:border-brand-gold font-bold"><option value="PF">Física (PF)</option><option value="PJ">Jurídica (PJ)</option></select></div>
                        <div><label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">CPF/CNPJ (Busca Auto)</label><input type="text" name="cpf_cnpj" id="cpf_cnpj" placeholder="Apenas números..." class="w-full bg-white border border-gray-300 rounded-md px-3 py-2 shadow-sm text-sm focus:border-brand-gold font-bold"></div>
                        <div class="md:col-span-2"><label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Nome Completo / Razão Social</label><input type="text" name="cliente_nome" id="cliente_nome" required class="w-full bg-white border border-gray-300 rounded-md px-3 py-2 shadow-sm text-sm font-bold text-brand-dark focus:border-brand-gold"></div>
                        <div><label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">RG / Insc. Estadual</label><input type="text" name="rg_ie" class="w-full bg-white border border-gray-300 rounded-md px-3 py-2 shadow-sm text-sm focus:border-brand-gold"></div>
                        <div><label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">WhatsApp</label><input type="text" name="cliente_telefone" required class="w-full bg-white border border-gray-300 rounded-md px-3 py-2 shadow-sm text-sm font-bold focus:border-brand-gold"></div>
                        <div class="md:col-span-2"><label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">E-mail</label><input type="email" name="email" id="email" class="w-full bg-white border border-gray-300 rounded-md px-3 py-2 shadow-sm text-sm focus:border-brand-gold"></div>
                    </div>
                </div>

                {{-- 2. MATRIZ LOGÍSTICA E PAGAMENTO --}}
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                    <h4 class="text-xs font-black text-brand-dark uppercase tracking-widest border-b-2 border-brand-dark pb-2 mb-4">2. Matriz de Tempo Logístico e Finanças</h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 bg-gray-100 border border-gray-300 p-4 rounded-md mb-6">
                        <div><label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Locação (Fechamento)</label><input type="date" name="data_locacao" value="{{ date('Y-m-d') }}" required class="w-full bg-white border border-gray-300 rounded-md px-3 py-2 shadow-sm text-sm font-bold text-gray-700"></div>
                        <div><label class="block text-[10px] font-black text-blue-600 uppercase tracking-widest mb-1">1. Saída (Entrega) *</label><input type="date" name="data_entrega" required class="w-full bg-blue-50 border border-blue-400 rounded-md px-3 py-2 shadow-sm text-sm font-black text-blue-900 focus:border-blue-600"></div>
                        <div><label class="block text-[10px] font-black text-brand-gold uppercase tracking-widest mb-1">2. Festa (Evento) *</label><input type="date" name="data_evento" required class="w-full bg-yellow-50 border border-brand-gold rounded-md px-3 py-2 shadow-sm text-sm font-black text-brand-dark focus:border-yellow-600"></div>
                        <div><label class="block text-[10px] font-black text-red-600 uppercase tracking-widest mb-1">3. Retorno (Devolucão) *</label><input type="date" name="data_devolucao" required class="w-full bg-red-50 border border-red-400 rounded-md px-3 py-2 shadow-sm text-sm font-black text-red-900 focus:border-red-600"></div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-black text-green-700 uppercase tracking-widest mb-1">Forma de Pagamento</label>
                            <select name="forma_pagamento" required class="w-full bg-white border border-green-300 rounded-md px-3 py-2 shadow-sm text-sm font-bold text-green-900 focus:border-green-500">
                                <option value="A COMBINAR">A Combinar</option>
                                <option value="PIX">Pix / Transferência</option>
                                <option value="DINHEIRO">Dinheiro (Espécie)</option>
                                <option value="CARTÃO (DÉBITO E CRÉDITO)">Cartão (Débito e Crédito)</option>
                                <option value="BOLETO À VISTA">Boleto à Vista</option>
                                <option value="BOLETO PARCELADO">Boleto Parcelado</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-red-600 uppercase tracking-widest mb-1">Ação de Estoque Inicial <span class="text-red-500">*</span></label>
                            <select name="status" required class="w-full bg-red-50 border border-red-300 rounded-md px-3 py-2 shadow-sm text-sm font-black text-red-700 focus:border-red-500">
                                <option value="orcamento">Salvar como Orçamento (Não bloqueia galpão)</option>
                                <option value="confirmado">Confirmar OS e BLOQUEAR peças agora</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- 3. LOCAL DA FESTA --}}
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                    <h4 class="text-xs font-black text-brand-dark uppercase tracking-widest border-b-2 border-gray-200 pb-2 mb-4">3. Local de Entrega</h4>
                    <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                        <div class="md:col-span-2"><label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">CEP Entrega (Busca Auto)</label><input type="text" name="cep_entrega" id="cep_entrega" onblur="buscaCep(this.value, 'entrega_')" class="w-full bg-white border border-gray-300 rounded-md px-3 py-2 shadow-sm text-sm font-bold"></div>
                        <div class="md:col-span-3"><label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Endereço da Festa</label><input type="text" name="endereco_entrega" id="endereco_entrega" class="w-full bg-gray-50 border border-gray-300 rounded-md px-3 py-2 shadow-sm text-sm"></div>
                        <div class="md:col-span-1"><label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Nº</label><input type="text" name="numero_entrega" class="w-full bg-white border border-gray-300 rounded-md px-3 py-2 shadow-sm text-sm"></div>
                        <div class="md:col-span-2"><label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Complemento / Salão</label><input type="text" name="complemento_entrega" class="w-full bg-white border border-gray-300 rounded-md px-3 py-2 shadow-sm text-sm"></div>
                        <div class="md:col-span-2"><label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Bairro</label><input type="text" name="bairro_entrega" id="bairro_entrega" class="w-full bg-gray-50 border border-gray-300 rounded-md px-3 py-2 shadow-sm text-sm"></div>
                        <div class="md:col-span-1"><label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Cidade</label><input type="text" name="cidade_entrega" id="cidade_entrega" class="w-full bg-gray-50 border border-gray-300 rounded-md px-3 py-2 shadow-sm text-sm"></div>
                        <div class="md:col-span-1"><label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">UF</label><input type="text" name="estado_entrega" id="estado_entrega" maxlength="2" class="w-full bg-gray-50 border border-gray-300 rounded-md px-3 py-2 shadow-sm text-sm uppercase"></div>
                        <div class="md:col-span-6 mt-2"><label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Anotações da Logística (Visível na Via de Separação)</label><textarea name="observacoes" rows="2" class="w-full bg-white border border-gray-300 rounded-md px-3 py-2 shadow-sm text-sm"></textarea></div>
                    </div>
                </div>

                <div class="flex justify-end pt-4">
                    <button type="submit" class="px-8 py-4 bg-brand-gold text-brand-black font-black rounded-md hover:bg-brand-hover shadow-xl uppercase tracking-widest text-sm flex items-center">
                        Salvar e Abrir Carrinho de Peças &rarr;
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script>
        document.getElementById('cpf_cnpj').addEventListener('blur', function(e) {
            let val = e.target.value.replace(/\D/g, ''); 
            if (val.length === 14) {
                document.body.style.cursor = 'wait';
                fetch(`https://brasilapi.com.br/api/cnpj/v1/${val}`).then(res => res.json()).then(data => {
                    if (data.razao_social) { document.getElementById('cliente_nome').value = data.razao_social; }
                }).finally(() => document.body.style.cursor = 'default');
            }
        });
        function buscaCep(cep, prefix) {
            let val = cep.replace(/\D/g, '');
            if (val.length === 8) {
                document.body.style.cursor = 'wait';
                fetch(`https://viacep.com.br/ws/${val}/json/`).then(res => res.json()).then(data => {
                    if (!data.erro) {
                        document.getElementById(prefix+'endereco_entrega').value = data.logradouro || '';
                        document.getElementById(prefix+'bairro_entrega').value = data.bairro || '';
                        document.getElementById(prefix+'cidade_entrega').value = data.localidade || '';
                        document.getElementById(prefix+'estado_entrega').value = data.uf || '';
                    }
                }).finally(() => document.body.style.cursor = 'default');
            }
        }
    </script>
</x-app-layout>