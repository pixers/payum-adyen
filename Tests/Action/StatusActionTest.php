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
