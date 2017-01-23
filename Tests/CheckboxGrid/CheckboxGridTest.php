<?php

/*
 * (c) Infinite Networks <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Infinite\FormBundle\Tests\CheckboxGrid;

use Infinite\FormBundle\Form\Type\CheckboxGridType;
use Infinite\FormBundle\Form\Type\CheckboxRowType;
use Infinite\FormBundle\Form\Util\LegacyFormUtil;
use Infinite\FormBundle\Tests\CheckboxGrid\Model\ColorFinish;
use Symfony\Component\Form\Forms;

class CheckboxGridTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
    protected $factory;

    protected function setUp()
    {
        $this->factory = Forms::createFormFactoryBuilder()
            ->addType(new CheckboxGridType())
            ->addType(new CheckboxRowType())
            ->getFormFactory();
    }

    protected function makeForm($data, $options)
    {
        return $this->factory->create(LegacyFormUtil::getType('Infinite\FormBundle\Form\Type\CheckboxGridType'), $data, $options + array(
            'x_choices' => array(
                'white' => 'white',
                'beige' => 'beige',
                'yellow' => 'yellow',
            ),
            'x_path' => '[color]',
            'y_choices' => array(
                'matte' => 'matte',
                'satin' => 'satin',
                'gloss' => 'gloss',
                'high_gloss' => 'high gloss',
            ),
            'y_path' => '[finish]',
        ));
    }

    /**
     * Test that all checkboxes are created correctly and checked when necessary.
     */
    public function testSetData()
    {
        $form = $this->makeForm(
            array(
                array('color' => 'beige', 'finish' => 'matte'),
                array('color' => 'beige', 'finish' => 'satin'),
                array('color' => 'white', 'finish' => 'gloss'),
                array('color' => 'yellow', 'finish' => 'gloss'),
            ),
            array()
        );

        $view = $form->createView();
        $checkboxViewData = array();

        foreach ($view as $rowView) {
            foreach ($rowView as $checkbox) {
                $checkboxViewData[$rowView->vars['name']][$checkbox->vars['name']] = $checkbox->vars['data'];
            }
        }

        $this->assertEquals(
            array(
                'matte' => array('white' => false, 'beige' => true, 'yellow' => false),
                'satin' => array('white' => false, 'beige' => true, 'yellow' => false),
                'gloss' => array('white' => true, 'beige' => false, 'yellow' => true),
                'high_gloss' => array('white' => false, 'beige' => false, 'yellow' => false),
            ),
            $checkboxViewData
        );
    }

    /**
     * Test how bound data is mapped back (are the transformers being called correctly?).
     */
    public function testBind()
    {
        $form = $this->makeForm(array(), array());

        $form->submit(array(
            'satin' => array('beige' => '1'),
            'gloss' => array('yellow' => '1', 'white' => '1'),
            'matte' => array('beige' => '1'),
            'invalid' => array('invalid' => '1'), // Invalid values should be ignored
        ));

        $this->assertEquals(
            array(
                array('color' => 'beige', 'finish' => 'matte'),
                array('color' => 'beige', 'finish' => 'satin'),
                array('color' => 'white', 'finish' => 'gloss'),
                array('color' => 'yellow', 'finish' => 'gloss'),
            ),
            $form->getData()
        );
    }

    /**
     * Test that the cell_filter option prevents the checkboxes that we don't want.
     */
    public function testCellFilter()
    {
        $form = $this->makeForm(array(), array(
            'cell_filter' => function ($x, $y) {
                return !(
                    $x == 'beige' && $y == 'gloss' ||
                    $x == 'white' && $y == 'high_gloss'
                );
            },
        ));

        $view = $form->createView();

        $checkboxCount = 0;

        foreach ($view->children as $row) {
            foreach ($row->children as $cell) {
                if (in_array('checkbox', $cell->vars['block_prefixes'])) {
                    ++$checkboxCount;
                }
            }
        }

        $this->assertEquals(10, $checkboxCount);
    }

    /**
     * Test that an object is preserved when its checkbox is left alone.
     */
    public function testObjectPreserved()
    {
        $originalObject = new ColorFinish('white', 'satin');

        $initial = array($originalObject);

        $form = $this->makeForm($initial, array(
            'class' => 'Infinite\FormBundle\Tests\CheckboxGrid\Model\ColorFinish',
            'x_path' => 'color',
            'y_path' => 'finish',
        ));

        $form->submit(array(
            'satin' => array('white' => '1'),
        ));

        $data = $form->getData();

        $this->assertEquals(1, count($data));
        $this->assertSame($originalObject, $data[0]);
    }
}
