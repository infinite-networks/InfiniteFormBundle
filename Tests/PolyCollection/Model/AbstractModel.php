<?php

namespace Infinite\FormBundle\Tests\PolyCollection\Model;

class AbstractModel
{
    public $id;

    public $text;

    public function __construct($text = null, $id = null)
    {
        $this->id = $id;

        $this->text = $text;
    }
}
