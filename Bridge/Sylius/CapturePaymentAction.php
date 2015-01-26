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
    /**
     * @var EngineInterface
     */
    protected $templating;

    /**
     * @var string
     */
    protected $templateName;

    /**
     * @var Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * @param EngineInterface $templating
     * @param string $templateName
     * @param Request $request
     */
    public function __construct(EngineInterface $templating, $templateName, $request)
    {
        $this->templating = $templating;
        $this->templateName = $templateName;
        $this->request = $request;

        if ($this->request->get('PaRes') !== null) {
            $_SESSION['PaRes'] = $this->request->get('PaRes');
        }
    }

    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request Capture */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        /** @var $payment PaymentInterface */
        $payment = $request->getModel();

        /** @var OrderInterface $order */
        $order = $payment->getOrder();

        $details = $payment->getDetails();

        if (empty($details)) {
            $this->payment->execute($obtainCreditCardRequest = new ObtainCreditCard($order));
            $creditCard = $obtainCreditCardRequest->obtain();

            $details = array();
            $details['firstName'] = $order->getBillingAddress()->getFirstName();
            $details['lastName'] = $order->getBillingAddress()->getLastName();
            $details['email'] = $order->getUser()->getEmail();
            $details['street'] = $order->getBillingAddress()->getStreet();
            $details['city'] = $order->getBillingAddress()->getCity();
            $details['postalCode'] = $order->getBillingAddress()->getPostcode();
            $details['country'] = $order->getBillingAddress()->getCountry()->getName();
            $details['phoneNumber'] = ''; // ?: 'Unknown'
            $details['amount'] = 2350; //$order->getTotal();

//            $details['card'] = new SensitiveValue(array(
//                'number'      => $creditCard->getNumber(),
//                'expiryMonth' => $creditCard->getExpiryMonth(),
//                'expiryYear'  => $creditCard->getExpiryYear(),
//                'cvd'         => $creditCard->getSecurityCode()
//            ));

            $details['card_number'] = $creditCard->getNumber();
            $details['card_expiryMonth'] = $creditCard->getExpiryMonth();
            $details['card_expiryYear'] = $creditCard->getExpiryYear();
            $details['card_cvd'] = $creditCard->getSecurityCode();

            $details['numOfInstallments'] =  1;

            $details['shoppingCartId'] =  $order->getNumber() . "-" . rand();
            $details['paymentMode'] =  1;

            $details['httpAccept'] = $this->request->headers->get('Accept');
            $details['httpUserAgent'] = $this->request->headers->get('User-Agent');
            $details['originIP'] = $this->request->getClientIp();

            $payment->setDetails($details);
        }

        $details = ArrayObject::ensureArrayObject($details);

        try {
            $request->setModel($details);
            $this->payment->execute($request);

            $payment->setDetails((array) $details);
            $request->setModel($payment);
        } catch (HttpRedirect $interactiveRequest) {
            $payment->setDetails((array) $details);
            $request->setModel($payment);
//            $rawDetails = (array) $details;

            throw $interactiveRequest;

//            throw new ResponseInteractiveRequest($this->templating->render(
//                $this->templateName,
//                array(
//                    "ASCUrl" => $rawDetails['ASCUrl'],
//                    "PaReq" => $rawDetails['PaReq'],
//                    "TermUrl" => $rawDetails['PaReq']
//                )
//            ));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof PaymentInterface
        ;
    }
}
