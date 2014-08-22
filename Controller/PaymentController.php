<?php
namespace Locastic\TcomPaywayPayumBundle\Controller;

use Locastic\TcomPaywayPayumBundle\Entity\CreditCard;
use Payum\Core\Request\GetHumanStatus;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Locastic\TcomPaywayPayumBundle\Entity\PaymentDetails;
use Payum\Core\Registry\RegistryInterface;
use Payum\Core\Security\GenericTokenFactoryInterface;

class PaymentController extends Controller
{
    public function prepareAction(Request $request)
    {
        $paymentName = 'tcompayway';

        $form = $this->createCreditCardForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            /** @var CreditCard $creditCard */
            $creditCard = $form->getData();
            $storage = $this->getPayum()->getStorage('Locastic\TcomPaywayPayumBundle\Entity\PaymentDetails');

            /** @var PaymentDetails */
            $paymentDetails = $storage->createModel();

            $paymentDetails['firstName'] = $creditCard->getHolder();
            $paymentDetails['lastName'] = $creditCard->getHolderSurname();
            $paymentDetails['email'] = $creditCard->getEmail();
            $paymentDetails['address'] = $creditCard->getAddress();
            $paymentDetails['city'] = $creditCard->getCity();
            $paymentDetails['zipCode'] = $creditCard->getZipCode();
            $paymentDetails['country'] = $creditCard->getCountry();
            $paymentDetails['phoneNumber'] = $creditCard->getPhoneNumber();

            // todo get amount from form
            $paymentDetails['amount'] = 300;

            $paymentDetails['card_number'] = $creditCard->getNumber();
            $paymentDetails['card_expiration_date'] = $creditCard->getExpireAt();
            $paymentDetails['card_cvd'] = $creditCard->getSecurityCode();

            // todo get values from form
            $paymentDetails['numOfInstallments'] = 1;
            $paymentDetails['paymentMode'] = 1;

            $paymentDetails['httpAccept'] = $request->headers->get('Accept');
            $paymentDetails['httpUserAgent'] = $request->headers->get('User-Agent');
            $paymentDetails['originIP'] = $request->getClientIp();

            $storage->updateModel($paymentDetails);

            $paymentDetails['shoppingCartId'] = "Kladise-" . $paymentDetails->getId();

            $captureToken = $this->getTokenFactory()->createCaptureToken(
                $paymentName,
                $paymentDetails,
                'locastic_tcompaywaypayum_capture_done'
            );

            return $this->redirect($captureToken->getTargetUrl());
        }

        return $this->render(
            'LocasticTcomPaywayPayumBundle:TcomPayWay:prepare.html.twig',
            array(
                'form' => $form->createView()
            )
        );
    }

    public function captureDoneAction(Request $request)
    {
        $token = $this->get('payum.security.http_request_verifier')->verify($request);

        $payment = $this->get('payum')->getPayment($token->getPaymentName());

        $payment->execute($status = new GetHumanStatus($token));
        if ($status->isSuccess()) {
            // do some custom action
            // for example add add user some credits
            $this->get('session')->getFlashBag()->set(
                'notice',
                'Payment success.'
            );
        } else {
            if ($status->isPending()) {
                $this->get('session')->getFlashBag()->set(
                    'notice',
                    'Payment is still pending.'
                );
            } else {
                $this->get('session')->getFlashBag()->set('error', 'Payment failed');
            }
        }

        return $this->render('LocasticTcomPaywayPayumBundle:TcomPayWay:done.html.twig');
    }

    protected function createCreditCardForm()
    {
        return $this->createForm('locastic_credit_card');
    }

    /**
     * @return RegistryInterface
     */
    protected function getPayum()
    {
        return $this->get('payum');
    }

    /**
     * @return GenericTokenFactoryInterface
     */
    protected function getTokenFactory()
    {
        return $this->get('payum.security.token_factory');
    }
}
