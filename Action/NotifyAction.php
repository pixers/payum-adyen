<?php
namespace Payum\Adyen\Action;

use Payum\Adyen\Api;
use Payum\Core\Action\GatewayAwareAction;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\Notify;

class NotifyAction extends GatewayAwareAction implements ApiAwareInterface
{
    /**
     * @var Api
     */
    protected $api;

    /**
     * {@inheritDoc}
     */
    public function setApi($api)
    {
        if (false == $api instanceof Api) {
            throw new UnsupportedApiException(sprintf('Not supported. Expected %s instance to be set as api.', Api::class));
        }

        $this->api = $api;
    }

    /**
     * {@inheritDoc}
     *
     * @param Notify $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        $this->gateway->execute($httpRequest = new GetHttpRequest());

        if (!isset($httpRequest->request['merchantReference']) || empty($httpRequest->request['merchantReference'])) {
            $details['response_status'] = 401;
            return;
        }

        if (!isset($details['merchantReference']) || ($details['merchantReference'] != $httpRequest->request['merchantReference'])) {
            $details['response_status'] = 402;
            return;
        }

        if (false === $this->api->verifyNotification($httpRequest->request)) {
            $details['response_status'] = 403;
            return;
        }

        // Check notification code
        if (isset($httpRequest->request['eventCode'])) {
            $httpRequest->request['authResult'] = 'UNKNOWN';
            if ('AUTHORISATION' == $httpRequest->request['eventCode']) {
                if ('true' == $httpRequest->request['success']) {
                    $httpRequest->request['authResult'] = 'AUTHORISED';
                } elseif (!empty($httpRequest->request['reason'])) {
                    $httpRequest->request['authResult'] = 'REFUSED';
                }
            }
        }

        $details['authResult'] = $httpRequest->request['authResult'];

        $details['response_status'] = 200;
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Notify &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
