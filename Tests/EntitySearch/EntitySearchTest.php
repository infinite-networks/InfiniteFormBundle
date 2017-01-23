<?php

/*
 * (c) Infinite Networks <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Infinite\FormBundle\Tests\CheckboxGrid;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Infinite\FormBundle\Form\DataTransformer\EntitySearchTransformerFactory;
use Infinite\FormBundle\Form\Type\EntitySearchType;
use Infinite\FormBundle\Form\Util\LegacyFormUtil;
use Infinite\FormBundle\Tests\EntitySearch\Entity\Fruit;
use Symfony\Bridge\Doctrine\Test\DoctrineTestHelper;
use Symfony\Component\Form\Forms;

class EntitySearchTest extends \PHPUnit_Framework_TestCase
{
    /** @var EntityManager */
    private $em;

    /** @var \Symfony\Component\Form\FormFactoryInterface */
    private $factory;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $emRegistry;

    protected function setUp()
    {
        // Create a test database, tables and a few rows
        $this->em = DoctrineTestHelper::createTestEntityManager();

        $schemaTool = new SchemaTool($this->em);
        $classes = array(
            $this->em->getClassMetadata('Infinite\\FormBundle\\Tests\\EntitySearch\\Entity\\Fruit'),
        );

        $schemaTool->createSchema($classes);

        foreach (explode(' ', 'apple avocado banana durian orange pineapple') as $fruitName) {
            $fruit = new Fruit();
            $fruit->name = $fruitName;
            $this->em->persist($fruit);
        }

        $this->em->flush();

        $this->emRegistry = $this->getMock('Doctrine\\Common\\Persistence\\ManagerRegistry');
        $this->emRegistry->expects($this->once())
            ->method('getManagerForClass')
            ->with($this->equalTo('Infinite\\FormBundle\\Tests\\EntitySearch\\Entity\\Fruit'))
            ->will($this->returnValue($this->em));

        // Prepare to create forms
        $this->factory = Forms::createFormFactoryBuilder()
            ->addType(new EntitySearchType(new EntitySearchTransformerFactory($this->emRegistry)))
            ->getFormFactory();
    }

    public function testSubmitWithNameOnly()
    {
        // This happens if someone types in a name but doesn't click on the Javascript dropdown list.
        $form = $this->makeForm();

        $form->submit(array(
            'id' => '',
            'name' => 'avocado',
        ));

        $fruit = $form->getData();
        $this->assertEquals('Infinite\\FormBundle\\Tests\\EntitySearch\\Entity\\Fruit', get_class($fruit));
        $this->assertEquals(2, $fruit->id);
    }

    public function testSubmitWithId()
    {
        // This happens if someone clicks on the Javascript dropdown list.
        $form = $this->makeForm();

        $form->submit(array(
            'id' => '4',
            'name' => '', // (Ignored by the transformer since the ID is available)
        ));

        $fruit = $form->getData();
        $this->assertEquals('Infinite\\FormBundle\\Tests\\EntitySearch\\Entity\\Fruit', get_class($fruit));
        $this->assertEquals('durian', $fruit->name);
    }

    public function testFormView()
    {
        $form = $this->makeForm();
        $form->setData($this->em->find('Infinite\\FormBundle\\Tests\\EntitySearch\\Entity\\Fruit', 6));
        $view = $form->createView();

        $this->assertEquals(6, $view->vars['value']['id']);
        $this->assertEquals('pineapple', $view->vars['value']['name']);
    }

    private function makeForm()
    {
        return $this->factory->createBuilder(LegacyFormUtil::getType('Infinite\FormBundle\Form\Type\EntitySearchType'), null, array(
            'invalid_message' => 'Item not found',
            'class' => 'Infinite\\FormBundle\\Tests\\EntitySearch\\Entity\\Fruit',
        ))->getForm();
    }
}
