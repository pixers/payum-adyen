<?php
// notify.php

include_once __DIR__.'/config.php';

use Payum\Core\Reply\HttpResponse;
use Payum\Core\Reply\ReplyInterface;
use Payum\Core\Request\Notify;
use Payum\Core\Security\TokenInterface;

/**
 * @var TokenInterface
 */
$token = $payum->getHttpRequestVerifier()->verify($_REQUEST);
$gateway = $payum->getGateway($token->getGatewayName());

try {
    $gateway->execute(new Notify($token));

    http_response_code(204);
    echo 'OK';
} catch (HttpResponse $reply) {
    foreach ($reply->getHeaders() as $name => $value) {
        header("$name: $value");
    }

    http_response_code($reply->getStatusCode());
    echo ($reply->getContent());

    exit;
} catch (ReplyInterface $reply) {
    throw new \LogicException('Unsupported reply', null, $reply);
}
