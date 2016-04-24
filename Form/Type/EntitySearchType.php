<?php

namespace Infinite\FormBundle\Form\Type;

use Infinite\FormBundle\Form\DataTransformer\EntitySearchTransformerFactory;
use Infinite\FormBundle\Form\Util\LegacyFormUtil;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EntitySearchType extends AbstractType
{
    /**
     * @var EntitySearchTransformerFactory
     */
    private $transformerFactory;

    public function __construct(EntitySearchTransformerFactory $transformerFactory)
    {
        $this->transformerFactory = $transformerFactory;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', LegacyFormUtil::getType('Symfony\Component\Form\Extension\Core\Type\HiddenType'))
            ->add('name', LegacyFormUtil::getType('Symfony\Component\Form\Extension\Core\Type\TextType'), array('required' => $options['required']))
            ->setAttribute('search_route', $options['search_route'])
            ->addModelTransformer($this->transformerFactory->createFromOptions($options))
        ;
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['search_route'] = $form->getConfig()->getAttribute('search_route');
    }

    public function getBlockPrefix()
    {
        return 'infinite_form_entity_search';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'allow_not_found' => false,
            'error_bubbling' => false,
            'invalid_message' => 'Item not found',
            'name' => null,
            'search_route' => null,
        ));

        $resolver->setRequired(array(
            'class'
        ));
    }
    
    // BC for SF < 2.7
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $this->configureOptions($resolver);
    }
    
    // BC for SF < 2.8
    public function getName()
    {
        return $this->getBlockPrefix();
    }
}
