<?php

namespace Infinite\FormBundle\Attachment;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class PathHelper
{
    /**
     * @var Sanitiser
     */
    private $sanitiser;

    /**
     * Configuration array for different attachment types.
     *
     * @var array
     */
    protected $config;

    public function __construct(Sanitiser $sanitiser, array $config)
    {
        $this->sanitiser = $sanitiser;
        $this->config = $config;
    }

    /**
     * Returns the full path to the attachment.
     *
     * @param AttachmentInterface $attachment
     *
     * @return string
     */
    public function getFullPath(AttachmentInterface $attachment)
    {
        return sprintf('%s/%s', $this->getSaveDir($attachment), $attachment->getPhysicalName());
    }

    protected function getConfig(AttachmentInterface $attachment)
    {
        if (isset($this->config[get_class($attachment)])) {
            return $this->config[get_class($attachment)];
        } else {
            foreach (class_parents($attachment) as $class) {
                if (isset($this->config[$class])) {
                    return $this->config[$class];
                }
            }
        }

        throw new \Exception('Class is not configured as an attachment: '.get_class($attachment));
    }

    /**
     * Returns the root saving location for a specific attachment type.
     *
     * @param AttachmentInterface $attachment
     *
     * @return string
     */
    public function getSaveDir(AttachmentInterface $attachment)
    {
        $cfg = $this->getConfig($attachment);

        return $cfg['dir'];
    }

    /**
     * @param AttachmentInterface $attachment
     *
     * @return string
     */
    public function getFormat(AttachmentInterface $attachment)
    {
        $cfg = $this->getConfig($attachment);

        return $cfg['format'];
    }

    /**
     * Gets the full path for a file.
     *
     * @param UploadedFile        $file
     * @param AttachmentInterface $attachment
     * @param string              $hash
     *
     * @return string
     */
    public function getName(UploadedFile $file, AttachmentInterface $attachment, $hash)
    {
        $format = $this->getFormat($attachment);
        $name = $this->buildName($file, $hash, $format, $attachment);

        return $name;
    }

    /**
     * Builds a filename to be used.
     *
     * @param UploadedFile        $file
     * @param string              $hash
     * @param string              $format
     * @param AttachmentInterface $attachment
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    protected function buildName(UploadedFile $file, $hash, $format, AttachmentInterface $attachment)
    {
        // Whether to rename files in case two files have the same name.
        // (Set to false if the format string contains the full hash. In that case it's OK to have the same file.)
        $renameDupes = true;
        $sanitiser = $this->sanitiser;

        $name = preg_replace_callback(
            '/\{(\w+)(\((\d+)\.\.(\d+)\))?\}/',
            function ($match) use (&$renameDupes, $file, $format, $hash, $sanitiser) {
                /** @var UploadedFile $file */
                $partName = $match[1];
                $substrStart = count($match) >= 3 ? $match[3] : null;
                $substrEnd = count($match) >= 4 ? $match[4] : null;

                switch ($partName) {
                    case 'hash':
                        $value = $hash;

                        if ($substrStart === null) {
                            $renameDupes = false;
                        }

                        break;
                    case 'name':
                        $value = $sanitiser->sanitiseFilename($file->getClientOriginalName());
                        break;
                    case 'ext':
                        $value = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
                        break;
                    default:
                        throw new \RuntimeException(sprintf('Unknown name part: %s', $partName));
                }

                if ($substrStart !== null) {
                    $value = substr($value, $substrStart, $substrEnd);
                }

                return $value;
            },
            $format
        );

        if ($renameDupes) {
            $name = $this->ensureUnique($attachment, $name);
        }

        return $name;
    }

    /**
     * @param AttachmentInterface $attachment
     * @param $name
     *
     * @return string
     */
    protected function ensureUnique(AttachmentInterface $attachment, $name)
    {
        $saveDir = $this->getSaveDir($attachment);

        if (file_exists(sprintf('%s/%s', $saveDir, $name))) {
            $pathInfo = pathinfo($name);
            $i = 0;
            $dotExt = $pathInfo['extension'] !== '' ?
                '.'.$pathInfo['extension'] :
                '';

            do {
                ++$i;
                $name = sprintf(
                    '%s/%s_%d%s',
                    $pathInfo['dirname'],
                    $pathInfo['filename'],
                    $i,
                    $dotExt
                );
            } while (file_exists(sprintf('%s/%s', $saveDir, $name)));
        }

        return $name;
    }
}
