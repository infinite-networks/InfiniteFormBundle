<?php

namespace Infinite\FormBundle\Attachment;

class Sanitiser
{
    /**
     * Sanitises a filename.
     *
     * @param string $filename
     * @return string
     */
    public function sanitiseFilename($filename)
    {
        // If there are slashes or backslashes, only keep the base name after them
        $parts = explode('\\', basename($filename));
        $filename = end($parts);

        // Replace illegal file name characters with hyphens
        // Illegal chars are 00 to 1F and < > : " / \ | ? *
        $filename =  preg_replace('/[\x00-\x1F\<\>\:\"\/\\\\|\?\*]/', '-', $filename);

        if ($filename == '' || $filename[0] == '.') {
            $filename = '_' . $filename;
        }

        return $filename;
    }

    /**
     * Sanitises a mime type.
     *
     * @param string $mime
     * @return string
     */
    public function sanitiseMimeType($mime)
    {
        // Mime types are foo/bar, where foo and bar can contain anything except 00 to 20 and ()<>@,::\"/[]?=
        if (!preg_match('/^[!#$%&\'*+\-.0-9A-Z\^_`a-z\{|\}~]+\/[!#$%&\'*+\-.0-9A-Z\^_`a-z\{|\}~]+$/', $mime)) {
            return 'application/octet-stream';
        }

        return $mime;
    }
}
