<x-app-layout>
    <x-slot name="header">Nova Abertura de OS</x-slot>

    <div class="max-w-5xl mx-auto space-y-6">
        <form action="{{ route('admin.pedidos.store') }}" method="POST" class="bg-white rounded-xl shadow-md border-t-4 border-brand-gold overflow-hidden">
            @csrf
            
            <div class="bg-brand-black px-6 py-4 flex justify-between items-center">
                <h3 class="text-brand-gold font-black uppercase tracking-widest text-sm">Abertura Rápida de Evento</h3>
                <a href="{{ route('admin.pedidos.index') }}" class="text-[10px] font-bold bg-gray-800 text-gray-300 px-3 py-1.5 rounded uppercase hover:bg-brand-gold hover:text-brand-dark transition-colors">Voltar</a>
            </div>

            <div class="p-6 md:p-8 space-y-8">
                
                <div>
                    <h4 class="text-xs font-black text-brand-dark uppercase tracking-widest border-b-2 border-brand-gold pb-2 mb-4">1. Dados do Cliente</h4>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Tipo de Cliente</label>
                            <select name="tipo_pessoa" class="w-full rounded text-sm border-gray-300 focus:border-brand-gold focus:ring focus:ring-brand-gold font-bold">
                                <option value="PF">Pessoa Física (PF)</option>
                                <option value="PJ">Pessoa Jurídica (PJ)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">CPF / CNPJ (Busca Automática)</label>
                            <input type="text" name="cpf_cnpj" id="cpf_cnpj" placeholder="Apenas números" class="w-full rounded text-sm border-gray-300 focus:border-brand-gold focus:ring focus:ring-brand-gold font-bold">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Nome Completo / Razão Social</label>
                            <input type="text" name="cliente_nome" id="cliente_nome" required class="w-full rounded text-sm border-gray-300 focus:border-brand-gold focus:ring focus:ring-brand-gold font-bold text-brand-dark">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">RG / Inscrição Estadual</label>
                            <input type="text" name="rg_ie" id="rg_ie" class="w-full rounded text-sm border-gray-300 focus:border-brand-gold focus:ring focus:ring-brand-gold">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">WhatsApp / Telefone</label>
                            <input type="text" name="cliente_telefone" required class="w-full rounded text-sm border-gray-300 focus:border-brand-gold focus:ring focus:ring-brand-gold font-bold">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">E-mail</label>
                            <input type="email" name="email" id="email" class="w-full rounded text-sm border-gray-300 focus:border-brand-gold focus:ring focus:ring-brand-gold">
                        </div>
                    </div>
                </div>

                <div>
                    <h4 class="text-xs font-black text-brand-dark uppercase tracking-widest border-b-2 border-gray-200 pb-2 mb-4">2. Endereço Residencial/Faturamento</h4>
                    <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">CEP (Busca Automática)</label>
                            <input type="text" name="cep" id="cep" onblur="buscaCep(this.value, '')" placeholder="00000000" class="w-full rounded text-sm border-gray-300 focus:border-brand-gold focus:ring focus:ring-brand-gold font-bold">
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-[10px] font-gray-500 text-gray-500 uppercase tracking-widest mb-1">Rua / Logradouro</label>
                            <input type="text" name="endereco" id="endereco" class="w-full rounded text-sm border-gray-300 focus:border-brand-gold focus:ring focus:ring-brand-gold bg-gray-50">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Nº</label>
                            <input type="text" name="numero" id="numero" class="w-full rounded text-sm border-gray-300 focus:border-brand-gold focus:ring focus:ring-brand-gold">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Complemento</label>
                            <input type="text" name="complemento" id="complemento" class="w-full rounded text-sm border-gray-300 focus:border-brand-gold focus:ring focus:ring-brand-gold">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-[10px] font-gray-500 text-gray-500 uppercase tracking-widest mb-1">Bairro</label>
                            <input type="text" name="bairro" id="bairro" class="w-full rounded text-sm border-gray-300 focus:border-brand-gold focus:ring focus:ring-brand-gold bg-gray-50">
                        </div>
                        <div class="md:col-span-1">
                            <label class="block text-[10px] font-gray-500 text-gray-500 uppercase tracking-widest mb-1">Cidade</label>
                            <input type="text" name="cidade" id="cidade" class="w-full rounded text-sm border-gray-300 focus:border-brand-gold focus:ring focus:ring-brand-gold bg-gray-50">
                        </div>
                        <div class="md:col-span-1">
                            <label class="block text-[10px] font-gray-500 text-gray-500 uppercase tracking-widest mb-1">UF</label>
                            <input type="text" name="estado" id="estado" class="w-full rounded text-sm border-gray-300 focus:border-brand-gold focus:ring focus:ring-brand-gold bg-gray-50">
                        </div>
                    </div>
                </div>

                <div>
                    <h4 class="text-xs font-black text-brand-dark uppercase tracking-widest border-b-2 border-brand-dark pb-2 mb-4">3. Logística e Local do Evento</h4>
                    <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                        <div class="md:col-span-3">
                            <label class="block text-[10px] font-black text-brand-dark uppercase tracking-widest mb-1">Data do Evento</label>
                            <input type="date" name="data_evento" required class="w-full rounded text-sm border-gray-300 focus:border-brand-gold focus:ring focus:ring-brand-gold font-black text-brand-dark">
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-[10px] font-black text-red-600 uppercase tracking-widest mb-1">Ação de Estoque Inicial</label>
                            <select name="status" required class="w-full rounded text-sm border-red-300 focus:border-red-500 focus:ring focus:ring-red-500 font-black text-red-700 bg-red-50">
                                <option value="orcamento">Salvar como Orçamento (Estoque Livre)</option>
                                <option value="confirmado">Confirmar OS AGORA (Bloquear Estoque)</option>
                            </select>
                        </div>
                        
                        <div class="md:col-span-2 mt-4">
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">CEP do Evento</label>
                            <input type="text" name="cep_entrega" id="cep_entrega" onblur="buscaCep(this.value, 'entrega_')" placeholder="00000000" class="w-full rounded text-sm border-gray-300 focus:border-brand-gold focus:ring focus:ring-brand-gold font-bold">
                        </div>
                        <div class="md:col-span-3 mt-4">
                            <label class="block text-[10px] font-gray-500 text-gray-500 uppercase tracking-widest mb-1">Local / Endereço da Festa</label>
                            <input type="text" name="endereco_entrega" id="endereco_entrega" class="w-full rounded text-sm border-gray-300 focus:border-brand-gold focus:ring focus:ring-brand-gold bg-gray-50">
                        </div>
                        <div class="md:col-span-1 mt-4">
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Nº</label>
                            <input type="text" name="numero_entrega" id="numero_entrega" class="w-full rounded text-sm border-gray-300 focus:border-brand-gold focus:ring focus:ring-brand-gold">
                        </div>
                        
                        <div class="md:col-span-2">
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Complemento / Nome do Salão</label>
                            <input type="text" name="complemento_entrega" id="complemento_entrega" class="w-full rounded text-sm border-gray-300 focus:border-brand-gold focus:ring focus:ring-brand-gold">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-[10px] font-gray-500 text-gray-500 uppercase tracking-widest mb-1">Bairro</label>
                            <input type="text" name="bairro_entrega" id="bairro_entrega" class="w-full rounded text-sm border-gray-300 focus:border-brand-gold focus:ring focus:ring-brand-gold bg-gray-50">
                        </div>
                        <div class="md:col-span-1">
                            <label class="block text-[10px] font-gray-500 text-gray-500 uppercase tracking-widest mb-1">Cidade</label>
                            <input type="text" name="cidade_entrega" id="cidade_entrega" class="w-full rounded text-sm border-gray-300 focus:border-brand-gold focus:ring focus:ring-brand-gold bg-gray-50">
                        </div>
                        <div class="md:col-span-1">
                            <label class="block text-[10px] font-gray-500 text-gray-500 uppercase tracking-widest mb-1">UF</label>
                            <input type="text" name="estado_entrega" id="estado_entrega" class="w-full rounded text-sm border-gray-300 focus:border-brand-gold focus:ring focus:ring-brand-gold bg-gray-50">
                        </div>

                        <div class="md:col-span-6 mt-2">
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Anotações Internas Logísticas (Ponto de referência, horários, etc)</label>
                            <textarea name="observacoes" rows="2" class="w-full rounded text-sm border-gray-300 focus:border-brand-gold focus:ring focus:ring-brand-gold"></textarea>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-4 border-t border-gray-100">
                    <button type="submit" class="px-8 py-4 bg-brand-gold text-brand-black font-black rounded hover:bg-brand-hover transition-colors shadow-xl uppercase tracking-widest text-sm flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        Salvar e Montar Peças
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script>
        // Busca de CNPJ
        document.getElementById('cpf_cnpj').addEventListener('blur', function(e) {
            let val = e.target.value.replace(/\D/g, ''); 
            if (val.length === 14) {
                document.body.style.cursor = 'wait';
                fetch(`https://brasilapi.com.br/api/cnpj/v1/${val}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.razao_social) {
                            document.getElementById('cliente_nome').value = data.razao_social;
                            document.getElementById('cep').value = data.cep || '';
                            document.getElementById('endereco').value = data.logradouro || '';
                            document.getElementById('numero').value = data.numero || '';
                            document.getElementById('complemento').value = data.complemento || '';
                            document.getElementById('bairro').value = data.bairro || '';
                            document.getElementById('cidade').value = data.municipio || '';
                            document.getElementById('estado').value = data.uf || '';
                        }
                    })
                    .finally(() => document.body.style.cursor = 'default');
            }
        });

        // Busca de CEP Dinâmico
        function buscaCep(cep, prefix) {
            let val = cep.replace(/\D/g, '');
            if (val.length === 8) {
                document.body.style.cursor = 'wait';
                fetch(`https://viacep.com.br/ws/${val}/json/`)
                    .then(res => res.json())
                    .then(data => {
                        if (!data.erro) {
                            let idEndereco = prefix === 'entrega_' ? 'endereco_entrega' : 'endereco';
                            let idBairro = prefix === 'entrega_' ? 'bairro_entrega' : 'bairro';
                            let idCidade = prefix === 'entrega_' ? 'cidade_entrega' : 'cidade';
                            let idEstado = prefix === 'entrega_' ? 'estado_entrega' : 'estado';
                            
                            document.getElementById(idEndereco).value = data.logradouro || '';
                            document.getElementById(idBairro).value = data.bairro || '';
                            document.getElementById(idCidade).value = data.localidade || '';
                            document.getElementById(idEstado).value = data.uf || '';
                        }
                    })
                    .finally(() => document.body.style.cursor = 'default');
            }
        }
    </script>
</x-app-layout>