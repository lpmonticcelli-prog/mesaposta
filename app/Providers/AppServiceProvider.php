<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Força HTTPS na HostGator para segurança e evitar roubo de dados
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
        
        // [O ESCUDO]: Ativa o Modo Paranoico apenas no seu computador.
        // Trava o sistema na sua tela se houver buscas lentas no banco, 
        // variáveis vazias sendo chamadas ou falhas de segurança de massa.
        Model::shouldBeStrict(! $this->app->environment('production'));
    }
}