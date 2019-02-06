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

class ConfigurationTest extends \PHPUnit\Framework\TestCase
{
    public function testDefaultConfig()
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), []);

        $this->assertEquals($config, self::getBundleDefaultConfig());
    }

    public function testAttachmentsConfig()
    {
        $attachmentConfig = [
            'attachments' => [
                'Foo\Bar' => [
                    'dir' => 'a/b/c',
                    'format' => '{hash(0..2)}/{name}',
                ],
            ],
        ];

        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), [$attachmentConfig]);

        $this->assertEquals($config, array_merge(self::getBundleDefaultConfig(), $attachmentConfig));
    }

    public function testNullAttachmentConfig()
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), [['attachment' => null]]);

        $this->assertEquals($config, self::getBundleDefaultConfig());
    }

    public function testNullCheckboxConfig()
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), [['checkbox_grid' => null]]);

        $this->assertEquals($config, self::getBundleDefaultConfig());
    }

    public function testNullEntitySearchConfig()
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), [['entity_search' => null]]);

        $this->assertEquals($config, self::getBundleDefaultConfig());
    }

    public function testNullPolycollectionConfig()
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), [['polycollection' => null]]);

        $this->assertEquals($config, self::getBundleDefaultConfig());
    }

    public function testNullTwigConfig()
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), [['twig' => null]]);

        $this->assertEquals($config, self::getBundleDefaultConfig());
    }

    protected static function getBundleDefaultConfig()
    {
        return array(
            'attachment' => true,
            'attachments' => [],
            'checkbox_grid' => true,
            'entity_search' => true,
            'polycollection' => true,
            'twig' => true,
        );
    }
}
