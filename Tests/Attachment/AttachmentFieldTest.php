<?php

/*
 * (c) Infinite Networks <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Infinite\FormBundle\Tests\Attachment;

use Doctrine\ORM\Tools\SchemaTool;
use Infinite\FormBundle\Attachment\PathHelper;
use Infinite\FormBundle\Attachment\Sanitiser;
use Infinite\FormBundle\Attachment\Uploader;
use Infinite\FormBundle\Form\Type\AttachmentType;
use Infinite\FormBundle\Tests\Attachment\Attachments\StandardAttachment;
use Infinite\FormBundle\Tests\BundleTest;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\Form\Extension\HttpFoundation\Type\FormTypeHttpFoundationExtension;
use Symfony\Component\Form\Forms;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class AttachmentFieldTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Doctrine\ORM\EntityManager */
    private $em;

    /** @var \Symfony\Component\Form\FormFactoryInterface */
    private $factory;

    /** @var Uploader */
    private $uploader;

    protected function setUp(): void
    {
        // Create a test database and table
        $this->em = BundleTest::createTestEntityManager();

        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects($this->any())
            ->method('getManager')
            ->will($this->returnValue($this->em));

        $schemaTool = new SchemaTool($this->em);
        $classes = array(
            $this->em->getClassMetadata(StandardAttachment::class),
        );

        $schemaTool->createSchema($classes);

        // Prepare to create forms
        $sanitiser = new Sanitiser();
        $pathHelper = new PathHelper($sanitiser, array(
            StandardAttachment::class => array(
                'dir' => sys_get_temp_dir(),
                'format' => 'test/{name}',
            ),
        ));
        $this->uploader = new Uploader($sanitiser, $pathHelper);

        $this->factory = Forms::createFormFactoryBuilder()
            ->addType(new AttachmentType('foobar', $doctrine, $pathHelper, $this->uploader))
            ->addTypeExtension(new FormTypeHttpFoundationExtension())
            ->getFormFactory();
    }

    public function testPreserveUnsavedAttachment()
    {
        $form1 = $this->makeAttachmentForm();

        $form1->submit(array(
            'file' => $this->createFooUpload(),
            'meta' => null,
        ));

        $attachment = $form1->getData();
        $this->assertEquals(StandardAttachment::class, get_class($attachment));
        $this->assertNull($attachment->getId());
        $view1 = $form1->createView();

        // Verify that the unsaved attachment can be successfully rebuilt from the form's view data
        $form2 = $this->makeAttachmentForm();
        $form2->submit(array(
            'file' => null,
            'meta' => $view1->children['meta']->vars['value'],
        ));

        $attachment2 = $form2->getData();
        $this->assertEquals(3, $attachment2->getFileSize());
    }

    public function testRemoveAttachment()
    {
        $att = new StandardAttachment();

        $form = $this->makeAttachmentForm();
        $form->setData($att);
        $form->submit(array(
            'file' => null,
            'meta' => '',
        ));

        $this->assertNull($form->getData());
    }

    public function testKeepAttachment()
    {
        $attachment1 = new StandardAttachment();
        $this->uploader->acceptUpload($this->createFooUpload(), $attachment1);

        $this->em->persist($attachment1);
        $this->em->flush();
        $this->assertEquals(1, $attachment1->getId());

        $form1 = $this->makeAttachmentForm();
        $form1->setData($attachment1);
        $view1 = $form1->createView();

        $form2 = $this->makeAttachmentForm();
        $form2->submit(array(
            'file' => null,
            'meta' => $view1->children['meta']->vars['value'],
        ));

        $attachment2 = $form2->getData();

        $this->assertEquals('test.txt', $attachment2->getFilename());
        $this->assertEquals('0beec7b5ea3f0fdbc95d0dd47f3c5bc275da8a33', $attachment2->getFileHash());
        $this->assertEquals(3, $attachment2->getFilesize());
        $this->assertEquals('text/plain', $attachment2->getMimeType());
    }

    private function makeAttachmentForm()
    {
        return $this->factory->create(AttachmentType::class, null, [
            'data_class' => StandardAttachment::class,
        ]);
    }

    private function createFooUpload()
    {
        $tempFilename = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($tempFilename, 'foo');

        $uploadedFileClass = new \ReflectionClass(UploadedFile::class);

        if ($uploadedFileClass->getConstructor()->getParameters()[3]->getName() === 'error') {
            return new UploadedFile($tempFilename, 'test.txt', 'text/plain', null, true);
        } else {
            return new UploadedFile($tempFilename, 'test.txt', 'text/plain', 3, null, true);
        }
    }
}
