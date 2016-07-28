<?php

namespace Locastic\TcomPayWayPayumBundle\Action;

use Locastic\TcomPayWay\AuthorizeDirect\Model\Payment as Api;
use Locastic\TcomPayWayPayumBundle\Entity\CreditCard;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Exception\LogicException;
use Locastic\TcomPayWayPayumBundle\Request\ObtainCreditCard;
use Payum\Core\Request\RenderTemplate;
use Payum\Core\Security\SensitiveValue;

/**
 * @property Api $api
 */
class CaptureDirectAction extends CaptureOffsiteAction
{
    public function __construct($templateName)
    {
        parent::__construct($templateName);

        $this->apiClass = Api::class;
    }

    /**
     * {@inheritDoc}
     *
     * @param Capture $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = new ArrayObject($request->getModel());
        $httpCardError = null;

        $httpRequest = new GetHttpRequest();
        $this->gateway->execute($httpRequest);

        //we are back from tcomapway site so we have to just update model and complete action
        if (isset($httpRequest->request['pgw_trace_ref'])) {
            $creditCardErrors = array(1006, 1100, 1101, 1102, 1400, 1401, 1402, 1403, 1404, 1405, 1406, 1407);

            // payment is completed with success or failed (but not with credit card errors)
            if (false == isset($httpRequest->request['pgw_result_code']) || !in_array(
                    $httpRequest->request['pgw_result_code'],
                    $creditCardErrors
                )
            ) {
                $model['tcompayway_response'] = $this->checkandUpdateReponse($httpRequest->request);

                return;
            }
            // save credit card error and show again obtain card form
            $model['pgwCreditCardError'] = $httpRequest->request['pgw_result_code'];
        }

        // required fields for tcompayway
        $cardFields = array(
            'pgwCardNumber',
            'pgwCardExpirationDate',
            'pgwCardVerificationData',
            'pgwFirstName',
            'pgwLastName',
            'pgwStreet',
            'pgwCity',
            'pgwPostCode',
            'pgwCountry',
            'pgwEmail',
        );

        if (false == $model->validateNotEmpty($cardFields, false) && false == $model['ALIAS']) {
            try {
                $creditCardRequest = new ObtainCreditCard();
                $creditCardRequest->setModel($model);
                $this->gateway->execute($creditCardRequest);
                /** @var CreditCard $card */
                $card = $creditCardRequest->obtain();

                // update model
                $model['pgwCardNumber'] = $card->getMaskedNumber();
                $model['pgwCardExpirationDate'] = new SensitiveValue($card->getExpireAt()->format('m-y'));
                $model['pgwCardVerificationData'] = new SensitiveValue($card->getSecurityCode());
                $model['pgwFirstName'] = $card->getHolder();
                $model['pgwLastName'] = $card->getLastName();
                $model['pgwStreet'] = $card->getStreet();
                $model['pgwCity'] = $card->getCity();
                $model['pgwPostCode'] = $card->getPostCode();
                $model['pgwCountry'] = $card->getCountry();
                $model['pgwPhoneNumber'] = $card->getPhoneNumber();
                $model['pgwEmail'] = $card->getEmail();
            } catch (RequestNotSupportedException $e) {
                throw new LogicException(
                    'Credit card details has to be set explicitly or there has to be an action that supports ObtainCreditCard request.'
                );
            }
        }

        // update tcompayway api

        $this->api->setPgwOrderId($model['pgwOrderId']);
        $this->api->setPgwAmount($model['pgwAmount']);
        $this->api->setPgwSuccessUrl($request->getToken()->getTargetUrl());
        $this->api->setPgwFailureUrl($request->getToken()->getTargetUrl());
        $this->api->setPgwCardNumber($card->getNumber());
        $this->api->setPgwCardExpirationDate($card->getExpireAt()->format('ym'));
        $this->api->setPgwCardVerificationData($card->getSecurityCode());
        $this->api->setPgwInstallments($card->getInstallments());
        $this->api->setPgwFirstName($card->getHolder());
        $this->api->setPgwLastName($card->getLastName());
        $this->api->setPgwStreet($card->getStreet());
        $this->api->setPgwCity($card->getCity());
        $this->api->setPgwPostCode($card->getPostCode());
        $this->api->setPgwCountry($card->getCountry());
        $this->api->setPgwPhoneNumber($card->getPhoneNumber());
        $this->api->setPgwEmail($card->getEmail());

        // render template and process payment

        $renderTemplate = new RenderTemplate(
            $this->templateName, array(
                'payment' => $this->api,
            )
        );
        $this->gateway->execute($renderTemplate);

        throw new HttpResponse($renderTemplate->getResult());
    }
}