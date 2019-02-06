InfiniteFormBundle's Attachment Form Type
=========================================

Synopsis
--------

A form type that moves file uploads and remembers them even if there's a
validation error elsewhere in the form.

Suppose you have a Company entity with a single, optional CompanyLogo.
Your CompanyLogo entity must implement AttachmentInterface
(or just extend Attachment):

```php
    class CompanyLogo extends \Infinite\FormBundle\Attachment\Attachment
    {
        // etc
    }
```

And you must define a save path and filename format in your YAML configuration:

```
    infinite_form:
        attachments:
            'App\Entity\CompanyLogo':
                dir:    '%kernel.project_dir%/var/uploads'
                format: 'logos/{hash(0..2)}/{name}'
```

Then you can add the logo to your form as follows:

```php
    $builder->add('logo', AttachmentType::class, array(
        'allowed_mime_types' => ['image/jpeg', 'image/png'],
        'data_class' => CompanyLogo::class,
        'required' => false,
    ));
```

And that's it. Your form will appear with a standard file field. If you edit
a company with an existing logo, the form will instead display the existing
file name and a Remove button.
