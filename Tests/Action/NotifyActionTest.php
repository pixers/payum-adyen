<?php
namespace Payum\Adyen\Tests\Action;

use Payum\Adyen\Action\NotifyAction;
use Payum\Adyen\Api;
use Payum\Core\Action\GatewayAwareAction;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\Notify;
use Payum\Core\Tests\GenericActionTest;

class NotifyActionTest extends GenericActionTest
{
    protected $actionClass = NotifyAction::class;

    protected $requestClass = Notify::class;

    /**
     * @test
     */
    public function shouldBeSubClassOfGatewayAwareAction()
    {
        $rc = new \ReflectionClass(NotifyAction::class);

        $this->assertTrue($rc->isSubclassOf(GatewayAwareAction::class));
    }

    /**
     * @test
     */
    public function shouldImplementApiAwareInterface()
    {
        $rc = new \ReflectionClass(NotifyAction::class);

        $this->assertTrue($rc->implementsInterface(ApiAwareInterface::class));
    }

    /**
     * @test
     */
    public function shouldAllowSetApi()
    {
        $expectedApi = $this->createApiMock();

        $action = new NotifyAction();
        $action->setApi($expectedApi);

        $this->assertAttributeSame($expectedApi, 'api', $action);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\UnsupportedApiException
     */
    public function throwIfUnsupportedApiGiven()
    {
        $action = new NotifyAction();

        $action->setApi(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldSetErrorIfQueryDoesNotHaveMerchantReference()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(GetHttpRequest::class))
            ->will($this->returnCallback(function(GetHttpRequest $request) {
                $request->request = [];
            }));

        $apiMock = $this->createApiMock();

        $action = new NotifyAction();
        $action->setGateway($gatewayMock);
        $action->setApi($apiMock);

        $action->execute($notify = new Notify([]));
        $model = $notify->getModel();
        $this->assertSame(401, $model['response_status']);
    }

    /**
     * @test
     */
    public function shouldSetErrorIfDetailsMerchantReferenceDoesNotExist()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(GetHttpRequest::class))
            ->will($this->returnCallback(function(GetHttpRequest $request) {
                $request->request = [
                    'merchantReference' => 'SomeReference',
                ];
            }));

        $apiMock = $this->createApiMock();

        $action = new NotifyAction();
        $action->setGateway($gatewayMock);
        $action->setApi($apiMock);

        $details = new \ArrayObject([]);

        $action->execute($notify = new Notify($details));
        $model = $notify->getModel();
        $this->assertSame(402, $model['response_status']);
    }

    /**
     * @test
     */
    public function shouldSetErrorIfMerchantReferenceDoesNotMatchExpected()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(GetHttpRequest::class))
            ->will($this->returnCallback(function(GetHttpRequest $request) {
                $request->request = [
                    'merchantReference' => 'SomeReference',
                ];
            }));

        $apiMock = $this->createApiMock();

        $action = new NotifyAction();
        $action->setGateway($gatewayMock);
        $action->setApi($apiMock);

        $details = new \ArrayObject([
            'merchantReference' => 'SomeReference2',
        ]);

        $action->execute($notify = new Notify($details));
        $model = $notify->getModel();
        $this->assertSame(402, $model['response_status']);
    }

    /**
     * @test
     */
    public function shouldSetErrorIfQuerySignDoesNotMatchExpected()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(GetHttpRequest::class))
            ->will($this->returnCallback(function(GetHttpRequest $request) {
                $request->request = ['merchantReference' => 'SomeReference'];
            }));

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('verifyNotification')
            ->willReturn(false);

        $action = new NotifyAction();
        $action->setGateway($gatewayMock);
        $action->setApi($apiMock);

        $details = new \ArrayObject([
            'merchantReference' => 'SomeReference'
        ]);

        $action->execute($notify = new Notify($details));
        $model = $notify->getModel();
        $this->assertSame(403, $model['response_status']);
    }

    /**
     * @test
     */
    public function shouldSetRefusedIfNotificationCodeIsAuthorizedAndNotSuccess()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(GetHttpRequest::class))
            ->will($this->returnCallback(function(GetHttpRequest $request) {
                $request->request = [
                    'merchantReference' => 'SomeReference',
                    'eventCode' => 'AUTHORISATION',
                    'success' => 'false',
                    'reason' => 'Reason',
                ];
            }));

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('verifyNotification')
            ->willReturn(true);

        $action = new NotifyAction();
        $action->setGateway($gatewayMock);
        $action->setApi($apiMock);

        $details = new \ArrayObject([
            'merchantReference' => 'SomeReference',
        ]);

        $action->execute($notify = new Notify($details));
        $model = $notify->getModel();
        $this->assertSame(200, $model['response_status']);
        $this->assertSame('REFUSED', $model['authResult']);
    }

    /**
     * @test
     */
    public function shouldSetAuthorisedIfNotificationCodeIsAuthorizedAndSuccess()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(GetHttpRequest::class))
            ->will($this->returnCallback(function(GetHttpRequest $request) {
                $request->request = [
                    'merchantReference' => 'SomeReference',
                    'eventCode' => 'AUTHORISATION',
                    'success' => 'true',
                    'reason' => '',
                ];
            }));

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('verifyNotification')
            ->willReturn(true);

        $action = new NotifyAction();
        $action->setGateway($gatewayMock);
        $action->setApi($apiMock);

        $details = new \ArrayObject([
            'merchantReference' => 'SomeReference',
        ]);

        $action->execute($notify = new Notify($details));
        $model = $notify->getModel();
        $this->assertSame(200, $model['response_status']);
        $this->assertSame('AUTHORISED', $model['authResult']);
    }

    /**
     * @test
     */
    public function shouldSetOkIfVerifyRequestIsOk()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(GetHttpRequest::class))
            ->will($this->returnCallback(function(GetHttpRequest $request) {
                $request->request = [
                    'merchantReference' => 'SomeReference',
                    'authResult' => 'result',
                ];
            }));

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('verifyNotification')
            ->willReturn(true);

        $action = new NotifyAction();
        $action->setGateway($gatewayMock);
        $action->setApi($apiMock);

        $details = new \ArrayObject([
            'merchantReference' => 'SomeReference',
        ]);

        $action->execute($notify = new Notify($details));
        $model = $notify->getModel();
        $this->assertSame(200, $model['response_status']);
        $this->assertSame('result', $model['authResult']);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Api
     */
    protected function createApiMock()
    {
        return $this->getMock(Api::class, ['merchantSig', 'verifyNotification'], [], '', false);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->getMock(GatewayInterface::class);
    }
}
