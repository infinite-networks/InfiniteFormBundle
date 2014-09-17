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
use Infinite\FormBundle\Tests\Attachment\Attachments\StandardAttachment;
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
            'Infinite\\FormBundle\\Tests\\Attachment\\Attachments\\StandardAttachment' => array(
                'dir'    => sys_get_temp_dir(),
                'format' => 'test/{hash(0..4)}/{name}',
            )
        ));

        $this->streamer = new Streamer($sanitiser, $this->pathHelper);
    }

    public function testStreamerNonExistantFile()
    {
        $attachment = new StandardAttachment;
        $attachment->setPhysicalName('/non/existant/path.txt');

        $response = $this->streamer->stream($attachment);
        $this->assertInstanceOf('Symfony\\Component\\HttpKernel\\Exception\\NotFoundHttpException', $response);
    }

    public function testStreamer()
    {
        $attachment = $this->makeFooAttachment();

        $response = $this->streamer->stream($attachment);
        $this->assertInstanceOf('Symfony\\Component\\HttpFoundation\\StreamedResponse', $response);

        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        $this->assertEquals('hello', $content);
    }

    public function testDisposition()
    {
        // The Streamer should choose a sensible default disposition for different attachment types
        $normalRequest = new Request;
        $oldIERequest = new Request;
        $oldIERequest->headers->set('User-Agent', 'Mozilla/4.0 (compatible; MSIE 7.0b; Windows NT 6.0)');

        // text/plain is safe for inline display
        $attachment = $this->makeFooAttachment();
        $stream = $this->streamer->stream($attachment, $normalRequest);
        $stream->prepare($normalRequest);
        $this->assertRegExp('/^inline/',  $stream->headers->get('Content-Disposition'));

        // text/html is unsafe for inline display
        $attachment->setMimeType('text/html');
        $this->assertRegExp('/^attachment/', $this->streamer->stream($attachment, $normalRequest)->headers->get('Content-Disposition'));

        // Old IE versions try to sniff the MIME type, which is unsafe for inline display
        $attachment->setMimeType('text/plain');
        $this->assertRegExp('/^attachment/', $this->streamer->stream($attachment, $oldIERequest)->headers->get('Content-Disposition'));
    }

    private function makeFooAttachment($mime = 'text/plain')
    {
        $attachment = new StandardAttachment;
        $attachment->setPhysicalName('infinite-streamer-test.txt');
        $attachment->setFilename('test.txt');
        $attachment->setMimeType($mime);

        file_put_contents($this->pathHelper->getFullPath($attachment), 'hello');

        return $attachment;
    }
}
