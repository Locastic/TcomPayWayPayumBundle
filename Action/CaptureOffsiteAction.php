<?php

namespace Locastic\TcomPayWayPayumBundle\Action;

use Locastic\TcomPayWay\AuthorizeForm\Model\Payment as Api;
use Payum\Core\Action\PaymentAwareAction;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Reply\HttpPostRedirect;

class CaptureOffsiteAction extends PaymentAwareAction implements ApiAwareInterface
{
    /**
     * @var Api
     */
    protected $api;

    /**
     * {@inheritDoc}
     */
    public function setApi($api)
    {
        if (false === $api instanceof Api) {
            throw new UnsupportedApiException('Not supported.');
        }

        $this->api = $api;
    }

    /**
     * {@inheritDoc}
     *
     * @param Capture $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());


        var_dump($this->api);


        $httpRequest = new GetHttpRequest();
        $this->payment->execute($httpRequest);



        //we are back from be2bill site so we have to just update model.
        if (isset($httpRequest->query['EXECCODE'])) {
            $model->replace($httpRequest->query);
        } else {
            throw new HttpPostRedirect(
                $this->api->getOffsiteUrl(),
                $this->api->prepareOffsitePayment($model->toUnsafeArray())
            );
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof \ArrayAccess;
    }
}