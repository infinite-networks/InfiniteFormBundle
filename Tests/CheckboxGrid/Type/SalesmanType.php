<?php

namespace Infinite\FormBundle\Tests\CheckboxGrid\Type;

use Infinite\FormBundle\Form\Util\LegacyFormUtil;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SalesmanType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $productAreaOptions = $options['product_area_options'];

        $builder->add('name', LegacyFormUtil::getType('Symfony\Component\Form\Extension\Core\Type\TextType'));
        $builder->add('productAreas', LegacyFormUtil::getType('Infinite\FormBundle\Form\Type\EntityCheckboxGridType'), $productAreaOptions + array(
            'class' => 'Infinite\FormBundle\Tests\CheckboxGrid\Entity\SalesmanProductArea',
            'x_path' => 'productSold',
            'y_path' => 'areaServiced',
        ));
    }

    public function getBlockPrefix()
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

    // BC for SF < 2.8
    public function getName()
    {
        return $this->getBlockPrefix();
    }
}
