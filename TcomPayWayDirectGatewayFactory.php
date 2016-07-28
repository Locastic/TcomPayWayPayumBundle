<?php

namespace Locastic\TcomPayWayPayumBundle;

use Locastic\TcomPayWay\AuthorizeDirect\Model\Payment as PaymentOnsite;
use Locastic\TcomPayWayPayumBundle\Action\CaptureDirectAction;
use Locastic\TcomPayWayPayumBundle\Action\ConvertPaymentAction;
use Locastic\TcomPayWayPayumBundle\Action\ObtainCreditCardAction;
use Locastic\TcomPayWayPayumBundle\Action\StatusAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

class TcomPayWayDirectGatewayFactoryTcomPayWay extends GatewayFactory
{
    /**
     * {@inheritDoc}
     */
    protected function populateConfig(ArrayObject $config)
    {
        $config = ArrayObject::ensureArrayObject($config);
        $config->defaults($this->defaultConfig);
        $config->defaults($this->coreGatewayFactory->createConfig((array)$config));

        $config->defaults([
            'payum.tcompayway.template.capture' => '@LocasticTcomPayWayDirect/capture.html.twig',
            'payum.tcompayway.template.obtain_credit_card' => '@LocasticTcomPayWayDirect/obtainCreditCard.html.twig',

            'payum.factory_name' => 'tcompayway_direct',
            'payum.factory_title' => 'TcomPayWay Direct',
            'payum.action.capture' => new CaptureDirectAction($config['payum.tcompayway.template.capture']),
            'payum.action.status' => new StatusAction(),
            'payum.action.convert_payment' => new ConvertPaymentAction(),
            'payum.action.obtain_credit_card' => function (ArrayObject $config) {
                return new ObtainCreditCardAction();
            }
        ]);

        if (false == $config['payum.api']) {
            $config['payum.default_options'] = array(
                'shop_id' => '',
                'secret_key' => '',
                'authorization_type' => '0',
                'sandbox' => true,
            );
            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = array(
                'shop_id',
                'secret_key',
            );
            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);
                $api = new PaymentOnsite(
                    $config['shop_id'],
                    $config['secret_key'],
                    null,
                    null,
                    $config['authorization_type'],
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    $config['sandbox']
                );

                return $api;
            };
        }

        $config['payum.paths'] = array_replace([
            'LocasticTcomPayWayDirect' => __DIR__.'/Resources/views/Direct',
        ], $config['payum.paths'] ?: []);
    }
}
