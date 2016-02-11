# Adyen

[![Build Status](https://travis-ci.org/NetTeam/payum-adyen.png?branch=master)](https://travis-ci.org/NetTeam/payum-adyen)

The Payum extension for Adyen.

## Instalation

The preferred way to install the library is using [composer](http://getcomposer.org/).

Run:

```bash
php composer.phar require "netteam/payum-adyen"
```

## Configuration
```php
<?php
// configure.php
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

## Symfony Integration

### Add AdyenGatewayFactory to payum:
```php
<?php
// src/Acme/PaymentBundle/AcmePaymentBundle.php

namespace Acme\PaymentBundle;

use Payum\Adyen\Bridge\Symfony\AdyenGatewayFactory;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AcmePaymentBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $extension = $container->getExtension('paum');
        $extension->addPaymentFactory(new AdyenGatewayFactory());
    }
}
```

### Configuration in config.yml:

You should remember that HMAC is SHA-256 (SHA-1 is deprecated).

```yaml
payum:
    gateways:
        ...
        adyen_gateway:
            adyen:
                sandbox: true
                skinCode: ADYEN_SKINCODE
                merchantAccount: ADYEN_ACCOUNT
                hmacKey: SECRET_KEY
                default_payment_fields:
                    shopperLocale: de
                    ...
        ...
```

## Resources

* [Payum Documentation](http://payum.org/doc)
* [Adyen Documentation](https://docs.adyen.com/manuals)

## License

Adyen plugin is released under the [BSD License](LICENSE).
