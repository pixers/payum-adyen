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

        if (isset($details['response_status'])) {
            if (200 != $details['response_status']) {
                $request->markFailed();
            }
            return;
        }

        // Payment Response
        switch ($details['authResult']) {
            case null:
                $request->markNew();
                break;
            case 'AUTHORISED':
            case 'AUTHORISATION':
                $request->markAuthorized();
                break;
            case 'PENDING':
                $request->markPending();
                break;
            case 'CAPTURE':
                $request->markCaptured();
                break;
            case 'CANCELLED':
            case 'CANCELLATION':
            case 'CANCEL_OR_REFUND':
                $request->markCanceled();
                break;
            case 'REFUSED':
            case 'ERROR':
                $request->markFailed();
                break;
            case 'NOTIFICATION_OF_CHARGEBACK':
            case 'CHARGEBACK':
            case 'CHARGEBACK_REVERSED':
            case 'REFUND_FAILED':
            case 'CAPTURE_FAILED':
                $request->markSuspended();
                break;
            case 'EXPIRE':
                $request->markExpired();
                break;
            case 'REFUND':
            case 'REFUNDED_REVERSED':
                $request->markRefunded();
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
