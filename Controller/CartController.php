<?php

namespace alkr\ShopBundle\Controller;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use alkr\ShopBundle\Entity\Good;
use alkr\ShopBundle\Entity\Order;
use alkr\ShopBundle\Entity\Cart;

/**
 * Order controller.
 *
 * @Route("/cart")
 */
class CartController extends Controller
{

    /**
     * Lists all Good entities.
     *
     * @Route("/", name="cart")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $cart = $em->getRepository('ShopBundle:Cart')->findOneByHash($request->getSession()->get('cart',false));
        print_r ($this->container->getParameter('imagine.filters'));
        return array(
            'cart' => $cart,
        );
    }

    /**
     * @Route("/add/{goodId}", name="add_order")
     * @Method("POST")
     * @ParamConverter("good", class="ShopBundle:Good", options={"id" = "goodId"})
     */
    public function addAction(Request $request, Good $good)
    {
        $em = $this->getDoctrine()->getManager();
        $cart = $em->getRepository('ShopBundle:Cart')->findOneByHash($request->getSession()->get('cart',0));

        if(!is_object($cart)) {
            $cart = new Cart($this->getUser());
            $em->persist($cart);
            $request->getSession()->set('cart',$cart->getHash());
            $order = new Order();
            $order->setGood($good)->setCount($request->get('count',0))->setCart($cart);
            $good->addOrder($order);
            $cart->addOrder($order);
            $em->persist($order);
        } else {
            $check = true;
            foreach ($cart->getOrders() as $order) {
                if($order->getGood() == $good) {
                    $order->setCount($order->getCount()+$request->get('count',0));
                    $check = false;
                    break;
                }
            }
            if($check) {
                $order = new Order();
                $order->setGood($good)->setCount($request->get('count',0))->setCart($cart);
                $good->addOrder($order);
                $cart->addOrder($order);
                $em->persist($order);                
            }
        }

        $em->flush();

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/delete/{id}/{cart}", name="delete_order")
     * @Method("POST")
     */
    public function deleteAction(Request $request, $id, $cart)
    {
        $em = $this->getDoctrine()->getManager();
        $cart = $em->getRepository('ShopBundle:Cart')->findOneByHash($cart);
        $order = $em->createQueryBuilder('o')
            ->select('o')
            ->from('ShopBundle:Order','o')
            ->where('o.cart = ?0')
            ->andWhere('o.id = ?1')
            ->setParameters(array($cart,$id))
            ->getQuery()
            ->getSingleResult();

        $order->getGood()->removeOrder($order);
        $order->getCart()->removeOrder($order);
        $order->setGood(null)->setCart(null);
        $em->remove($order);
        $em->flush();

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/get_cart/{cart}", name="get_cart")
     * @Method("POST")
     * @Template()
     */
    public function getCartAction($cart)
    {
        $em = $this->getDoctrine()->getManager();
        $cart = $em->getRepository('ShopBundle:Cart')->findOneByHash($cart);
        if(is_object($cart->getUser()) && $cart->getUser()!=$this->getUser())
            throw new AccessDeniedException();

        return array(
            'cart'=>$cart
        );
    }
}
