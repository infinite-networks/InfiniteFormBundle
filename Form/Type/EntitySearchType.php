<?php

namespace Infinite\FormBundle\Form\Type;

use Infinite\FormBundle\Form\DataTransformer\EntitySearchTransformerFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

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

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('id', HiddenType::class)
            ->add('name', TextType::class, ['required' => $options['required']])
            ->setAttribute('search_route', $options['search_route'])
            ->addModelTransformer($this->transformerFactory->createFromOptions($options))
        ;
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['search_route'] = $form->getConfig()->getAttribute('search_route');
    }

    public function getBlockPrefix(): string
    {
        return 'infinite_form_entity_search';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'allow_not_found' => false,
            'error_bubbling' => false,
            'invalid_message' => 'Item not found',
            'name' => null,
            'search_route' => null,
        ]);

        $resolver->setRequired([
            'class',
        ]);
    }
}
