<?php
namespace Payum\Adyen\Action;

use Payum\Adyen\Api;
use Payum\Core\Action\GatewayAwareAction;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Reply\HttpPostRedirect;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Security\TokenInterface;

class CaptureAction extends GatewayAwareAction implements ApiAwareInterface, GenericTokenFactoryAwareInterface
{
    /**
     * @var Api
     */
    protected $api;

    /**
     * @var GenericTokenFactoryInterface
     */
    protected $tokenFactory;

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
     * @param GenericTokenFactoryInterface $genericTokenFactory
     *
     * @return void
     */
    public function setGenericTokenFactory(GenericTokenFactoryInterface $genericTokenFactory = null)
    {
        $this->tokenFactory = $genericTokenFactory;
    }

    /**
     * {@inheritDoc}
     *
     * @param Capture $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /**
         * @var TokenInterface
         */
        $token = $request->getToken();

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $this->gateway->execute($httpRequest = new GetHttpRequest());

        // Check httpRequest
        $extraData = $model['extraData'] ? json_decode($model['extraData'], true) : [];

        if (false == isset($extraData['capture_token']) && $token) {
            $extraData['capture_token'] = $token->getHash();
        }

        if (false == isset($extraData['notify_token']) && $token && $this->tokenFactory) {
            $notifyToken = $this->tokenFactory->createNotifyToken(
                $token->getGatewayName(),
                $token->getDetails()
            );
            $extraData['notify_token'] = $notifyToken->getHash();
            $model['resURL'] = $notifyToken->getTargetUrl();
        }

        $model['extraData'] = json_encode($extraData);

        throw new HttpPostRedirect(
            $this->api->getApiEndpoint(),
            $this->api->prepareFields($model->toUnsafeArray())
        );
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
