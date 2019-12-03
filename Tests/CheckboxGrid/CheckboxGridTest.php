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
use Infinite\FormBundle\Tests\CheckboxGrid\Model\ColorFinish;
use Symfony\Component\Form\Forms;

class CheckboxGridTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
    protected $factory;

    protected function setUp(): void
    {
        $this->factory = Forms::createFormFactoryBuilder()
            ->addType(new CheckboxGridType())
            ->addType(new CheckboxRowType())
            ->getFormFactory();
    }

    protected function makeForm($data, $options)
    {
        return $this->factory->create(CheckboxGridType::class, $data, $options + [
            'x_choices' => [
                'white' => 'white',
                'beige' => 'beige',
                'yellow' => 'yellow',
            ],
            'x_path' => '[color]',
            'y_choices' => [
                'matte' => 'matte',
                'satin' => 'satin',
                'gloss' => 'gloss',
                'high_gloss' => 'high gloss',
            ],
            'y_path' => '[finish]',
        ]);
    }

    /**
     * Test that all checkboxes are created correctly and checked when necessary.
     */
    public function testSetData()
    {
        $form = $this->makeForm(
            [
                ['color' => 'beige', 'finish' => 'matte'],
                ['color' => 'beige', 'finish' => 'satin'],
                ['color' => 'white', 'finish' => 'gloss'],
                ['color' => 'yellow', 'finish' => 'gloss'],
            ],
            []
        );

        $view = $form->createView();
        $checkboxViewData = [];

        foreach ($view as $rowView) {
            foreach ($rowView as $checkbox) {
                $checkboxViewData[$rowView->vars['name']][$checkbox->vars['name']] = $checkbox->vars['data'];
            }
        }

        $this->assertEquals(
            [
                'matte' => ['white' => false, 'beige' => true, 'yellow' => false],
                'satin' => ['white' => false, 'beige' => true, 'yellow' => false],
                'gloss' => ['white' => true, 'beige' => false, 'yellow' => true],
                'high_gloss' => ['white' => false, 'beige' => false, 'yellow' => false],
            ],
            $checkboxViewData
        );
    }

    /**
     * Test how bound data is mapped back (are the transformers being called correctly?).
     */
    public function testBind()
    {
        $form = $this->makeForm([], []);

        $form->submit([
            'satin' => ['beige' => '1'],
            'gloss' => ['yellow' => '1', 'white' => '1'],
            'matte' => ['beige' => '1'],
            'invalid' => ['invalid' => '1'], // Invalid values should be ignored
        ]);

        $this->assertEquals(
            [
                ['color' => 'beige', 'finish' => 'matte'],
                ['color' => 'beige', 'finish' => 'satin'],
                ['color' => 'white', 'finish' => 'gloss'],
                ['color' => 'yellow', 'finish' => 'gloss'],
            ],
            $form->getData()
        );
    }

    /**
     * Test that the cell_filter option prevents the checkboxes that we don't want.
     */
    public function testCellFilter()
    {
        $form = $this->makeForm([], [
            'cell_filter' => function ($x, $y) {
                return !(
                    $x == 'beige' && $y == 'gloss' ||
                    $x == 'white' && $y == 'high_gloss'
                );
            },
        ]);

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

        $initial = [$originalObject];

        $form = $this->makeForm($initial, [
            'class' => ColorFinish::class,
            'x_path' => 'color',
            'y_path' => 'finish',
        ]);

        $form->submit([
            'satin' => ['white' => '1'],
        ]);

        $data = $form->getData();

        $this->assertEquals(1, count($data));
        $this->assertSame($originalObject, $data[0]);
    }
}
