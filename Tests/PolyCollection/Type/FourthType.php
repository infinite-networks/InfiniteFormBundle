<?php

namespace Infinite\FormBundle\Tests\PolyCollection\Type;

use Infinite\FormBundle\Form\Util\LegacyFormUtil;
use Symfony\Component\Form\FormBuilderInterface;

class FourthType extends AbstractType
{
    protected $dataClass = 'Infinite\\FormBundle\\Tests\\PolyCollection\\Model\\Fourth';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('file', LegacyFormUtil::getType('Symfony\Component\Form\Extension\Core\Type\FileType'), array('required' => false));
    }

    public function getBlockPrefix()
    {
        return 'fourth_type';
    }
}
