<?php
    use Cielo\API30\Ecommerce\Environment;
    use Cielo\API30\Merchant;

    return [
        'merchant_id' => env('MERCHANT_ID', ''),
        'merchant_key' => env('MERCHANT_KEY', ''),
        'merchant' => new Merchant(env('MERCHANT_ID', ''), env('MERCHANT_KEY', '')),
        'environment' => app()->environment('production') ? Environment::production() : Environment::sandbox(),
        'url_bin' => app()->environment('production')
                        ? "https://apiquery.cieloecommerce.cielo.com.br/1/cardBin"
                        : "https://apiquerysandbox.cieloecommerce.cielo.com.br/1/cardBin"
    ];