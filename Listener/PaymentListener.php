<?php

namespace alkr\ShopBundle\Listener;

use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;

class PaymentListener {

    protected $router;
    protected $mailer;
    protected $email;
    protected $logger;
    protected $container;
    protected $currency;

    function __construct($container, $email, $currency)
    {
        $this->router = $container->get('router');
        $this->mailer = $container->get('mailer');
        $this->email = $email;
        $this->logger = $container->get('logger');
        $this->container = $container;
        $this->currency = $currency;
    }

    public function preUpdate(PreUpdateEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        if ($entity instanceof \JMS\Payment\CoreBundle\Entity\Payment) {
            if ($eventArgs->hasChangedField('state')) {
                $em = $eventArgs->getObjectManager();
                $cart = $em->getRepository('ShopBundle:Cart')->findOneByPaymentInstruction($entity->getPaymentInstruction()->getId());
                $this->logger->emergency('found cart '.$cart->getId());
                $state = $eventArgs->getNewValue('state');
                $body = $this->container->get('templating')->render('ShopBundle:Email:paymentChangeStatus.html.twig',array('cart'=>$cart,'currency'=>$this->currency,'payment'=>$entity));
                $message = \Swift_Message::newInstance()
                    ->setSubject('Заказ №'.$cart->getId().' оплачен!')
                    ->setFrom(array($this->email))
                    ->setTo('sandello.alkr@gmail.com')
                    ->setBody(
                        $body,'text/html'
                    );
                $this->mailer->send($message);

                $states = array(
                    1 => 'STATE_APPROVED',
                    2 => 'STATE_APPROVING',
                    3 => 'STATE_CANCELED',
                    4 => 'STATE_EXPIRED',
                    5 => 'STATE_FAILED',
                    6 => 'STATE_NEW',
                    7 => 'STATE_DEPOSITING',
                    8 => 'STATE_DEPOSITED',
                );
            }
        }
    }
}