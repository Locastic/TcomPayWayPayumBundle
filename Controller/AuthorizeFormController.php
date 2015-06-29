<?php

namespace Locastic\TcomPaywayPayumBundle\Controller;

use Payum\Core\Request\GetHumanStatus;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;


class AuthorizeFormController extends Controller
{
    public function prepareAction()
    {
        $paymentName = 'offline';

        $storage = $this->get('payum')->getStorage('Locastic\TcomPayWayPayumBundle\Entity\Payment');

        $payment = $storage->create();
        $payment->setNumber(uniqid());
        $payment->setCurrencyCode('EUR');
        $payment->setTotalAmount(123); // 1.23 EUR
        $payment->setDescription('A description');
        $payment->setClientId('anId');
        $payment->setClientEmail('foo@example.com');

        $storage->update($payment);

        $captureToken = $this->get('payum.security.token_factory')->createCaptureToken(
            $paymentName,
            $payment,
            'locastic_tcompaywaypayum_authorize_form_capture_done' // the route to redirect after capture
        );

        return $this->redirect($captureToken->getTargetUrl());
    }

    public function captureDoneAction(Request $request)
    {
        $token = $this->get('payum.security.http_request_verifier')->verify($request);

        $payment = $this->get('payum')->getPayment($token->getPaymentName());

        // you can invalidate the token. The url could not be requested any more.
        // $this->get('payum.security.http_request_verifier')->invalidate($token);

        // Once you have token you can get the model from the storage directly.
        //$identity = $token->getDetails();
        //$order = $payum->getStorage($identity->getClass())->find($identity);

        // or Payum can fetch the model for you while executing a request (Preferred).
        $payment->execute($status = new GetHumanStatus($token));
        $order = $status->getFirstModel();

        // you have order and payment status
        // so you can do whatever you want for example you can just print status and payment details.

        return new JsonResponse(array(
            'status' => $status->getValue(),
            'order' => array(
                'total_amount' => $order->getTotalAmount(),
                'currency_code' => $order->getCurrencyCode(),
                'details' => $order->getDetails(),
            ),
        ));
    }
}
