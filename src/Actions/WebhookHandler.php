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

    public function handle(Request $request, string $provider): WebhookEvent
    {
        $driver = $this->registry->get($provider);

        $event = $driver->parse($request);

        return WebhookEvent::updateOrCreate(
            [
                'provider' => $event->provider(),
                'tx_ref' => $event->getReference(),
            ],
            [
                'is_success' => $event->isSuccess(),
                'provider_ref' => $event->getProviderReference(),
                'status' => $event->getStatus(),
                'amount' => $event->getAmount(),
                'charge' => $event->getCharge(),
                'currency' => $event->getCurrency(),
                'data' => $event->raw(),
            ]
        );
    }
}
