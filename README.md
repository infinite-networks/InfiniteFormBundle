InfiniteFormBundle
==================

A collection of useful form types and extensions for Symfony.

See the [form demo project](https://github.com/infinite-networks/form-demo)
for working examples.

Installation
------------

Installation instructions [can be found here](Resources/doc/installation.md).

PolyCollection
--------------

The PolyCollection form type allows you to create a collection type
on a property where the relationship is to a polymorphic object structure
like Doctrine2's Single or Multi table inheritance.

For example, if you had an Invoice entity that had a relationship to an
entity that was using Doctrine inheritance `InvoiceLine` and you wanted
to define multiple InvoiceLine types depending on what you wanted to invoice
like `InvoiceProductLine`, `InvoiceShippingLine` and `InvoiceDiscountLine`
you could use this form type to achieve a form collection that would support
all 4 types of `InvoiceLine` inside the same collection.

For more information see the [PolyCollection Documentation](Resources/doc/polycollection.md).

Collection Helper
-----------------

InfiniteFormBundle supplies some helper javascript for working with form collections. It
supports both the standard Symfony2 collection type and the PolyCollection type supplied
by this bundle.

For more information see the [Collection Helper Documentation](Resources/doc/collection-helper.md).

CheckboxGrid
------------

The CheckboxGrid form type allows editing many-to-many relationships with
a grid of checkboxes. It has handy shortcuts for Doctrine entities but can
also be used with arrays of regular objects.

For example, a company might sell multiple products, and operate in
different areas. Any of its salesmen could sell any combination of products
in areas. The salesman form needs a table of checkboxes where the rows are
products and the columns are areas (or vice versa!)

For more information see the [CheckboxGrid Documentation](Resources/doc/checkboxgrid.md).

EntitySearch
------------

EntitySearchType is an alternative to Symfony's built-in EntityType. Instead of
loading all entities into a drop-down list, it renders a single text field that
loads autocomplete suggestions through an AJAX callback.

AJAX callback not included.

For more information see the [EntitySearch Documentation](Resources/doc/entitysearch.md).

Attachment
----------

AttachmentType is an alternative to Symfony's built-in FileType.

For more information see the [EntitySearch Documentation](Resources/doc/attachment.md).

Twig Helper
-----------

InfiniteFormBundle comes with a Twig extension that adds form specific helpers
for use when rendering templates.

For more information see the [Twig Helper](Resources/doc/twig-helper.md).
