/**
 * This file is part of the InfiniteFormBundle package.
 *
 * (c) Infinite Networks Pty Ltd <http://infinite.net.au>
 */

/**
 * Provides helper javascript for handling adding and removing items from a form
 * collection. It requires jQuery to operate.
 */

let $ = require('jquery');

window.infinite = window.infinite || {};

/**
 * For most use cases, the following code can initialize a collection for you
 * if you put a class name of "collection-autoinitialize" on it.
 *
 *   <table class="collection collection-autoinitialize">
 *     <thead><tr>
 *       <td><a class="add_item" data-prototype="<tr class=&quot;item&quot;>...</tr>">Add</a></td>
 *     </tr></thead>
 *     <tbody class="items">
 *       <tr class="item">...</tr>
 *       ...
 *     </tbody>
 *   </table>
 *
 */
$(document.documentElement).on('focus', '.collection-autoinitialize .add_item, .collection-autoinitialize .remove_item', function() {
    const coll = $(this).closest('.collection');

    if (coll.hasClass('collection-autoinitialize')) {
        new infinite.Collection(
          coll.children('.items'),
          coll.children(':not(.items)').find('.add_item'),
          {
              itemSelector: '.item',
              prototypeName: coll.attr('data-prototype-name'),
              removeSelector: coll.attr('data-remove-selector') || '> .item > .remove_item, > .item > td > .remove_item'
          }
        );

        coll.removeClass('collection-autoinitialize');
    }
});


/**
 * Creates a new collection object.
 *
 * @param collection The DOM element passed here is expected to be a reference to the
 *                   containing element that wraps all items.
 * @param prototypes We expect a jQuery array passed here that will provide one or
 *                   more clickable elements that contain a prototype to be inserted
 *                   into the collection as a data-prototype attribute.
 * @param options    Allows configuration of different aspects of the Collection
 *                   objects behavior.
 */
window.infinite.Collection = function (collection, prototypes, options) {
    this.$collection = $(collection);
    this.internalCount = this.$collection.children().length;
    this.$prototypes = prototypes;

    this.options = $.extend({
        allowAdd: true,
        allowDelete: true,
        disabledSelector: '[data-disabled]',
        itemSelector: '.item',
        prototypeAttribute: 'data-prototype',
        prototypeName: '__name__',
        removeSelector: '.remove_item',
        keepScripts: false
    }, options || {});

    this.initialise();
};

window.infinite.Collection.prototype = {
    /**
     * Sets up the collection and its prototypes for action.
     */
    initialise: function () {
        var that = this;
        this.$prototypes.on('click', function (e) {
            e.preventDefault();

            that.addToCollection($(this));
        });

        this.$collection.on('click', this.options.removeSelector, function (e) {
            e.preventDefault();

            that.removeFromCollection($(this).closest(that.options.itemSelector));
        });

        this.$collection.data('collection', this);
    },

    /**
     * Adds another row to the collection
     */
    addToCollection: function ($prototype) {
        if (!this.options.allowAdd || this.$collection.is(this.options.disabledSelector)) {
            return;
        }

        var html = this._getPrototypeHtml($prototype, this.internalCount++),
            $row = $($.parseHTML(html, document, this.options.keepScripts));

        var event = this._createEvent('infinite_collection_add');
        event.$triggeredPrototype = $prototype;
        event.$row = $row;
        event.insertBefore = null;
        this.$collection.trigger(event);

        if (!event.isDefaultPrevented()) {
            if (event.insertBefore) {
                $row.insertBefore(event.insertBefore);
            } else {
                this.$collection.append($row);
            }

            return $row;
        }
    },

    /**
     * Removes a supplied row from the collection.
     */
    removeFromCollection: function ($row) {
        if (!this.options.allowDelete || this.$collection.is(this.options.disabledSelector)) {
            return;
        }

        var event = this._createEvent('infinite_collection_remove');
        $row.trigger(event);

        if (!event.isDefaultPrevented()) {
            $row.remove();
        }
    },

    /**
     * Retrieves the HTML from the prototype button, replacing __name__label__
     * and __name__ with the supplied replacement value.
     *
     * @private
     */
    _getPrototypeHtml: function ($prototype, replacement) {
        var event = this._createEvent('infinite_collection_prototype');
        event.$triggeredPrototype = $prototype;
        event.html = $prototype.attr(this.options.prototypeAttribute);
        event.replacement = replacement;
        this.$collection.trigger(event);

        if (!event.isDefaultPrevented()) {
            var labelRegex = new RegExp(this.options.prototypeName + 'label__', 'gi'),
                prototypeRegex = new RegExp(this.options.prototypeName, 'gi');

            event.html = event.html.replace(labelRegex, replacement)
                .replace(prototypeRegex, replacement);
        }

        return event.html;
    },

    /**
     * Creates a jQuery event object with the given name.
     *
     * @private
     */
    _createEvent: function (eventName) {
        var event = $.Event(eventName);
        event.collection = this;

        return event;
    }
};
