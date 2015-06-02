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
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Configures the DI container for InfiniteFormBundle.
 *
 * @author Tim Nagel <t.nagel@infinite.net.au>
 */
class InfiniteFormExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = $this->getConfiguration($configs, $container);
        $configs       = $this->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        if ($configs['attachment']) {
            $loader->load('attachment.xml');
        }

        if ($configs['checkbox_grid']) {
            $loader->load('checkbox_grid.xml');
        }

        if ($configs['entity_search']) {
            $loader->load('entity_search.xml');
        }

        if ($configs['polycollection']) {
            $loader->load('polycollection.xml');
        }

        if ($configs['twig']) {
            $loader->load('twig.xml');
        }

        $attachmentDefinition = $container->getDefinition('infinite_form.attachment.form_type');

        if (method_exists($attachmentDefinition, 'setFactory')) {
            $attachmentDefinition->setFactory(array(new Reference('doctrine'), 'getManager'));
        } else {
            $attachmentDefinition->setFactoryService('doctrine');
            $attachmentDefinition->setFactoryMethod('getManager');
        }
    }
}
