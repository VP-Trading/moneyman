<?php

declare(strict_types=1);

return [
    'ref_prefix' => env('MONEYMAN_REF_PREFIX', 'mm_ref_'),
    'providers' => [
        'chapa' => [
            'secret_key' => env('MONEYMAN_CHAPA_SECRET_KEY'),
            'base_url' => env('MONEYMAN_CHAPA_BASE_URL', 'https://api.chapa.co/v1'),
            'callback_url' => env('MONEYMAN_CHAPA_CALLBACK_URL'),
            'webhook_secret' => env('MONEYMAN_CHAPA_WEBHOOK_SECRET'),

        ],
        'santimpay' => [
            'base_url' => env('MONEYMAN_SANTIMPAY_BASE_URL', 'https://services.santimpay.com/api/v1/gateway'),
            'public_key' => env('MONEYMAN_SANTIMPAY_PUBLIC_KEY'),
            'private_key' => env('MONEYMAN_SANTIMPAY_PRIVATE_KEY'),
            'merchant_id' => env('MONEYMAN_SANTIMPAY_MERCHANT_ID'),
            'token' => env('MONEYMAN_SANTIMPAY_TOKEN'),
            'callback_url' => env('MONEYMAN_SANTIMPAY_CALLBACK_URL')
        ],
        'telebirr' => [
            'merchant_app_id' => env('MONEYMAN_TELEBIRR_MERCHANT_APP_ID'),
            'fabric_app_id' => env('MONEYMAN_TELEBIRR_FABRIC_APP_ID'),
            'short_code' => env('MONEYMAN_TELEBIRR_SHORT_CODE'),
            'app_secret' => env('MONEYMAN_TELEBIRR_APP_SECRET'),
            'private_key' => env('MONEYMAN_TELEBIRR_PRIVATE_KEY'),
            'base_url' => env('MONEYMAN_TELEBIRR_BASE_URL', 'https://developerportal.ethiotelebirr.et:38443/apiaccess/payment/gateway'),
            'timeout' => env('MONEYMAN_TELEBIRR_TIMEOUT', 5),
            'callback_url' => env('MONEYMAN_TELEBIRR_CALLBACK_URL'),
            'web_base_url' => env('MONEYMAN_TELEBIRR_WEB_BASE_URL', 'https://developerportal.ethiotelebirr.et:38443/payment/web/paygate')
        ],
    ]
];
