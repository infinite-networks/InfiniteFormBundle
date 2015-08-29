<?php

namespace Infinite\FormBundle\Tests\PolyCollection\Model;

class Second extends AbstractModel
{
    public $checked;

    public function __construct($text = null, $checked = false, $id = null)
    {
        parent::__construct($text, $id);

        $this->checked = $checked;
    }
}
