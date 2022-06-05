<?php

/*
 * (c) Infinite Networks <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Infinite\FormBundle\Form\EventListener;

use Infinite\FormBundle\Form\DataTransformer\AnythingToBooleanTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormInterface;

/**
 * When a checkbox grid is created, there may already be a few boxes checked. When the grid is bound,
 * we want to match up any still-checked checkboxes with their original objects so that we don't have
 * to delete and recreate them (which would wipe out any bookkeeping data on them).
 *
 * To accomplish this, the checkbox must be linked to its original data object. The form framework
 * only allows this to be done when the checkbox is created, thus the checkboxes must be created when
 * the data is first available.
 *
 * This listener creates the link between checkboxes and their original data objects with a transformer.
 * The CheckboxGridTransformer then uses that value when it's available.
 */
class CheckboxRowCreationListener implements EventSubscriberInterface
{
    protected $factory;

    public function __construct(FormFactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    public static function getSubscribedEvents(): array
    {
        return array(
            FormEvents::PRE_SET_DATA => 'preSetData',
        );
    }

    public function preSetData(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        if ($data === null) {
            $data = array();
        }

        $options = $form->getConfig()->getOptions();

        // Now that we have data available, create the checkboxes for the form. For every box that should
        // be checked, attach a transformer that will convert between its data object and a boolean.
        foreach ($options['choice_list']->getChoices() as $value => $choice) {
            $this->addCheckbox($options, $choice, $form, $value, $data);
        }
    }

    /**
     * @param array $options
     * @param $choice
     * @param FormInterface $form
     * @param $value
     * @param $data
     */
    protected function addCheckbox($options, $choice, FormInterface $form, $value, $data)
    {
        if (isset($options['cell_filter']) && !$options['cell_filter']($choice, $options['row'])) {
            // Blank cell - put a dummy form control here
            $formType = FormType::class;
        } else {
            $formType = CheckboxType::class;
        }

        $builder = $this->factory->createNamedBuilder(
            $value,
            $formType,
            isset($data[$value]),
            array(
                'auto_initialize' => false,
                'required' => false,
            )
        );

        if (isset($data[$value])) {
            $builder->addViewTransformer(new AnythingToBooleanTransformer($data[$value]), true);
        }

        $form->add($builder->getForm());
    }
}
