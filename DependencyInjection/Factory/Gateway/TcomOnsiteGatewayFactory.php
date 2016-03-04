<?php

namespace Locastic\TcomPayWayPayumBundle\DependencyInjection\Factory\Gateway;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Parameter;

class TcomOnsiteGatewayFactory extends TcomOffsiteGatewayFactory
{
    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container)
    {
        parent::load($container);

        $container->setParameter(
            'payum.tcompayway_onsite.template.obtain_credit_card',
            'LocasticTcomPayWayPayumBundle:TcomPayWay/Onsite:obtainCreditCard.html.twig'
        );

        $container->setParameter('payum.tcompayway_onsite.template.capture', 'LocasticTcomPayWayPayumBundle:TcomPayWay/Onsite:capture.html.twig');
    }

    /**
     * @return array
     */
    protected function createFactoryConfig()
    {
        $config = parent::createFactoryConfig();

        $config['payum.tcompayway_onsite.template.obtain_credit_card'] = new Parameter(
            'payum.tcompayway_onsite.template.obtain_credit_card'
        );

        $config['payum.tcompayway_onsite.template.capture'] = new Parameter('payum.tcompayway_onsite.template.capture');

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
    protected function getPayumGatewayFactoryClass()
    {
        return 'Locastic\TcomPayWayPayumBundle\OnsiteGatewayFactory';
    }
}
