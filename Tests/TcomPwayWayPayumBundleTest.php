<?php
namespace Payum\Bundle\TcomPayWayPayumBundle\Tests;

use Locastic\TcomPayWayPayumBundle\LocasticTcomPayWayPayumBundle;

class PayumBundleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBundle()
    {
        $rc = new \ReflectionClass('Locastic\TcomPayWayPayumBundle\LocasticTcomPayWayPayumBundle');
        $this->assertTrue($rc->isSubclassOf('Symfony\Component\HttpKernel\Bundle\Bundle'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new LocasticTcomPayWayPayumBundle;
    }
}