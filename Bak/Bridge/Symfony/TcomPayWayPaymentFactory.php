<?php

namespace Locastic\TcomPaywayPayumBundle\Bridge\Symfony;

use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\AbstractPaymentFactory;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class TcomPayWayPaymentFactory extends AbstractPaymentFactory
{
    /**
     * @param Definition $paymentDefinition
     * @param ContainerBuilder $container
     * @param $contextName
     * @param array $config
     */
    protected function addActions(
        Definition $paymentDefinition,
        ContainerBuilder $container,
        $contextName,
        array $config
    ) {
        $captureAction = new Definition;
        $captureAction->setClass('Locastic\TcomPaywayPayumBundle\Action\CaptureAction');
        $captureAction->setPublic(false);
        $captureAction->setArguments(array(
            'shop_id' => $container->getParameter('tcompayway.shop_id'),
            'shop_username' => $container->getParameter('tcompayway.shop_username'),
            'shop_password' => $container->getParameter('tcompayway.shop_password'),
            'shop_secret_key' => $container->getParameter('tcompayway.shop_secret_key'),
            'secure3d_template' => $config['secure3d_template'],
            'preauth_required' => !$config['preauth_required'],
        ));
        $captureAction->addTag(
            'payum.action',
            array(
                'factory' => 'tcompayway'
            )
        );
        $container->setDefinition('locastic.tcompayway_payum.action.capture', $captureAction);

        $statusAction = new Definition;
        $statusAction->setClass('Locastic\TcomPaywayPayumBundle\Action\StatusAction');
        $statusAction->setPublic(false);
        $statusAction->addTag(
            'payum.action',
            array(
                'factory' => 'tcompayway'
            )
        );
        $container->setDefinition('locastic.tcompayway_payum.action.status', $statusAction);

        $container->setParameter('done_template', $config['done_template']);
        $container->setParameter('prepare_template', $config['prepare_template']);
    }

    /**
     * @param Definition $paymentDefinition
     * @param ContainerBuilder $container
     * @param $contextName
     * @param array $config
     */
    protected function addApis(Definition $paymentDefinition, ContainerBuilder $container, $contextName, array $config)
    {
        $tcompayway = new Definition;
        $tcompayway->setClass('Locastic\TcomPayWay\Handlers\TcomPayWayPaymentProcessHandler');
        $tcompayway->addArgument($config['api_wsdl']);
        $tcompayway->addArgument($config['api_options_location']);
        $tcompayway->addArgument($config['api_options_trace']);
        $tcompayway->addArgument($config['api_options_url']);
        $container->setDefinition('locastic.tcompayway_payum.api', $tcompayway);

        $paymentDefinition->addMethodCall('addApi', array(new Reference('locastic.tcompayway_payum.api')));
    }

    /**
     * {@inheritDoc}
     */
    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        parent::addConfiguration($builder);

        $builder
                ->children()
                    ->scalarNode('preauth_required')
                        ->defaultValue(1)
                    ->end()
                    ->scalarNode('api_wsdl')
                        ->defaultValue('https://pgw.t-com.hr/MerchantPayment/PaymentWS.asmx?wsdl')
                    ->end()
                    ->scalarNode('api_options_location')
                        ->defaultValue('https://pgw.t-com.hr/MerchantPayment/PaymentWS.asmx')
                    ->end()
                    ->scalarNode('api_options_trace')
                        ->defaultValue(1)
                    ->end()
                    ->scalarNode('api_options_url')
                        ->defaultValue('https://pgw.t-com.hr/MerchantPayment/PaymentWS.asmx')
                    ->end()
                    ->scalarNode('done_template')
                        ->defaultValue('LocasticTcomPaywayPayumBundle:TcomPayWay:done.html.twig')
                    ->end()
                    ->scalarNode('prepare_template')
                        ->defaultValue('LocasticTcomPaywayPayumBundle:TcomPayWay:prepare.html.twig')
                    ->end()
                    ->scalarNode('secure3d_template')
                        ->defaultValue('LocasticTcomPaywayPayumBundle:TcomPayWay:secure3d.html.twig')
                    ->end()
                ->end()
            ->end();
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'tcompayway';
    }
}