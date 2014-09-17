<?php

/*
 * (c) Infinite Networks <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Infinite\FormBundle\Tests\Attachment;

use Infinite\FormBundle\Attachment\Sanitiser;

class SanitiserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Sanitiser
     */
    public $sanitiser;

    protected function setUp()
    {
        $this->sanitiser = new Sanitiser;
    }

    /**
     * @dataProvider getFilenames
     */
    public function testSanitiseFile($filename, $expected)
    {
        $sanitised = $this->sanitiser->sanitiseFilename($filename);

        $this->assertEquals($expected, $sanitised);
    }

    public function getFilenames()
    {
        return array(
            array('file.zip', 'file.zip'),
            array('path/to/file.zip', 'file.zip'),
            array('file?.txt', 'file-.txt'),
            array('', '_')
        );
    }

    /**
     * @dataProvider getMimetypes
     */
    public function testSanitiseMime($mime, $expected)
    {
        $sanitised = $this->sanitiser->sanitiseMimeType($mime);

        $this->assertEquals($expected, $sanitised);
    }

    public function getMimetypes()
    {
        return array(
            array('invalid(/)mime', 'application/octet-stream'),
            array('application/pdf', 'application/pdf'),
            array('image/png', 'image/png'),
        );
    }
}
