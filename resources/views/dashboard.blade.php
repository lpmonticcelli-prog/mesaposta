<x-app-layout>
    <x-slot name="header">
        @can('admin')
            Painel de Controle da Diretoria
        @else
            Central de Operações 
        @endcan
    </x-slot>

    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

    <div class="max-w-7xl mx-auto space-y-8" x-data="{ modalScanner: false }">
        @if(session('success')) <div class="p-4 bg-green-100 border-l-4 border-green-500 text-green-800 font-bold shadow-sm">{{ session('success') }}</div> @endif
        
        {{-- ================================================================= --}}
        {{-- VISÃO 1: ADMINISTRADOR (COFRE E RADAR COMERCIAL)                  --}}
        {{-- ================================================================= --}}
        @can('admin')
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-brand-black rounded-xl p-6 shadow-xl border-t-4 border-green-500 relative overflow-hidden">
                    <div class="absolute right-0 top-0 opacity-10 text-9xl -mt-6 -mr-4">💰</div>
                    <p class="text-gray-400 text-[10px] font-black uppercase tracking-widest mb-2 relative z-10">Faturamento Realizado (Mês)</p>
                    <h3 class="text-3xl font-black text-green-500 relative z-10">R$ {{ number_format($receitasMes ?? 0, 2, ',', '.') }}</h3>
                </div>
                
                <div class="bg-brand-black rounded-xl p-6 shadow-xl border-t-4 border-red-500 relative overflow-hidden">
                    <div class="absolute right-0 top-0 opacity-10 text-9xl -mt-6 -mr-4">⚠️</div>
                    <p class="text-gray-400 text-[10px] font-black uppercase tracking-widest mb-2 relative z-10">Risco: Inadimplência Atrasada</p>
                    <h3 class="text-3xl font-black text-red-500 relative z-10">R$ {{ number_format($aReceberAtrasado ?? 0, 2, ',', '.') }}</h3>
                </div>
                
                <div class="bg-brand-light rounded-xl p-6 shadow-md border border-gray-200 border-t-4 border-brand-gold relative overflow-hidden">
                    <div class="absolute right-0 top-0 opacity-10 text-9xl -mt-6 -mr-4 text-brand-dark"></div>
                    <p class="text-gray-500 text-[10px] font-black uppercase tracking-widest mb-2 relative z-10">Contratos na Rua </p>
                    <h3 class="text-3xl font-black text-brand-dark relative z-10">{{ $osRua ?? 0 }} <span class="text-sm font-bold text-gray-400">Ativos</span></h3>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                <div class="bg-brand-dark px-6 py-4 border-b-4 border-brand-gold">
                    <h3 class="text-brand-gold font-black uppercase tracking-widest text-sm flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Radar Logístico: Eventos nos Próximos 7 Dias
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Data do Evento</th>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Cliente / Parceiro</th>
                                <th class="px-6 py-4 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest">Status Almoxarifado</th>
                                <th class="px-6 py-4 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Ação</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-50">
                            @forelse($eventosProximos ?? [] as $evento)
                                <tr class="hover:bg-brand-light transition-colors">
                                    <td class="px-6 py-4 text-sm font-black text-brand-dark">
                                        {{ \Carbon\Carbon::parse($evento->data_evento)->format('d/m/Y') }}
                                        @if($evento->data_evento === now()->toDateString()) 
                                            <span class="ml-2 bg-red-600 text-white text-[9px] px-2 py-0.5 rounded uppercase font-black shadow-sm">É Hoje!</span> 
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm font-bold text-gray-700">
                                        {{ $evento->cliente->nome }} 
                                        <span class="block text-[10px] text-gray-400 uppercase tracking-widest mt-1">Contrato OS #{{ str_pad($evento->id, 5, '0', STR_PAD_LEFT) }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="bg-brand-gold text-brand-dark px-3 py-1 text-[10px] font-black rounded uppercase tracking-widest shadow-sm">Liberado p/ Check-out</span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('admin.pedidos.show', $evento->id) }}" class="inline-block px-4 py-2 bg-gray-100 text-brand-dark hover:bg-brand-dark hover:text-brand-gold font-black text-[10px] uppercase tracking-widest rounded transition-colors border border-gray-200">Ver OS</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-6 py-16 text-center text-gray-400 font-bold uppercase tracking-widest text-xs">Nenhum evento engatilhado para os próximos 7 dias. O galpão está limpo.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endcan

        {{-- ================================================================= --}}
        {{-- VISÃO 2: OPERADOR LOGÍSTICO (APP NATIVO)                          --}}
        {{-- ================================================================= --}}
        @cannot('admin')
            <div class="bg-brand-dark rounded-xl p-8 shadow-xl text-center relative overflow-hidden border-t-4 border-brand-gold mt-6">
                <div class="absolute right-0 top-0 opacity-5 text-[150px] -mt-10 -mr-4 text-brand-gold">📦</div>
                
                <h2 class="text-3xl font-black text-white uppercase tracking-widest relative z-10 mb-2">
                    Olá, {{ explode(' ', Auth::user()->name)[0] }}! 👋
                </h2>
                <p class="text-brand-gold font-bold mb-8 relative z-10">Terminal de Expedição Ativo.</p>

                <div class="bg-gray-900 border border-gray-700 rounded-xl p-6 max-w-lg mx-auto relative z-10 shadow-2xl">
                    
                    <button @click="modalScanner = true; ligarScannerDashboard()" type="button" class="w-full flex flex-col items-center justify-center bg-brand-gold text-brand-black px-6 py-8 rounded-xl shadow-[0_10px_20px_rgba(255,194,12,0.3)] hover:scale-105 hover:bg-yellow-400 transition-all cursor-pointer border-2 border-transparent hover:border-brand-dark mb-6 group">
                        <svg class="w-16 h-16 mb-3 group-hover:animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H4a1 1 0 01-1-1V4zm14 0a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V4zM3 16a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H4a1 1 0 01-1-1v-4zm14 0a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"></path></svg>
                        <span class="font-black text-xl md:text-2xl uppercase tracking-widest">Ler QR Code da O.S.</span>
                        <span class="text-[10px] font-bold text-gray-800 mt-1 uppercase tracking-wider">Abre a câmera integrada do sistema</span>
                    </button>
                    
                    <div class="border-t border-gray-700 pt-6">
                        <p class="text-[10px] font-black text-gray-500 uppercase tracking-widest mb-3">Ou digite o número da OS manualmente:</p>
                        <form action="{{ url('/estoque/conferencia') }}" method="GET" class="flex flex-col sm:flex-row gap-2 justify-center">
                            <input type="number" name="os" placeholder="Nº da OS" required class="bg-white text-brand-dark border-0 rounded px-4 py-3 font-black text-center w-full sm:w-32 focus:ring-2 focus:ring-brand-gold">
                            <button type="submit" class="bg-gray-800 text-brand-gold font-black uppercase tracking-widest text-[10px] px-6 py-3 rounded border border-gray-600 hover:bg-gray-700 transition-colors">Abrir Manual</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="mt-6 max-w-lg mx-auto pb-10">
                <div class="bg-white rounded-xl p-6 shadow-md border-t-4 border-green-500 flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-[10px] font-black uppercase tracking-widest mb-1">contratos em aberto na rua</p>
                        <h3 class="text-3xl font-black text-brand-dark">{{ $osRua ?? 0 }} <span class="text-sm font-bold text-gray-400">Ativos</span></h3>
                    </div>
                    <div class="text-5xl opacity-20 text-green-500">🚚</div>
                </div>
            </div>

            <div x-cloak x-show="modalScanner" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/95 backdrop-blur-md p-4">
                <div @click.away="fecharScannerDashboard(); modalScanner = false" class="bg-brand-black w-full max-w-lg rounded-2xl overflow-hidden shadow-2xl border-2 border-brand-gold flex flex-col relative">
                    
                    <div class="p-4 bg-gray-900 flex justify-between items-center border-b border-gray-800">
                        <span class="text-brand-gold font-black uppercase tracking-widest text-sm flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span> Escaneando O.S.
                        </span>
                        <button @click="fecharScannerDashboard(); modalScanner = false" class="text-white bg-red-600 hover:bg-red-700 px-4 py-1.5 rounded font-black text-xs uppercase transition-colors shadow">Fechar</button>
                    </div>
                    
                    <div id="leitor-dashboard" class="w-full bg-black flex items-center justify-center text-gray-500 font-bold text-sm" style="min-height: 400px;">
                        Ligando hardware da câmera...
                    </div>

                    <div class="absolute bottom-4 left-0 right-0 text-center pointer-events-none">
                        <p class="text-brand-gold font-black text-[10px] uppercase tracking-widest drop-shadow-md bg-black/50 inline-block px-3 py-1 rounded">Aponte para o papel da expedição</p>
                    </div>
                </div>
            </div>
        @endcannot
        {{-- FIM DA SEPARAÇÃO DE VISÕES --}}
    </div>

    <script>
        let leitorGlobal = null;

        function ligarScannerDashboard() {
            setTimeout(() => {
                const readerDiv = document.getElementById('leitor-dashboard');
                readerDiv.style.minHeight = "400px";
                
                leitorGlobal = new Html5Qrcode("leitor-dashboard");
                
                leitorGlobal.start(
                    { facingMode: "environment" }, // Força a lente traseira do smartphone
                    { fps: 15, qrbox: { width: 250, height: 250 } },
                    (textoDecodificado) => {
                        // Bip de Sucesso
                        let audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                        let osc = audioCtx.createOscillator();
                        osc.type = "square"; osc.frequency.setValueAtTime(880, audioCtx.currentTime);
                        osc.connect(audioCtx.destination); osc.start(); osc.stop(audioCtx.currentTime + 0.1);
                        
                        leitorGlobal.stop().then(() => {
                            if(textoDecodificado.includes('/estoque/conferencia')) {
                                window.location.href = textoDecodificado; // Vai direto para a OS lida
                            } else {
                                alert("⚠️ QR Code Inválido! Isso não é uma O.S. do sistema.");
                                document.querySelector('[x-data]').__x.$data.modalScanner = false;
                            }
                        });
                    },
                    (erro) => { /* Silêncio enquanto foca */ }
                ).catch(err => {
                    alert("⚠️ O Sistema não conseguiu acessar a câmera. Verifique as permissões de câmera do seu navegador.");
                    document.querySelector('[x-data]').__x.$data.modalScanner = false;
                });
            }, 300);
        }

        function fecharScannerDashboard() {
            if (leitorGlobal) {
                leitorGlobal.stop().catch(err => console.log(err));
            }
        }
    </script>
</x-app-layout>