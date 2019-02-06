<?php

/*
 * (c) Infinite Networks <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Infinite\FormBundle\Tests\FormExtension;

use Infinite\FormBundle\Form\Util\LegacyFormUtil;
use Infinite\FormBundle\Twig\FormExtension;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\Forms;

class FormExtensionTest extends \PHPUnit\Framework\TestCase
{
    /** @var FormFactory */
    private $formFactory;

    public function setUp()
    {
        $this->formFactory = Forms::createFormFactory();
    }

    public function testInvalidTest()
    {
        $twig = new \Twig_Environment(new \Twig_Loader_Array(array('template' => '{{ form is invalid ? 1 : 0 }}')));
        $twig->addExtension(new FormExtension());

        $formWithError = $this->formFactory->create(LegacyFormUtil::getType('Symfony\Component\Form\Extension\Core\Type\TextType'));
        $formWithError->addError(new FormError('test error'));
        $this->assertEquals('1', $twig->render('template', array('form' => $formWithError->createView())));

        $formWithoutError = $this->formFactory->create(LegacyFormUtil::getType('Symfony\Component\Form\Extension\Core\Type\TextType'));
        $this->assertEquals('0', $twig->render('template', array('form' => $formWithoutError->createView())));
    }
}
