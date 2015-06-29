<?php

namespace Locastic\TcomPayWayPayumBundle\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Request\GetStatusInterface;
use Payum\Core\Exception\RequestNotSupportedException;


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

        if (false == isset($model['paymentStatus'])) {
            $request->markNew();

            return;
        }

        if ('success' == $model['paymentStatus'] or 'finished' == $model['paymentStatus']) {
            $request->markCaptured();

            return;
        }

        if ('secure3d' == $model['paymentStatus']) {
            $request->markPending();

            return;
        }

        if ('error' == $model['paymentStatus']) {
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