<x-app-layout>
    <x-slot name="header">
        @can('admin') Configurações de Segurança e Acessos @else Alterar Minha Senha @endcan
    </x-slot>

    {{-- CARREGA AS CONFIGURAÇÕES ATUAIS DO ARQUIVO JSON --}}
    @php
        $settingsPath = storage_path('app/settings.json');
        $settings = file_exists($settingsPath) ? json_decode(file_get_contents($settingsPath), true) : [];
    @endphp

    <div class="max-w-7xl mx-auto space-y-6 pb-10">
        @if(session('success')) <div class="p-4 bg-green-100 border-l-4 border-green-500 text-green-800 font-bold shadow-sm rounded-md">{{ session('success') }}</div> @endif
        @if(session('error')) <div class="p-4 bg-red-100 border-l-4 border-red-500 text-red-800 font-bold shadow-sm rounded-md">{{ session('error') }}</div> @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <div class="space-y-6">
                
                @can('admin')
                {{-- BLOCO 1: DADOS DA LICENÇA --}}
                <div class="bg-white rounded-xl shadow-md border-t-4 border-gray-500 overflow-hidden">
                    <div class="bg-gray-900 px-6 py-4 border-b border-gray-800 flex items-center justify-between">
                        <h3 class="text-white font-black uppercase tracking-widest text-sm flex items-center gap-2">
                            <svg class="w-4 h-4 text-brand-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            Titular da Licença
                        </h3>
                        <span class="text-[9px] bg-red-600 text-white px-2 py-1 rounded font-bold uppercase tracking-widest shadow-sm">Inalterável</span>
                    </div>
                    <div class="p-6 space-y-5 bg-gray-50">
                        <div>
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Razão Social / Nome Licenciado</label>
                            <input type="text" value="{{ $empresaNome ?? auth()->user()->name }}" readonly disabled class="w-full rounded-md border border-gray-300 bg-gray-200 text-gray-500 cursor-not-allowed px-4 py-3 font-black text-sm shadow-inner select-none">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">CNPJ Atrelado à Licença</label>
                            <input type="text" value="{{ $empresaCnpj ?? '00.000.000/0001-00' }}" readonly disabled class="w-full rounded-md border border-gray-300 bg-gray-200 text-gray-500 cursor-not-allowed px-4 py-3 font-black text-sm shadow-inner select-none">
                        </div>
                    </div>
                </div>
                @endcan

                {{-- BLOCO 2: ALTERAÇÃO DE SENHA --}}
                <div class="bg-white rounded-xl shadow-md border-t-4 border-brand-dark overflow-hidden">
                    <div class="bg-gray-100 px-6 py-4 border-b border-gray-200">
                        <h3 class="text-brand-dark font-black uppercase tracking-widest text-sm">Alterar Senha de Segurança</h3>
                    </div>
                    <form method="post" action="{{ route('password.update') }}" class="p-6 space-y-5 bg-white">
                        @csrf @method('put')
                        <div>
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Senha Atual</label>
                            <input name="current_password" type="password" required autocomplete="current-password" class="w-full bg-white border border-gray-300 px-4 py-3 rounded-md shadow-sm text-sm font-bold text-gray-900 focus:border-brand-gold focus:ring-1 focus:ring-brand-gold">
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Nova Senha</label>
                                <input name="password" type="password" required autocomplete="new-password" class="w-full bg-white border border-gray-300 px-4 py-3 rounded-md shadow-sm text-sm font-bold text-gray-900 focus:border-brand-gold focus:ring-1 focus:ring-brand-gold">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Confirmar Nova Senha</label>
                                <input name="password_confirmation" type="password" required autocomplete="new-password" class="w-full bg-white border border-gray-300 px-4 py-3 rounded-md shadow-sm text-sm font-bold text-gray-900 focus:border-brand-gold focus:ring-1 focus:ring-brand-gold">
                            </div>
                        </div>
                        <div class="mt-6 flex justify-end pt-5 border-t border-gray-100 shrink-0">
                            <button type="submit" class="px-6 py-3 bg-brand-dark text-brand-gold font-black rounded-md shadow-md hover:bg-black uppercase tracking-widest text-[10px] transition-colors">Atualizar Senha</button>
                        </div>
                    </form>
                </div>

                @can('admin')
                {{-- BLOCO NOVO: CONFIGURAÇÕES DO PIX (RECEBIMENTO) --}}
                <div class="bg-white rounded-xl shadow-md border-t-4 border-green-500 overflow-hidden">
                    <div class="bg-gray-900 px-6 py-4 border-b border-gray-800 flex items-center justify-between">
                        <h3 class="text-white font-black uppercase tracking-widest text-sm flex items-center gap-2">
                            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Dados de Recebimento (PIX Próprio)
                        </h3>
                    </div>
                    <form method="post" action="{{ route('admin.financeiro.pix') }}" class="p-6 space-y-4 bg-gray-50">
                        @csrf
                        <div class="p-3 bg-green-50 border border-green-200 rounded-md shadow-sm mb-4">
                            <p class="text-[10px] text-green-800 font-bold uppercase tracking-widest text-center leading-relaxed">
                                Estas informações gerarão o QR Code automático nos contratos para os clientes pagarem sem taxas.
                            </p>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Sua Chave PIX</label>
                            <input type="text" name="pix_chave" value="{{ $settings['pix_chave'] ?? '' }}" placeholder="CNPJ, E-mail, Celular ou Aleatória..." required class="w-full bg-white border border-gray-300 px-4 py-3 rounded-md shadow-sm text-sm font-bold text-gray-900 focus:border-brand-gold">
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Nome do Titular (Banco)</label>
                                <input type="text" name="pix_nome" value="{{ $settings['pix_nome'] ?? 'MESA POSTA LOCACOES' }}" placeholder="Ex: MESA POSTA" required class="w-full bg-white border border-gray-300 px-4 py-3 rounded-md shadow-sm text-sm font-bold text-gray-900 focus:border-brand-gold">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Cidade da Conta</label>
                                <input type="text" name="pix_cidade" value="{{ $settings['pix_cidade'] ?? 'ITATIBA' }}" placeholder="Ex: SAO PAULO" required class="w-full bg-white border border-gray-300 px-4 py-3 rounded-md shadow-sm text-sm font-bold text-gray-900 focus:border-brand-gold">
                            </div>
                        </div>
                        <div class="pt-2">
                            <button type="submit" class="w-full py-4 bg-green-600 text-white font-black uppercase tracking-widest rounded-md text-[10px] hover:bg-green-700 shadow-md transition-colors">Salvar Dados do PIX</button>
                        </div>
                    </form>
                </div>
                @endcan
            </div>

            @can('admin')
            {{-- BLOCO 3: GESTÃO DA EQUIPE --}}
            <div class="bg-white rounded-xl shadow-md border-t-4 border-brand-gold overflow-hidden flex flex-col h-full">
                <div class="bg-brand-black px-6 py-4 border-b border-gray-800 flex justify-between items-center shrink-0">
                    <h3 class="text-brand-gold font-black uppercase tracking-widest text-sm flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        Gestão de Acessos (Equipe)
                    </h3>
                </div>
                
                <div class="overflow-y-auto max-h-[400px] border-b border-gray-200 flex-grow bg-white">
                    <ul class="divide-y divide-gray-100">
                        @foreach(\App\Models\User::orderBy('name')->get() as $membro)
                            <li class="p-5 hover:bg-gray-50 transition-colors flex justify-between items-center">
                                <div>
                                    <p class="font-black text-sm text-brand-dark uppercase">
                                        {{ $membro->name }}
                                        <span class="ml-2 text-[9px] font-black px-2 py-0.5 rounded shadow-sm {{ $membro->nivel_acesso === 'admin' ? 'bg-brand-gold text-brand-black' : 'bg-gray-200 text-gray-600' }}">
                                            {{ $membro->nivel_acesso === 'admin' ? 'ADMINISTRADOR' : 'OPERADOR DE GALPÃO' }}
                                        </span>
                                    </p>
                                    <p class="text-[10px] text-gray-500 font-bold tracking-widest mt-1">{{ $membro->email }}</p>
                                </div>
                                @if($membro->id !== auth()->id())
                                    <form action="{{ route('profile.users.destroy', $membro->id) }}" method="POST" onsubmit="return confirm('ATENÇÃO: Deseja revogar permanentemente o acesso deste usuário?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="px-4 py-2 bg-red-50 text-red-600 border border-red-200 text-[9px] font-black uppercase tracking-widest rounded hover:bg-red-600 hover:text-white transition-colors shadow-sm">Revogar</button>
                                    </form>
                                @else
                                    <span class="px-3 py-1 bg-green-100 text-green-700 border border-green-200 text-[9px] font-black uppercase tracking-widest rounded shadow-sm">Seu Acesso</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="p-6 bg-gray-50 shrink-0 border-t-2 border-dashed border-gray-200">
                    <h4 class="text-[10px] font-black text-brand-dark uppercase tracking-widest mb-4">Conceder Novo Acesso</h4>
                    <form method="POST" action="{{ route('profile.users.store') }}" class="space-y-4" autocomplete="off">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <input type="text" name="name" placeholder="Nome (Ex: Jéssica)" required autocomplete="off" class="w-full bg-white border border-gray-300 px-4 py-3 rounded-md shadow-sm text-sm font-bold text-gray-900 focus:border-brand-gold focus:ring-1 focus:ring-brand-gold">
                            </div>
                            <div>
                                <input type="email" name="email" placeholder="E-mail Corporativo" required autocomplete="off" class="w-full bg-white border border-gray-300 px-4 py-3 rounded-md shadow-sm text-sm font-bold text-gray-900 focus:border-brand-gold focus:ring-1 focus:ring-brand-gold">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <input type="password" name="password" placeholder="Senha Provisória (Mín: 8 caract.)" required autocomplete="new-password" class="w-full bg-white border border-gray-300 px-4 py-3 rounded-md shadow-sm text-sm font-bold text-gray-900 focus:border-brand-gold focus:ring-1 focus:ring-brand-gold">
                            </div>
                            <div>
                                <select name="nivel_acesso" required class="w-full bg-yellow-50 border border-yellow-300 px-4 py-3 rounded-md shadow-sm text-[11px] font-black text-brand-dark focus:border-brand-gold">
                                    <option value="admin">Acesso Total (Master / Financeiro)</option>
                                    <option value="operador" selected>Operador Logístico (Apenas QR Code)</option>
                                </select>
                            </div>
                        </div>
                        <div class="pt-2">
                            <button type="submit" class="w-full py-4 bg-brand-gold text-brand-dark font-black uppercase tracking-widest rounded-md text-[10px] hover:bg-brand-hover shadow-md transition-colors">+ Cadastrar e Liberar Acesso</button>
                        </div>
                    </form>
                </div>
            </div>
            @endcan

        </div>
    </div>
</x-app-layout>