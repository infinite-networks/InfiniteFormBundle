<?php

namespace Infinite\FormBundle\Tests\CheckboxGrid\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class SalesmanProductArea
{
    /**
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    protected $id;

    /**
     * @var Area
     */
    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: Area::class)]
    protected $areaServiced;

    /**
     * @var Product
     */
    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: Product::class)]
    protected $productSold;

    /**
     * @var Salesman
     */
    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: Salesman::class, inversedBy: 'productAreas')]
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
