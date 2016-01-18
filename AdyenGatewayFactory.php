<?php
namespace Payum\Adyen;

use Payum\Adyen\Action\AuthorizeAction;
use Payum\Adyen\Action\CancelAction;
use Payum\Adyen\Action\ConvertPaymentAction;
use Payum\Adyen\Action\CaptureAction;
use Payum\Adyen\Action\NotifyAction;
use Payum\Adyen\Action\RefundAction;
use Payum\Adyen\Action\StatusAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

class AdyenGatewayFactory extends GatewayFactory
{
    /**
     * {@inheritDoc}
     */
    protected function populateConfig(ArrayObject $config)
    {
        $config->defaults([
            'payum.factory_name' => 'adyen',
            'payum.factory_title' => 'Adyen',
        ]);

        $config->defaults([
            'payum.action.capture' => new CaptureAction(),
            'payum.action.notify' => new NotifyAction(),
            'payum.action.status' => new StatusAction(),
            'payum.action.convert_payment' => new ConvertPaymentAction(),
        ]);

        if (false == $config['payum.api']) {
            $config['payum.default_options'] = [
                'skinCode' => '',
                'merchantAccount' => '',
                'hmacKey' => '',
                'sandbox' => true,
            ];
            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = [
                'skinCode',
                'merchantAccount',
                'hmacKey',
            ];

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                return new Api(
                    [
                        'skinCode' => $config['skinCode'],
                        'merchantAccount' => $config['merchantAccount'],
                        'hmacKey' => $config['hmacKey'],
                    ],
                    $config['payum.http_client']
                );
            };
        }
    }
}
