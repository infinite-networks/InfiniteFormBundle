<?php

namespace Infinite\FormBundle\Attachment;

/**
 * An interface to be implemented by a class that is to
 * act as an attachment for the FormBundle attachment type.
 *
 * @author Jon Mclean <j.mclean@infinite.net.au>
 */
interface AttachmentInterface
{
    /**
     * The filename to be displayed to the user.
     *
     * @return string
     */
    public function getFilename();

    public function setFilename($filename);

    /**
     * SHA1 hash of the file's contents (for use as an Etag).
     *
     * @return string
     */
    public function getFileHash();

    public function setFileHash($fileHash);

    /**
     * File size in bytes.
     *
     * @return int
     */
    public function getFileSize();

    public function setFileSize($fileSize);

    /**
     * The MIME type of the file.
     *
     * @return string
     */
    public function getMimeType();

    public function setMimeType($mimeType);

    /**
     * The name of the saved file, relative to the upload path.
     *
     * @return string
     */
    public function getPhysicalName();

    public function setPhysicalName($physicalName);
}
