<?php

namespace Infinite\FormBundle\Tests\PolyCollection\Type;

use Infinite\FormBundle\Tests\PolyCollection\Model\Second;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;

class SecondType extends AbstractType
{
    protected $dataClass = Second::class;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('checked', CheckboxType::class, ['required' => false]);
    }

    public function getBlockPrefix()
    {
        return 'second_type';
    }
}
