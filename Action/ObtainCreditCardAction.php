<?php

namespace Locastic\TcomPayWayPayumBundle\Action;

use Locastic\TcomPayWay\Helpers\ResponseCodeInterpreter;
use Locastic\TcomPayWayPayumBundle\Entity\CreditCard;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Symfony\Reply\HttpResponse;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Model\CreditCardInterface;
use Payum\Core\Request\ObtainCreditCard;
use Payum\Core\Request\RenderTemplate;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Payum\Core\Bridge\Spl\ArrayObject;

class ObtainCreditCardAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var RequestStack
     */
    protected $httpRequestStack;

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
     * @param RequestStack|null $httpRequestStack
     */
    public function setRequestStack(RequestStack $httpRequestStack = null)
    {
        $this->httpRequestStack = $httpRequestStack;
    }

    /**
     * {@inheritDoc}
     *
     * @param ObtainCreditCard $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        if (!$this->httpRequestStack) {
            throw new LogicException('The request stack is not set.');
        }

        if ($httpRequest = $this->httpRequestStack->getMasterRequest()) {
            throw new LogicException('The action can be run only when http master request is set.');
        }

        $form = $this->createCreditCardForm($request->getModel());

        $form->handleRequest($httpRequest);
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
        $this->gateway->execute($renderTemplate);

        throw new HttpResponse(new Response($renderTemplate->getResult(), 200, [
            'Cache-Control' => 'no-store, no-cache, max-age=0, post-check=0, pre-check=0',
            'Pragma' => 'no-cache',
        ]));
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof ObtainCreditCard && $request->getModel() instanceof \ArrayAccess;
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
