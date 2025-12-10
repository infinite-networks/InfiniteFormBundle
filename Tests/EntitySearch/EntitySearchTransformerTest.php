<?php

/*
 * (c) Infinite Networks <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Infinite\FormBundle\Tests\EntitySearch;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Infinite\FormBundle\Form\DataTransformer\EntitySearchTransformer;
use Infinite\FormBundle\Tests\EntitySearch\Entity\Fruit;

class EntitySearchTransformerTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $em;

    protected function setUp(): void
    {
        parent::setUp();

        $this->em = $this->createMock(EntityManager::class);
        $mockMetadata = $this->createMock(ClassMetadata::class);

        $this->em->expects($this->any())
            ->method('getClassMetadata')
            ->with($this->equalTo(Fruit::class))
            ->will($this->returnValue($mockMetadata));

        $mockMetadata->expects($this->any())
            ->method('getIdentifierFieldNames')
            ->will($this->returnValue(array('id')));
    }

    public function testClassRequired()
    {
        $this->expectException('InvalidArgumentException');
        $this->makeTransformer(array('class' => null));
    }

    public function testValidClassRequired()
    {
        $this->expectException('InvalidArgumentException');
        $this->makeTransformer(array('class' => 'Infinite\\FormBundle\\ThisClassDoesNotExist'));
    }

    public function testCorrectClassRequired()
    {
        $this->expectException('Symfony\\Component\\Form\\Exception\\UnexpectedTypeException');
        $this->makeTransformer()->transform('omglol');
    }

    public function testTransform()
    {
        $fruit = new Fruit();
        $fruit->id = 42;
        $fruit->name = 'watermelon';

        $transformer = $this->makeTransformer();
        $data = $transformer->transform($fruit);

        $this->assertEquals(42, $data['id']);
        $this->assertEquals('watermelon', $data['name']);
    }

    public function testReverseTransformById()
    {
        $fruit = new Fruit();
        $fruit->id = 23;
        $fruit->name = 'coconut';

        $this->expectsGetRepository()->expects($this->once())
            ->method('find')
            ->with($this->equalTo('23'))
            ->will($this->returnValue($fruit));

        $transformer = $this->makeTransformer();
        $recoveredFruit = $transformer->reverseTransform(array('id' => '23', 'name' => ''));

        $this->assertSame($fruit, $recoveredFruit);
    }

    public function testReverseTransformByName()
    {
        $fruit = new Fruit();
        $fruit->id = 34;
        $fruit->name = 'pear';

        $this->expectsGetRepository()->expects($this->once())
            ->method('findOneBy')
            ->with($this->equalTo(array('name' => 'pear')))
            ->will($this->returnValue($fruit));

        $transformer = $this->makeTransformer();
        $recoveredFruit = $transformer->reverseTransform(array('id' => '', 'name' => 'pear'));

        $this->assertSame($fruit, $recoveredFruit);
    }

    public function testEmptyReverseTransform()
    {
        $this->assertNull($this->makeTransformer()->reverseTransform(array('id' => '', 'name' => '')));
    }

    public function testOverrideNameField()
    {
        $fruit = new Fruit();
        $fruit->id = 48;
        $fruit->name = 'feijoa';

        $transformer = $this->makeTransformer(array('name' => 'id'));
        $data = $transformer->transform($fruit);

        $this->assertEquals(48, $data['id']);
        $this->assertEquals(48, $data['name']);

        $this->expectsGetRepository()->expects($this->once())
            ->method('findOneBy')
            ->with($this->equalTo(array('id' => '48')))
            ->will($this->returnValue($fruit));

        $recoveredFruit = $transformer->reverseTransform(array('id' => '', 'name' => '48'));

        $this->assertSame($recoveredFruit, $fruit);
    }

    public function testObjectNotFoundById()
    {
        $this->expectException('Symfony\\Component\\Form\\Exception\\TransformationFailedException');

        $this->expectsGetRepository()->expects($this->once())
            ->method('find')
            ->with($this->equalTo('99'))
            ->will($this->returnValue(null));

        $transformer = $this->makeTransformer();
        $transformer->reverseTransform(array('id' => '99', 'name' => 'foo'));
    }

    public function testObjectNotFoundByName()
    {
        $this->expectException('Symfony\\Component\\Form\\Exception\\TransformationFailedException');

        $this->expectsGetRepository()->expects($this->once())
            ->method('findOneBy')
            ->with($this->equalTo(array('name' => 'foo')))
            ->will($this->returnValue(null));

        $transformer = $this->makeTransformer();
        $transformer->reverseTransform(array('id' => '', 'name' => 'foo'));
    }

    private function expectsGetRepository()
    {
        $mockRepository = $this->createMock(EntityRepository::class);

        $this->em->expects($this->once())
            ->method('getRepository')
            ->will($this->returnValue($mockRepository));

        return $mockRepository;
    }

    private function makeTransformer($options = array())
    {
        return new EntitySearchTransformer($this->em, $options + array(
            'class' => Fruit::class,
        ));
    }
}
