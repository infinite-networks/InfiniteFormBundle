<?php

namespace Infinite\FormBundle\Tests\PolyCollection\Type;

use Infinite\FormBundle\Tests\PolyCollection\Model\First;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class FirstTypeIdType extends AbstractTypeIdType
{
    protected $dataClass = First::class;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('text2', TextType::class);
    }

    public function getBlockPrefix()
    {
        return 'first_type_id_type';
    }
}
