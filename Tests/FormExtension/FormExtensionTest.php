<?php

/*
 * (c) Infinite Networks <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Infinite\FormBundle\Tests\FormExtension;

use Infinite\FormBundle\Twig\FormExtension;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\Forms;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

class FormExtensionTest extends \PHPUnit\Framework\TestCase
{
    /** @var FormFactory */
    private $formFactory;

    public function setUp(): void
    {
        $this->formFactory = Forms::createFormFactory();
    }

    public function testInvalidTest()
    {
        $twig = new Environment(new ArrayLoader(['template' => '{{ form is invalid ? 1 : 0 }}']));
        $twig->addExtension(new FormExtension());

        $formWithError = $this->formFactory->create(TextType::class);
        $formWithError->addError(new FormError('test error'));
        $this->assertEquals('1', $twig->render('template', ['form' => $formWithError->createView()]));

        $formWithoutError = $this->formFactory->create(TextType::class);
        $this->assertEquals('0', $twig->render('template', ['form' => $formWithoutError->createView()]));
    }
}
