<?php

namespace Locastic\TcomPayWayPayumBundle\Action;

use Locastic\TcomPayWay\AuthorizeForm\Model\Payment as Api;
use Payum\Core\Action\PaymentAwareAction;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Reply\HttpPostRedirect;
use Payum\Core\Request\RenderTemplate;
use Symfony\Component\Form\FormBuilder;

class CaptureOffsiteAction extends PaymentAwareAction implements ApiAwareInterface
{
    /**
     * @var Api
     */
    protected $api;

    protected $templateName = 'LocasticTcomPayWayPayumBundle:TcomPayWay/Offsite:prepare.html.twig';

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

        if ($model['pgw_trace_ref']) {
            // tcom return
        } else {

            $this->api->setPgwAmount(500);
            $this->api->setPgwOrderId('nardužba123');
            $this->api->setPgwSuccessUrl($request->getToken()->getAfterUrl());
            $this->api->setPgwFailureUrl($request->getToken()->getAfterUrl());

            $renderTemplate = new RenderTemplate(
                $this->templateName, array(
                    'payment' => $this->api,
                )
            );
            $this->payment->execute($renderTemplate);

            throw new HttpResponse($renderTemplate->getResult());
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