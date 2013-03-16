<?php

namespace Infinite\FormBundle\Tests\PolyCollection\Model;

class First extends AbstractModel
{
    public $text2;

    public function __construct($text = null, $text2 = null)
    {
        parent::__construct($text);

        $this->text2 = $text2;
    }
}
