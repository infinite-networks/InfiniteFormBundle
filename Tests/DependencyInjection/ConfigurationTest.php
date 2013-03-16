<?php

/*
 * (c) Infinite Networks <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Infinite\FormBundle\Tests\DependencyInjection;

use Infinite\FormBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaultConfig()
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), array());

        $this->assertEquals($config, self::getBundleDefaultConfig());
    }

    public function testNullPolycollectionConfig()
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), array('polycollection' => null));

        $this->assertEquals($config, self::getBundleDefaultConfig());
    }

    protected static function getBundleDefaultConfig()
    {
        return array(
            'polycollection' => true
        );
    }
}
