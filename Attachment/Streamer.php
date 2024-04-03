<?php

namespace Infinite\FormBundle\Attachment;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Streamer
{
    /**
     * An array of mimetypes that will display inline.
     *
     * @var array
     */
    public static $inlineMimes = array(
        'application/pdf',
        'image/png',
        'image/jpeg',
        'image/pjpeg',
        'image/gif',
        'image/bmp',
        'text/plain',
    );

    /**
     * @var PathHelper
     */
    private $pathHelper;

    /**
     * @var Sanitiser
     */
    private $sanitiser;

    public function __construct(Sanitiser $sanitiser, PathHelper $pathHelper)
    {
        $this->pathHelper = $pathHelper;
        $this->sanitiser = $sanitiser;
    }

    public function stream(AttachmentInterface $attachment, Request $request = null, $disposition = null)
    {
        $fullPhysicalPath = $this->pathHelper->getFullPath($attachment);
        $mimeType = $this->sanitiser->sanitiseMimeType($attachment->getMimeType());

        if (!file_exists($fullPhysicalPath)) {
            return new NotFoundHttpException(sprintf(
                'Attachment %s not found.',
                $attachment->getFilename()
            ));
        }

        if (null !== $request and null === $disposition) {
            // If this is a safe MIME type, display inline ...
            $disposition = in_array($mimeType, static::$inlineMimes) ? 'inline' : 'attachment';

            // Unless it's Internet Explorer prior to version 8, which can try
            // to guess the MIME type (dangerous!). A user-agent check is the
            // only real option here.
            $userAgent = $request->headers->get('User-Agent', '');
            if (preg_match('/MSIE [1-7]\./', $userAgent) && !preg_match('/\) Opera/', $userAgent)) {
                $disposition = 'attachment';
            }
        } else {
            $disposition = 'attachment';
        }

        $headers = array(
            'Cache-Control' => 'private, max-age=31536000',
            'Content-Disposition' => sprintf(
                '%s; filename="%s"',
                $disposition,
                $this->sanitiser->sanitiseFilename($attachment->getFilename())
            ),
            'Content-Length' => $attachment->getFileSize(),
            'Content-Type' => $mimeType,
        );

        return new StreamedResponse(function () use ($fullPhysicalPath) {
            fpassthru(fopen($fullPhysicalPath, 'rb'));
        }, 200, $headers);
    }
}
