<?php

namespace Infinite\FormBundle\Tests\CheckboxGrid\Type;

use Infinite\FormBundle\Form\Type\EntityCheckboxGridType;
use Infinite\FormBundle\Tests\CheckboxGrid\Entity\Salesman;
use Infinite\FormBundle\Tests\CheckboxGrid\Entity\SalesmanProductArea;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SalesmanType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $productAreaOptions = $options['product_area_options'];

        $builder->add('name', TextType::class);
        $builder->add('productAreas', EntityCheckboxGridType::class, $productAreaOptions + [
            'class' => SalesmanProductArea::class,
            'x_path' => 'productSold',
            'y_path' => 'areaServiced',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'infinite_form_test_salesman';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Salesman::class,
            'product_area_options' => [],
        ]);
    }
}
