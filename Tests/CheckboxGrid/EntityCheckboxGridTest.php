<?php

/*
 * (c) Infinite Networks <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Infinite\FormBundle\Tests\CheckboxGrid;

use Doctrine\ORM\Tools\SchemaTool;
use Infinite\FormBundle\Form\Type\CheckboxGridType;
use Infinite\FormBundle\Form\Type\CheckboxRowType;
use Infinite\FormBundle\Form\Type\EntityCheckboxGridType;
use Infinite\FormBundle\Tests\CheckboxGrid\Entity as TestEntity;
use Infinite\FormBundle\Tests\CheckboxGrid\Type\SalesmanType;
use Symfony\Bridge\Doctrine\Tests\DoctrineOrmTestCase;
use Symfony\Component\Form\Forms;
use Symfony\Component\HttpFoundation\Request;

class EntityCheckboxGridTest extends DoctrineOrmTestCase
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

    protected function setUp()
    {
        parent::setUp();

        // Create a test database, tables and a few rows
        $this->em = $this->createTestEntityManager();

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
        $emRegistry = $this->getMock('Doctrine\Common\Persistence\ManagerRegistry');
        $emRegistry->expects($this->any())
            ->method('getManager')
            ->will($this->returnValue($this->em));
        $emRegistry->expects($this->any())
            ->method('getManagerForClass')
            ->will($this->returnValue($this->em));

        $this->factory = Forms::createFormFactoryBuilder()
            ->addType(new CheckboxGridType)
            ->addType(new CheckboxRowType)
            ->addType(new EntityCheckboxGridType($emRegistry))
            ->addType(new SalesmanType)
            ->getFormFactory();
    }

    /**
     * Test that bound data is mapped back correctly
     */
    public function testBind()
    {
        $salesman = new TestEntity\Salesman;

        $form = $this->factory->create('infinite_form_test_salesman', $salesman);

        $form->bind(array(
            'name' => 'John Smith',
            'productAreas' => array(
                1 => array(1 => '1', 2 => '1'),
                3 => array(1 => '1'),
                5 => array(1 => '1'), // Invalid values should be ignored by the transformer
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
}
