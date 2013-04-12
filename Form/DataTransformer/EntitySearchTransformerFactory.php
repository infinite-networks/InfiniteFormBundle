<?php

namespace Infinite\FormBundle\Form\DataTransformer;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * A quick class to create EntitySearchTransformers from options.
 * Some forms will have multiply entity searches at once and we need at least one transformer for each different class.
 */
class EntitySearchTransformerFactory
{
    private $registry;

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function createFromOptions($options)
    {
        $manager = $this->registry->getManagerForClass($options['class']);

        return new EntitySearchTransformer($manager, $options);
    }
}
