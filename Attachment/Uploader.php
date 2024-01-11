<?php

namespace Infinite\FormBundle\Attachment;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class Uploader
{
    private $pathHelper;
    private $sanitiser;

    public function __construct(Sanitiser $sanitiser, PathHelper $pathHelper)
    {
        $this->pathHelper = $pathHelper;
        $this->sanitiser = $sanitiser;
    }

    /**
     * Accepts an uploaded file and fills out the details into an Attachment object.
     *
     * @param UploadedFile        $file
     * @param AttachmentInterface $attachment
     *
     * @return \Symfony\Component\HttpFoundation\File\File
     */
    public function acceptUpload(UploadedFile $file, AttachmentInterface $attachment)
    {
        $hash = sha1_file($file->getPathname());
        $name = $this->sanitiser->applyMaxLength($this->pathHelper->getName($file, $attachment, $hash), 100);
        $filename = $this->sanitiser->applyMaxLength($this->sanitiser->sanitiseFilename($file->getClientOriginalName()), 100);
        $mimeType = $this->sanitiser->sanitiseMimeType($file->getMimeType());

        $attachment->setFilename($filename);
        $attachment->setFileHash($hash);
        $attachment->setFileSize(filesize($file->getPathname()));
        $attachment->setMimeType($mimeType);
        $attachment->setPhysicalName($name);

        $fullPath = sprintf('%s/%s', $this->pathHelper->getSaveDir($attachment), $name);

        return $file->move(dirname($fullPath), basename($fullPath));
    }
}
