<?php

/*
 * (c) Infinite Networks <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Infinite\FormBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This class contains the configuration information for the bundle
 *
 * This information is solely responsible for how the different configuration
 * sections are normalized, and merged.
 */
class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     *
     * @return TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('infinite_form');

        $rootNode
            ->children()
                ->booleanNode('attachment')
                    ->defaultTrue()
                ->end()
                ->booleanNode('checkbox_grid')
                    ->defaultTrue()
                ->end()
                ->booleanNode('entity_search')
                    ->defaultTrue()
                ->end()
                ->booleanNode('polycollection')
                    ->defaultTrue()
                ->end()
                ->booleanNode('choice_tree')
                    ->defaultTrue()
                ->end()
                ->booleanNode('twig')
                    ->defaultTrue()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
