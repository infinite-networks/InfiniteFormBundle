<?php

/*
 * (c) Infinite Networks <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Infinite\FormBundle\Twig;

use Symfony\Component\Form\FormView;
use Twig_Extension;

/**
 * Adds a helper function to determine if a form has errors.
 *
 * @author Tim Nagel <t.nagel@infinite.net.au>
 */
class FormExtension extends Twig_Extension
{
    public function getTests()
    {
        return array(
            new \Twig_SimpleTest('invalid', array($this, 'hasErrors')),
        );
    }

    /**
     * Tests if the FormView has errors.
     *
     * @param \Symfony\Component\Form\FormView $form
     *
     * @return bool
     */
    public function hasErrors(FormView $form)
    {
        return array_key_exists('errors', $form->vars) and count($form->vars['errors']);
    }

    public function getName()
    {
        return 'infinite_form';
    }
}
