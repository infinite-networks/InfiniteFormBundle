<?php

/*
 * (c) Infinite Networks <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Infinite\FormBundle\Tests\FormExtension;

use Infinite\FormBundle\Twig\FormExtension;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\Forms;

class FormExtensionTest extends \PHPUnit_Framework_TestCase
{
    /** @var FormFactory */
    private $formFactory;

    /** @var \Twig_Environment */
    private $twig;

    public function setUp()
    {
        $this->formFactory = Forms::createFormFactory();

        $this->twig = new \Twig_Environment;
        $this->twig->addExtension(new FormExtension);
        $this->twig->setLoader(new \Twig_Loader_String);
    }

    public function testInvalidTest()
    {
        $template = $this->twig->loadTemplate('{{ form is invalid ? 1 : 0 }}');

        $formWithError = $this->formFactory->create('text');
        $formWithError->addError(new FormError('test error'));
        $this->assertEquals('1', $template->render(array('form' => $formWithError->createView())));

        $formWithoutError = $this->formFactory->create('text');
        $this->assertEquals('0', $template->render(array('form' => $formWithoutError->createView())));
    }
}
