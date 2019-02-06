<?php

/*
 * (c) Infinite Networks <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Infinite\FormBundle\Tests\Attachment;

use Infinite\FormBundle\Tests\Attachment\Attachments\StandardAttachment;

class AttachmentObjectTest extends \PHPUnit\Framework\TestCase
{
    public function testIsImage()
    {
        $attachment = new StandardAttachment();
        $attachment->setMimeType('text/plain');
        $this->assertEquals(false, $attachment->isImage());

        $attachment->setMimeType('image/png');
        $this->assertEquals(true, $attachment->isImage());
    }
}
