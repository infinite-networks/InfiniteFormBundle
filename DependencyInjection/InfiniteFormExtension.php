<?php

/*
 * (c) Infinite Networks <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Infinite\FormBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Configures the DI container for InfiniteFormBundle.
 *
 * @author Tim Nagel <t.nagel@infinite.net.au>
 */
class InfiniteFormExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = $this->getConfiguration($configs, $container);
        $configs = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        if ($configs['attachment']) {
            $loader->load('attachment.yaml');

            $container->setParameter('infinite_form.attachment.save_config', $configs['attachments']);
        }

        if ($configs['checkbox_grid']) {
            $loader->load('checkbox_grid.yaml');
        }

        if ($configs['entity_search']) {
            $loader->load('entity_search.yaml');
        }

        if ($configs['polycollection']) {
            $loader->load('polycollection.yaml');
        }

        if ($configs['twig']) {
            $loader->load('twig.yaml');
        }
    }
}
