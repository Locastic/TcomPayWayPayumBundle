<?php

namespace Locastic\TcomPayWayPayumBundle\Controller\Api;

use Locastic\TcomPayWay\AuthorizeDirect\Api;
use Locastic\TcomPayWayPayumBundle\Request\GetApi;
use Payum\Core\Payum;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class PayWayController extends Controller
{
    public function authorizationAnnounceAction(Request $request)
    {
        $result = $this->getApi()->authorizationAnnounce(
            $request->request->get('pgwOrderId'),
            $request->request->get('pgwAmount'),
            $request->request->get('pgwAnnouncementDuration')
        );

        if ($result->ResultCode > 0) {
            throw new NotFoundHttpException($result->Description);
        }

        return new JsonResponse($result);
    }

    public function authorizationCompleteAction(Request $request)
    {
        $result = $this->getApi()->authorizationComplete(
            $request->request->get('pgwTransactionId'),
            $request->request->get('pgwAmount')
        );

        if ($result->ResultCode > 0) {
            throw new NotFoundHttpException($result->Description);
        }

        return new JsonResponse($result);
    }

    public function authorizationCancelAction(Request $request)
    {
        $result = $this->getApi()->authorizationCancel(
            $request->request->get('pgwTransactionId')
        );

        if ($result->ResultCode > 0) {
            throw new NotFoundHttpException($result->Description);
        }

        return new JsonResponse($result);
    }

    public function authorizationRefundAction(Request $request)
    {
        $result = $this->getApi()->authorizationRefund(
            $request->request->get('pgwTransactionId'),
            $request->request->get('pgwAmount')
        );

        if ($result->ResultCode > 0) {
            throw new NotFoundHttpException($result->Description);
        }

        return new JsonResponse($result);
    }

    public function authorizationInfoAction(Request $request)
    {
        $result = $this->getApi()->authorizationRefund(
            $request->request->get('pgwTransactionId'),
            $request->request->get('pgwOrderId')
        );

        if ($result->ResultCode > 0) {
            throw new NotFoundHttpException($result->Description);
        }

        return new JsonResponse($result);
    }


    public function installmentsAction(Request $request)
    {
        $result = $this->getApi()->installments(
            $request->request->get('pgwAmount'),
            $request->request->get('pgwCreditCard')
        );

        if ($result->ResultCode > 0) {
            throw new NotFoundHttpException($result->Description);
        }

        return new JsonResponse($result);
    }

    /**
     * @return Api
     */
    private function getApi()
    {
        /** @var Payum $payum */
        $payum = $this->get('payum');

        $payum->getGateway('tcompayway')->execute($getApi = new GetApi());
        
        return $getApi->getApi();
    }
}
