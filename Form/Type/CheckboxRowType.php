<?php

/*
 * (c) Infinite Networks <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Infinite\FormBundle\Form\Type;

use Infinite\FormBundle\Form\EventListener\CheckboxRowCreationListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CheckboxRowType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Rows added by listener (need the data to be available before creating checkboxes)
        $builder->addEventSubscriber(new CheckboxRowCreationListener($builder->getFormFactory()));
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['label'] = $options['row_label'];
    }

    public function getBlockPrefix(): string
    {
        return 'infinite_form_checkbox_row';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(array(
            'cell_filter' => null,
            'choice_list' => null,
            'label_path' => null,
            'row' => null,
            'row_label' => null,
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
