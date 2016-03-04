<?php

namespace Locastic\TcomPayWayPayumBundle;

use Locastic\TcomPayWay\AuthorizeForm\Model\Payment as PaymentOffsite;
use Locastic\TcomPayWayPayumBundle\Action\CaptureOffsiteAction;
use Locastic\TcomPayWayPayumBundle\Action\FillOrderDetailsAction;
use Locastic\TcomPayWayPayumBundle\Action\StatusAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactoryInterface;
use Payum\Core\GatewayFactory as CoreGatewayFactory;


class OffsiteGatewayFactory implements GatewayFactoryInterface
{
    /**
     * @var GatewayFactoryInterface
     */
    protected $coreGatewayFactory;

    /**
     * @var array
     */
    protected $defaultConfig;

    /**
     * @param array $defaultConfig
     * @param GatewayFactoryInterface $coreGatewayFactory
     */
    public function __construct(array $defaultConfig = array(), GatewayFactoryInterface $coreGatewayFactory = null)
    {
        $this->coreGatewayFactory = $coreGatewayFactory ?: new CoreGatewayFactory();
        $this->defaultConfig = $defaultConfig;
    }

    /**
     * {@inheritDoc}
     */
    public function createConfig(array $config = array())
    {
        $config = ArrayObject::ensureArrayObject($config);
        $config->defaults($this->defaultConfig);
        $config->defaults($this->coreGatewayFactory->createConfig((array)$config));

        $config->defaults(
            array(
                'payum.factory_name' => 'tcompayway_offsite',
                'payum.factory_title' => 'TcomPayWay Offsite',
                'payum.action.capture' => new CaptureOffsiteAction($config['payum.tcompayway_offsite.template.capture']),
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

        return (array)$config;
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $config = array())
    {
        return $this->coreGatewayFactory->create($this->createConfig($config));
    }
}
