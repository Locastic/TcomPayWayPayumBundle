<?php

namespace Locastic\TcomPayWayPayumBundle;

use Locastic\TcomPayWay\AuthorizeDirect\Model\Payment as PaymentOnsite;
use Locastic\TcomPayWayPayumBundle\Action\CaptureOnsiteAction;
use Locastic\TcomPayWayPayumBundle\Action\FillOrderDetailsAction;
use Locastic\TcomPayWayPayumBundle\Action\StatusAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Bridge\Twig\TwigFactory;
use Payum\Core\PaymentFactoryInterface;
use Payum\Core\PaymentFactory as CorePaymentFactory;

class OnsitePaymentFactory extends OffsitePaymentFactory
{
    /**
     * {@inheritDoc}
     */
    public function createConfig(array $config = array())
    {
        $config = ArrayObject::ensureArrayObject($config);
        $config->defaults($this->defaultConfig);
        $config->defaults($this->corePaymentFactory->createConfig((array)$config));

        $config->defaults(
            array(
                'payum.factory_name' => 'tcompayway_onsite',
                'payum.factory_title' => 'TcomPayWay Onsite',
                'payum.action.capture' => new CaptureOnsiteAction($config['payum.tcompayway_onsite.template.capture']),
                'payum.action.status' => new StatusAction(),
                'payum.action.fill_order_details' => new FillOrderDetailsAction(),
            )
        );

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

        return (array)$config;
    }
}
