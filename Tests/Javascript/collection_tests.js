(function ($) {

    module("Basic Collection Helper tests");
    test( "Initialisation Test", 3, function() {
        var collection = setUpCollection('#markup .list-collection');

        equal(collection.$collection.data('collection'), collection,
            'Collection added itself to the data-collection attribute');
        equal($._data(collection.$prototypes[0], 'events').click.length, 1,
            'Add Event listener bound to the prototype');
        equal(collection.internalCount, 1,
            'Internal count is properly set');
    });

    test( "Remove Item Test", function() {
        var collection = setUpCollection('#markup .list-collection');
        collection.$collection.find('.remove_item').click();

        var items = collection.$collection.find('.item');

        equal(items.length, 0,
            'Remove item removed item from collection');
    });

    test( "Add Item Test", function() {
        var collection = setUpCollection('#markup .list-collection');
        collection.$prototypes.click();

        var items = collection.$collection.find('.item');

        equal(items.length, 2,
            'Add item added another prototype to the collection');
        equal(collection.internalCount, 2,
            'Internal count is incremented when adding');
    });

    test( "Complicated Item Test", function() {
        var collection = setUpCollection('#markup .list-collection');

        collection.$prototypes.click();
        collection.$collection.find('.remove_item').eq(0).click();
        collection.$prototypes.click();
        collection.$prototypes.click();

        var items = collection.$collection.find('.item');

        equal(items.length, 3,
            'Add item added another prototype to the collection');
        equal(collection.$collection.find('[data-original]').length, 0,
            'Original entry is removed');
        equal(collection.internalCount, 4,
            'Internal count is incremented when adding');
    });

    function setUpCollection(selector) {
        var $fixture = $('#qunit-fixture');

        var $dom = $(selector).clone();
        $dom.appendTo($fixture);

        var colEl = $dom.find('.collection'),
            prototypes = $dom.find('[data-prototype]');

        collection = new window.infinite.Collection(colEl, prototypes);

        return collection;
    }

})(jQuery);
