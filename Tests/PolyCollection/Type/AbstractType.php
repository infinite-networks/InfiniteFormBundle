<?php

namespace Infinite\FormBundle\Tests\PolyCollection\Type;

use Infinite\FormBundle\Form\Util\LegacyFormUtil;
use Symfony\Component\Form\AbstractType as BaseType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AbstractType extends BaseType
{
    protected $dataClass = 'Infinite\\FormBundle\\Tests\\PolyCollection\\Model\\AbstractModel';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id', LegacyFormUtil::getType('Symfony\Component\Form\Extension\Core\Type\NumberType'));

        $builder->add('text', LegacyFormUtil::getType('Symfony\Component\Form\Extension\Core\Type\TextType'));

        $builder->add('_type', LegacyFormUtil::getType('Symfony\Component\Form\Extension\Core\Type\HiddenType'), array(
            'data' => $this->getName(),
            'mapped' => false,
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        // BC for Symfony 2.6 and older
        $this->configureOptions($resolver);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => $this->dataClass,
            'model_class' => $this->dataClass,
            'max_length' => 50,
        ));
    }

    public function getBlockPrefix()
    {
        return 'abstract_type';
    }

    // BC for SF < 2.8
    public function getName()
    {
        return $this->getBlockPrefix();
    }
}
