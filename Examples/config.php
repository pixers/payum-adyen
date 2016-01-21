<?php
// config.php

require_once __DIR__.'/../../../../vendor/autoload.php';

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

$builder = new PayumBuilder();
$builder
    ->addGatewayFactory($gatewayName, function(array $config, GatewayFactoryInterface $coreGatewayFactory) {
        return new \Payum\Adyen\AdyenGatewayFactory($config, $coreGatewayFactory);
    });
$builder
    ->addGateway($gatewayName, $defaultConfig);
$builder
    ->addDefaultStorages();

$builder
    ->setGenericTokenFactoryPaths([
        'capture' => 'Examples/capture.php',
        'notify' => 'Examples/notify.php',
    ]);

$payum = $builder
    ->getPayum();
