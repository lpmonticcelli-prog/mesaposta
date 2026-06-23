<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Gate; // <-- IMPORTAÇÃO DO MOTOR DE SEGURANÇA ADICIONADA AQUI

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // =========================================================================
        // 1. O CÃO DE GUARDA DAS PATENTES (Impede Operadores de verem o Financeiro)
        // =========================================================================
        Gate::define('admin', function ($user) {
            return $user->nivel_acesso === 'admin';
        });

        // =========================================================================
        // 2. MOTOR GLOBAL DA LICENÇA (Identidade Visual)
        // =========================================================================
        try {
            $settingsPath = storage_path('app/settings.json');
            
            if (file_exists($settingsPath)) {
                $settings = json_decode(file_get_contents($settingsPath), true);
                $empresaNome = $settings['empresa_nome'] ?? 'Mesa Posta Locações';
                $empresaLogo = $settings['empresa_logo'] ?? null;
                $empresaCnpj = $settings['empresa_cnpj'] ?? '00.000.000/0001-00';
            } else {
                $empresaNome = 'Mesa Posta Locações';
                $empresaLogo = null;
                $empresaCnpj = '00.000.000/0001-00';
            }
            
            View::share('empresaNome', $empresaNome);
            View::share('empresaLogo', $empresaLogo);
            View::share('empresaCnpj', $empresaCnpj);
        } catch (\Exception $e) {
            View::share('empresaNome', 'Mesa Posta Locações');
            View::share('empresaLogo', null);
            View::share('empresaCnpj', '00.000.000/0001-00');
        }
    }
}