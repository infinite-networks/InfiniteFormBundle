<?php

namespace Infinite\FormBundle\Tests\PolyCollection\Type;

use Infinite\FormBundle\Form\Util\LegacyFormUtil;
use Symfony\Component\Form\FormBuilderInterface;

class FirstTypeIdType extends AbstractTypeIdType
{
    protected $dataClass = 'Infinite\\FormBundle\\Tests\\PolyCollection\\Model\\First';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('text2', LegacyFormUtil::getType('Symfony\Component\Form\Extension\Core\Type\TextType'));
    }

    public function getBlockPrefix()
    {
        return 'first_type_id_type';
    }
}
