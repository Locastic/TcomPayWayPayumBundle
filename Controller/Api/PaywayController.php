<?php

namespace Locastic\TcomPayWayPayumBundle\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class PayWayController extends Controller
{
    public function authorizationAnnounceAction(Request $request)
    {
        $result = $this->get('locastic.tcompayway.api')->authorizationAnnounce(
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
        $result = $this->get('locastic.tcompayway.api')->authorizationComplete(
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
        $result = $this->get('locastic.tcompayway.api')->authorizationCancel(
            $request->request->get('pgwTransactionId')
        );

        if ($result->ResultCode > 0) {
            throw new NotFoundHttpException($result->Description);
        }

        return new JsonResponse($result);
    }

    public function authorizationRefundAction(Request $request)
    {
        $result = $this->get('locastic.tcompayway.api')->authorizationRefund(
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
        $result = $this->get('locastic.tcompayway.api')->authorizationRefund(
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
        $result = $this->get('locastic.tcompayway.api')->installments(
            $request->request->get('pgwAmount'),
            $request->request->get('pgwCreditCard')
        );

        if ($result->ResultCode > 0) {
            throw new NotFoundHttpException($result->Description);
        }

        return new JsonResponse($result);
    }
}
