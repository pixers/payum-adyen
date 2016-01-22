<?php
// capture.php

include_once __DIR__.'/config.php';

use Payum\Core\Reply\HttpPostRedirect;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Request\Capture;
use Payum\Core\Security\TokenInterface;

/**
 * @var TokenInterface
 */
$token = $payum->getHttpRequestVerifier()->verify($_REQUEST);
$gateway = $payum->getGateway($token->getGatewayName());

if ($reply = $gateway->execute(new Capture($token), true)) {
    if ($reply instanceof HttpRedirect) {
        header("Location: ".$reply->getUrl());
        exit();
    }
    if ($reply instanceof HttpPostRedirect) {
        echo($reply->getContent());
        exit();
    }

    throw new \LogicException('Unsupported reply', null, $reply);
}

$payum->getHttpRequestVerifier()->invalidate($token);

header("Location: ".$token->getAfterUrl());
