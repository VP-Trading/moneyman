<?php

declare(strict_types=1);

use Alazark94\MoneyMan\Providers\Chapa\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

Route::post(config('moneyman.chapa.callback_url'), WebhookController::class)
    ->name('chapa.webhook');
