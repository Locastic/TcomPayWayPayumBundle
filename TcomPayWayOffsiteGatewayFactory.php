<?php

namespace Locastic\TcomPayWayPayumBundle;

use Locastic\TcomPayWay\AuthorizeForm\Model\Payment as PaymentOffsite;
use Locastic\TcomPayWayPayumBundle\Action\CaptureOffsiteAction;
use Locastic\TcomPayWayPayumBundle\Action\ConvertPaymentAction;
use Locastic\TcomPayWayPayumBundle\Action\StatusAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

class TcomPayWayOffsiteGatewayFactory extends GatewayFactory
{
    /**
     * {@inheritDoc}
     */
    protected function populateConfig(ArrayObject $config)
    {
        $config->defaults([
            'payum.tcompayway.template.capture' => '@LocasticTcomPayWayOffsite/capture.html.twig',

            'payum.factory_name' => 'tcompayway_offsite',
            'payum.factory_title' => 'TcomPayWay Offsite',
            'payum.action.capture' => function (ArrayObject $config) {
                return new CaptureOffsiteAction($config['payum.tcompayway.template.capture']);
            },
            'payum.action.status' => new StatusAction(),
            'payum.action.fill_order_details' => new ConvertPaymentAction(),
        ]);

        if (false == $config['payum.api']) {
            $config['payum.default_options'] = array(
                'shop_id' => '',
                'secret_key' => '',
                'authorization_type' => '0',
                'sandbox' => true,
                'disable_installments' => '1',
            );
            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = array(
                'shop_id',
                'secret_key',
            );
            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                $api = new PaymentOffsite(
                    $config['shop_id'],
                    $config['secret_key'],
                    null,
                    null,
                    $config['authorization_type'],
                    null,
                    null,
                    $config['sandbox']
                );

                $api->setPgwDisableInstallments($config['disable_installments']);

                return $api;
            };
        }

        $config['payum.paths'] = array_replace([
            'LocasticTcomPayWayOffsite' => __DIR__.'/Resources/views/TcomPayWay/Offsite',
        ], $config['payum.paths'] ?: []);
    }
}
