InfiniteFormBundle
==================

[![Build Status](https://travis-ci.org/infinite-networks/InfiniteFormBundle.png?branch=master)](https://travis-ci.org/infinite-networks/InfiniteFormBundle)

A collection of useful form types and extensions for Symfony2.

**Note:** Documenting these form types is an ongoing effort. We'd appreciate
any feedback, corrections additions you can provide.

Installation
------------

Installation instructions [can be found here](https://github.com/infinite-networks/InfiniteFormBundle/blob/master/Resources/doc/installation.md)

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

For more information see [PolyCollection Documentation](https://github.com/infinite-networks/InfiniteFormBundle/blob/master/Resources/doc/polycollection.md)

