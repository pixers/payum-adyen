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
    public function throwIfQueryDoesNotHaveMerchantReference()
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

        try {
            $action->execute(new Notify([]));
        } catch (HttpResponse $reply) {
            $this->assertSame(400, $reply->getStatusCode());
            $this->assertSame('[failed]', $reply->getContent());

            return;
        }

        $this->fail('The exception is expected');
    }

    /**
     * @test
     */
    public function throwIfDetailsMerchantReferenceDoesNotExist()
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

        try {
            $action->execute(new Notify($details));
        } catch (HttpResponse $reply) {
            $this->assertSame(400, $reply->getStatusCode());
            $this->assertSame('[failed]', $reply->getContent());

            return;
        }

        $this->fail('The exception is expected');
    }

    /**
     * @test
     */
    public function throwIfMerchantReferenceDoesNotMatchExpected()
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

        try {
            $action->execute(new Notify($details));
        } catch (HttpResponse $reply) {
            $this->assertSame(400, $reply->getStatusCode());
            $this->assertSame('[failed]', $reply->getContent());

            return;
        }

        $this->fail('The exception is expected');
    }

    /**
     * @test
     */
    public function throwIfQuerySignDoesNotMatchExpected()
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

        try {
            $action->execute(new Notify($details));
        } catch (HttpResponse $reply) {
            $this->assertSame(400, $reply->getStatusCode());
            $this->assertSame('[failed]', $reply->getContent());

            return;
        }

        $this->fail('The exception is expected');
    }

    /**
     * @test
     */
    public function throwIfSignIsOk()
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
            ->willReturn('value');

        $action = new NotifyAction();
        $action->setGateway($gatewayMock);
        $action->setApi($apiMock);

        $details = new \ArrayObject([
            'merchantReference' => 'SomeReference',
        ]);

        try {
            $action->execute(new Notify($details));
        } catch (HttpResponse $reply) {
            $this->assertSame(200, $reply->getStatusCode());
            $this->assertSame('[accepted]', $reply->getContent());

            return;
        }

        $this->fail('The exception is expected');
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
