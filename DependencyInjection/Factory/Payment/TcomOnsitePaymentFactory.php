<?php

namespace Locastic\TcomPayWayPayumBundle\DependencyInjection\Factory\Payment;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\AbstractPaymentFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Parameter;

class TcomOnsitePaymentFactory extends AbstractPaymentFactory
{
    /**
     * {@inheritdoc}
     */
    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        parent::addConfiguration($builder);

        $builder
            ->children()
                ->scalarNode('shop_id')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('secret_key')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('authorization_type')->defaultValue(0)->end()
                ->booleanNode('test_mode')->defaultTrue()->end()
            ->end();
    }

    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container)
    {
        parent::load($container);

        $container->setParameter(
            'payum.tcompayway_onsite.obtain_credit_card',
            'LocasticTcomPayWayPayumBundle:TcomPayWay/Onsite:obtainCreditCard.html.twig'
        );
    }

    /**
     * @return array
     */
    protected function createFactoryConfig()
    {
        $config = parent::createFactoryConfig();

        $config['payum.tcompayway_onsite.obtain_credit_card'] = new Parameter(
            'payum.tcompayway_onsite.obtain_credit_card'
        );

        return $config;
    }


    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'tcompayway_onsite';
    }

    /**
     * {@inheritDoc}
     */
    protected function getPayumPaymentFactoryClass()
    {
        return 'Locastic\TcomPayWayPayumBundle\OnsitePaymentFactory';
    }

    /**
     * {@inheritDoc}
     */
    protected function getComposerPackage()
    {
        return 'locastc/tcompayway';
    }
}