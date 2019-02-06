<?php

/*
 * (c) Infinite Networks <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Infinite\FormBundle\Tests\CheckboxGrid;

use Infinite\FormBundle\Form\DataTransformer\CheckboxGridTransformer;
use Symfony\Component\Form\ChoiceList\ArrayChoiceList;
use Symfony\Component\Form\Extension\Core\ChoiceList\SimpleChoiceList;

class TransformerTest extends \PHPUnit\Framework\TestCase
{
    protected function makeTransformer()
    {
        return new CheckboxGridTransformer(array(
            'class' => null,
            'x_choice_list' => $this->makeChoiceList(array(
                'white' => 'white',
                'beige' => 'beige',
                'yellow' => 'yellow',
            )),
            'x_path' => '[color]',
            'y_choice_list' => $this->makeChoiceList(array(
                'matte' => 'matte',
                'satin' => 'satin',
                'gloss' => 'gloss',
                'high_gloss' => 'high gloss',
            )),
            'y_path' => '[finish]',
        ));
    }

    protected function makeChoiceList($choices)
    {
        // SF 2.7+
        if (class_exists('Symfony\Component\Form\ChoiceList\ArrayChoiceList')) {
            return new ArrayChoiceList(array_keys($choices));
        }

        // BC
        return new SimpleChoiceList($choices);
    }

    /**
     * Test the transformer on a low level.
     */
    public function testForwardTransform()
    {
        $modelData = array(
            array('color' => 'beige', 'finish' => 'matte'),
            array('color' => 'beige', 'finish' => 'satin'),
            array('color' => 'white', 'finish' => 'gloss'),
            array('color' => 'yellow', 'finish' => 'gloss'),
        );

        $viewData = array(
            'matte' => array('beige' => $modelData[0]),
            'satin' => array('beige' => $modelData[1]),
            'gloss' => array('white' => $modelData[2], 'yellow' => $modelData[3]),
        );

        $this->assertEquals(
            $viewData,
            $this->makeTransformer()->transform($modelData)
        );
    }

    public function testReverseTransform()
    {
        $modelData = array(
            array('color' => 'beige', 'finish' => 'matte'),
            array('color' => 'beige', 'finish' => 'satin'),
            array('color' => 'white', 'finish' => 'gloss'),
            array('color' => 'yellow', 'finish' => 'gloss'),
        );

        // The reverse transform should work whether the entries are booleans or not
        $viewData = array(
            'matte' => array('beige' => $modelData[0]),
            'satin' => array('beige' => true),
            'gloss' => array('white' => true, 'yellow' => $modelData[3]),
        );

        $this->assertEquals(
            $modelData,
            $this->makeTransformer()->reverseTransform($viewData)
        );
    }

    public function testInvalidValuesIgnored()
    {
        $transformer = $this->makeTransformer();

        $this->assertEquals(
            array(),
            $transformer->transform(array(
                array('color' => 'invalid', 'finish' => 'invalid'),
            ))
        );

        $this->assertEquals(
            array(),
            $transformer->reverseTransform(array(
                'invalid' => array('invalid' => true),
            ))
        );
    }

    public function testTransformerExpectsArray()
    {
        $this->setExpectedException('Symfony\\Component\\Form\\Exception\\TransformationFailedException');

        $this->makeTransformer()->transform('not an array');
    }

    public function testReverseTransformerExpectsArray()
    {
        $this->setExpectedException('Symfony\\Component\\Form\\Exception\\TransformationFailedException');

        $this->makeTransformer()->reverseTransform('not an array');
        $this->makeTransformer()->reverseTransform(array('not a 2D array'));
    }

    public function testReverseTransformerExpects2DArray()
    {
        $this->setExpectedException('Symfony\\Component\\Form\\Exception\\TransformationFailedException');

        $this->makeTransformer()->reverseTransform(array('not a 2D array'));
    }
}
