InfiniteFormBundle's PolyCollection Form Type
=============================================

Introduction
------------

The PolyCollection form type allows you to create a collection type
on a property where the relationship is to a polymorphic object structure
like Doctrine2's Single or Multi table inheritance.

For example, if you had an Invoice entity that had a relationship to an
entity that was using Doctrine inheritance `InvoiceLine` and you wanted
to define multiple InvoiceLine types depending on what you wanted to invoice
like `InvoiceProductLine`, `InvoiceShippingLine` and `InvoiceDiscountLine`
you could use this form type to achieve a form collection that would support
all 4 types of `InvoiceLine` inside the same collection.

Requirements and notes
----------------------

* The PolyCollection type requires Symfony 2.2 or greater.
* The object hierarchy must contain a common ancestor for the base form type.
* Each object in the hierarchy must map to a form type.
* If no form type is defined for a specific object, it defaults to the
  abstract form type.
* Form types are not required to inherit from each other (but will be shown
  this way in the examples).
* The PolyCollection does not support array data types.

Installation
------------

[Installation of InfiniteFormBundle](installation.md) is covered in a separate
document. The PolyCollection type is automatically enabled when the bundle is
installed.

Object Structure
----------------

The PolyCollection type will only work with specific types of object hierarchies
and there must be a common ancestor. The ancestor can be abstract or concrete.

According to the example discussed above, the objects could look something like
the following. Note that the examples are abbreviated and do not contain metadata
mapping for Doctrine or getters/setters/constructors when appropriate.

For brevity, we are only including 2 types, but you can use as many as you need.

```php
<?php
// src/Infinite/InvoiceBundle/Entity/Invoice.php

namespace Infinite\InvoiceBundle\Entity;

class Invoice
{
    protected $lines;
    // ...
}
```

```php
<?php
// src/Infinite/InvoiceBundle/Entity/InvoiceLine.php

namespace Infinite\InvoiceBundle\Entity;

class InvoiceLine
{
    protected $quantity;
    protected $unitAmount;
    protected $description;
}
```

```php
<?php
// src/Infinite/InvoiceBundle/Entity/InvoiceProductLine.php

namespace Infinite\InvoiceBundle\Entity;

class InvoiceProductLine extends InvoiceLine
{
    protected $product;

    public function getDescription()
    {
        return $this->product->getDescription();
    }

    public function setDescription()
    {
        // Do nothing. We get the description from the relationship.
    }
}
```

Form Types
----------

Given our object hierarchy contains a common ancestor that has default fields to
display we can define the basic fields in a common FormType.

All FormTypes defined for use with the PolyCollection must contain an additional
unmapped field which by default is called _type that has a default data value of
the form's name. This is used internally when data is posted back to the
PolyCollection so we know what kind of object must be created for new data.

In our examples we assume that each FormType has been registered with the container.

**Note:** The Collection FormTypes must set both a data_class and model_class option for the
PolyCollection to know which type to use when it encounters an object.

```php
<?php
// src/Infinite/InvoiceBundle/Form/Type/InvoiceType.php

namespace Infinite\InvoiceBundle\Form\Type;

use Symfony\Component\Form\AbstractType as BaseType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class InvoiceType extends BaseType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('customer', 'entity', array( /* ... */ ));
        $builder->add('address', 'entity', array( /* ... */ ));

        $builder->add('lines', 'infinite_form_polycollection', array(
            'types' => array(
                'invoice_line_type', // The first defined Type becomes the default
                'invoice_product_line_type',
            ),
            'allow_add' => true,
            'allow_delete' => true,
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('data_class'  => 'Infinite\\InvoiceBundle\\Entity\\Invoice'));
    }

    public function getName()
    {
        return 'invoice_type';
    }
}
```

```php
<?php
// src/Infinite/InvoiceBundle/Form/Type/InvoiceLineType.php

namespace Infinite\InvoiceBundle\Form\Type;

use Symfony\Component\Form\AbstractType as BaseType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class InvoiceLineType extends BaseType
{
    protected $dataClass = 'Infinite\\InvoiceBundle\\Entity\\InvoiceLine';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('quantity', 'number');
        $builder->add('unitAmount', 'text');
        $builder->add('description', 'textarea');

        $builder->add('_type', 'hidden', array(
            'data'   => $this->getName(),
            'mapped' => false
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'  => $this->dataClass,
            'model_class' => $this->dataClass,
        ));
    }

    public function getName()
    {
        return 'invoice_line_type';
    }
}
```

```php
<?php
// src/Infinite/InvoiceBundle/Form/Type/InvoiceProductLineType.php

namespace Infinite\InvoiceBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;

class InvoiceProductType extends InvoiceLineType
{
    protected $dataClass = 'Infinite\\InvoiceBundle\\Entity\\InvoiceProductType';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('product', 'entity', array(
            // entity field definition here
        ));
    }

    public function getName()
    {
        return 'invoice_product_line_type';
    }
}
```

Rendering the form
------------------

Coming Soon. Still a work in progress.
