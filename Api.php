<?php
namespace Payum\Adyen;

use GuzzleHttp\Psr7\Request;
use Payum\Core\Bridge\Guzzle\HttpClientFactory;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\Http\HttpException;
use Payum\Core\Exception\LogicException;
use Payum\Core\HttpClientInterface;

class Api
{
    /**
     * @var HttpClientInterface
     */
    protected $client;

    /**
     * @var array
     */
    protected $options = [
        'skinCode' => null,
        'merchantAccount' => null,
        'hmacKey' => null,
        'sandbox' => null,
    ];

    /**
     * @param array               $options
     * @param HttpClientInterface $client
     *
     * @throws \Payum\Core\Exception\InvalidArgumentException if an option is invalid
     * @throws \Payum\Core\Exception\LogicException if a sandbox is not boolean
     */
    public function __construct(array $options, HttpClientInterface $client = null)
    {
        $options = ArrayObject::ensureArrayObject($options);
        $options->defaults($this->options);
        $options->validateNotEmpty(array(
            'skinCode',
            'merchantAccount',
            'hmacKey',
        ));

        if (false == is_bool($options['sandbox'])) {
            throw new LogicException('The boolean sandbox option must be set.');
        }
        $this->options = $options;
        $this->client = $client ?: HttpClientFactory::create();
    }

    /**
     * @param array $params
     *
     * @return string
     */
    public function merchantSig(array $params)
    {
        unset($params['merchantSig']);

        // The character escape function
        $escape = function($val) {
            return str_replace(':','\\:',str_replace('\\','\\\\',$val));
        };

        // Sort the array by key using SORT_STRING order
        ksort($params, SORT_STRING);

        // Generate the signing data string
        $signData = implode(":", array_map($escape, array_merge(array_keys($params), array_values($params))));

        // base64-encode the binary result of the HMAC computation
        $merchantSig = base64_encode(hash_hmac('sha256', $signData, pack("H*", $this->options['hmacKey']), true));

        return $merchantSig;
    }

    /**
     * @param array $params
     *
     * @return bool
     */
    public function verifySign(array $params)
    {
        if (empty($params['merchantSig'])) {
            return false;
        }
        $merchantSig = $params['merchantSig'];

        return $merchantSig == $this->merchantSig($params);
    }

    /**
     * @param array $params
     *
     * @return array
     */
    public function prepareFields(array $params)
    {
        $supportedParams = [
            // Required
            'merchantReference' => null,
            'paymentAmount' => null,
            'currencyCode' => null,
            'shipBeforeDate' => null,
            'skinCode' => null,
            'merchantAccount' => null,
            'sessionValidity' => null,
            // Optional
            'shopperLocale' => null,
            'orderData' => null,
            'merchantReturnData' => null,
            'countryCode' => null,
            'shopperEmail' => null,
            'shopperReference' => null,
            'allowedMethods' => null,
            'blockedMethods' => null,
            'offset' => null,
            'brandCode' => null,
            'issuerId' => null,
            'shopperStatement' => null,
            'offerEmail' => null,
            'resURL' => null,
        ];

        $params = array_filter(array_replace(
            $supportedParams,
            array_intersect_key($params, $supportedParams)
        ));

        $params['merchantSig'] = $this->merchantSig($params);

        return $params;
    }

    public function startHostedPaymentPages($id, $amount, $currency, $returnUrl, $method)
    {
        $params = [
            // Required
            'merchantReference' => $id,
            'paymentAmount' => $amount * 100,
            'currencyCode' => $currency,
            'shipBeforeDate' => date('Y-m-d', strtotime('+1 hour')),
            'skinCode' => $this->options['skinCode'],
            'merchantAccount' => $this->options['merchantAccount'],
            'sessionValidity' => date(DATE_ATOM, strtotime('+1 hour')),
            // Optional
            'shopperReference' => '1',
            'resURL' => $returnUrl,
        ];

        switch ($method) {
            case 'adyen_mister_cash':
                $params['allowedMethods'] = 'bcmc';
                $params['countryCode'] = 'BE';
                break;

            case 'adyen_direct_ebanking':
                $params['allowedMethods'] = 'directEbanking';
                $params['countryCode'] = 'DE';
                break;

            case 'adyen_giropay':
                $params['allowedMethods'] = 'giropay';
                $params['countryCode'] = 'DE';
                break;

            case 'adyen_credit_card':
                $params['allowedMethods'] = 'amex,visa,mc';
                break;
        }

        $params = $this->prepareFields($params);
    }

    /**
     * @param array  $fields
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Payum\Core\Exception\Http\HttpException
     */
    protected function doRequest(array $fields)
    {
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];

        $request = new Request('POST', $this->getApiEndpoint(), $headers, http_build_query($fields));

        $response = $this->client->send($request);

        if (false == ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300)) {
            throw HttpException::factory($request, $response);
        }

        // Check response
        $result = $response->getBody()->getContents();

        return $result;
    }

    /**
     * @return string
     */
    protected function getApiEndpoint()
    {
        return sprintf('https://%s.adyen.com/hpp/select.shtml', $this->options['sandbox'] ? 'test' : 'live');
    }
}
