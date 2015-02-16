<?php

namespace Locastic\TcomPaywayPayumBundle\Action;

use Locastic\TcomPayWay\Handlers\TcomPayWayPaymentProcessHandler;
use Locastic\TcomPayWay\Model\Card;
use Locastic\TcomPayWay\Model\Customer\Customer;
use Locastic\TcomPayWay\Model\Customer\CustomersClient;
use Locastic\TcomPayWay\Model\Payment;
use Locastic\TcomPayWay\Model\Shop;
use Locastic\TcomPayWay\Model\Transaction;
use Payum\Core\Action\PaymentAwareAction;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Request\Capture;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\RenderTemplate;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Exception\LogicException;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\ObtainCreditCard;


class CaptureAction extends PaymentAwareAction implements ApiAwareInterface
{
    private $shop_id;

    private $shop_username;

    private $shop_password;

    private $shop_secret_key;

    private $secure3d_template;

    private $preauth_required;

    /**
     * @var TcomPayWayPaymentProcessHandler
     */
    protected $api;

    function __construct(
        $shop_id,
        $shop_username,
        $shop_password,
        $shop_secret_key,
        $secure3d_template,
        $preauth_required
    ) {
        $this->shop_id = $shop_id;
        $this->shop_username = $shop_username;
        $this->shop_password = $shop_password;
        $this->shop_secret_key = $shop_secret_key;
        $this->secure3d_template = $secure3d_template;
        $this->preauth_required = $preauth_required;
    }

    /**
     * {@inheritDoc}
     */
    public function setApi($api)
    {
        if (false == $api instanceof TcomPayWayPaymentProcessHandler) {
            throw new UnsupportedApiException('Expected instance of TcomPayWayPaymentProcessHandler object.');
        }

        $this->api = $api;
    }

    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request SecuredCapture */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        $model = ArrayObject::ensureArrayObject($request->getModel());
        if (
            $model['paymentStatus'] != 'secure3d' &&
            $model['paymentStatus'] != 'error' &&
            $model['paymentStatus'] != 'finished' &&
            $model['paymentStatus'] != 'success') {
            if (false == $model['httpUserAgent']) {
                $this->payment->execute($httpRequest = new GetHttpRequest());
                $model['httpUserAgent'] = $httpRequest->userAgent;
            }

            if (false == $model['originIP']) {
                $this->payment->execute($httpRequest = new GetHttpRequest());
                $model['originIP'] = $httpRequest->clientIp;
            }

            if (false == $model['httpAccept']) {
                $this->payment->execute($httpRequest = new GetHttpRequest());
                $model['httpAccept'] = $httpRequest->headers['accept'][0];
            }

            $cardFields = array(
                'card_number',
                'card_expiration_date',
                'card_cvd',
                'firstName',
                'lastName',
                'address',
                'city',
                'zipCode',
                'country',
                'email',
                'phoneNumber'
            );
            if (false == $model->validateNotEmpty($cardFields, false)) {
                try {
                    $this->payment->execute($creditCardRequest = new ObtainCreditCard());
                    $card = $creditCardRequest->obtain();
                    $model['card_expiration_date'] = $card->getExpireAt()->format('Y-m-d');
                    $model['card_number'] = $card->getNumber();
                    $model['card_cvd'] = $card->getSecurityCode();
                    $model['firstName'] = $card->getHolder();
                    $model['lastName'] = $card->getHolderSurname();
                    $model['email'] = $card->getEmail();
                    $model['address'] = $card->getAddress();
                    $model['city'] = $card->getCity();
                    $model['zipCode'] = $card->getZipCode();
                    $model['country'] = $card->getCountry();
                    $model['phoneNumber'] = $card->getPhoneNumber();
                    $model['numOfInstallments'] = 1; // TODO set custom number of installments
                    $model['paymentMode'] = $this->preauth_required;
                } catch (RequestNotSupportedException $e) {
                    throw new LogicException(
                        'Credit card details has to be set explicitly or there has to be an action that supports ObtainCreditCard request.'
                    );
                }
            }
        }

        $shop = new Shop($this->shop_id, $this->shop_username, $this->shop_password, $this->shop_secret_key);
        $customersClient = new CustomersClient($model['httpAccept'], $model['httpUserAgent'], $model['originIP']);

        $customer = new Customer(
            $model['firstName'],
            $model['lastName'],
            $model['address'],
            $model['city'],
            $model['zipCode'],
            $model['country'],
            $model['email'],
            $model['phoneNumber'],
            $customersClient
        );

        $card = new Card(
            $model['card_number'],
            $model['card_expiration_date'],
            $model['card_cvd']
        );

        $payment = new Payment(
            $model['shoppingCartId'],
            $model['amount'],
            $model['numOfInstallments'],
            $model['paymentMode']
        );

        if (is_object($shop) && is_object($customer) && is_object($card) && is_object($payment)) {
            $transaction = new Transaction($shop, $customer, $card, $payment);
            if (isset($_POST['PaRes'])) {
                $transaction->setSecure3dpares($_POST['PaRes']);
            }

            $response = $this->api->process($transaction);

            $model['paymentStatus'] = $response['status'];

            if ($response['status'] == 'secure3d') {
                $model['ASCUrl'] = $response['ASCUrl'];
                $model['PaReq'] = $response['PaReq'];
                $model['TermUrl'] = $request->getToken()->getTargetUrl();

                $secure3dTmpl = new RenderTemplate(
                    $this->secure3d_template, array(
                        'ASCUrl' => $response['ASCUrl'],
                        'PaReq' => $response['PaReq'],
                        'TermUrl' => $request->getToken()->getTargetUrl(),
                    )
                );

                $this->payment->execute($secure3dTmpl);
                throw new HttpResponse($secure3dTmpl->getResult());
            } elseif ($response['status'] == 'error') {
                $model['tcomData'] = $response;
            }
        }
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
}
