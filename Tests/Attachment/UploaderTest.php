<?php

/*
 * (c) Infinite Networks <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Infinite\FormBundle\Tests\Attachment;

use Infinite\FormBundle\Attachment\AttachmentInterface;
use Infinite\FormBundle\Attachment\PathHelper;
use Infinite\FormBundle\Attachment\Sanitiser;
use Infinite\FormBundle\Attachment\Uploader;
use Infinite\FormBundle\Tests\Attachment\Attachments\FullHashAttachment;
use Infinite\FormBundle\Tests\Attachment\Attachments\InvalidFormatAttachment;
use Infinite\FormBundle\Tests\Attachment\Attachments\StandardAttachment;
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
            'Infinite\\FormBundle\\Tests\\Attachment\\Attachments\\StandardAttachment' => array(
                'dir'    => sys_get_temp_dir(),
                'format' => 'test/{hash(0..4)}/{name}',
            ),
            'Infinite\\FormBundle\\Tests\\Attachment\\Attachments\\FullHashAttachment' => array(
                'dir'    => sys_get_temp_dir(),
                'format' => 'test/{hash}.{ext}',
            ),
            'Infinite\\FormBundle\\Tests\\Attachment\\Attachments\\InvalidFormatAttachment' => array(
                'dir'    => sys_get_temp_dir(),
                'format' => 'test/{invalid}',
            ),
        ));
        $this->uploader = new Uploader($sanitiser, $this->pathHelper);
    }

    public function testAcceptUpload()
    {
        $attachment1 = $this->uploadFooAttachment(new StandardAttachment);

        $this->assertEquals('test.txt', $attachment1->getFilename());
        $this->assertEquals(3, $attachment1->getFileSize());
        $this->assertEquals('text/plain', $attachment1->getMimeType());
        $this->assertEquals('0beec7b5ea3f0fdbc95d0dd47f3c5bc275da8a33', $attachment1->getFileHash());
        $this->assertRegExp('#^test/0bee/test(_\d*)?.txt$#', $attachment1->getPhysicalName());
    }

    public function testAcceptUploadWithRename()
    {
        // Multiple uploads with the same file name should usually be renamed.
        $attachment1 = $this->uploadFooAttachment(new StandardAttachment);
        $attachment2 = $this->uploadFooAttachment(new StandardAttachment);

        $this->assertNotEquals($attachment1->getPhysicalName(), $attachment2->getPhysicalName());
    }

    public function testAcceptUploadWithDeduplication()
    {
        // If the format string contains the full hash of the file contents,
        // then multiple identical uploads can safely overwrite each other.
        $attachment1 = $this->uploadFooAttachment(new FullHashAttachment);
        $attachment2 = $this->uploadFooAttachment(new FullHashAttachment);

        $this->assertEquals('test/0beec7b5ea3f0fdbc95d0dd47f3c5bc275da8a33.txt', $attachment1->getPhysicalName());
        $this->assertEquals('test/0beec7b5ea3f0fdbc95d0dd47f3c5bc275da8a33.txt', $attachment2->getPhysicalName());
    }

    public function testInvalidFormatString()
    {
        $this->setExpectedException('RuntimeException', 'Unknown name part: invalid');
        $this->uploadFooAttachment(new InvalidFormatAttachment);
    }

    private function uploadFooAttachment(AttachmentInterface $attachment)
    {
        $tempFilename = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($tempFilename, 'foo');
        $file = new UploadedFile($tempFilename, 'test.txt', 'text/plain', 3, null, true);
        $this->uploader->acceptUpload($file, $attachment);

        return $attachment;
    }
}
