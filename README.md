# Chapa Laravel Package

[![Total Downloads](https://poser.pugx.org/vp-trading/moneyman/d/total.svg)](https://packagist.org/packages/vp-trading/moneyman)
[![Latest Stable Version](https://poser.pugx.org/vp-trading/moneyman/v/stable.svg)](https://packagist.org/packages/vp-trading/moneyman)
[![License](https://poser.pugx.org/vp-trading/moneyman/license.svg)](https://packagist.org/packages/vp-trading/moneyman)

## Installation

To install the `MoneyMan` package, follow these steps:

1. **Require the package via Composer:**

    ```bash
    composer require vp-trading/moneyman
    ```

2. **Publish the configuration, route, and migration files:**

    ```bash
    php artisan vendor:publish --provider="Vptrading\MoneyMan\MoneyManServiceProvider"
    ```

    The package comes with a config files to help you get started quickly.

3. **Configure your `.env` file:**

    Add your Chapa API keys:

    ```bash
    MONEYMAN_REF_PREFIX=

    MONEYMAN_CHAPA_SECRET_KEY=
    MONEYMAN_CHAPA_BASE_URL=
    MONEYMAN_CHAPA_CALLBACK_URL=

    MONEYMAN_SANTIMPAY_BASE_URL=
    MONEYMAN_SANTIMPAY_PUBLIC_KEY=
    MONEYMAN_SANTIMPAY_PRIVATE_KEY=
    MONEYMAN_SANTIMPAY_MERCHANT_ID=
    MONEYMAN_SANTIMPAY_TOKEN=
    MONEYMAN_SANTIMPAY_CALLBACK_URL=

    MONEYMAN_TELEBIRR_MERCHANT_APP_ID=
    MONEYMAN_TELEBIRR_FABRIC_APP_ID=
    MONEYMAN_TELEBIRR_SHORT_CODE=
    MONEYMAN_TELEBIRR_APP_SECRET=
    MONEYMAN_TELEBIRR_PRIVATE_KEY=
    MONEYMAN_TELEBIRR_BASE_URL=
    MONEYMAN_TELEBIRR_TIMEOUT=
    MONEYMAN_TELEBIRR_CALLBACK_URL=
    MONEYMAN_TELEBIRR_WEB_BASE_URL=
    ```

4. **Ready to use!**

    You can now use the package in your Laravel application.

## Support

| **Provider** | **Payment Initialize** | **Payment Verify** | **Payment Refund** |
| ------------ | ---------------------- | ------------------ | ------------------ |
| Chapa        | ✅                     | ✅                 | ✅                 |
| Telebirr     | ✅                     | ✅                 | ✅                 |
| SantimPay    | ✅                     | ✅                 | ❌                 |

## Usage

> Before you start using this package you should know all amount must be put in **_cent values_**. The development of this package took standards from **Stripe**. So for example if you want the amount be 100 Birr you will put 10000.

Here are some basic usage examples:

### Initialize Payment

```php
use Vptrading\MoneyMan\MoneyMan;
use Vptrading\MoneyMan\Enums\Provider;
use Vptrading\MoneyMan\ValueObjects\User;
use Money\Money;

$response = MoneyMan::provider(Provider::Chapa)->initiate([
    Money::ETB(10000),
    new User(
        firstName: 'John',
        lastName: 'Doe',
        email: 'johndoe@example.com',
        phoneNumber: '0912345678'
    ),
    route('return-url')
]);

// Redirect user to payment page
return redirect($response->checkoutUrl);
```

The initiate response is a DTO that consists of the `status` of of the request, `message` if the provider has one, transactionId, and `checkoutUrl` if the request was successful.

### Verify Payment

```php
use Vptrading\MoneyMan\MoneyMan;
use Vptrading\MoneyMan\Enums\Provider;
use Money\Money;

$response = MoneyMan::provider(Provider::Chapa)->verify($transactionId);
```

The verify response is a DTO that consists of the `status` of of the request, `message` if the provider has one, `transactionId`, and `data` which is the response from the provider.

### Refund Payment

```php
use Vptrading\MoneyMan\MoneyMan;
use Vptrading\MoneyMan\Enums\Provider;
use Money\Money;

$response = MoneyMan::provider(Provider::Chapa)->refund($transactionId, Money::ETB(1000));
```

The refund response is a DTO that consists of the `status` of of the request, `message` if the provider has one, `transactionId`, and `data` which is the response from the provider.
