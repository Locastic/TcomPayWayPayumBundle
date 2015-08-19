<?php
namespace Locastic\TcomPayWayPayumBundle\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\FillOrderDetails;

class FillOrderDetailsAction implements ActionInterface
{
    /**
     * {@inheritDoc}
     *
     * @param FillOrderDetails $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $order = $request->getOrder();

        $details = $order->getDetails();
        $details['pgwOrderId'] = $order->getNumber();
        $details['pgwAmount'] = $order->getTotalAmount();
        $details['pgwEmail'] = $order->getClientEmail();

        $model['pgwFirstName'] = '';
        $model['pgwLastName'] = '';
        $model['pgwStreet'] = '';
        $model['pgwCity'] = '';
        $model['pgwPostCode'] = '';
        $model['pgwCountry'] = '';
        $model['pgwPhoneNumber'] = '';

        $model['pgwLanguage'] = '';
        $model['pgwMerchantData'] = '';
        $model['pgwOrderInfo'] = '';
        $model['pgwOrderItems'] = '';

        $order->setDetails($details);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof FillOrderDetails;
    }
}
