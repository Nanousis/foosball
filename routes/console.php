<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

use App\Cli\RatingUtils;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('recompute-elo', function () {
    RatingUtils::recomputeEloThenPrint();
})->purpose('Run a demo for Glicko ratings');
