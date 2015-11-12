<?php

namespace Infinite\FormBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\View\ChoiceView;

class TreeChoiceView extends ChoiceView
{
    public $level;

    public function __construct($data, $value, $label, $level)
    {
        parent::__construct($data, $value, $label);
        $this->level = $level;
    }
}
