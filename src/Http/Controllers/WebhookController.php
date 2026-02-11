<?php

declare(strict_types=1);

namespace Vptrading\MoneyMan\Http\Controllers;

use Illuminate\Http\Request;
use Vptrading\MoneyMan\Actions\WebhookHandler;

class WebhookController
{
    public function __invoke(Request $request, string $provider, WebhookHandler $handler)
    {
        $handler->handle($request, $provider);

        return response()->json([
            'message' => 'Webhook Received',
        ]);
    }
}
