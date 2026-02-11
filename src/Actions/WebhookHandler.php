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
            'provider' => $event->provider(),
            'is_success' => $event->isSuccess(),
            'tx_ref' => $event->getReference(),
            'provider_ref' => $event->getProviderReference(),
            'status' => $event->getStatus(),
            'amount' => $event->getAmount(),
            'charge' => $event->getCharge(),
            'currency' => $event->getCurrency(),
            'data' => $event->raw(),
        ]);
    }
}
