<?php

namespace Locastic\TcomPayWayPayumBundle;

use Locastic\TcomPayWay\AuthorizeDirect\Api;
use Locastic\TcomPayWay\AuthorizeDirect\Model\Payment as PaymentOnsite;
use Locastic\TcomPayWayPayumBundle\Action\CaptureDirectAction;
use Locastic\TcomPayWayPayumBundle\Action\ConvertPaymentAction;
use Locastic\TcomPayWayPayumBundle\Action\ObtainCreditCardAction;
use Locastic\TcomPayWayPayumBundle\Action\StatusAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

class TcomPayWayDirectGatewayFactory extends GatewayFactory
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
            // it will not work outside symfony full stack. If have to configure form factory manually and set it.
            'symfony.form_factory' => '@form.factory',
            'symfony.request_stack' => '@request_stack',

            'payum.tcompayway.template.capture' => '@LocasticTcomPayWayDirect/capture.html.twig',
            'payum.template.obtain_credit_card' => '@LocasticTcomPayWayDirect/obtainCreditCard.html.twig',

            'payum.factory_name' => 'tcompayway_direct',
            'payum.factory_title' => 'TcomPayWay Direct',
            'payum.action.capture' => new CaptureDirectAction($config['payum.tcompayway.template.capture']),
            'payum.action.status' => new StatusAction(),
            'payum.action.convert_payment' => new ConvertPaymentAction(),
            'payum.action.tcompayway_obtain_credit_card' => function(ArrayObject $config) {
                $action = new ObtainCreditCardAction(
                    $config['symfony.form_factory'],
                    $config['payum.template.obtain_credit_card']
                );
                $action->setRequestStack($config['symfony.request_stack']);

                return $action;
            }
        ]);


        $prependActions = $config->getArray('payum.prepend_actions');
        $prependActions[] = 'payum.action.tcompayway_obtain_credit_card';
        $config['payum.prepend_actions'] = (array) $prependActions;

        if (false == $config['payum.api']) {
            $config['payum.default_options'] = array(
                'shop_id' => '',
                'shop_username' => null,
                'shop_password' => null,
                'shop_secret_key' => null,
                'shop_name' => null, # this will be used in transaction description in user's bank account transactions
                'secret_key' => '',
                'authorization_type' => '0',
                'sandbox' => true,
                'disable_installments' => true,
            );
            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = array(
                'shop_id',
                'shop_username',
                'shop_password',
                'shop_secret_key',
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

            $config['payum.api.authorization'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);
                $api = new Api(
                    $config['shop_username'],
                    $config['shop_password'],
                    $config['shop_id'],
                    $config['shop_secret_key'],
                    $config['authorization_type'],
                    $config['sandbox']
                );

                return $api;
            };
        }

        $config['payum.paths'] = array_replace([
            'LocasticTcomPayWayDirect' => __DIR__.'/Resources/views/TcomPayWay/Direct',
        ], $config['payum.paths'] ?: []);
    }
}
