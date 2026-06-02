<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Solicitar Orçamento - Mesa Posta Locações</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans antialiased">
    
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
        <div class="mb-8 text-center">
            <h1 class="text-4xl font-extrabold text-indigo-900 tracking-tight">Mesa Posta</h1>
            <p class="text-sm text-gray-500 mt-2">Locação de Materiais para Eventos Premium</p>
        </div>

        <div class="w-full sm:max-w-md mt-6 px-6 py-8 bg-white shadow-xl overflow-hidden sm:rounded-xl border border-gray-100">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Solicitar Orçamento</h2>
            
            <form action="/api/orcamento" method="POST" class="space-y-5">
                @csrf
                
                <div>
                    <label for="nome" class="block text-sm font-medium text-gray-700">Nome Completo</label>
                    <input type="text" name="name" id="nome" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-3 bg-gray-50" placeholder="Ex: Maria Silva" required>
                </div>

                <div>
                    <label for="telefone" class="block text-sm font-medium text-gray-700">WhatsApp</label>
                    <input type="text" name="telefone" id="telefone" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-3 bg-gray-50" placeholder="(11) 99999-9999" required>
                </div>

                <div>
                    <label for="data_evento" class="block text-sm font-medium text-gray-700">Data do Evento</label>
                    <input type="date" name="data_evento" id="data_evento" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-3 bg-gray-50" required>
                </div>

                <div>
                    <label for="message" class="block text-sm font-medium text-gray-700">Mensagem</label>
                    <textarea name="message" id="message" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-3 bg-gray-50" placeholder="Quais itens deseja alugar?" required></textarea>
                </div>

                <input type="text" name="url_website_fake" class="hidden" style="display:none" autocomplete="off" tabindex="-1">

                <div class="pt-4">
                    <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-md text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        Enviar Pedido de Orçamento
                    </button>
                </div>
            </form>
        </div>
        
        <p class="mt-8 text-xs text-gray-400 text-center">
            &copy; {{ date('Y') }} Mesa Posta Locações. Infraestrutura Protegida.
        </p>
    </div>

</body>
</html>