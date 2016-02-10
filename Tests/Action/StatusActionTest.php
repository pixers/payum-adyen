<?php
namespace Payum\Adyen\Tests\Action;

use Payum\Adyen\Action\StatusAction;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Tests\GenericActionTest;

class StatusActionTest extends GenericActionTest
{
    protected $actionClass = StatusAction::class;

    protected $requestClass = GetHumanStatus::class;

    /**
     * @test
     */
    public function shouldMarkNewIfDetailsEmpty()
    {
        $action = new StatusAction();

        $action->execute($status = new GetHumanStatus([]));

        $this->assertTrue($status->isNew());
    }

    /**
     * @test
     */
    public function shouldMarkFailedIfResponseStatusIsFailed()
    {
        $action = new StatusAction();

        $action->execute($status = new GetHumanStatus([
            'authResult' => 'Something',
            'response_status' => 400,
        ]));

        $this->assertTrue($status->isFailed());
    }

    /**
     * @test
     */
    public function shouldMarkNewIfAuthResultIsNull()
    {
        $action = new StatusAction();

        $action->execute($status = new GetHumanStatus([
            'authResult' => null,
        ]));

        $this->assertTrue($status->isNew());
    }

    /**
     * @test
     */
    public function shouldMarkAuthorizedIfAuthResultIsAuthorized()
    {
        $action = new StatusAction();

        $action->execute($status = new GetHumanStatus([
            'authResult' => 'AUTHORISED',
        ]));

        $this->assertTrue($status->isAuthorized());
    }

    /**
     * @test
     */
    public function shouldMarkPendindIfAuthResultIsPending()
    {
        $action = new StatusAction();

        $action->execute($status = new GetHumanStatus([
            'authResult' => 'PENDING',
        ]));

        $this->assertTrue($status->isPending());
    }

    /**
     * @test
     */
    public function shouldMarkCaptureIfAuthResultIsCapture()
    {
        $action = new StatusAction();

        $action->execute($status = new GetHumanStatus([
            'authResult' => 'CAPTURE',
        ]));

        $this->assertTrue($status->isCaptured());
    }

    /**
     * @test
     */
    public function shouldMarkCanceledIfAuthResultIsCanceled()
    {
        $action = new StatusAction();

        $action->execute($status = new GetHumanStatus([
            'authResult' => 'CANCELLED',
        ]));

        $this->assertTrue($status->isCanceled());
    }

    /**
     * @test
     */
    public function shouldMarkFaildeIfAuthResultIsRefused()
    {
        $action = new StatusAction();

        $action->execute($status = new GetHumanStatus([
            'authResult' => 'REFUSED',
        ]));

        $this->assertTrue($status->isFailed());
    }

    /**
     * @test
     */
    public function shouldMarkSuspendedIfAuthResultIsChargeback()
    {
        $action = new StatusAction();

        $action->execute($status = new GetHumanStatus([
            'authResult' => 'CHARGEBACK',
        ]));

        $this->assertTrue($status->isSuspended());
    }

    /**
     * @test
     */
    public function shouldMarkExpiredIfAuthResultIsExpire()
    {
        $action = new StatusAction();

        $action->execute($status = new GetHumanStatus([
            'authResult' => 'EXPIRE',
        ]));

        $this->assertTrue($status->isExpired());
    }

    /**
     * @test
     */
    public function shouldMarkRefundedIfAuthResultIsRefund()
    {
        $action = new StatusAction();

        $action->execute($status = new GetHumanStatus([
            'authResult' => 'REFUND',
        ]));

        $this->assertTrue($status->isRefunded());
    }

    /**
     * @test
     */
    public function shouldMarkFailedIfAuthResultIsError()
    {
        $action = new StatusAction();

        $action->execute($status = new GetHumanStatus([
            'authResult' => 'ERROR',
        ]));

        $this->assertTrue($status->isFailed());
    }

    /**
     * @test
     */
    public function shouldMarkUnknownIfAuthResultIsUnknown()
    {
        $action = new StatusAction();

        $action->execute($status = new GetHumanStatus([
            'authResult' => 'SomeStatus',
        ]));

        $this->assertTrue($status->isUnknown());
    }

}
