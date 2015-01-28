<?php

namespace Locastic\TcomPaywayPayumBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Payum\Bundle\PayumBundle\DependencyInjection\PayumExtension;

use Locastic\TcomPaywayPayumBundle\Bridge\Symfony\TcomPayWayPaymentFactory;

class LocasticTcomPaywayPayumBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        /** @var $extension PayumExtension */
        $extension = $container->getExtension('payum');

        $extension->addPaymentFactory(new TcomPayWayPaymentFactory());
    }
}