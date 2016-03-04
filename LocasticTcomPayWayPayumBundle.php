<?php

namespace Locastic\TcomPayWayPayumBundle;

use Locastic\TcomPayWayPayumBundle\DependencyInjection\Factory\Gateway\TcomOffsiteGatewayFactory;
use Locastic\TcomPayWayPayumBundle\DependencyInjection\Factory\Gateway\TcomOnsiteGatewayFactory;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Payum\Bundle\PayumBundle\DependencyInjection\PayumExtension;


class LocasticTcomPayWayPayumBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        /** @var $extension PayumExtension */
        $extension = $container->getExtension('payum');

        $extension->addGatewayFactory(new TcomOffsiteGatewayFactory());
        $extension->addGatewayFactory(new TcomOnsiteGatewayFactory());
    }
}
