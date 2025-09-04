<?php

declare(strict_types=1);

namespace Alazark94\CashierEt\Http\Controllers;

use Illuminate\Http\Request;
use Vptrading\ChapaLaravel\Models\ChapaWebhookEvent;

class WebhookController
{
    public function __invoke(Request $request)
    {
        if (! is_null(config('chapa.webhook_secret'))) {
            $secret = config('chapa.webhook_secret');

            $hash = hash_hmac('sha256', $request->getContent(), $secret);

            if (! hash_equals($hash, $request->header('x-chapa-signature'))) {
                return response()->json(['message' => 'Invalid signature'], 403);
            }
        }

        $content = json_decode($request->getContent(), true);

        return response()->json(['message' => 'Webhook received'], 200);
    }
}
