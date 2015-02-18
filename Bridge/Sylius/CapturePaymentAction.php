<?php
namespace Locastic\TcomPaywayPayumBundle\Bridge\Sylius;

use Payum\Core\Action\PaymentAwareAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Capture;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Symfony\Component\Templating\EngineInterface;
use Payum\Core\Reply\HttpRedirect;
use Symfony\Component\HttpFoundation\Request;
use Payum\Core\Request\ObtainCreditCard;

class CapturePaymentAction extends PaymentAwareAction
{
    protected $shopName;

    function __construct($shopName)
    {
        $this->shopName = $shopName;
    }

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

        $this->composeDetails($payment);

        $details = ArrayObject::ensureArrayObject($payment->getDetails());

        try {
            $request->setModel($details);
            $this->payment->execute($request);

            $payment->setDetails($details);
            $request->setModel($payment);
        } catch (\Exception $e) {
            $payment->setDetails($details);
            $request->setModel($payment);

            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function composeDetails(PaymentInterface $payment)
    {
        if ($payment->getDetails()) {

            return;
        }
        $order = $payment->getOrder();

        $details = array();

        $details['firstName'] = $order->getBillingAddress()->getFirstName();
        $details['lastName'] = $order->getBillingAddress()->getLastName();
        $details['address'] = $order->getBillingAddress()->getStreet();
        $details['city'] = $order->getBillingAddress()->getCity();
        $details['zipCode'] = $order->getBillingAddress()->getPostCode();
        $details['country'] = $order->getBillingAddress()->getCountry();
        $details['phoneNumber'] = $order->getBillingAddress()->getPhoneNumber();
        $details['email'] = $order->getUser()->getEmail();
        $details['amount'] = $order->getTotal();
        $details['shoppingCartId'] = $this->shopName . ' ' . $order->getNumber() . ' (' . (new \DateTime())->format(
                'd.m.Y.'
            ) . ')';

        $payment->setDetails($details);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof PaymentInterface;
    }
}
