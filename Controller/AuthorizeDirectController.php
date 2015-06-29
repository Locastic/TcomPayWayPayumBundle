<?php
namespace Locastic\TcomPaywayPayumBundle\Controller;

use Payum\Core\Request\GetHumanStatus;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Payum\Core\Registry\RegistryInterface;
use Payum\Core\Security\GenericTokenFactoryInterface;

class AuthorizeDirectController extends Controller
{
    public function prepareAction()
    {
        return $this->redirect($this->get('locastic_tcompaywaypayumbundle.service.prepare')->prepare(1000));
    }

    public function captureDoneAction(Request $request)
    {
        $token = $this->get('payum.security.http_request_verifier')->verify($request);

        $payment = $this->get('payum')->getPayment($token->getPaymentName());

        $payment->execute($status = new GetHumanStatus($token));
        if ($status->isSuccess()) {
            // do some custom action
            // for example add add user some credits
            $this->get('session')->getFlashBag()->set(
                'notice',
                'Payment success.'
            );
        } else {
            if ($status->isPending()) {
                $this->get('session')->getFlashBag()->set(
                    'notice',
                    'Payment is still pending.'
                );
            } else {
                $this->get('session')->getFlashBag()->set('error', 'Payment failed');
            }
        }

        return $this->render($this->container->getParameter('done_template'));
    }
}
