<?php

namespace Locastic\TcomPayWayPayumBundle\Action;

use Locastic\TcomPayWay\AuthorizeForm\Model\Payment as Api;
use Locastic\TcomPayWay\Helpers\CardTypeInterpreter;
use Locastic\TcomPayWay\Helpers\ResponseCodeInterpreter;
use Payum\Core\Action\GatewayAwareAction;
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

class CaptureOffsiteAction extends GatewayAwareAction implements ApiAwareInterface
{
    /**
     * @var Api
     */
    protected $api;

    /**
     * @var string
     */
    protected $templateName;

    /**
     * CaptureOffsiteAction constructor.
     * @param string $templateName
     */
    public function __construct($templateName)
    {
        $this->templateName = $templateName;
    }


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

        $httpRequest = new GetHttpRequest();
        $this->payment->execute($httpRequest);

        //we are back from tcomapway site so we have to just update model and complete action
        if (isset($httpRequest->request['pgw_trace_ref'])) {
            $model['tcompayway_response'] = $this->checkandUpdateReponse($httpRequest->request);

            return;
        }

        $this->api->setPgwAmount($model['pgwAmount']);
        $this->api->setPgwOrderId($model['pgwOrderId']);
        $this->api->setPgwEmail($model['pgwEmail']);
        $this->api->setPgwSuccessUrl($request->getToken()->getTargetUrl());
        $this->api->setPgwFailureUrl($request->getToken()->getTargetUrl());

        $this->api->setPgwFirstName($model['pgwFirstName']);
        $this->api->setPgwLastName($model['pgwLastName']);
        $this->api->setPgwStreet($model['pgwStreet']);
        $this->api->setPgwCity($model['pgwCity']);
        $this->api->setPgwPostCode($model['pgwPostCode']);
        $this->api->setPgwCountry($model['pgwCountry']);
        $this->api->setPgwPhoneNumber($model['pgwPhoneNumber']);

        $this->api->setPgwLanguage($model['pgwLanguage']);
        $this->api->setPgwMerchantData($model['pgwMerchantData']);
        $this->api->setPgwOrderInfo($model['pgwOrderInfo']);
        $this->api->setPgwOrderItems($model['pgwOrderItems']);

        $renderTemplate = new RenderTemplate(
            $this->templateName, array(
                'payment' => $this->api,
            )
        );
        $this->payment->execute($renderTemplate);

        throw new HttpResponse($renderTemplate->getResult());
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

    protected function checkandUpdateReponse($pgwResponse)
    {
        if (!$this->api->isPgwResponseValid($pgwResponse)) {
            throw new RequestNotSupportedException('Not valid PGW Response');
        }

        // tcompayway request failed
        if (isset($pgwResponse['pgw_result_code'])) {
            $pgwResponse['error'] = ResponseCodeInterpreter::getPgwResultCode($pgwResponse['pgw_result_code']);

            return $pgwResponse;
        }

        // tcom request success, add status code 0 manually
        $pgwResponse['credit_card'] = CardTypeInterpreter::getPgwCardType($pgwResponse['pgw_card_type_id']);
        $pgwResponse['pgw_result_code'] = 0;

        return $pgwResponse;
    }
}
