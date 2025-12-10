<?php

namespace Infinite\FormBundle\Tests\PolyCollection\Type;

use Infinite\FormBundle\Tests\PolyCollection\Model\Fourth;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;

class FourthType extends AbstractType
{
    protected string $dataClass = Fourth::class;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder->add('file', FileType::class, ['required' => false]);
    }

    public function getBlockPrefix(): string
    {
        return 'fourth_type';
    }
}
