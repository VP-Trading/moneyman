<?php

declare(strict_types=1);

return [
    'chapa' => [
        'secret_key' => env('CHAPA_SECRET_KEY'),
        'base_url' => env('CHAPA_BASE_URL', 'https://api.chapa.co/v1'),
        'callback_url' => env('CHAPA_CALLBACK_URL', '/vp/chapa/webhook'),
        'webhook_secret' => env('CHAPA_WEBHOOK_SECRET'),
        'ref_prefix' => env('CHAPA_REF_PREFIX', 'vp_chapa_'),
    ],
    'fenan-pay' => [],
    'santim-pay' => [],
    'telebirr-h5' => [],
    'telebirr-ussd' => [],
    'safaricom-ussd' => [],
];
