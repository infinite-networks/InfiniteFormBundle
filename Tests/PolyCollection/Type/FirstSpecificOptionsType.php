<?php

namespace Infinite\FormBundle\Tests\PolyCollection\Type;

use Symfony\Component\OptionsResolver\OptionsResolver;

class FirstSpecificOptionsType extends FirstType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);
        $resolver->setRequired(['first_option']);
    }

    public function getBlockPrefix(): string
    {
        return 'first_type_specific';
    }
}
