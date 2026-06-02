<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model; // <-- IMPORTAÇÃO VITAL ADICIONADA

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Trava anti-DDoS em camada de aplicação: Máximo de 3 envios por minuto por IP.
        RateLimiter::for('orcamento-site', function (Request $request) {
            return Limit::perMinute(3)->by($request->ip());
        });

        // TRAVA NUCLEAR ANTI N+1: Proíbe o Lazy Loading no ambiente local/dev.
        // Se você esquecer um "with('relacionamento')" nas consultas, o Laravel força um Erro 500 para te avisar.
        Model::preventLazyLoading(! app()->isProduction());
    }
}