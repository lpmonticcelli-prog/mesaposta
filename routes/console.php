<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// ---------------------------------------------------------------------
// FALSO DAEMON: O Trabalhador de Fila para cPanel (HostGator)
// ---------------------------------------------------------------------
// Ele roda a cada minuto, processa os jobs pendentes e se mata quando:
// 1. A fila esvaziar (--stop-when-empty)
// 2. Atingir 50 segundos de vida (--max-time=50) -> Evita que 2 crons se choquem no minuto seguinte.
Schedule::command('queue:work --stop-when-empty --max-time=50 --memory=128')
    ->everyMinute()
    ->withoutOverlapping() // Impede que dois workers abram juntos e estoquem RAM
    ->appendOutputTo(storage_path('logs/worker.log'));