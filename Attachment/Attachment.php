<?php

namespace Infinite\FormBundle\Attachment;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
#[ORM\MappedSuperclass]
abstract class Attachment implements AttachmentInterface
{
    /**
     * @ORM\Column(type="string", length=100)
     *
     * @var string
     */
    #[ORM\Column(type: 'string', length: 100)]
    protected $filename;

    /**
     * @ORM\Column(type="string", length=40)
     *
     * @var string
     */
    #[ORM\Column(type: 'string', length: 40)]
    protected $fileHash;

    /**
     * @ORM\Column(type="integer")
     *
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    protected $fileSize;

    /**
     * @ORM\Column(type="string", length=100)
     *
     * @var string
     */
    #[ORM\Column(type: 'string', length: 100)]
    protected $mimeType;

    /**
     * @ORM\Column(type="string", length=100)
     *
     * @var string
     */
    #[ORM\Column(type: 'string', length: 100)]
    protected $physicalName;

    /**
     * @param string $fileHash
     */
    public function setFileHash($fileHash)
    {
        $this->fileHash = $fileHash;
    }

    /**
     * @return string
     */
    public function getFileHash()
    {
        return $this->fileHash;
    }

    /**
     * @param int $fileSize
     */
    public function setFileSize($fileSize)
    {
        $this->fileSize = $fileSize;
    }

    /**
     * @return int
     */
    public function getFileSize()
    {
        return $this->fileSize;
    }

    /**
     * @param string $filename
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @param string $mimeType
     */
    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;
    }

    /**
     * @return string
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * @param string $physicalName
     */
    public function setPhysicalName($physicalName)
    {
        $this->physicalName = $physicalName;
    }

    /**
     * @return string
     */
    public function getPhysicalName()
    {
        return $this->physicalName;
    }

    public function isImage()
    {
        return substr($this->mimeType, 0, 6) === 'image/';
    }
}
