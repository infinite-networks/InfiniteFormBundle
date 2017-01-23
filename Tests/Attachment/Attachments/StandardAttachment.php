<?php

/*
 * (c) Infinite Networks <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Infinite\FormBundle\Tests\Attachment\Attachments;

use Doctrine\ORM\Mapping as ORM;
use Infinite\FormBundle\Attachment\Attachment as BaseAttachment;

/**
 * @ORM\Entity
 */
class StandardAttachment extends BaseAttachment
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     * @ORM\Id
     *
     * @var int
     */
    protected $id;

    public function getId()
    {
        return $this->id;
    }

    public function getAdditionalFormData()
    {
        return array();
    }

    public function setAdditionalFormData(array $data)
    {
    }
}
