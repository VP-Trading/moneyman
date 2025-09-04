<?php

declare(strict_types=1);

use Alazark94\CashierEt\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

Route::post(config('cashier-et.chapa.callback_url'), WebhookController::class)
    ->name('chapa.webhook');
