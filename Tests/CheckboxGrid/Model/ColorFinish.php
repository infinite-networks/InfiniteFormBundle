<?php

namespace Infinite\FormBundle\Tests\CheckboxGrid\Model;

class ColorFinish
{
    public $color;
    public $finish;

    public function __construct($color = null, $finish = null)
    {
        $this->color = $color;
        $this->finish = $finish;
    }
}
