# Adyen

[![Build Status](https://travis-ci.org/pixers/payum-adyen.png?branch=master)](https://travis-ci.org/pixers/payum-adyen)

The Payum extension for Adyen.

## Instalation

The preferred way to install the library is using [composer](http://getcomposer.org/).

Run:

```bash
php composer.phar require "pixers/payum-adyen"
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

## Symfony Integration (payum-bundle < 2.0)

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
        adyen_gateway:
            adyen:
                sandbox: true
                skinCode: ADYEN_SKINCODE
                merchantAccount: ADYEN_ACCOUNT
                hmacKey: SECRET_KEY
                notification_method: basic
                default_payment_fields:
                    shopperLocale: de
```

## Symfony Integration (payum-bundle >= 2.0)

### Add AdyenGatewayFactory to payum in services.yml:

```yaml
    adyen_gateway:
        class: Payum\Core\Bridge\Symfony\Builder\GatewayFactoryBuilder
        arguments: [Payum\Adyen\AdyenGatewayFactory]
        tags:
            - { name: payum.gateway_factory_builder, factory: adyen_gateway }
```

### Configuration in config.yml:

You should remember that HMAC is SHA-256 (SHA-1 is deprecated).

```yaml
payum:
    gateways:
        adyen_gateway:
            factory: adyen
            sandbox: true
            skinCode: ADYEN_SKINCODE
            merchantAccount: ADYEN_ACCOUNT
            hmacKey: SECRET_KEY
            notification_method: basic
            default_payment_fields:
                shopperLocale: de
```

## Resources

* [Payum Repository](https://github.com/Payum/Payum)
* [Adyen Documentation](https://docs.adyen.com/manuals)

## License

Copyright 2016 PIXERS Ltd - www.pixersize.com

Licensed under the [BSD 3-Clause](LICENSE)