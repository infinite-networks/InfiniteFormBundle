<?php

namespace Infinite\FormBundle\Tests\CheckboxGrid\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SalesmanType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $productAreaOptions = $options['product_area_options'];

        $builder->add('name', 'text');
        $builder->add('productAreas', 'infinite_form_entity_checkbox_grid', $productAreaOptions + array(
            'class' => 'Infinite\FormBundle\Tests\CheckboxGrid\Entity\SalesmanProductArea',
            'x_path' => 'productSold',
            'y_path' => 'areaServiced',
        ));
    }

    public function getName()
    {
        return 'infinite_form_test_salesman';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        // BC for Symfony 2.6 and older
        $this->configureOptions($resolver);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Infinite\FormBundle\Tests\CheckboxGrid\Entity\Salesman',
            'product_area_options' => array(),
        ));
    }
}
