<?php

/*
 * (c) Infinite Networks <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Infinite\FormBundle\Tests;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Infinite\FormBundle\InfiniteFormBundle;

class BundleTest extends \PHPUnit\Framework\TestCase
{
    public function testCreateBundle()
    {
        new InfiniteFormBundle();
        $this->assertTrue(true);
    }

    public static function createTestEntityManager()
    {
        $config = new Configuration();
        $config->setEntityNamespaces(['SymfonyTestsDoctrine' => 'Symfony\Bridge\Doctrine\Tests\Fixtures']);
        $config->setAutoGenerateProxyClasses(true);
        $config->setProxyDir(sys_get_temp_dir());
        $config->setProxyNamespace('SymfonyTests\Doctrine');
        $config->enableNativeLazyObjects(true);
        if (class_exists(AttributeDriver::class)) {
            $config->setMetadataDriverImpl(new AttributeDriver([
                __DIR__,
                __DIR__.'/../src/Attachment',
            ]));
        } else {
            $config->setMetadataDriverImpl(new AnnotationDriver(new AnnotationReader()));
        }

        $conn = DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ]);

        return new EntityManager($conn, $config);
    }
}
