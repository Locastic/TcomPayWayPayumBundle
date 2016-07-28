<?php
namespace Locastic\TcomPayWayPayumBundle\Action;

use Locastic\TcomPayWay\AuthorizeDirect\Api;
use Locastic\TcomPayWay\Model\Payment;
use Locastic\TcomPayWayPayumBundle\Request\GetApi;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Exception\RequestNotSupportedException;

/**
 * @property Api $api
 */
class GetApiAction implements ActionInterface, ApiAwareInterface
{
    use ApiAwareTrait;

    public function __construct()
    {
        $this->apiClass = Api::class;
    }

    /**
     * {@inheritdoc}
     *
     * @param GetApi $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $request->setApi($this->api);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return $request instanceof GetApi;
    }
}
