<?php

namespace alkr\ShopBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table()
 * @ORM\Entity
 */
class Cart
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity="alkr\ShopBundle\Entity\Order", mappedBy="cart", cascade={"remove","persist"})
     * @var ArrayCollection $orders
     */
    private $orders;

    /**
     * @var float
     *
     * @ORM\Column(name="amount", type="float")
     */
    private $amount;

    /** @ORM\OneToOne(targetEntity="\JMS\Payment\CoreBundle\Entity\PaymentInstruction") */
    private $paymentInstruction;

    /**
     * @ORM\ManyToOne(targetEntity="alkr\UserBundle\Entity\User", inversedBy="carts")
     * @ORM\JoinColumn(name="user", referencedColumnName="id" )
     */
    private $user;

    /**
     * @ORM\Column(name="hash", type="string", unique=true)
     */
    private $hash;

    function __construct($user = null) {
        $this->hash = md5(sha1(rand()));
        if($user)
            $this->user = $user;
    }
    
    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set amount
     *
     * @param float $amount
     *
     * @return Payment
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return float 
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set paymentInstruction
     *
     * @param \JMS\Payment\CoreBundle\Entity\PaymentInstruction $paymentInstruction
     *
     * @return Payment
     */
    public function setPaymentInstruction(\JMS\Payment\CoreBundle\Entity\PaymentInstruction $paymentInstruction = null)
    {
        $this->paymentInstruction = $paymentInstruction;

        return $this;
    }

    /**
     * Get paymentInstruction
     *
     * @return \JMS\Payment\CoreBundle\Entity\PaymentInstruction 
     */
    public function getPaymentInstruction()
    {
        return $this->paymentInstruction;
    }

    /**
     * Set hash
     *
     * @param string $hash
     *
     * @return Cart
     */
    public function setHash($hash)
    {
        $this->hash = $hash;

        return $this;
    }

    /**
     * Get hash
     *
     * @return string 
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * Add orders
     *
     * @param \alkr\ShopBundle\Entity\Order $orders
     *
     * @return Cart
     */
    public function addOrder(\alkr\ShopBundle\Entity\Order $orders)
    {
        $this->orders[] = $orders;
        $this->calculateAmount();

        return $this;
    }

    /**
     * Remove orders
     *
     * @param \alkr\ShopBundle\Entity\Order $orders
     */
    public function removeOrder(\alkr\ShopBundle\Entity\Order $orders)
    {
        $this->orders->removeElement($orders);
        $this->calculateAmount();
    }

    /**
     * Get orders
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getOrders()
    {
        return $this->orders;
    }

    public function calculateAmount()
    {
        $this->amount = 0;
        foreach ($this->orders as $order) {
            $this->amount += $order->getCount() * $order->getGood()->getPrice();
        }

        return $this;
    }

    /**
     * Set user
     *
     * @param \alkr\UserBundle\Entity\User $user
     *
     * @return Cart
     */
    public function setUser(\alkr\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \alkr\UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }
}
