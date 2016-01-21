<?php
// prepare.php

require_once __DIR__.'/config.php';

use Payum\Core\Model\Payment;

$storage = $payum->getStorage($paymentClass);

/**
 * @var Payment
 */
$payment = $storage->create();
$payment->setNumber(uniqid());
$payment->setCurrencyCode('EUR');
$payment->setTotalAmount(1.23); // 1.23 EUR
$payment->setDescription('A description');
$payment->setClientId('anId');
$payment->setClientEmail('foo@example.com');

$payment->setDetails([]);

$storage->update($payment);

$captureToken = $payum->getTokenFactory()->createCaptureToken($gatewayName, $payment, 'Examples/done.php');

header('Location: '.$captureToken->getTargetUrl());
