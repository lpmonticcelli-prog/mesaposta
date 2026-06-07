@props(['alpineTitle'])
<div x-cloak x-show="aberto" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-70 backdrop-blur-sm transition-opacity" x-transition>
    <div @click.away="aberto = false" class="bg-white rounded-xl shadow-2xl w-full max-w-2xl overflow-hidden mx-4 transform transition-all border-t-4 border-brand-gold">
        <div class="bg-brand-dark px-6 py-4 flex justify-between items-center">
            <h3 class="text-brand-gold font-extrabold uppercase tracking-widest" x-text="{{ $alpineTitle }}"></h3>
            <button @click="aberto = false" type="button" class="text-gray-400 hover:text-white text-2xl font-bold">&times;</button>
        </div>
        {{ $slot }}
    </div>
</div>
