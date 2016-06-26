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
use Symfony\Bridge\Doctrine\Form\ChoiceList\EntityChoiceList;
use Symfony\Bridge\Doctrine\Form\ChoiceList\EntityLoaderInterface;
use Symfony\Component\Form\ChoiceList\ChoiceListInterface;
use Symfony\Component\Form\ChoiceList\Factory\DefaultChoiceListFactory;
use Symfony\Component\Form\ChoiceList\Factory\PropertyAccessDecorator;
use Symfony\Component\Form\FormTypeInterface;

/**
 * @internal
 */
final class LegacyChoiceListUtil
{
    /**
     * @param EntityManager $em
     * @param string $class
     * @param string $labelPath
     * @param EntityLoaderInterface|null $loader
     * @param callable $valueCallback
     * @return ChoiceListInterface|EntityChoiceList
     */
    public static function createEntityChoiceList(
        EntityManager $em,
        $class,
        $labelPath,
        EntityLoaderInterface $loader = null,
        $valueCallback
    )
    {
        if (class_exists('Symfony\Component\Form\ChoiceList\Factory\DefaultChoiceListFactory')) {
            // The constructor's arguments changed in 3.1.
            // If the first argument's name is "factory" then it's the older version. 
            
            $factory = new PropertyAccessDecorator(new DefaultChoiceListFactory());
            $refl = new \ReflectionClass('Symfony\Bridge\Doctrine\Form\ChoiceList\DoctrineChoiceLoader');
            $constructor = $refl->getConstructor();

            if ($constructor->getParameters()[0]->getName() == 'factory') {
                // BC < 3.1
                return $factory->createListFromLoader(
                    new DoctrineChoiceLoader(
                        $factory,
                        $em,
                        $class,
                        null,
                        $loader
                    ),
                    $valueCallback
                );
            }

            // 3.1+
            return $factory->createListFromLoader(
                new DoctrineChoiceLoader($em, $class, null, $loader),
                $valueCallback
            );
        }

        // Older BC
        return new EntityChoiceList(
            $em,
            $class,
            $labelPath,
            $loader
        );
    }

    private function __construct()
    {
    }

    private function __clone()
    {
    }
}