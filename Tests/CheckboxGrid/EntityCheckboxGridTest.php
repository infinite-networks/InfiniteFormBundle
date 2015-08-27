<?php

/*
 * (c) Infinite Networks <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Infinite\FormBundle\Tests\CheckboxGrid;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\SchemaTool;
use Infinite\FormBundle\Form\Type\CheckboxGridType;
use Infinite\FormBundle\Form\Type\CheckboxRowType;
use Infinite\FormBundle\Form\Type\EntityCheckboxGridType;
use Infinite\FormBundle\Tests\CheckboxGrid\Entity as TestEntity;
use Infinite\FormBundle\Tests\CheckboxGrid\Type\SalesmanType;
use Symfony\Bridge\Doctrine\Test\DoctrineTestHelper;
use Symfony\Component\Form\Forms;

class EntityCheckboxGridTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
    protected $factory;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var TestEntity\Area[]
     */
    protected $areas;

    /**
     * @var TestEntity\Product[]
     */
    protected $products;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $emRegistry;

    protected function setUp()
    {
        // Create a test database, tables and a few rows
        $this->em = DoctrineTestHelper::createTestEntityManager();

        $schemaTool = new SchemaTool($this->em);
        $classes = array(
            $this->em->getClassMetadata('Infinite\FormBundle\Tests\CheckboxGrid\Entity\Area'),
            $this->em->getClassMetadata('Infinite\FormBundle\Tests\CheckboxGrid\Entity\Product'),
            $this->em->getClassMetadata('Infinite\FormBundle\Tests\CheckboxGrid\Entity\Salesman'),
            $this->em->getClassMetadata('Infinite\FormBundle\Tests\CheckboxGrid\Entity\SalesmanProductArea'),
        );

        $schemaTool->createSchema($classes);

        // The area/product arrays are keyed by their IDs
        $this->areas = array(
            1 => new TestEntity\Area('North side'),
            2 => new TestEntity\Area('East side'),
            3 => new TestEntity\Area('Inner north'),
            4 => new TestEntity\Area('Inner south'),
        );

        $this->products = array(
            1 => new TestEntity\Product('Chair'),
            2 => new TestEntity\Product('Desk'),
            3 => new TestEntity\Product('Table'),
        );

        foreach ($this->areas as $area) {
            $this->em->persist($area);
        }

        foreach ($this->products as $product) {
            $this->em->persist($product);
        }

        $this->em->flush();

        // This mock registry returns the real entity manager created above
        $this->emRegistry = $emRegistry = $this->getMock('Doctrine\\Common\\Persistence\\ManagerRegistry');

        $this->factory = Forms::createFormFactoryBuilder()
            ->addType(new CheckboxGridType)
            ->addType(new CheckboxRowType)
            ->addType(new EntityCheckboxGridType($emRegistry))
            ->addType(new SalesmanType)
            ->getFormFactory();
    }

    /**
     * Most tests will call getManagerForClass() once on the S.P.A. class.
     */
    protected function expectSpa()
    {
        $this->emRegistry->expects($this->once())
            ->method('getManagerForClass')
            ->with($this->equalTo('Infinite\\FormBundle\\Tests\\CheckboxGrid\\Entity\\SalesmanProductArea'))
            ->will($this->returnValue($this->em));
    }

    /**
     * Test that bound data is mapped back correctly
     */
    public function testBind()
    {
        $this->expectSpa();

        $salesman = new TestEntity\Salesman;

        $form = $this->factory->create('infinite_form_test_salesman', $salesman);

        $form->bind(array(
            'name' => 'John Smith',
            'productAreas' => array(
                1 => array(1 => '1', 2 => '1'),
                3 => array(1 => '1'),
                5 => array(1 => '1'), // Invalid values should be ignored
            ),
        ));

        $this->assertEquals(
            array(
                array('area' => $this->areas[1], 'product' => $this->products[1]),
                array('area' => $this->areas[1], 'product' => $this->products[2]),
                array('area' => $this->areas[3], 'product' => $this->products[1]),
            ),
            $salesman->getProductAreas()->map(function (TestEntity\SalesmanProductArea $spa) {
                return array('area' => $spa->getAreaServiced(), 'product' => $spa->getProductSold());
            })->toArray()
        );
    }

    /**
     * Test that an entity is preserved when its checkbox is left alone.
     */
    public function testEntityPreserved()
    {
        $this->expectSpa();

        $spa = new TestEntity\SalesmanProductArea();
        $spa->setAreaServiced($this->areas[2]);
        $spa->setProductSold($this->products[3]);

        $salesman = new TestEntity\Salesman;
        $salesman->addProductArea($spa);

        $form = $this->factory->create('infinite_form_test_salesman', $salesman);

        $form->bind(array(
            'name' => 'John Smith',
            'productAreas' => array(
                2 => array(3 => '1'),
            ),
        ));

        $this->assertCount(1, $salesman->getProductAreas());
        $this->assertSame($spa, $salesman->getProductAreas()->first());
    }

    /**
     * Query builders are allowed on both axes
     */
    public function testQueryBuilder()
    {
        $this->expectSpa();

        $form = $this->factory->create('infinite_form_test_salesman', null, array(
            'product_area_options' => array(
                'x_query_builder' => function (EntityRepository $repo) {
                    return $repo->createQueryBuilder('p')
                        ->where('p.name <> \'Chair\'');
                },
                'y_query_builder' => function (EntityRepository $repo) {
                    return $repo->createQueryBuilder('a')
                        ->where('a.name NOT LIKE \'Inner%\'');
                },
            ),
        ));

        $view = $form->createView();

        $checkboxCount = 0;

        foreach ($view->children['productAreas'] as $row) {
            foreach ($row->children as $cell) {
                if (in_array('checkbox', $cell->vars['block_prefixes'])) {
                    $checkboxCount++;
                }
            }
        }

        $this->assertEquals(4, $checkboxCount);
    }

    /**
     * Test that we can specify a named entity manager
     */
    public function testNamedEntityManager()
    {
        $this->emRegistry->expects($this->once())
            ->method('getManager')
            ->with($this->equalTo('salesman_em'))
            ->will($this->returnValue($this->em));

        $this->factory->create('infinite_form_test_salesman', null, array(
            'product_area_options' => array(
                'em' => 'salesman_em',
            ),
        ));
    }

    /**
     * If no entity manager name is specified, the system will pick the correct one for the given Doctrine class.
     * If the specified class isn't a Doctrine class, it should throw an exception.
     */
    public function testExpectsDoctrineObject()
    {
        $this->setExpectedException('Symfony\\Component\\OptionsResolver\\Exception\\InvalidOptionsException');

        $this->emRegistry->expects($this->once())
            ->method('getManagerForClass')
            ->with($this->equalTo('stdClass'))
            ->will($this->returnValue(null));

        $this->factory->create('infinite_form_entity_checkbox_grid', array(), array(
            'class' => 'stdClass',
            'x_path' => 'productSold',
            'y_path' => 'areaServiced',
        ));
    }
}
