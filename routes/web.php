<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Vptrading\MoneyMan\Http\Controllers\WebhookController;

Route::match(['get', 'post'], 'moneyman/webhook/{provider}', WebhookController::class)->name('moneyman.webhook');
