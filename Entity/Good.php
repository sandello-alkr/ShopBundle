<?php

namespace alkr\ShopBundle\Entity;

use alkr\CMSBundle\Entity\Page as Page;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Good
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Gedmo\Tree\Entity\Repository\MaterializedPathRepository")
 */
class Good extends Page
{
    /**
     * @var float
     * @Assert\Range(
     *      min = 0,
     *      minMessage = "Цена должна быть неотрицательной"
     * )
     * @ORM\Column(name="price", type="float", nullable=true)
     */
    private $price;

    /**
     * @var integer
     * @Assert\Range(
     *      min = 0,
     *      minMessage = "Количество на складе не должно быть отрицательным"
     * )
     * @ORM\Column(name="count", type="integer", nullable=true)
     */
    private $count;

    /**
     * @ORM\OneToMany(targetEntity="alkr\ShopBundle\Entity\Order", mappedBy="good", cascade={"persist","remove"})
     */
    private $orders;

    public function __construct()
    {
        parent::__construct();
        // your own logic
    }

    // /**
    //  * Get id
    //  *
    //  * @return integer 
    //  */
    // public function getId()
    // {
    //     return parent::getId();
    // }

    /**
     * Set price
     *
     * @param float $price
     *
     * @return Good
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return float 
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set count
     *
     * @param integer $count
     *
     * @return Good
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
     * Add orders
     *
     * @param \alkr\ShopBundle\Entity\Order $orders
     *
     * @return Good
     */
    public function addOrder(\alkr\ShopBundle\Entity\Order $orders)
    {
        $this->orders[] = $orders;

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
}
