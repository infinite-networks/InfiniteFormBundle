<?php

/*
 * (c) Infinite Networks <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Infinite\FormBundle\Tests;

use Infinite\FormBundle\InfiniteFormBundle;

class BundleTest extends \PHPUnit\Framework\TestCase
{
    public function testCreateBundle()
    {
        new InfiniteFormBundle();
        $this->assertTrue(true);
    }
}
