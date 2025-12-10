<?php

namespace Infinite\FormBundle\Tests\PolyCollection\Type;

use Symfony\Component\OptionsResolver\OptionsResolver;

class SecondSpecificOptionsType extends SecondType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);
        $resolver->setRequired('second_option');
    }
}
