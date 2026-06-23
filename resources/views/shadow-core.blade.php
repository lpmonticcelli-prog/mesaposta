<!DOCTYPE html>
<html lang="pt-br">
<head>
    <title>SHADOW CONSOLE - DEV ONLY</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black text-green-500 font-mono flex items-center justify-center min-h-screen p-4">
    <div class="w-full max-w-lg border border-green-500 p-8 shadow-[0_0_20px_rgba(0,255,0,0.4)] bg-gray-900 rounded-lg relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1 bg-green-500"></div>
        <h1 class="text-2xl font-black mb-6 text-center tracking-widest border-b border-green-500 pb-4">
            [ SHADOW_CORE_OVERRIDE ]<br><span class="text-xs text-gray-400">Motor White-Label (Propriedade Intelectual)</span>
        </h1>

        @if(session('success'))
            <div class="bg-green-900 text-green-300 p-3 mb-6 border border-green-500 text-center text-xs font-bold animate-pulse">>>> {{ session('success') }}</div>
        @endif

        {{-- AÇÃO RELATIVA: O "?key..." força o Laravel a manter a sessão e evita o 419 --}}
        <form method="POST" action="?key=Supremo2026" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <div>
                <label class="block text-xs uppercase tracking-widest mb-2">> Set_ERP_Title (Nome da Empresa Cliente)</label>
                <input type="text" name="empresa_nome" value="{{ $settings['empresa_nome'] ?? '' }}" class="w-full bg-black border border-green-500 p-3 text-green-400 focus:outline-none focus:ring-2 focus:ring-green-500 rounded" required>
            </div>
            
            <div>
                <label class="block text-xs uppercase tracking-widest mb-2">> Set_ERP_CNPJ (CNPJ da Licença)</label>
                <input type="text" name="empresa_cnpj" value="{{ $settings['empresa_cnpj'] ?? '' }}" placeholder="00.000.000/0001-00" class="w-full bg-black border border-green-500 p-3 text-green-400 focus:outline-none focus:ring-2 focus:ring-green-500 rounded" required>
            </div>

            <div>
                <label class="block text-xs uppercase tracking-widest mb-2">> Inject_Base64_Logo (PNG sem fundo)</label>
                <input type="file" name="empresa_logo" accept="image/png, image/jpeg" class="w-full bg-black border border-green-500 p-2 text-green-400 focus:outline-none rounded cursor-pointer">
                @if(isset($settings['empresa_logo']))
                    <p class="text-[10px] text-gray-500 mt-2">Logo Atual: [ CARREGADA EM MEMÓRIA ]</p>
                @endif
            </div>
            
            <button type="submit" class="w-full bg-green-500 text-black font-black uppercase tracking-widest p-4 hover:bg-green-400 transition-colors rounded shadow-lg mt-4">
                INJETAR IDENTIDADE VISUAL
            </button>
        </form>
    </div>
</body>
</html>