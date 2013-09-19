/**
 * This file is part of the InfiniteFormBundle package.
 *
 * (c) Infinite Networks Pty Ltd <http://infinite.net.au>
 */

/**
 * Provides helper javascript for handling adding and removing items from a form
 * collection. It requires jQuery to operate.
 *
 * To use this collection javascript, initialise it against a collection and pass in any
 * prototype add links as a second argument.
 *
 * The example below assumes that PR https://github.com/symfony/symfony/pull/7713 has been
 * merged.
 *
 *      $('[data-form-widget=collection]').each(function () {
 *          new window.infinite.Collection(this, $('[data-prototype]', this));
 *      });
 *
 * @author Tim Nagel <t.nagel@infinite.net.au>
 */
(function ($) {
    "use strict";

    window.infinite = window.infinite || {};

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
            itemSelector: '.item',
            prototypeAttribute: 'data-prototype',
            prototypeName: '__name__',
            removeSelector: '.remove_item'
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
            if (!this.options.allowAdd) {
                return;
            }

            var $row = $($.parseHTML(this._getPrototypeHtml($prototype, this.internalCount++)));

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
            }
        },

        /**
         * Removes a supplied row from the collection.
         */
        removeFromCollection: function ($row) {
            if (!this.options.allowDelete) {
                return;
            }

            var event = this._createEvent('infinite_collection_remove');
            this.$row.trigger(event);

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
}(window.jQuery));
