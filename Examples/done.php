<?php
// done.php

include_once __DIR__.'/config.php';

use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Security\TokenInterface;

/**
 * @var TokenInterface
 */
$token = $payum->getHttpRequestVerifier()->verify($_REQUEST);
$gateway = $payum->getGateway($token->getGatewayName());

$gateway->execute($status = new GetHumanStatus($token));
$payment = $status->getFirstModel();

header('Content-Type: application/json');
echo json_encode(array(
    'status' => $status->getValue(),
    'order' => array(
        'total_amount' => $payment->getTotalAmount(),
        'currency_code' => $payment->getCurrencyCode(),
        'details' => $payment->getDetails(),
    ),
));
