<?php

/*
 * (c) Infinite Networks <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Infinite\FormBundle\Tests\CheckboxGrid;

use Infinite\FormBundle\Form\DataTransformer\CheckboxGridTransformer;
use Symfony\Component\Form\Extension\Core\ChoiceList\SimpleChoiceList;

class TransformerTest extends \PHPUnit_Framework_TestCase
{
    protected function makeTransformer()
    {
        return new CheckboxGridTransformer(array(
            'class' => null,
            'x_choice_list' => new SimpleChoiceList(array(
                'white' => 'white',
                'beige' => 'beige',
                'yellow' => 'yellow',
            )),
            'x_path' => '[color]',
            'y_choice_list' => new SimpleChoiceList(array(
                'matte' => 'matte',
                'satin' => 'satin',
                'gloss' => 'gloss',
                'high_gloss' => 'high gloss',
            )),
            'y_path' => '[finish]',
        ));
    }

    /**
     * Test the transformer on a low level
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

//    /**
//     * @var \Symfony\Component\Form\FormFactoryInterface
//     */
//    protected $factory;
//
//    protected function setUp()
//    {
//        $this->factory = Forms::createFormFactoryBuilder()
//            ->addType(new CheckboxGridType)
//            ->addType(new CheckboxRowType)
//            ->getFormFactory();
//    }
//
//    protected function makeForm($data, $options)
//    {
//        return $this->factory->create('infinite_form_checkbox_grid', $data, $options + array(
//            'x_choice_list' => new SimpleChoiceList(array(
//                'white' => 'white',
//                'beige' => 'beige',
//                'yellow' => 'yellow',
//            )),
//            'x_path' => '[color]',
//            'y_choice_list' => new SimpleChoiceList(array(
//                'matte' => 'matte',
//                'satin' => 'satin',
//                'gloss' => 'gloss',
//                'high_gloss' => 'high gloss',
//            )),
//            'y_path' => '[finish]',
//        ));
//    }
//
//    /**
//     * Test that all checkboxes are created correctly and checked when necessary
//     */
//    public function testSetData()
//    {
//        $form = $this->makeForm(
//            array(
//                array('color' => 'beige', 'finish' => 'matte'),
//                array('color' => 'beige', 'finish' => 'satin'),
//                array('color' => 'white', 'finish' => 'gloss'),
//                array('color' => 'yellow', 'finish' => 'gloss'),
//            ),
//            array()
//        );
//
//        $view = $form->createView();
//        $checkboxViewData = array();
//
//        foreach ($view as $rowView) {
//            foreach ($rowView as $checkbox) {
//                $checkboxViewData[$rowView->vars['name']][$checkbox->vars['name']] = $checkbox->vars['data'];
//            }
//        }
//
//        $this->assertEquals(
//            array(
//                'matte' => array('white' => false, 'beige' => true, 'yellow' => false),
//                'satin' => array('white' => false, 'beige' => true, 'yellow' => false),
//                'gloss' => array('white' => true, 'beige' => false, 'yellow' => true),
//                'high_gloss' => array('white' => false, 'beige' => false, 'yellow' => false),
//            ),
//            $checkboxViewData
//        );
//    }
//
//    /**
//     * Test that bound data is mapped back correctly
//     */
//    public function testBind()
//    {
//        $form = $this->makeForm(array(), array());
//
//        $form->bind(array(
//            'satin' => array('beige' => '1'),
//            'gloss' => array('yellow' => '1', 'white' => '1'),
//            'matte' => array('beige' => '1'),
//            'invalid' => array('invalid' => '1'), // Invalid values should be ignored by the transformer
//        ));
//
//        $this->assertEquals(
//            array(
//                array('color' => 'beige', 'finish' => 'matte'),
//                array('color' => 'beige', 'finish' => 'satin'),
//                array('color' => 'white', 'finish' => 'gloss'),
//                array('color' => 'yellow', 'finish' => 'gloss'),
//            ),
//            $form->getData()
//        );
//    }
//
//    /**
//     * Test that the cell_filter option prevents the checkboxes that we don't want
//     */
//    public function testCellFilter()
//    {
//        $form = $this->makeForm(array(), array(
//            'cell_filter' => function($x, $y) {
//                return !(
//                    $x == 'beige' && $y == 'gloss' ||
//                        $x == 'white' && $y == 'high_gloss'
//                );
//            },
//        ));
//
//        $view = $form->createView();
//
//        $checkboxCount = 0;
//
//        foreach ($view->children as $row) {
//            foreach ($row->children as $cell) {
//                if (in_array('checkbox', $cell->vars['block_prefixes'])) {
//                    $checkboxCount++;
//                }
//            }
//        }
//
//        $this->assertEquals(10, $checkboxCount);
//    }
//
//    /**
//     * Test that an object is preserved when its checkbox is left alone.
//     */
//    public function testObjectPreserved()
//    {
//        $originalObject = new ColorFinish('white', 'satin');
//
//        $initial = array($originalObject);
//
//        $form = $this->makeForm($initial, array(
//            'class' => 'Infinite\FormBundle\Tests\CheckboxGrid\Model\ColorFinish',
//            'x_path' => 'color',
//            'y_path' => 'finish',
//        ));
//
//        $form->bind(array(
//            'satin' => array('white' => '1'),
//        ));
//
//        $data = $form->getData();
//
//        $this->assertEquals(1, count($data));
//        $this->assertSame($originalObject, $data[0]);
//    }
}
