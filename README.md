InfiniteFormBundle
==================

[![Build Status](https://travis-ci.org/infinite-networks/InfiniteFormBundle.png?branch=master)](https://travis-ci.org/infinite-networks/InfiniteFormBundle)

A collection of useful form types and extensions for Symfony2.

**Note:** Documenting these form types is an ongoing effort. We'd appreciate
any feedback, corrections additions you can provide.

Installation
------------

Installation instructions [can be found here][].

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

For more information see the [PolyCollection Documentation][].

Collection Helper
-----------------

InfiniteFormBundle supplies some helper javascript for working with form collections. It
supports both the standard Symfony2 collection type and the PolyCollection type supplied
by this bundle.

For more information see the [Collection Helper Documentation][].

CheckboxGrid
------------

The CheckboxGrid form type allows editing many-to-many relationships with
a grid of checkboxes. It has handy shortcuts for Doctrine entities but can
also be used with arrays of regular objects.

For example, a company might sell multiple products, and operate in
different areas. Any of its salesmen could sell any combination of products
in areas. The salesman form needs a table of checkboxes where the rows are
products and the columns are areas (or vice versa!)

For more information see the [CheckboxGrid Documentation][].

Twig Helper
-----------

InfiniteFormBundle comes with a Twig extension that adds form specific helpers
for use when rendering templates.

For more information see the [Twig Helper][].

Validators
----------

InfiniteFormBundle comes with some common validators that are not included in the Symfony
Validator component.

For more information see the [Validation][] page.

[PolyCollection Documentation]: https://github.com/infinite-networks/InfiniteFormBundle/blob/master/Resources/doc/polycollection.md
[Collection Helper Documentation]: https://github.com/infinite-networks/InfiniteFormBundle/blob/master/Resources/doc/collection-helper.md
[CheckboxGrid Documentation]: https://github.com/infinite-networks/InfiniteFormBundle/blob/master/Resources/doc/checkboxgrid.md
[Twig Helper]: https://github.com/infinite-networks/InfiniteFormBundle/blob/master/Resources/doc/twig-helper.md
[Validation]: https://github.com/infinite-networks/InfiniteFormBundle/blob/master/Resources/doc/validators.md
[can be found here]: https://github.com/infinite-networks/InfiniteFormBundle/blob/master/Resources/doc/installation.md
