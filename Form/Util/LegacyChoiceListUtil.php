<?php

/*
 * (c) Infinite Networks <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Infinite\FormBundle\Form\Util;

use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Doctrine\Form\ChoiceList\DoctrineChoiceLoader;
use Symfony\Bridge\Doctrine\Form\ChoiceList\EntityLoaderInterface;
use Symfony\Component\Form\ChoiceList\ChoiceListInterface;
use Symfony\Component\Form\ChoiceList\Factory\DefaultChoiceListFactory;
use Symfony\Component\Form\ChoiceList\Factory\PropertyAccessDecorator;

/**
 * @internal
 */
final class LegacyChoiceListUtil
{
    /**
     * @param EntityManager              $em
     * @param string                     $class
     * @param string                     $labelPath
     * @param EntityLoaderInterface|null $loader
     * @param callable                   $valueCallback
     *
     * @return ChoiceListInterface
     */
    public static function createEntityChoiceList(
        EntityManager $em,
        $class,
        $labelPath,
        ?EntityLoaderInterface $loader,
        $valueCallback
    ) {
        $factory = new PropertyAccessDecorator(new DefaultChoiceListFactory());

        return $factory->createListFromLoader(
            new DoctrineChoiceLoader($em, $class, null, $loader),
            $valueCallback
        );
    }

    private function __construct()
    {
    }

    private function __clone()
    {
    }
}
