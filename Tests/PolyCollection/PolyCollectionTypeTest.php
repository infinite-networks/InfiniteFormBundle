<?php

/*
 * (c) Infinite Networks <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Infinite\FormBundle\Tests\PolyCollection;

use Infinite\FormBundle\Form\Util\LegacyFormUtil;
use Infinite\FormBundle\Tests\PolyCollection\Model\AbstractModel;
use Infinite\FormBundle\Tests\PolyCollection\Model\First;
use Infinite\FormBundle\Tests\PolyCollection\Model\Second;
use Infinite\FormBundle\Tests\PolyCollection\Model\Third;
use Symfony\Component\Form\Test\TypeTestCase;

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
        $this->setExpectedException('Symfony\\Component\\Form\\Exception\\ExceptionInterface');
        $form = $this->factory->create($this->getPolyCollectionType(), null, array(
            'types' => $this->getTestTypes(),
        ));
        $form->setData(array(
            new \stdClass
        ));
    }

    public function testInvalidBindType()
    {
        $this->setExpectedException('InvalidArgumentException');
        $form = $this->factory->create($this->getPolyCollectionType(), null, array(
            'types' => $this->getTestTypes(),
            'allow_add' => true
        ));
        $form->submit(array(
            array(
                '_type' => LegacyFormUtil::getType('Infinite\FormBundle\Tests\PolyCollection\Type\UnknownType'),
                'text' => 'Green'
            )
        ));
    }

    public function testBindInvalidData()
    {
        $this->setExpectedException('Symfony\Component\Form\Exception\UnexpectedTypeException');
        $form = $this->factory->create($this->getPolyCollectionType(), null, array(
            'types' => $this->getTestTypes(),
        ));
        $form->submit('invalid_data');
    }

    public function testMultipartPropagation()
    {
        $form = $this->factory->create($this->getPolyCollectionType(), null, array(
            'types' => array(
                LegacyFormUtil::getType('Infinite\FormBundle\Tests\PolyCollection\Type\AbstractType'),
                LegacyFormUtil::getType('Infinite\FormBundle\Tests\PolyCollection\Type\FourthType')
            ),
            'allow_add' => true
        ));

        $this->assertTrue($form->createView()->vars['multipart']);
    }

    public function testBindNullEmptiesCollection()
    {
        $form = $this->factory->create($this->getPolyCollectionType(), null, array(
            'types' => $this->getTestTypes(),
            'allow_delete' => true
        ));
        $form->submit(null);

        $this->assertCount(0, $form->getData());
    }

    public function testResizedUpIfBoundWithExtraDataAndAllowAdd()
    {
        $form = $this->factory->create($this->getPolyCollectionType(), null, array(
            'types' => $this->getTestTypes(),
            'allow_add' => true
        ));

        $form->setData(array(
            new AbstractModel('Green'),
        ));
        $form->submit(array(
            array(
                '_type' => LegacyFormUtil::getType('Infinite\FormBundle\Tests\PolyCollection\Type\AbstractType'),
                'text' => 'Green'
            ),
            array(
                '_type' => LegacyFormUtil::getType('Infinite\FormBundle\Tests\PolyCollection\Type\FirstType'),
                'text' => 'Red',
                'text2' => 'Car'
            )
        ));

        $this->assertTrue($form->has('0'));
        $this->assertTrue($form->has('1'));
        $this->assertInstanceOf(
            'Infinite\\FormBundle\\Tests\\PolyCollection\\Model\\AbstractModel',
            $form[0]->getData()
        );
        $this->assertInstanceOf(
            'Infinite\\FormBundle\\Tests\\PolyCollection\\Model\\First',
            $form[1]->getData()
        );
        $this->assertEquals('Red', $form[1]->getData()->text);
        $this->assertEquals('Car', $form[1]->getData()->text2);
    }

    public function testResizedWithCustomTypeField()
    {
        $form = $this->factory->create($this->getPolyCollectionType(), null, array(
            'types' => $this->getTestTypes(),
            'type_name' => '_type_id',
            'allow_add' => true
        ));

        $form->setData(array(
            new AbstractModel('Green'),
        ));
        $form->submit(array(
            array(
                '_type_id' => LegacyFormUtil::getType('Infinite\FormBundle\Tests\PolyCollection\Type\AbstractType'),
                'text' => 'Green'
            ),
            array(
                '_type_id' => LegacyFormUtil::getType('Infinite\FormBundle\Tests\PolyCollection\Type\FirstType'),
                'text' => 'Red',
                'text2' => 'Car'
            )
        ));

        $this->assertTrue($form->has('0'));
        $this->assertTrue($form->has('1'));
        $this->assertInstanceOf(
            'Infinite\\FormBundle\\Tests\\PolyCollection\\Model\\AbstractModel',
            $form[0]->getData()
        );
        $this->assertInstanceOf(
            'Infinite\\FormBundle\\Tests\\PolyCollection\\Model\\First',
            $form[1]->getData()
        );
        $this->assertEquals('Red', $form[1]->getData()->text);
        $this->assertEquals('Car', $form[1]->getData()->text2);
    }

    public function testNotResizedIfBoundWithExtraData()
    {
        $form = $this->factory->create($this->getPolyCollectionType(), null, array(
            'types' => $this->getTestTypes(),
        ));
        $form->setData(array(
            new AbstractModel('Green'),
        ));
        $form->submit(array(
            array(
                '_type' => LegacyFormUtil::getType('Infinite\FormBundle\Tests\PolyCollection\Type\AbstractType'),
                'text' => 'Green'
            ),
            array(
                '_type' => LegacyFormUtil::getType('Infinite\FormBundle\Tests\PolyCollection\Type\FirstType'),
                'text' => 'Red',
                'text2' => 'Car'
            )
        ));

        $this->assertTrue($form->has('0'));
        $this->assertFalse($form->has('1'));
        $this->assertInstanceOf(
            'Infinite\\FormBundle\\Tests\\PolyCollection\\Model\\AbstractModel',
            $form[0]->getData()
        );
    }

    public function testResizedDownIfBoundWithMissingDataAndAllowDelete()
    {
        $form = $this->factory->create($this->getPolyCollectionType(), null, array(
            'types' => $this->getTestTypes(),
            'allow_delete' => true
        ));
        $form->setData(array(
            new AbstractModel('Green'),
            new First('Red', 'Car'),
            new Second('Blue', true)
        ));
        $form->submit(array(
            0=>array(
                '_type' => LegacyFormUtil::getType('Infinite\FormBundle\Tests\PolyCollection\Type\AbstractType'),
                'text' => 'Green'
            ),
            2=>array(
                '_type' => LegacyFormUtil::getType('Infinite\FormBundle\Tests\PolyCollection\Type\SecondType'),
                'checked' => 'true'
            )
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

    public function testNotResizedIfBoundWithMissingData()
    {
        $form = $this->factory->create($this->getPolyCollectionType(), null, array(
            'types' => $this->getTestTypes(),
        ));
        $form->setData(array(
            new AbstractModel('Green'),
            new First('Red', 'Car'),
            new Second('Blue', true)
        ));
        $form->submit(array(
            array(
                '_type' => LegacyFormUtil::getType('Infinite\FormBundle\Tests\PolyCollection\Type\AbstractType'),
                'text' => 'Brown'
            ),
            array(
                '_type' => LegacyFormUtil::getType('Infinite\FormBundle\Tests\PolyCollection\Type\FirstType'),
                'text' => 'Yellow',
                'text2' => 'Bicycle'
            )
        ));

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
        $form = $this->factory->create($this->getPolyCollectionType(), null, array(
            'types' => $this->getTestTypes(),
            'options' => array(
                'max_length' => 20,
            )
        ));
        $form->setData(array(
            new AbstractModel('Green'),
            new First('Red', 'Car'),
            new Second('Blue', true)
        ));

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
            new AbstractModel('Orange')
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
            'index_property' => 'id'
            ));
        $form->setData(array(
                new AbstractModel('Green', 1),
                new First('Red', 'Car', 2),
                new Second('Blue', true, 3)
            ));
        $form->bind(array(
                array(
                    '_type' => LegacyFormUtil::getType('Infinite\FormBundle\Tests\PolyCollection\Type\AbstractType'),
                    'text' => 'Green',
                    'id'=>1
                ),
                array(
                    '_type' => LegacyFormUtil::getType('Infinite\FormBundle\Tests\PolyCollection\Type\SecondType'),
                    'checked' => 'true',
                    'id'=>3
                )
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
            'index_property' => 'id'
            ));
        $form->setData(array(
                new AbstractModel('Green', 1),
            ));
        $form->bind(array(
                array(
                    '_type' => LegacyFormUtil::getType('Infinite\FormBundle\Tests\PolyCollection\Type\AbstractType'),
                    'text' => 'Green',
                    'id'=>1
                ),
                array(
                    '_type' => LegacyFormUtil::getType('Infinite\FormBundle\Tests\PolyCollection\Type\SecondType'),
                    'text' => 'Blue',
                    'checked' => 'true'
                )
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
            'index_property' => 'id'
            ));
        $form->setData(array(
                new AbstractModel('Green', 1),
                new Second('Blue', false, 2)
            ));
        $form->bind(array(
                array(
                    '_type' => LegacyFormUtil::getType('Infinite\FormBundle\Tests\PolyCollection\Type\SecondType'),
                    'checked' => 'true',
                    'id'=>2
                ),
                array(
                    '_type' => LegacyFormUtil::getType('Infinite\FormBundle\Tests\PolyCollection\Type\AbstractType'),
                    'text' => 'Green',
                    'id'=>1
                )
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
            'types' => array(),
        ));

        $this->assertCount(0, $form);
    }

    public function testThrowsExceptionIfObjectIsNotTraversable()
    {
        $form = $this->factory->create($this->getPolyCollectionType(), null, array(
            'types' => array(),
        ));
        $this->setExpectedException('Symfony\Component\Form\Exception\UnexpectedTypeException');
        $form->setData(new \stdClass());
    }

    public function testTypesMissingThrowsException()
    {
        $this->setExpectedException('Symfony\Component\OptionsResolver\Exception\MissingOptionsException');

        $this->factory->create($this->getPolyCollectionType(), null, array());
    }

    protected function getExtensions()
    {
        return array(
            new FormExtension()
        );
    }

    private function getPolyCollectionType()
    {
        return LegacyFormUtil::getType('Infinite\FormBundle\Form\Type\PolyCollectionType');
    }

    private function getTestTypes()
    {
        return array(
            LegacyFormUtil::getType('Infinite\FormBundle\Tests\PolyCollection\Type\AbstractType'),
            LegacyFormUtil::getType('Infinite\FormBundle\Tests\PolyCollection\Type\FirstType'),
            LegacyFormUtil::getType('Infinite\FormBundle\Tests\PolyCollection\Type\SecondType'),
        );
    }
}
