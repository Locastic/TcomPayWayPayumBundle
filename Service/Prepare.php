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

    public function prepareAuthorizeForm($amount, $cartName = "Locastic-")
    {
        $paymentName = 'tcompayway_offsite';
        $storage = $this->payum->getStorage('Locastic\TcomPaywayPayumBundle\Entity\PaymentDetails');

        /** @var $paymentDetails PaymentDetails */
        $paymentDetails = $storage->create();
        $paymentDetails['amount'] = $amount;
        $storage->update($paymentDetails);
        $paymentDetails['shoppingCartId'] = $cartName . $paymentDetails->getId();

        $captureToken = $this->tokenFactory->createCaptureToken(
            $paymentName,
            $paymentDetails,
            'locastic_tcompaywaypayum_authorize_form_capture_done'
        );

        return $captureToken->getTargetUrl();
    }


}