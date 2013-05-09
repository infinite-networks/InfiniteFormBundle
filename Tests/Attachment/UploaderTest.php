<?php

/*
 * (c) Infinite Networks <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Infinite\FormBundle\Tests\Attachment;

use Infinite\FormBundle\Attachment\PathHelper;
use Infinite\FormBundle\Attachment\Sanitiser;
use Infinite\FormBundle\Attachment\Uploader;
use Symfony\Component\Form\Forms;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class UploaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Uploader
     */
    public $uploader;

    /**
     * @var PathHelper
     */
    public $pathHelper;

    protected function setUp()
    {
        $sanitiser = new Sanitiser();

        $this->pathHelper = new PathHelper($sanitiser, array(
            'Infinite\\FormBundle\\Tests\\Attachment\\Attachment' => array(
                'dir'    => sys_get_temp_dir(),
                'format' => 'test/{hash(0..4)}/{name}',
            )
        ));
        $this->uploader = new Uploader($sanitiser, $this->pathHelper);
    }

    public function testAcceptUpload()
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test');
        $file     = new UploadedFile($tempFile, 'test.txt', 'text/plain', 0, null, true);
        $attachment = new Attachment();

        $this->uploader->acceptUpload($file, $attachment);

        $this->assertEquals('test.txt', $attachment->getFilename());
        $this->assertEquals(0, $attachment->getFileSize());
        $this->assertEquals('text/plain', $attachment->getMimeType());
        $this->assertRegExp('#^test/([a-f\d]{4})/test(_\d*)?.txt$#', $attachment->getPhysicalName());
    }
}
