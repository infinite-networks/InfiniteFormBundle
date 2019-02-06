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

class InfiniteFormExtensionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ContainerBuilder
     */
    protected $container;

    /**
     * @var InfiniteFormExtension
     */
    protected $extension;

    protected function setUp()
    {
        $this->container = new ContainerBuilder();
        $this->extension = new InfiniteFormExtension();
    }

    public function provideFeatures()
    {
        return array(
            array('polycollection', 'infinite_form.polycollection.form_type'),
            array('attachment', 'infinite_form.attachment.form_type'),
            array('checkbox_grid', 'infinite_form.form_type.checkbox_grid_type'),
            array('entity_search', 'infinite_form.entity_search.type'),
            array('twig', 'infinite_form.twig_extension'),
        );
    }

    /**
     * @dataProvider provideFeatures
     */
    public function testFeatureLoaded($feature, $serviceId)
    {
        $config = array(
            $feature => true,
        );

        $this->extension->load(array($config), $this->container);

        $this->assertHasDefinition($serviceId);
    }

    /**
     * @dataProvider provideFeatures
     */
    public function testFeatureNotLoaded($feature, $serviceId)
    {
        $config = array(
            $feature => false,
        );

        $this->extension->load(array($config), $this->container);

        $this->assertNotHasDefinition($serviceId);
    }

    /**
     * @param string $id
     */
    private function assertHasDefinition($id)
    {
        $this->assertTrue($this->container->hasDefinition($id) || $this->container->hasAlias($id));
    }

    /**
     * @param string $id
     */
    private function assertNotHasDefinition($id)
    {
        $this->assertFalse($this->container->hasDefinition($id) || $this->container->hasAlias($id));
    }

    protected function tearDown()
    {
        $this->container = null;
    }
}
