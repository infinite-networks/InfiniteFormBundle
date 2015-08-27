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
use Infinite\FormBundle\Tests\CheckboxGrid\Entity as TestEntity;
use Symfony\Bridge\Doctrine\Test\DoctrineTestHelper;
use Symfony\Component\Form\Forms;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class AttachmentFieldTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Doctrine\ORM\EntityManager */
    private $em;

    /** @var \Symfony\Component\Form\FormFactoryInterface */
    private $factory;

    /** @var Uploader */
    private $uploader;

    protected function setUp()
    {
        // Create a test database and table
        $this->em = DoctrineTestHelper::createTestEntityManager();

        $schemaTool = new SchemaTool($this->em);
        $classes = array(
            $this->em->getClassMetadata('Infinite\\FormBundle\\Tests\\Attachment\\Attachments\\StandardAttachment'),
        );

        $schemaTool->createSchema($classes);

        // Prepare to create forms
        $sanitiser = new Sanitiser;
        $pathHelper = new PathHelper($sanitiser, array(
            'Infinite\\FormBundle\\Tests\\Attachment\\Attachments\\StandardAttachment' => array(
                'dir'    => sys_get_temp_dir(),
                'format' => 'test/{name}',
            ),
        ));
        $this->uploader = new Uploader($sanitiser, $pathHelper);

        $this->factory = Forms::createFormFactoryBuilder()
            ->addType(new AttachmentType('foobar', $this->em, $pathHelper, $this->uploader))
            ->getFormFactory();
    }

    public function testPreserveUnsavedAttachment()
    {
        $form1 = $this->makeAttachmentForm();

        $form1->bind(array(
            'file' => $this->createFooUpload(),
            'removed' => false,
            'meta' => null,
        ));

        $attachment = $form1->getData();
        $this->assertEquals('Infinite\\FormBundle\\Tests\\Attachment\\Attachments\\StandardAttachment', get_class($attachment));
        $this->assertNull($attachment->getId());
        $view1 = $form1->createView();

        // Verify that the unsaved attachment can be successfully rebuilt from the form's view data
        $form2 = $this->makeAttachmentForm();
        $form2->bind(array(
            'file' => null,
            'removed' => false,
            'meta' => $view1->children['meta']->vars['value'],
        ));

        $attachment2 = $form2->getData();
        $this->assertEquals(3, $attachment2->getFileSize());
    }

    public function testRemoveAttachment()
    {
        $att = new StandardAttachment;

        $form = $this->makeAttachmentForm();
        $form->setData($att);
        $form->bind(array(
            'file' => null,
            'removed' => true,
            'meta' => '',
        ));

        $this->assertNull($form->getData());
    }

    public function testKeepAttachment()
    {
        $attachment1 = new StandardAttachment;
        $this->uploader->acceptUpload($this->createFooUpload(), $attachment1);

        $this->em->persist($attachment1);
        $this->em->flush();
        $this->assertEquals(1, $attachment1->getId());

        $form1 = $this->makeAttachmentForm();
        $form1->setData($attachment1);
        $view1 = $form1->createView();

        $form2 = $this->makeAttachmentForm();
        $form2->bind(array(
            'file' => null,
            'removed' => false,
            'meta' => $view1->children['meta']->vars['value'],
        ));

        $attachment2 = $form2->getData();

        $this->assertEquals(1, $attachment2->getId());
        $this->assertEquals(3, $attachment2->getFilesize());
    }

    private function makeAttachmentForm()
    {
        return $this->factory->create('infinite_form_attachment', null, array(
            'class' => 'Infinite\\FormBundle\\Tests\\Attachment\\Attachments\\StandardAttachment',
        ));
    }

    private function createFooUpload()
    {
        $tempFilename = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($tempFilename, 'foo');
        return new UploadedFile($tempFilename, 'test.txt', 'text/plain', 3, null, true);
    }
}
