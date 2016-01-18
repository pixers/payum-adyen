<?php
namespace Payum\Adyen\Tests;

use Payum\Adyen\Api;
use Payum\Core\HttpClientInterface;

class ApiTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function couldBeConstructedWithOptionsOnly()
    {
        $api = new Api([
            'skinCode' => 'skin',
            'merchantAccount' => 'account',
            'hmacKey' => '4468',
            'sandbox' => true,
        ]);

        $this->assertAttributeInstanceOf('Payum\Core\HttpClientInterface', 'client', $api);
    }

    /**
     * @test
     */
    public function couldBeConstructedWithOptionsAndHttpClient()
    {
        $client = $this->createHttpClientMock();

        $api = new Api([
            'skinCode' => 'skin',
            'merchantAccount' => 'account',
            'hmacKey' => '4468',
            'sandbox' => true,
        ], $client);

        $this->assertAttributeSame($client, 'client', $api);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The skinCode, merchantAccount, hmacKey fields are required.
     */
    public function throwIfRequiredOptionsNotSetInConstructor()
    {
        new Api([]);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The boolean sandbox option must be set.
     */
    public function throwIfSandboxOptionsNotBooleanInConstructor()
    {
        new Api([
            'skinCode' => 'skin',
            'merchantAccount' => 'account',
            'hmacKey' => '4468',
            'sandbox' => 'notABool',
        ]);
    }

    /**
     * @test
     */
    public function shouldReturnPostArrayWithMerchantAccountOnPrepareFields()
    {
        $api = new Api(array(
            'skinCode' => 'skin',
            'merchantAccount' => 'account',
            'hmacKey' => '4468',
            'sandbox' => true,
        ), $this->createHttpClientMock());

        $post = $api->prepareFields([
            'merchantAccount' => 'account',
        ]);

        $this->assertInternalType('array', $post);
        $this->assertArrayHasKey('merchantAccount', $post);
    }

    /**
     * @test
     */
    public function shouldFilterNotSupportedOnPrepareFields()
    {
        $api = new Api(array(
            'skinCode' => 'skin',
            'merchantAccount' => 'account',
            'hmacKey' => '4468',
            'sandbox' => true,
        ), $this->createHttpClientMock());

        $post = $api->prepareFields([
            'FOO' => 'fooVal',
            'BAR' => 'barVal',
        ]);

        $this->assertInternalType('array', $post);
        $this->assertArrayNotHasKey('FOO', $post);
        $this->assertArrayNotHasKey('BAR', $post);
    }

    /**
     * @test
     */
    public function shouldReturnFalseIfVerifySignNotSetToParams()
    {
        $api = new Api(array(
            'skinCode' => 'skin',
            'merchantAccount' => 'account',
            'hmacKey' => '4468',
            'sandbox' => true,
        ), $this->createHttpClientMock());

        $this->assertFalse($api->verifySign([]));
    }

    /**
     * @test
     */
    public function shouldReturnFalseIfHmacKeyMisMatched()
    {
        $params = array(
            'foo' => 'fooVal',
            'bar' => 'barVal',
        );
        $invalidSign = 'invalidHash';

        $api = new Api(array(
            'skinCode' => 'skin',
            'merchantAccount' => 'account',
            'hmacKey' => '4468',
            'sandbox' => true,
        ), $this->createHttpClientMock());

        // Guard
        $this->assertNotEquals($invalidSign, $api->merchantSig($params));

        $params['merchantSig'] = $invalidSign;

        $this->assertFalse($api->verifySign($params));
    }

    /**
     * @test
     */
    public function shouldReturnTrueIfHmacKeyMatched()
    {
        $params = array(
            'foo' => 'fooVal',
            'bar' => 'barVal',
        );

        $api = new Api(array(
            'skinCode' => 'skin',
            'merchantAccount' => 'account',
            'hmacKey' => '4468',
            'sandbox' => true,
        ), $this->createHttpClientMock());

        $params['merchantSig'] = $api->merchantSig($params);

        $this->assertTrue($api->verifySign($params));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|HttpClientInterface
     */
    protected function createHttpClientMock()
    {
        return $this->getMock('Payum\Core\HttpClientInterface');
    }
}
