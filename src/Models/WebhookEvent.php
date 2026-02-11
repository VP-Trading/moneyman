<?php

declare(strict_types=1);

namespace Vptrading\MoneyMan\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Vptrading\MoneyMan\Enums\Provider;

class WebhookEvent extends Model
{
    use HasUuids;

    protected $table = 'webhook_events';

    protected $casts = [
        'provider' => Provider::class,
        'data' => 'array',
    ];
}
