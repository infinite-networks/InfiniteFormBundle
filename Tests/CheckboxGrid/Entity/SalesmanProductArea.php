<?php

namespace Infinite\FormBundle\Tests\CheckboxGrid\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class SalesmanProductArea
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     * @ORM\Id
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\JoinColumn(nullable=false)
     * @ORM\ManyToOne(targetEntity="Area")
     *
     * @var Area
     */
    protected $areaServiced;

    /**
     * @ORM\JoinColumn(nullable=false)
     * @ORM\ManyToOne(targetEntity="Product")
     *
     * @var Product
     */
    protected $productSold;

    /**
     * @ORM\JoinColumn(nullable=false)
     * @ORM\ManyToOne(targetEntity="Salesman", inversedBy="productAreas")
     *
     * @var Salesman
     */
    protected $salesman;

    public function getId()
    {
        return $this->id;
    }

    public function setAreaServiced(Area $areaServiced)
    {
        $this->areaServiced = $areaServiced;
    }

    public function getAreaServiced()
    {
        return $this->areaServiced;
    }

    public function setProductSold(Product $productSold)
    {
        $this->productSold = $productSold;
    }

    public function getProductSold()
    {
        return $this->productSold;
    }

    public function setSalesman(Salesman $salesman)
    {
        $this->salesman = $salesman;
    }

    public function getSalesman()
    {
        return $this->salesman;
    }
}
