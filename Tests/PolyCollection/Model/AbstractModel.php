<?php

namespace Infinite\FormBundle\Tests\PolyCollection\Model;

class AbstractModel
{
    public $text;

    public function __construct($text = null)
    {
        $this->text = $text;
    }
}
