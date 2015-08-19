<?php

namespace Locastic\TcomPayWayPayumBundle\Bridge\Sylius;

use Locastic\TcomPayWay\AuthorizeDirect\Model\Payment as OnsiteApi;
use Locastic\TcomPayWay\AuthorizeForm\Model\Payment as OffsiteApi;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;
use SM\Factory\FactoryInterface;
use Sylius\Bundle\PayumBundle\Payum\Action\AbstractPaymentStateAwareAction;
use Sylius\Component\Payment\Model\PaymentInterface;
use Doctrine\Common\Persistence\ObjectManager;

class PaymentStatusAction extends AbstractPaymentStateAwareAction implements ApiAwareInterface
{
    /** @var  mixed */
    protected $api;

    protected $objectManager;

    public function __construct(ObjectManager $objectManager, FactoryInterface $factory)
    {
        parent::__construct($factory);

        $this->objectManager = $objectManager;
    }


    /**
     * {@inheritDoc}
     */
    public function setApi($api)
    {
        if (false == $api instanceof OnsiteApi && false == $api instanceof OffsiteApi) {
            throw new UnsupportedApiException('Not supported.');
        }

        $this->api = $api;
    }


    /**
     * {@inheritDoc}
     *
     * @param $request GetStatusInterface
     */
    public function execute($request)
    {
        if (!$this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        /** @var $payment PaymentInterface */
        $payment = $request->getModel();
        $paymentDetails = $payment->getDetails();

        if (empty($paymentDetails)) {
            $request->markNew();

            return;
        }


        if (false == isset($paymentDetails['tcompayway_response'])) {
            $request->markNew();

            return;
        }

        $statusCode = $paymentDetails['tcompayway_response']['pgw_result_code'];

        if (0 == $statusCode && 0 == $this->api->getPgwAuthorizationType()) {
            // to do this should be authorized but sylius has bug atm
            $request->markCaptured();

            return;
        }

        if (0 == $statusCode && 1 == $this->api->getPgwAuthorizationType()) {
            $request->markCaptured();

            return;
        }

        if ($statusCode > 0) {
            $request->markFailed();

            return;
        }

        $request->markUnknown();
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof GetStatusInterface &&
            $request->getModel() instanceof PaymentInterface;
    }
}
