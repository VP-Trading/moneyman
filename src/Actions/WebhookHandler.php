<?php

declare(strict_types=1);

namespace Vptrading\MoneyMan\Actions;

use Illuminate\Http\Request;
use Vptrading\MoneyMan\Contracts\WebhookRegistry;
use Vptrading\MoneyMan\Models\WebhookEvent;

class WebhookHandler
{
    public function __construct(private WebhookRegistry $registry)
    {
        //
    }

    public function handle(Request $request, string $provider)
    {
        $driver = $this->registry->get($provider);

        $event = $driver->parse($request);

        WebhookEvent::firstOrCreate([
            'provider' => $event->provider,
            'event_type' => $event->eventType,
            'tx_ref' => $event->txRef,
            'provider_ref' => $event->providerReference,
            'status' => $event->status,
            'amount' => $event->amount,
            'charge' => $event->charge ?? null,
            'currency' => $event->currency ?? 'ETB',
            'data' => $event->meta,
        ]);
    }
}
