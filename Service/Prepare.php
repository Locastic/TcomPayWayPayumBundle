<?php

namespace Locastic\TcomPaywayPayumBundle\Service;

use Locastic\TcomPaywayPayumBundle\Entity\PaymentDetails;

class Prepare
{
    private $payum;

    private $tokenFactory;

    function __construct($payum, $tokenFactory)
    {
        $this->payum = $payum;
        $this->tokenFactory = $tokenFactory;
    }

    public function prepare($amount, $cartName = "Locastic-")
    {
        $paymentName = 'tcompayway';
        $storage = $this->payum->getStorage('Locastic\TcomPaywayPayumBundle\Entity\PaymentDetails');

        /** @var $paymentDetails PaymentDetails */
        $paymentDetails = $storage->createModel();
        $paymentDetails['amount'] = $amount;
        $storage->updateModel($paymentDetails);
        $paymentDetails['shoppingCartId'] = $cartName . $paymentDetails->getId();

        $captureToken = $this->tokenFactory->createCaptureToken(
            $paymentName,
            $paymentDetails,
            'locastic_tcompaywaypayum_capture_done'
        );

        return $captureToken->getTargetUrl();
    }


}