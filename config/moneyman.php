<?php

declare(strict_types=1);

return [
    'ref_prefix' => env('CHAPA_REF_PREFIX', 'vp_chapa_'),
    'providers' => [
        'chapa' => [
            'secret_key' => env('CHAPA_SECRET_KEY'),
            'base_url' => env('CHAPA_BASE_URL', 'https://api.chapa.co/v1'),
            'callback_url' => env('CHAPA_CALLBACK_URL', '/vp/chapa/webhook'),
            'webhook_secret' => env('CHAPA_WEBHOOK_SECRET'),

        ],
        'fenan_pay' => [],
        'santim_pay' => [],
        'telebirr' => [
            'merchant_app_id' => env('TELEBIRR_MERCHANT_APP_ID'),
            'fabric_app_id' => env('TELEBIRR_FABRIC_APP_ID'),
            'short_code' => env('TELEBIRR_SHORT_CODE'),
            'app_secret' => env('TELEBIRR_APP_SECRET'),
            'private_key' => env('TELEBIRR_PRIVATE_KEY'),
            'base_url' => env('TELEBIRR_BASE_URL'),
            'timeout' => env('TELEBIRR_TIMEOUT', 1),
            'callback_url' => env('TELEBIRR_CALLBACK_URL')
        ],
        'safaricom_ussd' => [],
    ]
];
