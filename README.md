# Adyen

[![Build Status](https://travis-ci.org/NetTeam/payum-adyen.png?branch=master)](https://travis-ci.org/NetTeam/payum-adyen)

The Payum extension for Adyen.

Configuration:
```php
<?php

use Payum\Core\GatewayFactoryInterface;
use Payum\Core\Model\Payment;
use Payum\Core\PayumBuilder;

$paymentClass = Payment::class;
$gatewayName = 'adyen';

$defaultConfig = [
    'factory' => $gatewayName,
    'sandbox' => true,
    // Spec
    'skinCode' => '',
    'merchantAccount' => '',
    'hmacKey' => '',
];

$payum = (new PayumBuilder())
    ->addGatewayFactory($gatewayName, function(array $config, GatewayFactoryInterface $coreGatewayFactory) {
        return new \Payum\Adyen\AdyenGatewayFactory($config, $coreGatewayFactory);
    })

    ->addGateway($gatewayName, $defaultConfig);

    ->getPayum()
;
```

## Resources

* [Payum Documentation](http://payum.org/doc)
* [Adyen Documentation](https://docs.adyen.com/manuals)

## License

Adyen plugin is released under the [BSD License](LICENSE).
