<?php

namespace Infinite\FormBundle\Form\DataTransformer;

use Symfony\Bridge\Doctrine\ManagerRegistry;

/**
 * A quick class to create EntitySearchTransformers from options.
 * Some forms will have multiple entity searches at once and we need at least one transformer for each different class.
 */
class EntitySearchTransformerFactory
{
    private $registry;

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function createFromOptions($options): EntitySearchTransformer
    {
        $manager = $this->registry->getManagerForClass($options['class']);

        return new EntitySearchTransformer($manager, $options);
    }
}
