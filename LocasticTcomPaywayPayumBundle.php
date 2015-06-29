<?php

namespace Locastic\TcomPaywayPayumBundle;

use Locastic\TcomPaywayPayumBundle\DependencyInjection\Factory\Payment\TcomOffsitePaymentFactory;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Payum\Bundle\PayumBundle\DependencyInjection\PayumExtension;


class LocasticTcomPaywayPayumBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        /** @var $extension PayumExtension */
        $extension = $container->getExtension('payum');

        $extension->addPaymentFactory(new TcomOffsitePaymentFactory());
    }
}