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
use Infinite\FormBundle\Attachment\Streamer;
use Infinite\FormBundle\Attachment\Uploader;
use Symfony\Component\Form\Forms;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class StreamerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Streamer
     */
    public $streamer;

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

        $this->streamer = new Streamer($sanitiser, $this->pathHelper);
    }

    public function testStreamerNonExistantFile()
    {
        $attachment = new Attachment();
        $attachment->setPhysicalName('/non/existant/path.txt');

        $response = $this->streamer->stream($attachment);
        $this->assertInstanceOf('Symfony\\Component\\HttpKernel\\Exception\\NotFoundHttpException', $response);
    }

    public function testStreamer()
    {
        $attachment = new Attachment();
        $attachment->setPhysicalName('infinite-streamer-test.txt');
        $attachment->setFilename('test.txt');
        $attachment->setMimeType('text/plain');

        file_put_contents($this->pathHelper->getFullPath($attachment), 'hello');

        $response = $this->streamer->stream($attachment);
        $this->assertInstanceOf('Symfony\\Component\\HttpFoundation\\StreamedResponse', $response);
    }
}
