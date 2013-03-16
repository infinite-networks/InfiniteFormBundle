<?php

/*
 * (c) Infinite Networks <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Infinite\FormBundle\Tests\DependencyInjection;

use Infinite\FormBundle\DependencyInjection\InfiniteFormExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class InfiniteFormExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContainerBuilder
     */
    protected $configuration;

    /**
     * @var InfiniteFormExtension
     */
    protected $extension;

    protected function setUp()
    {
        $this->configuration = new ContainerBuilder;
        $this->extension     = new InfiniteFormExtension;
    }

    public function testPolyCollectionLoaded()
    {
        $config = array(
            'polycollection' => true
        );

        $this->extension->load(array($config), $this->configuration);

        $this->assertHasDefinition('infinite_form.polycollection.form_type');
    }

    public function testPolyCollectionNotLoaded()
    {
        $config = array(
            'polycollection' => false
        );

        $this->extension->load(array($config), $this->configuration);

        $this->assertNotHasDefinition('infinite_form.polycollection.form_type');
    }

    /**
     * @param string $id
     */
    private function assertHasDefinition($id)
    {
        $this->assertTrue(($this->configuration->hasDefinition($id) ?: $this->configuration->hasAlias($id)));
    }

    /**
     * @param string $id
     */
    private function assertNotHasDefinition($id)
    {
        $this->assertFalse(($this->configuration->hasDefinition($id) ?: $this->configuration->hasAlias($id)));
    }

    protected function tearDown()
    {
        unset($this->configuration);
    }
}
