<?php

namespace Locastic\TcomPayWayPayumBundle\Action;

use Locastic\TcomPayWay\Helpers\ResponseCodeInterpreter;
use Locastic\TcomPayWayPayumBundle\Entity\CreditCard;
use Payum\Core\Action\PaymentAwareAction;
use Payum\Core\Bridge\Symfony\Reply\HttpResponse;
use Payum\Core\Exception\LogicException;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Model\CreditCardInterface;
use Locastic\TcomPayWayPayumBundle\Request\ObtainCreditCard;
use Payum\Core\Request\RenderTemplate;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Payum\Core\Bridge\Spl\ArrayObject;

class ObtainCreditCardAction extends PaymentAwareAction
{
    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var Request
     */
    protected $httpRequest;

    /**
     * @var string
     */
    protected $templateName;

    /**
     * @param FormFactoryInterface $formFactory
     * @param string $templateName
     */
    public function __construct(FormFactoryInterface $formFactory, $templateName)
    {
        $this->formFactory = $formFactory;
        $this->templateName = $templateName;
    }

    /**
     * @param Request $request
     */
    public function setRequest(Request $request = null)
    {
        $this->httpRequest = $request;
    }

    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request ObtainCreditCard */
        if (!$this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }
        if (!$this->httpRequest) {
            throw new LogicException('The action can be run only when http request is set.');
        }

        $form = $this->createCreditCardForm($request->getModel());

        $form->handleRequest($this->httpRequest);
        if ($form->isSubmitted()) {
            /** @var CreditCardInterface $card */
            $card = $form->getData();
            $card->secure();

            if ($form->isValid()) {
                $request->set($card);

                return;
            }
        }

        $this->checkTcomPayWayValidation($form, $request->getModel());

        $renderTemplate = new RenderTemplate(
            $this->templateName, array(
                'form' => $form->createView(),
            )
        );
        $this->payment->execute($renderTemplate);

        throw new HttpResponse(
            new Response(
                $renderTemplate->getResult(), 200, array(
                    'Cache-Control' => 'no-store, no-cache, max-age=0, post-check=0, pre-check=0',
                    'Pragma' => 'no-cache',
                )
            )
        );
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof ObtainCreditCard;
    }

    /**
     * @return FormInterface
     */
    protected function createCreditCardForm(ArrayObject $model)
    {
        return $this->formFactory->create('payum_credit_card', $this->setInitialCreditCardData($model));
    }

    protected function setInitialCreditCardData(ArrayObject $model)
    {
        $creditCard = new CreditCard();
        $creditCard->setNumber(null);
        $creditCard->setSecurityCode(null);
        $creditCard->setExpireAt(null);
        $creditCard->setHolder(null);

        if (isset($model['pgwFirstName'])) {
            $creditCard->setHolder($model['pgwFirstName']);
        }

        if (isset($model['pgwLastName'])) {
            $creditCard->setLastName($model['pgwLastName']);
        }

        if (isset($model['pgwStreet'])) {
            $creditCard->setStreet($model['pgwStreet']);
        }

        if (isset($model['pgwCity'])) {
            $creditCard->setCity($model['pgwCity']);
        }

        if (isset($model['pgwPostCode'])) {
            $creditCard->setPostCode($model['pgwPostCode']);
        }

        if (isset($model['pgwCountry'])) {
            $creditCard->setCountry($model['pgwCountry']);
        }

        if (isset($model['pgwPhoneNumber'])) {
            $creditCard->setPhoneNumber($model['pgwPhoneNumber']);
        }

        if (isset($model['pgwEmail'])) {
            $creditCard->setEmail($model['pgwEmail']);
        }

        return $creditCard;
    }

    protected function checkTcomPayWayValidation(FormInterface $form, ArrayObject $model)
    {
        if (false == isset($model['pgwCreditCardError'])) {
            return;
        }

        if (1006 == $model['pgwCreditCardError']) {
            $form->get('installments')->addError($this->getFormError($model['pgwCreditCardError']));
        }

        if (1100 == $model['pgwCreditCardError']) {
            $form->get('number')->addError($this->getFormError($model['pgwCreditCardError']));
        }

        if (1101 == $model['pgwCreditCardError']) {
            $form->get('expireAt')->addError($this->getFormError($model['pgwCreditCardError']));
        }

        if (1102 == $model['pgwCreditCardError']) {
            $form->get('securityCode')->addError($this->getFormError($model['pgwCreditCardError']));
        }

        if (1400 == $model['pgwCreditCardError']) {
            $form->get('holder')->addError($this->getFormError($model['pgwCreditCardError']));
        }

        if (1401 == $model['pgwCreditCardError']) {
            $form->get('lastName')->addError($this->getFormError($model['pgwCreditCardError']));
        }

        if (1402 == $model['pgwCreditCardError']) {
            $form->get('street')->addError($this->getFormError($model['pgwCreditCardError']));
        }

        if (1403 == $model['pgwCreditCardError']) {
            $form->get('city')->addError($this->getFormError($model['pgwCreditCardError']));
        }

        if (1404 == $model['pgwCreditCardError']) {
            $form->get('postCode')->addError($this->getFormError($model['pgwCreditCardError']));
        }

        if (1405 == $model['pgwCreditCardError']) {
            $form->get('country')->addError($this->getFormError($model['pgwCreditCardError']));
        }

        if (1406 == $model['pgwCreditCardError']) {
            $form->get('phoneNumber')->addError($this->getFormError($model['pgwCreditCardError']));
        }

        if (1407 == $model['pgwCreditCardError']) {
            $form->get('email')->addError($this->getFormError($model['pgwCreditCardError']));
        }
    }

    private function getFormError($pgwErrorCode)
    {
        return new FormError(ResponseCodeInterpreter::getPgwResultCodeWithoutFields($pgwErrorCode));
    }

}
