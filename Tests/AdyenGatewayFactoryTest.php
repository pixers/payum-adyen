<?php
namespace Payum\Adyen\Tests;

use Payum\Adyen\AdyenGatewayFactory;
use Payum\Core\GatewayFactory;

class AdyenGatewayFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldSubClassGatewayFactory()
    {
        $rc = new \ReflectionClass(AdyenGatewayFactory::class);

        $this->assertTrue($rc->isSubclassOf(GatewayFactory::class));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new AdyenGatewayFactory();
    }

    /**
     * @test
     */
    public function shouldConfigContainDefaultOptions()
    {
        $factory = new AdyenGatewayFactory();

        $config = $factory->createConfig();

        $this->assertInternalType('array', $config);

        $this->assertArrayHasKey('payum.default_options', $config);

        $options = [
            'skinCode' => '',
            'merchantAccount' => '',
            'hmacKey' => '',
            'sandbox' => true,
            'default_payment_fields' => [],
        ];

        $this->assertEquals($options, $config['payum.default_options']);
    }

    /**
     * @test
     */
    public function shouldConfigContainFactoryNameAndTitle()
    {
        $factory = new AdyenGatewayFactory();

        $config = $factory->createConfig();

        $this->assertInternalType('array', $config);

        $this->assertArrayHasKey('payum.factory_name', $config);
        $this->assertEquals('adyen', $config['payum.factory_name']);

        $this->assertArrayHasKey('payum.factory_title', $config);
        $this->assertEquals('Adyen', $config['payum.factory_title']);
    }
}
