<?php

namespace Infinite\FormBundle\Tests\PolyCollection\Type;

use Infinite\FormBundle\Tests\PolyCollection\Model\AbstractModel;
use Symfony\Component\Form\AbstractType as BaseType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AbstractTypeIdType extends BaseType
{
    protected $dataClass = AbstractModel::class;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id', NumberType::class);

        $builder->add('text', TextType::class);

        $builder->add('_type_id', HiddenType::class, array(
            'data' => $this->getBlockPrefix(),
            'mapped' => false,
        ));
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
        return 'abstract_type_id_type';
    }
}
