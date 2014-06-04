<?php

namespace alkr\ShopBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Order
 *
 * @ORM\Table(name="Ordr")
 * @ORM\Entity
 */
class Order
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
     * @ORM\ManyToOne(targetEntity="alkr\ShopBundle\Entity\Cart", inversedBy="orders")
     * @ORM\JoinColumn(name="cart", referencedColumnName="id" )
     */
    private $cart;

    /**
     * @ORM\ManyToOne(targetEntity="alkr\ShopBundle\Entity\Good", inversedBy="orders")
     * @ORM\JoinColumn(name="good", referencedColumnName="id" )
     */
    private $good;

    /**
     * @var integer
     * @Assert\Range(
     *      min = 1,
     *      minMessage = "Нельзя заказать меньше одного товара"
     * )
     * @ORM\Column(name="count", type="integer")
     */
    private $count;

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
     * Set good
     *
     * @param string $good
     *
     * @return Order
     */
    public function setGood($good)
    {
        $this->good = $good;

        return $this;
    }

    /**
     * Get good
     *
     * @return string 
     */
    public function getGood()
    {
        return $this->good;
    }

    /**
     * Set count
     *
     * @param integer $count
     *
     * @return Order
     */
    public function setCount($count)
    {
        $this->count = $count;

        return $this;
    }

    /**
     * Get count
     *
     * @return integer 
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * Set cart
     *
     * @param \alkr\ShopBundle\Entity\Cart $cart
     *
     * @return Order
     */
    public function setCart(\alkr\ShopBundle\Entity\Cart $cart = null)
    {
        $this->cart = $cart;

        return $this;
    }

    /**
     * Get cart
     *
     * @return \alkr\ShopBundle\Entity\Cart 
     */
    public function getCart()
    {
        return $this->cart;
    }
}
