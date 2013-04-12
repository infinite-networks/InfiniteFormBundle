<?php

namespace Infinite\FormBundle\Form\Type;

use Doctrine\Common\Persistence\ObjectManager;
use Infinite\FormBundle\Form\DataTransformer\AttachmentTransformer;
use Infinite\FormBundle\Attachment\PathHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class AttachmentType extends AbstractType
{
    protected $defaultSecret;
    protected $om;
    protected $pathHelper;

    public function __construct($secret, ObjectManager $om, PathHelper $pathHelper)
    {
        $this->defaultSecret = $secret;
        $this->om = $om;
        $this->pathHelper = $pathHelper;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('file', 'file', array('required' => $options['required']))
            ->add('removed', 'hidden')
            ->add('meta', 'hidden', array('required' => false))
            ->addViewTransformer(new AttachmentTransformer($options, $this->om, $this->pathHelper))
        ;
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['attachment'] = $form->getData();
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        // Sometimes the metadata child isn't getting its value set properly ... bug?
        $view->children['meta']->vars['value'] = $view->vars['value']['meta'];
    }

    public function getName()
    {
        return 'infinite_form_attachment';
    }

    public function getParent()
    {
        return 'field';
    }

    public function getDefaultOptions(array $options)
    {
        return array(
            'class' => 'Infinite\FormBundle\Entity\Attachment', // Abstract - *must* be overridden
            'secret' => $this->defaultSecret,
        );
    }
}
