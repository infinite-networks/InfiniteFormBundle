<?php

namespace Infinite\FormBundle\Tests\PolyCollection\Type;

use Infinite\FormBundle\Tests\PolyCollection\Model\Fourth;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;

class FourthType extends AbstractType
{
    protected $dataClass = Fourth::class;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('file', FileType::class, ['required' => false]);
    }

    public function getBlockPrefix()
    {
        return 'fourth_type';
    }
}
