<?php

declare(strict_types=1);

return [
    'ref_prefix' => env('MONEYMAN_REF_PREFIX', 'mm_ref_'),
    'providers' => [
        'chapa' => [
            'secret_key' => env('CHAPA_SECRET_KEY'),
            'base_url' => env('CHAPA_BASE_URL', 'https://api.chapa.co/v1'),
            'callback_url' => env('CHAPA_CALLBACK_URL'),
            'webhook_secret' => env('CHAPA_WEBHOOK_SECRET'),

        ],
        'santimpay' => [
            'base_url' => env('SANTIMPAY_BASE_URL', 'https://services.santimpay.com/api/v1/gateway'),
            'public_key' => env('SANTIMPAY_PUBLIC_KEY'),
            'private_key' => env('SANTIMPAY_PRIVATE_KEY'),
            'merchant_id' => env('SANTIMPAY_MERCHANT_ID'),
            'token' => env('SANTIMPAY_TOKEN'),
            'callback_url' => env('SANTIMPAY_CALLBACK_URL')
        ],
        'telebirr' => [
            'merchant_app_id' => env('TELEBIRR_MERCHANT_APP_ID'),
            'fabric_app_id' => env('TELEBIRR_FABRIC_APP_ID'),
            'short_code' => env('TELEBIRR_SHORT_CODE'),
            'app_secret' => env('TELEBIRR_APP_SECRET'),
            'private_key' => env('TELEBIRR_PRIVATE_KEY'),
            'base_url' => env('TELEBIRR_BASE_URL', 'https://developerportal.ethiotelebirr.et:38443/apiaccess/payment/gateway'),
            'timeout' => env('TELEBIRR_TIMEOUT', 5),
            'callback_url' => env('TELEBIRR_CALLBACK_URL'),
            'web_base_url' => env('TELEBIRR_WEB_BASE_URL', 'https://developerportal.ethiotelebirr.et:38443/payment/web/paygate')
        ],
    ]
];
