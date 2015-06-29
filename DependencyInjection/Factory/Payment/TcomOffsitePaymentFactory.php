<?php

namespace Locastic\TcomPaywayPayumBundle\DependencyInjection\Factory\Payment;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\AbstractPaymentFactory;

class TcomOffsitePaymentFactory extends AbstractPaymentFactory
{
    /**
     * {@inheritdoc}
     */
    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        parent::addConfiguration($builder);
        
        $builder->children()
            ->scalarNode('shop_id')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('secret_key')->isRequired()->cannotBeEmpty()->end()
            ->booleanNode('authorization_type')->defaultTrue()->end()
        ->end();
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
    protected function getPayumPaymentFactoryClass()
    {
        return 'Locastic\TcomPayWayPayumBundle\TcomOffsitePaymentFactory';
    }

    /**
     * {@inheritDoc}
     */
    protected function getComposerPackage()
    {
        return 'locastc/tcompayway';
    }
}