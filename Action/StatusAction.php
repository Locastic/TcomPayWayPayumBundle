<?php

namespace Locastic\TcomPayWayPayumBundle\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Request\GetStatusInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;

class StatusAction implements ActionInterface
{
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