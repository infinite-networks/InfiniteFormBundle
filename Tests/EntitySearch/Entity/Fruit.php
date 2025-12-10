<?php

namespace Infinite\FormBundle\Tests\EntitySearch\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Fruit
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    public $id;

    #[ORM\Column(type: 'string', length: 50)]
    public $name;
}
