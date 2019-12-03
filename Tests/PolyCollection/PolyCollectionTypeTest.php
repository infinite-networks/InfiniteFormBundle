<?php

/*
 * (c) Infinite Networks <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Infinite\FormBundle\Tests\PolyCollection;

use Infinite\FormBundle\Form\Type\PolyCollectionType;
use Infinite\FormBundle\Tests\PolyCollection\Model\AbstractModel;
use Infinite\FormBundle\Tests\PolyCollection\Model\First;
use Infinite\FormBundle\Tests\PolyCollection\Model\Second;
use Infinite\FormBundle\Tests\PolyCollection\Model\Third;
use Infinite\FormBundle\Tests\PolyCollection\Type\AbstractType;
use Infinite\FormBundle\Tests\PolyCollection\Type\AbstractTypeIdType;
use Infinite\FormBundle\Tests\PolyCollection\Type\FirstSpecificOptionsType;
use Infinite\FormBundle\Tests\PolyCollection\Type\FirstType;
use Infinite\FormBundle\Tests\PolyCollection\Type\FirstTypeIdType;
use Infinite\FormBundle\Tests\PolyCollection\Type\FourthType;
use Infinite\FormBundle\Tests\PolyCollection\Type\SecondSpecificOptionsType;
use Infinite\FormBundle\Tests\PolyCollection\Type\SecondType;
use Symfony\Component\Form\Exception\ExceptionInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class PolyCollectionTypeTest extends TypeTestCase
{
    public function testObjectNotCoveredByTypesArray()
    {
        $form = $this->factory->create($this->getPolyCollectionType(), null, array(
            'types' => $this->getTestTypes(),
        ));
        $form->setData(array(
            new AbstractModel('Green'),
            new Third('Brown'),
        ));
    }

    public function testInvalidObject()
    {
        $this->expectException(ExceptionInterface::class);
        $form = $this->factory->create($this->getPolyCollectionType(), null, array(
            'types' => $this->getTestTypes(),
        ));
        $form->setData(array(
            new \stdClass(),
        ));
    }

    public function testInvalidBindType()
    {
        $this->expectException(\InvalidArgumentException::class);
        $form = $this->factory->create($this->getPolyCollectionType(), null, array(
            'types' => $this->getTestTypes(),
            'allow_add' => true,
        ));
        $form->submit(array(
            array(
                '_type' => 'unknown_type',
                'text' => 'Green',
            ),
        ));
    }

    public function testBindInvalidData()
    {
        $this->expectException(UnexpectedTypeException::class);
        $form = $this->factory->create($this->getPolyCollectionType(), null, array(
            'types' => $this->getTestTypes(),
        ));
        $form->submit('invalid_data');
    }

    public function testMultipartPropagation()
    {
        $form = $this->factory->create($this->getPolyCollectionType(), null, array(
            'types' => array(
                AbstractType::class,
                FourthType::class,
            ),
            'allow_add' => true,
        ));

        $this->assertTrue($form->createView()->vars['multipart']);
    }

    public function testBindNullEmptiesCollection()
    {
        $form = $this->factory->create($this->getPolyCollectionType(), null, array(
            'types' => $this->getTestTypes(),
            'allow_delete' => true,
        ));
        $form->submit(null);

        $this->assertCount(0, $form->getData());
    }

    public function testResizedUpIfBoundWithExtraDataAndAllowAdd()
    {
        $form = $this->factory->create($this->getPolyCollectionType(), null, array(
            'types' => $this->getTestTypes(),
            'allow_add' => true,
        ));

        $form->setData(array(
            new AbstractModel('Green'),
        ));
        $form->submit(array(
            array(
                '_type' => 'abstract_type',
                'text' => 'Green',
            ),
            array(
                '_type' => 'first_type',
                'text' => 'Red',
                'text2' => 'Car',
            ),
        ));

        $this->assertTrue($form->has('0'));
        $this->assertTrue($form->has('1'));
        $this->assertInstanceOf(
            AbstractModel::class,
            $form[0]->getData()
        );
        $this->assertInstanceOf(
            First::class,
            $form[1]->getData()
        );
        $this->assertEquals('Red', $form[1]->getData()->text);
        $this->assertEquals('Car', $form[1]->getData()->text2);
    }

    public function testResizedWithCustomTypeField()
    {
        $form = $this->factory->create($this->getPolyCollectionType(), null, [
            'types' => [
                AbstractTypeIdType::class,
                FirstTypeIdType::class,
            ],
            'type_name' => '_type_id',
            'allow_add' => true,
        ]);

        $form->setData([
            new AbstractModel('Green'),
        ]);
        $form->submit([
            [
                '_type_id' => 'abstract_type_id_type',
                'text' => 'Green',
            ],
            [
                '_type_id' => 'first_type_id_type',
                'text' => 'Red',
                'text2' => 'Car',
            ],
        ]);

        $this->assertTrue($form->has('0'));
        $this->assertTrue($form->has('1'));
        $this->assertInstanceOf(
            AbstractModel::class,
            $form[0]->getData()
        );
        $this->assertInstanceOf(
            First::class,
            $form[1]->getData()
        );
        $this->assertEquals('Red', $form[1]->getData()->text);
        $this->assertEquals('Car', $form[1]->getData()->text2);
    }

    public function testNotResizedIfBoundWithExtraData()
    {
        $form = $this->factory->create($this->getPolyCollectionType(), null, [
            'types' => $this->getTestTypes(),
        ]);
        $form->setData([
            new AbstractModel('Green'),
        ]);
        $form->submit([
            [
                '_type' => 'abstract_type',
                'text' => 'Green',
            ],
            [
                '_type' => 'first_type',
                'text' => 'Red',
                'text2' => 'Car',
            ],
        ]);

        $this->assertTrue($form->has('0'));
        $this->assertFalse($form->has('1'));
        $this->assertInstanceOf(
            AbstractModel::class,
            $form[0]->getData()
        );
    }

    public function testResizedDownIfBoundWithMissingDataAndAllowDelete()
    {
        $form = $this->factory->create($this->getPolyCollectionType(), null, [
            'types' => $this->getTestTypes(),
            'allow_delete' => true,
        ]);
        $form->setData([
            new AbstractModel('Green'),
            new First('Red', 'Car'),
            new Second('Blue', true),
        ]);
        $form->submit([
            0 => [
                '_type' => 'abstract_type',
                'text' => 'Green',
            ],
            2 => [
                '_type' => 'second_type',
                'checked' => 'true',
            ],
        ]);

        $this->assertTrue($form->has('0'));
        $this->assertFalse($form->has('1'));
        $this->assertTrue($form->has('2'));
        $this->assertInstanceOf(
            AbstractModel::class,
            $form[0]->getData()
        );
        $this->assertInstanceOf(
            Second::class,
            $form[2]->getData()
        );
    }

    public function testNotResizedIfBoundWithMissingData()
    {
        $form = $this->factory->create($this->getPolyCollectionType(), null, [
            'types' => $this->getTestTypes(),
        ]);
        $form->setData([
            new AbstractModel('Green'),
            new First('Red', 'Car'),
            new Second('Blue', true),
        ]);
        $form->submit([
            [
                '_type' => 'abstract_type',
                'text' => 'Brown',
            ],
            [
                '_type' => 'first_type',
                'text' => 'Yellow',
                'text2' => 'Bicycle',
            ],
        ]);

        $this->assertTrue($form->has('0'));
        $this->assertTrue($form->has('1'));
        $this->assertTrue($form->has('2'));
        $this->assertEquals('Brown', $form[0]->getData()->text);
        $this->assertEquals('Yellow', $form[1]->getData()->text);
        $this->assertEquals('Bicycle', $form[1]->getData()->text2);
        $this->assertEquals('', $form[2]->getData()->text);
        $this->assertFalse($form[2]->getData()->checked);
    }

    public function testSetDataAdjustsSize()
    {
        $form = $this->factory->create($this->getPolyCollectionType(), null, [
            'types' => $this->getTestTypes(),
            'options' => [
                'max_length' => 20,
            ],
        ]);
        $form->setData([
            new AbstractModel('Green'),
            new First('Red', 'Car'),
            new Second('Blue', true),
        ]);

        $this->assertCount(3, $form);
        $this->assertInstanceOf('Symfony\\Component\\Form\\Form', $form[0]);
        $this->assertInstanceOf('Symfony\\Component\\Form\\Form', $form[1]);
        $this->assertInstanceOf('Symfony\\Component\\Form\\Form', $form[2]);

        $this->assertInstanceOf(
            'Infinite\\FormBundle\\Tests\\PolyCollection\\Model\\AbstractModel',
            $form[0]->getData()
        );
        $this->assertInstanceOf(
            'Infinite\\FormBundle\\Tests\\PolyCollection\\Model\\First',
            $form[1]->getData()
        );
        $this->assertInstanceOf(
            'Infinite\\FormBundle\\Tests\\PolyCollection\\Model\\Second',
            $form[2]->getData()
        );
        $this->assertEquals('Green', $form[0]->getData()->text);
        $this->assertEquals('Red', $form[1]->getData()->text);
        $this->assertEquals('Car', $form[1]->getData()->text2);
        $this->assertEquals('Blue', $form[2]->getData()->text);
        $this->assertTrue($form[2]->getData()->checked);

        $this->assertEquals(20, $form[0]->getConfig()->getOption('max_length'));
        $this->assertEquals(20, $form[1]->getConfig()->getOption('max_length'));
        $this->assertEquals(20, $form[2]->getConfig()->getOption('max_length'));

        $form->setData(array(
            new AbstractModel('Orange'),
        ));

        $this->assertCount(1, $form);
        $this->assertInstanceOf('Symfony\\Component\\Form\\Form', $form[0]);
        $this->assertFalse(isset($form[1]));
        $this->assertFalse(isset($form[2]));
        $this->assertEquals('Orange', $form[0]->getData()->text);
        $this->assertEquals(20, $form[0]->getConfig()->getOption('max_length'));
    }

    public function testResizedDownIfBoundWithMissingDataAndAllowDeleteWithEntityIndexProperty()
    {
        $form = $this->factory->create($this->getPolyCollectionType(), null, array(
            'types' => $this->getTestTypes(),
            'allow_delete' => true,
            'index_property' => 'id',
            ));
        $form->setData(array(
                new AbstractModel('Green', 1),
                new First('Red', 'Car', 2),
                new Second('Blue', true, 3),
            ));
        $form->submit(array(
                array(
                    '_type' => 'abstract_type',
                    'text' => 'Green',
                    'id' => 1,
                ),
                array(
                    '_type' => 'second_type',
                    'checked' => 'true',
                    'id' => 3,
                ),
            ));

        $this->assertTrue($form->has('0'));
        $this->assertFalse($form->has('1'));
        $this->assertTrue($form->has('2'));
        $this->assertInstanceOf(
            'Infinite\\FormBundle\\Tests\\PolyCollection\\Model\\AbstractModel',
            $form[0]->getData()
        );
        $this->assertInstanceOf(
            'Infinite\\FormBundle\\Tests\\PolyCollection\\Model\\Second',
            $form[2]->getData()
        );
    }

    public function testResizedUpIfBoundWithExtraDataAndAllowAddWithEntityIndexPropertyMissing()
    {
        $form = $this->factory->create($this->getPolyCollectionType(), null, array(
            'types' => $this->getTestTypes(),
            'allow_add' => true,
            'index_property' => 'id',
            ));
        $form->setData(array(
                new AbstractModel('Green', 1),
            ));
        $form->submit(array(
                array(
                    '_type' => 'abstract_type',
                    'text' => 'Green',
                    'id' => 1,
                ),
                array(
                    '_type' => 'second_type',
                    'text' => 'Blue',
                    'checked' => 'true',
                ),
            ));

        $this->assertTrue($form->has('0'));
        $this->assertTrue($form->has('1'));
        $this->assertEquals(2, $form->count());
        $this->assertInstanceOf(
            'Infinite\\FormBundle\\Tests\\PolyCollection\\Model\\AbstractModel',
            $form[0]->getData()
        );
        $this->assertInstanceOf(
            'Infinite\\FormBundle\\Tests\\PolyCollection\\Model\\Second',
            $form[1]->getData()
        );
    }

    public function testReorderedIfBoundWithShuffledDataAndAllowMatchWithEntityIndexProperty()
    {
        $form = $this->factory->create($this->getPolyCollectionType(), null, array(
            'types' => $this->getTestTypes(),
            'allow_add' => true,
            'index_property' => 'id',
            ));
        $form->setData(array(
                new AbstractModel('Green', 1),
                new Second('Blue', false, 2),
            ));
        $form->submit(array(
                array(
                    '_type' => 'second_type',
                    'checked' => 'true',
                    'id' => 2,
                ),
                array(
                    '_type' => 'abstract_type',
                    'text' => 'Green',
                    'id' => 1,
                ),
            ));

        $this->assertTrue($form->has('0'));
        $this->assertTrue($form->has('1'));
        $this->assertInstanceOf(
            'Infinite\\FormBundle\\Tests\\PolyCollection\\Model\\AbstractModel',
            $form[0]->getData()
        );
        $this->assertInstanceOf(
            'Infinite\\FormBundle\\Tests\\PolyCollection\\Model\\Second',
            $form[1]->getData()
        );
    }

    public function testContainsNoChildByDefault()
    {
        $form = $this->factory->create($this->getPolyCollectionType(), null, array(
            'types' => array(FirstType::class),
        ));

        $this->assertCount(0, $form);
    }

    public function testThrowsExceptionIfObjectIsNotTraversable()
    {
        $form = $this->factory->create($this->getPolyCollectionType(), null, array(
            'types' => array(FirstType::class),
        ));
        $this->expectException('Symfony\Component\Form\Exception\UnexpectedTypeException');
        $form->setData(new \stdClass());
    }

    public function testTypesMissingThrowsException()
    {
        $this->expectException(MissingOptionsException::class);

        $this->factory->create($this->getPolyCollectionType(), null, array());
    }

    public function testTypeLegacyOptions()
    {
        $form = $this->factory->create($this->getPolyCollectionType(), null, array(
            'types' => $this->getTestTypes(),
            'options' => [
                'max_length' => '30',
            ],
        ));
        $form->setData(array(
            new AbstractModel('Green', 1),
            new Second('Blue', false, 2),
        ));

        $this->assertEquals(30, $form->get(0)->getConfig()->getOptions()['max_length']);
        $this->assertEquals(30, $form->get(1)->getConfig()->getOptions()['max_length']);
    }

    public function testTypesOptions()
    {
        $form = $this->factory->create($this->getPolyCollectionType(), null, [
            'types' => $this->getTestTypesWithSpecificOptions(),
            'types_options' => [
                FirstSpecificOptionsType::class => [
                    'first_option' => 888,
                ],
                SecondSpecificOptionsType::class => [
                    'second_option' => 999,
                ],
            ],
        ]);

        $form->setData([
            new First('Green', false),
            new Second('Blue', false),
        ]);

        $this->assertEquals(888, $form->get(0)->getConfig()->getOption('first_option'));
        $this->assertEquals(999, $form->get(1)->getConfig()->getOption('second_option'));
    }

    protected function getExtensions()
    {
        return array(
            new FormExtension(),
        );
    }

    private function getPolyCollectionType()
    {
        return PolyCollectionType::class;
    }

    private function getTestTypes()
    {
        return array(
            AbstractType::class,
            FirstType::class,
            SecondType::class,
        );
    }

    private function getTestTypesWithSpecificOptions()
    {
        return array(
            AbstractType::class,
            FirstSpecificOptionsType::class,
            SecondSpecificOptionsType::class,
        );
    }
}
