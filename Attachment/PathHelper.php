<?php

namespace Infinite\FormBundle\Attachment;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PathHelper
{
    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function getFullPath(AttachmentInterface $attachment)
    {
        return $this->getSaveDir($attachment) . '/' . $attachment->getPhysicalName();
    }

    public function getSaveDir(AttachmentInterface $attachment)
    {
        return $this->config[get_class($attachment)]['dir'];
    }

    public function getFormat(AttachmentInterface $attachment)
    {
        return $this->config[get_class($attachment)]['format'];
    }

    public function acceptUpload(UploadedFile $file, AttachmentInterface $attachment)
    {
        $hash = sha1_file($file->getPathname());
        $name = $this->getName($file, $attachment, $hash);

        $attachment->setFilename($this->sanitiseFilename($file->getClientOriginalName()));
        $attachment->setFileHash($hash);
        $attachment->setFileSize(filesize($file->getPathname()));
        $attachment->setMimeType($this->sanitiseMimeType($file->getClientMimeType()));
        $attachment->setPhysicalName($name);

        $fullPath = $this->getSaveDir($attachment) . '/' . $name;
        $file->move(dirname($fullPath), basename($fullPath));
    }

    public function getName(UploadedFile $file, AttachmentInterface $attachment, $hash)
    {
        // Whether to rename files in case two files have the same name.
        // (Set to false if the format string contains the full hash. In that case it's OK to have the same file.)
        $renameDupes = true;

        $saveDir = $this->getSaveDir($attachment);
        $format  = $this->getFormat($attachment);

        $name = preg_replace_callback('/\{(\w+)(\((\d+)\.\.(\d+)\))?\}/', function ($match) use (&$renameDupes, $file, $format, $hash) {
            /** @var UploadedFile $file */
            $partName = $match[1];
            $substrStart = count($match) >= 3 ? $match[3] : null;
            $substrEnd   = count($match) >= 4 ? $match[4] : null;

            switch ($partName) {
                case 'hash':
                    $value = $hash;

                    if ($substrStart === null) {
                        $renameDupes = false;
                    }

                    break;
                case 'name':
                    $value = $this->sanitiseFilename($file->getClientOriginalName());
                    break;
                case 'ext':
                    $value = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
                    break;
                default:
                    throw new \Exception(sprintf('Unknown name part: %s', $partName));
            }

            if ($substrStart !== null) {
                $value = substr($value, $substrStart, $substrEnd);
            }

            return $value;
        }, $format);

        if ($renameDupes && file_exists($saveDir . '/' . $name)) {
            // Generate a new name
            $pi = pathinfo($name);
            $i = 0;
            $dotExt = $pi['extension'] == '' ? '' : '.' . $pi['extension'];

            do {
                $i++;
                $name = $pi['dirname'] . '/' . $pi['filename'] . '_' . $i . $dotExt;
            } while (file_exists($saveDir . '/' . $name));
        }

        return $name;
    }

    public function stream(AttachmentInterface $attachment, Request $request, $disposition = null)
    {
        $fullPhysicalPath = $this->getFullPath($attachment);
        $mime = $this->sanitiseMimeType($attachment->getMimeType());

        if (!file_exists($fullPhysicalPath)) {
            return new NotFoundHttpException();
        }

        if ($disposition !== 'attachment' && $disposition !== 'inline') {
            // If this is a safe MIME type, display inline ...
            $disposition = strpos('text/plain image/png image/jpeg image/pjpeg image/gif image/bmp application/pdf', $mime) !== false ? 'inline' : 'attachment';

            // Unless it's Internet Explorer prior to version 8, which can try to guess the MIME type (dangerous!)
            // A user-agent check is the only real option here.
            $userAgent = $request->headers->get('User-Agent');

            if (preg_match('/MSIE [1-7]\./', $userAgent) && !preg_match('/\) Opera/', $userAgent)) {
                $disposition = 'attachment';
            }
        }

        $headers = array(
            'Cache-Control' => 'private, max-age=31536000',
            'Content-Disposition' => sprintf('%s; filename="%s"', $disposition, $this->sanitiseFilename($attachment->getFilename())),
            'Content-Length' => $attachment->getFileSize(),
            'Content-Type' => $mime,
        );

        return new StreamedResponse(function () use ($fullPhysicalPath, $headers) {
            header('Cache-Control: ' . $headers['Cache-Control']); // Work around StreamedResponse overwriting this header
            fpassthru(fopen($fullPhysicalPath, 'rb'));
        }, 200, $headers);
    }

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

    public function sanitiseMimeType($mime)
    {
        // Mime types are foo/bar, where foo and bar can contain anything except 00 to 20 and ()<>@,::\"/[]?=
        if (!preg_match('/^[!#$%&\'*+\-.0-9A-Z\^_`a-z\{|\}~]+\/[!#$%&\'*+\-.0-9A-Z\^_`a-z\{|\}~]+$/', $mime)) {
            return "application/octet-stream";
        }

        return $mime;
    }
}
