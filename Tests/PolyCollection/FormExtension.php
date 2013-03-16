<?php

/*
 * (c) Infinite Networks <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Infinite\FormBundle\Tests\PolyCollection;

use Infinite\FormBundle\Form\Type\PolyCollectionType;
use Infinite\FormBundle\Tests\PolyCollection\Type\AbstractType;
use Infinite\FormBundle\Tests\PolyCollection\Type\FirstType;
use Infinite\FormBundle\Tests\PolyCollection\Type\FourthType;
use Infinite\FormBundle\Tests\PolyCollection\Type\SecondType;
use Symfony\Component\Form\AbstractExtension;

/**
 * Testing extension for the PolyCollection
 *
 * @author Tim Nagel <t.nagel@infinite.net.au>
 * */
class FormExtension extends AbstractExtension
{
    protected function loadTypes()
    {
        return array(
            new PolyCollectionType(),
            new AbstractType(),
            new FirstType(),
            new SecondType(),
            new FourthType(),
        );
    }
}
