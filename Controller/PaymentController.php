<?php
namespace alkr\ShopBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use JMS\Payment\CoreBundle\Entity\Payment;
use JMS\Payment\CoreBundle\PluginController\Result;
use JMS\Payment\CoreBundle\Plugin\Exception\ActionRequiredException;
use JMS\Payment\CoreBundle\Plugin\Exception\Action\VisitUrl;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Doctrine\Common\Util\Debug;

/**
 * @Route("/payments")
 */
class PaymentController extends Controller
{
    /**
     * @Route("/{cart}/details", name = "payment_details")
     * @Template
     */
    public function detailsAction(Request $request, $cart)
    {
        $em = $this->getDoctrine()->getManager();
        $ppc = $this->get('payment.plugin_controller');
        $cart = $em->getRepository('ShopBundle:Cart')->findOneByHash($cart);
        $shop_params = $this->container->getParameter('shop');
        $form = $this->get('form.factory')->create('jms_choose_payment_method', null, array(
            'amount'   => $cart->calculateAmount()->getAmount(),
            'currency' => $shop_params['currency'],
            'predefined_data' => array(
                'paypal_express_checkout' => array(
                    'return_url' => $this->generateUrl('payment_complete', array(
                        'cart' => $cart->getHash(),
                    ), true),
                    'cancel_url' => $this->generateUrl('payment_cancel', array(
                        'cart' => $cart->getHash(),
                    ), true)
                ),
                'robokassa' => array(
                    'return_url' => $this->generateUrl('payment_result', array(
                        'cart' => $cart->getHash(),
                    ), true),
                    'cancel_url' => $this->generateUrl('payment_result', array(
                        'cart' => $cart->getHash(),
                    ), true)
                ),
            ),
        ));

        $form->add('submit', 'submit', array('label' => 'Создать'));

        if ('POST' === $request->getMethod()) {
            $form->bind($request);

            if ($form->isValid()) {
                $ppc->createPaymentInstruction($instruction = $form->getData());

                $cart->setPaymentInstruction($instruction);
                $em->persist($cart);
                $em->flush($cart);

                return new RedirectResponse($this->generateUrl('payment_complete', array(
                    'cart' => $cart->getHash(),
                )));
            }
        }

        return array(
            'form' => $form->createView()
        );
    }

    /**
     * @Route("/{cart}/complete", name = "payment_complete")
     */
    public function completeAction(Request $request,$cart)
    {
        $request->getSession()->set('cart',null);
        $em = $this->getDoctrine()->getManager();
        $ppc = $this->get('payment.plugin_controller');
        $cart = $em->getRepository('ShopBundle:Cart')->findOneByHash($cart);
        $instruction = $cart->getPaymentInstruction();
        if (null === $pendingTransaction = $instruction->getPendingTransaction()) {
            $payment = $ppc->createPayment($instruction->getId(), $instruction->getAmount() - $instruction->getDepositedAmount());
        } else {
            $payment = $pendingTransaction->getPayment();
        }

        $result = $ppc->approveAndDeposit($payment->getId(), $payment->getTargetAmount());
        if (Result::STATUS_PENDING === $result->getStatus()) {
            $ex = $result->getPluginException();

            if ($ex instanceof ActionRequiredException) {
                $action = $ex->getAction();

                if ($action instanceof VisitUrl) {
                    return new RedirectResponse($action->getUrl());
                }

                throw $ex;
            }
        } else if (Result::STATUS_SUCCESS !== $result->getStatus()) {
            throw new \RuntimeException('Transaction was not successful: '.$result->getReasonCode());
        }

        // payment was successful, do something interesting with the order
    }

    /**
     * @Route("/{cart}/cancel", name = "payment_cancel")
     */
    public function cancelAction(Request $request,$cart)
    {
        $request->getSession()->set('cart',null);
        $em = $this->getDoctrine()->getManager();
        $ppc = $this->get('payment.plugin_controller');
        $cart = $em->getRepository('ShopBundle:Cart')->findOneByHash($cart);
        $instruction = $cart->getPaymentInstruction();
        if (null === $pendingTransaction = $instruction->getPendingTransaction()) {
            $payment = $ppc->createPayment($instruction->getId(), $instruction->getAmount() - $instruction->getDepositedAmount());
        } else {
            $payment = $pendingTransaction->getPayment();
        }

        $result = $ppc->approveAndDeposit($payment->getId(), $payment->getTargetAmount());
        if (Result::STATUS_PENDING === $result->getStatus()) {
            $ex = $result->getPluginException();

            if ($ex instanceof ActionRequiredException) {
                $action = $ex->getAction();

                if ($action instanceof VisitUrl) {
                    return new RedirectResponse($action->getUrl());
                }

                throw $ex;
            }
        } else if (Result::STATUS_SUCCESS !== $result->getStatus()) {
            throw new \RuntimeException('Transaction was not successful: '.$result->getReasonCode());
        }

        // payment was successful, do something interesting with the order
    }

    /**
     * @Route("/{cart}/result", name = "payment_result")
     * @Template()
     */
    public function resultAction(Request $request,$cart)
    {
        $em = $this->getDoctrine()->getManager();
        $cart = $em->getRepository('ShopBundle:Cart')->findOneByHash($cart);
        $instruction = $cart->getPaymentInstruction();
        return array('instruction'=>$instruction,'cart'=>$cart);
    }
}