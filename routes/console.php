<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

use App\Cli\GlickoDemo;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('glicko-demo', function () {
    GlickoDemo::runDemo();
})->purpose('Run a demo for Glicko ratings');
