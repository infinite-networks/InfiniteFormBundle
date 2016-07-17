<?php

/*
 * (c) Infinite Networks <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Infinite\FormBundle\Form\Type;

use Infinite\FormBundle\Form\EventListener\ResizePolyFormListener;
use Infinite\FormBundle\Form\Util\LegacyFormUtil;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Exception\InvalidConfigurationException;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * A collection type that will take an array of other form types
 * to use for each of the classes in an inheritance tree.
 *
 * The collection allows you to use the form component to manipulate
 * objects that have a common parent, like Doctrine's single or
 * multi table inheritance strategies by registering different
 * types for each class in the inheritance tree.
 *
 * @author Tim Nagel <t.nagel@infinite.net.au>
 */
class PolyCollectionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $prototypes = $this->buildPrototypes($builder, $options);
        if ($options['allow_add'] && $options['prototype']) {
            $builder->setAttribute('prototypes', $prototypes);
        }

        $useTypesOptions = !empty($options['types_options']);

        $resizeListener = new ResizePolyFormListener(
            $prototypes,
            $useTypesOptions === true ? $options['types_options'] :$options['options'],
            $options['allow_add'],
            $options['allow_delete'],
            $options['type_name'],
            $options['index_property'],
            $useTypesOptions
        );

        $builder->addEventSubscriber($resizeListener);
    }

    /**
     * Builds prototypes for each of the form types used for the collection.
     *
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array                                        $options
     *
     * @return array
     */
    protected function buildPrototypes(FormBuilderInterface $builder, array $options)
    {
        $prototypes = array();
        $useTypesOptions = !empty($options['types_options']);

        foreach ($options['types'] as $type) {
            if ($type instanceof FormTypeInterface) {
                @trigger_error(sprintf('Passing type instances to PolyCollection is deprecated since version 1.0.5 and will not be supported in 2.0. Use the fully-qualified type class name instead (%s).', get_class($type)), E_USER_DEPRECATED);
            }

            $typeOptions = $options['options'];
            if ($useTypesOptions) {
                $typeOptions = [];
                if(isset($options['types_options'][$type])){
                    $typeOptions = $options['types_options'][$type];
                }
            }

            $prototype = $this->buildPrototype(
                $builder,
                $options['prototype_name'],
                $type,
                $typeOptions
            );

            if (LegacyFormUtil::isFullClassNameRequired()) {
                // SF 2.8+
                $key = $prototype->get($options['type_name'])->getData();
            } else {
                $key = $type instanceof FormTypeInterface ? $type->getName() : $type;
            }

            if (array_key_exists($key, $prototypes)) {
                throw new InvalidConfigurationException(sprintf(
                    'Each type of row in a polycollection must have a unique key. (Found "%s" in both %s and %s)',
                    $key,
                    get_class($prototypes[$key]->getConfig()->getType()->getInnerType()),
                    get_class($prototype->getType()->getInnerType())
                ));
            }

            $prototypes[$key] = $prototype->getForm();
        }

        return $prototypes;
    }

    /**
     * Builds an individual prototype.
     *
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param string                                       $name
     * @param string|FormTypeInterface                     $type
     * @param array                                        $options
     *
     * @return \Symfony\Component\Form\FormBuilderInterface
     */
    protected function buildPrototype(FormBuilderInterface $builder, $name, $type, array $options)
    {
        $prototype = $builder->create($name, $type, array_replace(array(
            'label' => $name . 'label__',
        ), $options));

        return $prototype;
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['allow_add']    = $options['allow_add'];
        $view->vars['allow_delete'] = $options['allow_delete'];

        if ($form->getConfig()->hasAttribute('prototypes')) {
            $view->vars['prototypes'] = array_map(function (FormInterface $prototype) use ($view) {
                return $prototype->createView($view);
            }, $form->getConfig()->getAttribute('prototypes'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        if ($form->getConfig()->hasAttribute('prototypes')) {
            $multiparts = array_filter(
                $view->vars['prototypes'],
                function (FormView $prototype) {
                    return $prototype->vars['multipart'];
                }
            );

            if ($multiparts) {
                $view->vars['multipart'] = true;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'infinite_form_polycollection';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'allow_add'      => false,
            'allow_delete'   => false,
            'prototype'      => true,
            'prototype_name' => '__name__',
            'type_name'      => '_type',
            'options'        => [],
            'types_options'  => [],
            'index_property' => null,
        ));

        $resolver->setRequired(array(
            'types'
        ));
        // OptionsResolver 2.6+
        if (method_exists($resolver, 'setNormalizer')) {
            $resolver->setAllowedTypes('types', 'array');
            $resolver->setNormalizer('options', $this->getOptionsNormalizer());
            $resolver->setNormalizer('types_options', $this->getTypesOptionsNormalizer());
        } else {
            $resolver->setAllowedTypes(array(
                'types' => 'array'
            ));
            $resolver->setNormalizers(array(
                'options' => $this->getOptionsNormalizer(),
            ));
            $resolver->setNormalizers(array(
                'types_options' => $this->getTypesOptionsNormalizer(),
            ));
        }
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

    private function getOptionsNormalizer()
    {
        return function (Options $options, $value) {
            $value['block_name'] = 'entry';

            return $value;
        };
    }

    private function getTypesOptionsNormalizer()
    {
        return function (Options $options, $value) {
            foreach($options['types'] as $type){
                if (isset($value[$type])) {
                    $value[$type]['block_name'] = 'entry';
                }
            }
            return $value;
        };
    }
}
