<?php

namespace Locastic\TcomPayWayPayumBundle\Controller;

use Locastic\TcomPayWay\AuthorizeForm\Model\Payment;
use Payum\Core\Request\GetHumanStatus;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;


class OffsiteController extends Controller
{
    public function prepareAction()
    {
        $model = new Payment(20000445, 'bHVG5tR$edS3w', uniqid(), 100, 0, 'http://www.locastic.com', 'http://www.locastic.com');

        return $this->render('LocasticTcomPayWayPayumBundle:TcomPayWay:prepareAuthorizationForm.html.twig', array('model' => $model));









        $paymentName = 'tcompayway_offline';

        $storage = $this->get('payum')->getStorage('Locastic\TcomPayWayPayumBundle\Entity\Payment');

        $payment = $storage->create();
        $payment->setNumber(uniqid());
        $payment->setCurrencyCode('EUR');
        $payment->setTotalAmount(123); // 1.23 EUR
        $payment->setDescription('A description');
        $payment->setClientId('anId');
        $payment->setClientEmail('foo@example.com');
        $payment->setMaet('gfd');

        $storage->update($payment);

        $captureToken = $this->get('payum.security.token_factory')->createCaptureToken(
            $paymentName,
            $payment,
            'locastic_tcompaywaypayum_offsite_capture_done' // the route to redirect after capture
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
