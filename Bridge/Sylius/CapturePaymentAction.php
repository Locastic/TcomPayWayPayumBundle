<?php

namespace Locastic\TcomPayWayPayumBundle\Bridge\Sylius;

use Payum\Core\Action\PaymentAwareAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Security\TokenInterface;
use Sylius\Component\Core\Model\Order;
use Sylius\Component\Payment\Model\PaymentInterface;

class CapturePaymentAction extends PaymentAwareAction
{
    /**
     * {@inheritdoc}
     *
     * @param $request Capture
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var $payment PaymentInterface */
        $payment = $request->getModel();

        $this->composeDetails($payment, $request->getToken());

        $details = ArrayObject::ensureArrayObject($payment->getDetails());

        try {
            $request->setModel($details);
            $this->payment->execute($request);

            $payment->setDetails($details);
        } catch (\Exception $e) {
            $payment->setDetails($details);

            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof PaymentInterface
            ;
    }

    /**
     * @param PaymentInterface $payment
     * @param TokenInterface   $token
     */
    protected function composeDetails(PaymentInterface $payment, TokenInterface $token)
    {
        if ($payment->getDetails()) {
            return;
        }

        /** @var Order $order */
        $order = $payment->getOrder();

        $details = array();

        $details['pgwOrderId'] = $order->getNumber();
        $details['pgwAmount'] = $order->getTotal();
//        $details['pgwLanguage'] = $order->getUser()->getLocale();
        $details['pgwFirstName'] = $order->getBillingAddress()->getFirstName();
        $details['pgwLastName'] = $order->getBillingAddress()->getLastName();
        $details['pgwStreet'] = $order->getBillingAddress()->getStreet();
        $details['pgwCity'] = $order->getBillingAddress()->getCity();
        $details['pgwPostCode'] = $order->getBillingAddress()->getPostCode();
        if($order->getBillingAddress()->getCountry()) {
            $details['pgwCountry'] = $order->getBillingAddress()->getCountry()->getIsoName();
        }
        $details['pgwPhoneNumber'] = $order->getBillingAddress()->getPhoneNumber();
        $details['pgwEmail'] = $order->getUser()->getEmail();

        $payment->setDetails($details);
    }
}
