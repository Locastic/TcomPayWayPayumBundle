<?php

namespace Locastic\TcomPayWayPayumBundle\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Request\GetStatusInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Locastic\TcomPayWay\AuthorizeDirect\Model\Payment as OnsiteApi;
use Locastic\TcomPayWay\AuthorizeForm\Model\Payment as OffsiteApi;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\UnsupportedApiException;

class StatusAction implements ActionInterface, ApiAwareInterface
{
    /** @var  mixed */
    protected $api;

    /**
     * {@inheritDoc}
     */
    public function setApi($api)
    {
        if (false == $api instanceof OnsiteApi && false == $api instanceof OffsiteApi) {
            throw new UnsupportedApiException('Not supported.');
        }

        $this->api = $api;
    }

    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {

        /** @var $request GetStatusInterface */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        $model = $request->getModel();

        if (false == isset($model['tcompayway_response'])) {
            $request->markNew();

            return;
        }

        $statusCode = $model['tcompayway_response']['pgw_result_code'];

        if (0 == $statusCode && 0 == $this->api->getPgwAuthorizationType()) {
            $request->markAuthorized();

            return;
        }

        if (0 == $statusCode && 1 == $this->api->getPgwAuthorizationType()) {
            $request->markCaptured();

            return;
        }

        if ($statusCode > 0) {
            $request->markFailed();

            return;
        }

        $request->markUnknown();
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof GetStatusInterface &&
            $request->getModel() instanceof \ArrayAccess;
    }
}