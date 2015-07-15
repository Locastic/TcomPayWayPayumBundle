<?php

namespace Locastic\TcomPayWayPayumBundle;

use Locastic\TcomPayWay\AuthorizeForm\Model\Payment as PaymentOffsite;
use Locastic\TcomPayWayPayumBundle\Action\CaptureOffsiteAction;
use Locastic\TcomPayWayPayumBundle\Action\FillOrderDetailsAction;
use Locastic\TcomPayWayPayumBundle\Action\StatusAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\PaymentFactoryInterface;
use Payum\Core\PaymentFactory as CorePaymentFactory;
use Symfony\Component\Form\FormBuilder;

class OffsitePaymentFactory implements PaymentFactoryInterface
{
    /**
     * @var PaymentFactoryInterface
     */
    protected $corePaymentFactory;

    /**
     * @var array
     */
    protected $defaultConfig;

    /**
     * @param array $defaultConfig
     * @param PaymentFactoryInterface $corePaymentFactory
     */
    public function __construct(array $defaultConfig = array(), PaymentFactoryInterface $corePaymentFactory = null)
    {
        $this->corePaymentFactory = $corePaymentFactory ?: new CorePaymentFactory();
        $this->defaultConfig = $defaultConfig;
    }

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
                'payum.factory_name' => 'tcompayway_offsite',
                'payum.factory_title' => 'TcomPayWay Offsite',
                'payum.action.capture' => new CaptureOffsiteAction(),
                'payum.action.status' => new StatusAction(),
                'payum.action.fill_order_details' => new FillOrderDetailsAction(),
            )
        );

        if (false == $config['payum.api']) {
            $config['payum.default_options'] = array(
                'shop_id' => '',
                'secret_key' => '',
                'authorization_type' => '0',
                'test_mode' => true,
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
                    null
                );

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
        return $this->corePaymentFactory->create($this->createConfig($config));
    }
}
