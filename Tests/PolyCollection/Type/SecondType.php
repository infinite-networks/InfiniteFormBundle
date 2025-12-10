<?php

namespace Infinite\FormBundle\Tests\PolyCollection\Type;

use Infinite\FormBundle\Tests\PolyCollection\Model\Second;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;

class SecondType extends AbstractType
{
    protected string $dataClass = Second::class;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder->add('checked', CheckboxType::class, ['required' => false]);
    }

    public function getBlockPrefix(): string
    {
        return 'second_type';
    }
}
