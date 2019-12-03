<?php

/*
 * (c) Infinite Networks <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Infinite\FormBundle\Form\Type;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Infinite\FormBundle\Attachment\AttachmentInterface;
use Infinite\FormBundle\Attachment\Uploader;
use Infinite\FormBundle\Form\DataTransformer\AttachmentTransformer;
use Infinite\FormBundle\Attachment\PathHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AttachmentType extends AbstractType
{
    protected $defaultSecret;
    protected $doctrine;
    protected $pathHelper;

    public function __construct($secret, ManagerRegistry $doctrine, PathHelper $pathHelper, Uploader $uploader)
    {
        $this->defaultSecret = $secret;
        $this->doctrine = $doctrine;
        $this->pathHelper = $pathHelper;
        $this->uploader = $uploader;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('file', FileType::class,   ['required' => $options['required'], 'mapped' => false])
            ->add('meta', HiddenType::class, ['required' => false, 'mapped' => false])
        ;

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) use ($options) {
            // Requirements:
            // The AttachmentType is intended for files that are attached to a main item.
            // If there is no file, it should return null.

            /** @var AttachmentInterface|null $data */
            $data = $event->getData();

            /** @var UploadedFile $file */
            $file = $event->getForm()->get('file')->getData();
            $meta = $event->getForm()->get('meta')->getData();

            $dataClass = $options['data_class'];

            if ($file !== null) {
                if (null !== $options['allowed_mime_types'] && !in_array($file->getMimeType(), $options['allowed_mime_types'])) {
                    $event->getForm()->get('file')->addError(new FormError($options['invalid_mime_message']));
                } elseif ($file->isValid()) {
                    // File posted - accept the new file
                    $data = $data ?: new $dataClass();
                    $this->uploader->acceptUpload($file, $data);
                } else {
                    $event->getForm()->get('file')->addError(new FormError($options['file_upload_failed_message']));
                }
            } elseif ($meta !== null) {
                // Preserve existing attachment data
                list($mac, $savedData) = explode('|', $meta, 2);

                if (hash_hmac('sha1', $savedData, $options['secret']) === $mac) {
                    $postedData = json_decode(base64_decode($savedData));
                    $data = $data ?: new $dataClass;

                    $data->setFilename($postedData[0]);
                    $data->setFileHash($postedData[1]);
                    $data->setFileSize($postedData[2]);
                    $data->setMimeType($postedData[3]);
                    $data->setPhysicalName($postedData[4]);
                }
            } else {
                $data = null;
            }

            $event->setData($data);
        });
    }

    public function finishView(
        FormView $view,
        FormInterface $form,
        array $options
    ) {
        $attachment = $view->vars['value'];

        if ($attachment && $attachment->getPhysicalName()) {
            $savedData = base64_encode(json_encode([
                $attachment->getFilename(),
                $attachment->getFileHash(),
                $attachment->getFileSize(),
                $attachment->getMimeType(),
                $attachment->getPhysicalName()
            ]));

            $mac = hash_hmac('sha1', $savedData, $options['secret']);

            $view['meta']->vars['value'] = $mac.'|'.$savedData;
        } else {
            $view['meta']->vars['value'] = '';
        }
    }

    public function getBlockPrefix()
    {
        return 'infinite_form_attachment';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(array('data_class'));
        $resolver->setDefaults(array(
            'allowed_mime_types' => null,
            'invalid_mime_message' => 'That file type is not allowed',
            'file_upload_failed_message' => 'File too large',
            'secret' => $this->defaultSecret,
        ));
    }
}
