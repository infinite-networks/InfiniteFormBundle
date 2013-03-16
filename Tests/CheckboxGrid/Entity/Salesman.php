<?php

namespace Infinite\FormBundle\Tests\CheckboxGrid\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Salesman
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     * @ORM\Id
     * @var int
     */
    public $id;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    public $name;

    /**
     * @ORM\OneToMany(targetEntity="SalesmanProductArea", mappedBy="salesman", cascade={"all"}, orphanRemoval=true)
     * @var ArrayCollection
     */
    public $productAreas;

    public function __construct()
    {
        $this->productAreas = new ArrayCollection;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function addProductArea(SalesmanProductArea $productArea)
    {
        $this->productAreas->add($productArea);
        $productArea->setSalesman($this);
    }

    public function removeProductArea(SalesmanProductArea $productArea)
    {
        $this->productAreas->removeElement($productArea);
    }

    public function getProductAreas()
    {
        return $this->productAreas;
    }
}
