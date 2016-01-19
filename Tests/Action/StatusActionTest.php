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
    public function shouldMarkUnknownIfDetailsEmpty()
    {
        $action = new StatusAction();

        $action->execute($status = new GetHumanStatus(array()));

        $this->assertTrue($status->isUnknown());
    }

}
