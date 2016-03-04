<?php

namespace Locastic\TcomPayWayPayumBundle\DependencyInjection\Factory\Gateway;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\AbstractGatewayFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Parameter;

class TcomOffsiteGatewayFactory extends AbstractGatewayFactory
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
                ->booleanNode('sandbox')->defaultTrue()->end()
                ->scalarNode('disable_installments')->defaultValue(1)->end()
            ->end();
    }

    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container)
    {
        parent::load($container);

        $container->setParameter('payum.tcompayway_offsite.template.capture', 'LocasticTcomPayWayPayumBundle:TcomPayWay/Offsite:capture.html.twig');
    }

    /**
     * @return array
     */
    protected function createFactoryConfig()
    {
        $config = parent::createFactoryConfig();

        $config['payum.tcompayway_offsite.template.capture'] = new Parameter('payum.tcompayway_offsite.template.capture');

        return $config;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'tcompayway_offsite';
    }

    /**
     * {@inheritDoc}
     */
    protected function getPayumGatewayFactoryClass()
    {
        return 'Locastic\TcomPayWayPayumBundle\OffsiteGatewayFactory';
    }

    /**
     * {@inheritDoc}
     */
    protected function getComposerPackage()
    {
        return 'locastc/tcompayway';
    }
}
