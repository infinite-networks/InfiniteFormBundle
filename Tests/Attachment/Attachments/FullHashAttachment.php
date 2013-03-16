<?php

/*
 * (c) Infinite Networks <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Infinite\FormBundle\Tests\Attachment\Attachments;

use Infinite\FormBundle\Attachment\Attachment as BaseAttachment;

class FullHashAttachment extends BaseAttachment
{
    public function getId()
    {
        return 1;
    }

    public function getAdditionalFormData()
    {
        return array();
    }

    public function setAdditionalFormData(array $data)
    {
    }
}
