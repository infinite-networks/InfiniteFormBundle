<?php

/*
 * (c) Infinite Networks <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Infinite\FormBundle\Form\Type;

use Infinite\FormBundle\Form\DataTransformer\CheckboxGridTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Provides a checkbox grid for non-Doctrine objects (rows and cols set by x/y_choice_list)
 */
class CheckboxGridType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['x_choice_list']->getPreferredViews()) {
            throw new \Exception('Checkbox grid: x choice list cannot have preferred views');
        }

        if ($options['y_choice_list']->getPreferredViews()) {
            throw new \Exception('Checkbox grid: y choice list cannot have preferred views');
        }

        foreach ($options['y_choice_list']->getRemainingViews() as $choice) {
            $rowOptions = array(
                'cell_filter' => $options['cell_filter'],
                'choice_list' => $options['x_choice_list'],
                'row'         => $choice,
            );

            $builder->add($choice->value, 'infinite_form_checkbox_row', $rowOptions);
        }

        $builder->addViewTransformer(new CheckboxGridTransformer($options));
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['headers'] = $options['x_choice_list'];
    }

    public function getBlockPrefix()
    {
        return 'infinite_form_checkbox_grid';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'class' => null,
            'cell_filter' => null,
        ));

        $resolver->setRequired(array(
            'x_choice_list',
            'x_path',
            'y_choice_list',
            'y_path',
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
