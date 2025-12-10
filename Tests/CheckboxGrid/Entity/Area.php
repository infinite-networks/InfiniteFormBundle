<?php

namespace Infinite\FormBundle\Tests\CheckboxGrid\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Area
{
    /**
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    protected $id;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string')]
    protected $name;

    public function __construct($name)
    {
        $this->name = $name;
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

    public function __toString()
    {
        return $this->name;
    }
}
