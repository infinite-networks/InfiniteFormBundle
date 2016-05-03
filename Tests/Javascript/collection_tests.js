(function ($) {

    module("Collection Helper tests");
    test( "Initialisation Test", 3, function() {
        var collection = setUpCollection('#markup .list-collection');

        equal(collection.$collection.data('collection'), collection,
            'Collection added itself to the data-collection attribute');
        equal($._data(collection.$prototypes[0], 'events').click.length, 1,
            'Add Event listener bound to the prototype');
        equal(collection.internalCount, 1,
            'Internal count is properly set');
    });

    test("Remove Item", function() {
        var collection = setUpCollection('#markup .list-collection');
        collection.$collection.find('.remove_item').click();

        var items = collection.$collection.find('.item');

        equal(items.length, 0,
            'Remove item removed item from collection');
    });

    test("Add Item", function() {
        var collection = setUpCollection('#markup .list-collection');
        collection.$prototypes.click();

        var items = collection.$collection.find('.item'),
            newItem = items.eq(1);

        equal(items.length, 2,
            'Add item added another prototype to the collection');
        equal(collection.internalCount, 2,
            'Internal count is incremented when adding');
        equal(newItem.find('input[type=email]').length, 1,
            'Added row has input');
    });

    test("Complicated Item", function() {
        var collection = setUpCollection('#markup .list-collection');

        collection.$prototypes.click();
        collection.$collection.find('.remove_item').eq(0).click();
        collection.$prototypes.click();
        collection.$prototypes.click();

        var items = collection.$collection.find('.item');

        equal(items.length, 3,
            'Added 3 and removed 1 item from the collection');
        equal(collection.$collection.find('[data-original]').length, 0,
            'Original entry is removed');
        equal(collection.internalCount, 4,
            'Internal count is incremented when adding');
    });

    test("Disabled Collection", function() {
        var collection = setUpCollection('#markup .list-collection');
        collection.$collection.attr('data-disabled', 1);

        collection.$prototypes.click();
        collection.$collection.find('.remove_item').eq(0).click();
        collection.$prototypes.click();
        collection.$prototypes.click();

        var items = collection.$collection.find('.item');

        equal(items.length, 1,
            'Collection didnt add or remove any rows');
        equal(collection.$collection.find('[data-original]').length, 1,
            'Original Entry was not removed');
        equal(collection.internalCount, 1,
            'Internal count stayed the same');
    });

    test("Disabled Add Collection", function() {
        var collection = setUpCollection('#markup .list-collection', {
            allowAdd: false
        });

        collection.$prototypes.click();
        collection.$collection.find('.remove_item').eq(0).click();

        var items = collection.$collection.find('.item');

        equal(items.length, 0,
            'Collection only removed items');
        equal(collection.$collection.find('[data-original]').length, 0,
            'Original Entry was removed');
        equal(collection.internalCount, 1,
            'Internal count stayed the same');
    });

    test("Disabled Delete Collection", function() {
        var collection = setUpCollection('#markup .list-collection', {
            allowDelete: false
        });

        collection.$prototypes.click();
        collection.$collection.find('.remove_item').eq(0).click();

        var items = collection.$collection.find('.item');

        equal(items.length, 2,
            'Collection only removed items');
        equal(collection.$collection.find('[data-original]').length, 1,
            'Original Entry was not removed');
        equal(collection.internalCount, 2,
            'Internal count incremented');
    });

    test("Custom Selectors", function() {
        var collection = setUpCollection('#markup .list-collection-different-selectors', {
            itemSelector: '.customitem',
            prototypeAttribute: 'data-customprototype',
            prototypeName: '__customname__',
            removeSelector: '.custom_remove_item'
        });

        collection.$prototypes.click();
        collection.$collection.find('.custom_remove_item').eq(0).click();
        collection.$prototypes.click();
        collection.$prototypes.click();

        var items = collection.$collection.find('.customitem');

        equal(items.length, 3,
            'Added 3 and removed 1 item from the collection');
        equal(collection.$collection.find('[data-original]').length, 0,
            'Original entry is removed');
        equal(collection.internalCount, 4,
            'Internal count is incremented when adding');
    });

    test("Keep scripts in prototype html", function() {
        var collection = setUpCollection('#markup .list-collection-with-prototype-scripts', {
            keepScripts: true
        });

        var result = collection.addToCollection(collection.$prototypes);

        equal(result.find('script').length, 1,
            'Prototype scripts were kept');
    });

    test("Add Event", function () {
        expect(2);

        var collection = setUpCollection('#markup .list-collection');

        collection.$collection.on('infinite_collection_add', function (e) {
            ok(true, 'Add event fired');
        });

        collection.$prototypes.click();

        var items = collection.$collection.find('.item');

        equal(items.length, 2,
            'Add item added another prototype to the collection');
    });

    test("Add Event Prevents adding", function () {
        var collection = setUpCollection('#markup .list-collection');
        collection.$collection.on('infinite_collection_add', function (e) {
            e.preventDefault();
        });

        collection.$prototypes.click();

        var items = collection.$collection.find('.item');

        equal(items.length, 1,
            'Add item skipped because we prevented it with an event');
    });

    test("Programatic addToCollection returns row", function () {
        var collection = setUpCollection('#markup .list-collection');
        var result = collection.addToCollection(collection.$prototypes);

        equal(result.length, 1, 'addToCollection returned the row');
    });

    test("Custom html structure internalCount", function() {
        var collection = setUpCollection('#markup .list-collection-different-html-structure');
        equal(collection.internalCount, 1,
            'Internal count is correctly initialized');
     });

     test("Nested collection internalCount", function() {
        var collection = setUpCollection('#markup .list-collection-with-nested-collection');
        equal(collection.internalCount, 1,
            'Internal count with nested collections is correctly initialized');

        var nestedcollection = setUpCollection('#markup .list-collection-with-nested-collection', { itemSelector: '.child-item' }, '.child-collection');
        equal(nestedcollection.internalCount, 3,
            'Internal count of nested collection is correctly initialized');

        var nestedcollection = setUpCollection('#markup .list-collection-with-nested-collection', {}, '.third-level-collection');
        equal(nestedcollection.internalCount, 2,
            'Internal count of multiple nested collection is correctly initialized');
    });

    function setUpCollection(selector, options, elSelector) {
        var $fixture = $('#qunit-fixture');

        var $dom = $(selector).clone();
        $dom.appendTo($fixture);

        var colEl = $dom.find(elSelector ? elSelector : '.collection'),
            prototypes = $dom.find('.add_item');

        collection = new window.infinite.Collection(colEl, prototypes, options);

        return collection;
    }

})(jQuery);
