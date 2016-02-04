<?php
namespace Payum\Adyen\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Request\GetStatusInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;

class StatusAction implements ActionInterface
{
    /**
     * {@inheritDoc}
     *
     * @param GetStatusInterface $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        if (!isset($details['authResult'])) {
            $request->markNew();
            return;
        }

        switch ($details['authResult']) {
            case null:
                $request->markNew();
                break;
            case 'AUTHORISED':
                $request->markAuthorized();
                break;
            case 'PENDING':
                $request->markPending();
                break;
            case 'CANCELLED':
                $request->markCanceled();
                break;
            case 'REFUSED':
            case 'ERROR':
                $request->markFailed();
                break;
            default:
                $request->markUnknown();
                break;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof GetStatusInterface &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
