<?php

declare(strict_types=1);

namespace Vptrading\MoneyMan\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class BoAToken extends Model
{
    use HasUuids;

    protected $table = 'boa_tokens';

    protected $fillable = [
        'access_token',
        'refresh_token',
    ];

    protected $casts = [
        'access_token' => 'encrypted',
        'refresh_token' => 'encrypted',
    ];
}
