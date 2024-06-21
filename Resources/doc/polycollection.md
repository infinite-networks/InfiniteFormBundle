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

use Infinite\FormBundle\Form\Type\PolyCollectionType;
use Infinite\InvoiceBundle\Entity\Invoice;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InvoiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('customer', EntityType::class, array( /* ... */ ));
        $builder->add('address', EntityType::class, array( /* ... */ ));

        $builder->add('lines', PolyCollectionType::class, array(
            'types' => array(
                InvoiceLineType::class,
                InvoiceProductLineType::class,
            ),
            'types_options' => array(
                InvoiceLineType::class => array(
                    // Here you can optionally define options for the InvoiceLineType
                ),
                InvoiceProductLineType::class => array(
                    // Here you can optionally define options for the InvoiceProductLineType
                )
            ),
            'allow_add' => true,
            'allow_delete' => true,
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array('data_class' => Invoice::class));
    }

    public function getBlockPrefix()
    {
        return 'invoice';
    }
}
```

```php
<?php
// src/Infinite/InvoiceBundle/Form/Type/InvoiceLineType.php

namespace Infinite\InvoiceBundle\Form\Type;

use Infinite\InvoiceBundle\Entity\InvoiceLine;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InvoiceLineType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('quantity', NumberType::class);
        $builder->add('unitAmount', TextType::class);
        $builder->add('description', TextareaType::class);

        $builder->add('_type', HiddenType::class, array(
            'data'   => 'line', // Arbitrary, but must be distinct
            'mapped' => false
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'  => InvoiceLine::class,
            'model_class' => InvoiceLine::class,
        ));
    }

    public function getBlockPrefix()
    {
        return 'invoice_line';
    }
}
```

```php
<?php
// src/Infinite/InvoiceBundle/Form/Type/InvoiceProductLineType.php

namespace Infinite\InvoiceBundle\Form\Type;

use Infinite\InvoiceBundle\Entity\InvoiceProductLine;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InvoiceProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('quantity', NumberType::class);

        $builder->add('product', EntityType::class, array(
            // entity field definition here
        ));

        $builder->add('_type', HiddenType::class, array(
            'data'   => 'product', // Arbitrary, but must be distinct
            'mapped' => false
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'  => InvoiceProductLine::class,
            'model_class' => InvoiceProductLine::class,
        ));
    }

    public function getBlockPrefix()
    {
        return 'invoice_product_line';
    }
}
```

Rendering the form
------------------

PolyCollections require manual work to render. This code can go
in the same template that renders the rest of the form.

You will need to render add buttons from the prototypes array, which is
keyed on the _type field in the form definition.

It is best illustrated by example.

```twig
{# AppBundle:Invoice:add.html.twig #}

{% form_theme form.lines _self %}

{# ... #}

{% block infinite_form_polycollection_row %}
    {% set collectionForm = form %}
    <hr>
    <div class="collection">
        <div class="clearfix">
            <div class="pull-left">
                {{ form_label(collectionForm, 'Invoice lines') }}
            </div>
            <div class="pull-right">
                {% set form = prototypes.line %}
                <a href="#" data-prototype="{{ block('entry_row') | escape }}"
                   class="btn btn-success add_item">
                    <i class="glyphicon glyphicon-plus"></i> Freight line
                </a>
                {% set form = prototypes.product %}
                <a href="#" data-prototype="{{ block('entry_row') | escape }}"
                   class="btn btn-success add_item">
                    <i class="glyphicon glyphicon-plus"></i> 
                </a>
            </div>
        </div>
        <div class="items">
            {% for form in collectionForm %}
                {{ block('entry_row') }}
            {% endfor %}
        </div>
    </div>
{% endblock %}

{% block entry_row %}
    <div class="item">
        <hr>
        {{ form_widget(form) }}
    </div>
{% endblock %}

{% block invoice_line_widget %}
    <div class="row">
        <div class="col-md-6">{{ form_row(form.description) }}</div>
        <div class="col-md-2">{{ form_row(form.unitAmount) }}</div>
        <div class="col-md-2">{{ form_row(form.quantity) }}</div>
        <div class="col-md-2 text-right">
            <label>&nbsp;</label><br>
            <a href="#" class="btn btn-danger remove_item">
                <i class="glyphicon glyphicon-minus"></i> Remove
            </a>
        </div>
    </div>
    {{ form_rest(form) }}
{% endblock %}

{% block invoice_product_line_widget %}
    <div class="row">
        <div class="col-md-6">{{ form_row(form.product) }}</div>
        <div class="col-md-2 col-md-offset-2">{{ form_row(form.quantity) }}</div>
        <div class="col-md-2 text-right">
            <label>&nbsp;</label><br>
            <a href="#" class="btn btn-danger remove_item">
                <i class="glyphicon glyphicon-minus"></i> Remove
            </a>
        </div>
    </div>
    {{ form_rest(form) }}
{% endblock %}
```
