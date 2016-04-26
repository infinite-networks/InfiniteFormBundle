<?php

namespace Infinite\FormBundle\Tests\PolyCollection\Type;

use Infinite\FormBundle\Form\Util\LegacyFormUtil;
use Symfony\Component\Form\FormBuilderInterface;

class SecondType extends AbstractType
{
    protected $dataClass = 'Infinite\\FormBundle\\Tests\\PolyCollection\\Model\\Second';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('checked', LegacyFormUtil::getType('Symfony\Component\Form\Extension\Core\Type\CheckboxType'), array('required' => false));
    }

    public function getBlockPrefix()
    {
        return 'second_type';
    }
}
