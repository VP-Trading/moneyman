<?php

declare(strict_types=1);

namespace Vptrading\MoneyMan\Contracts;

use Illuminate\Http\Request;
use Vptrading\MoneyMan\Dtos\WebhookEvent;

interface WebhookDriver
{
    public function verify(Request $request): bool;

    public function parse(Request $request): WebhookEvent;
}
