/**
 * This file is part of the InfiniteFormBundle package.
 *
 * (c) Infinite Networks Pty Ltd <http://infinite.net.au>
 */

/**
 * Provides helper javascript for handling adding and removing
 * items from a form collection.
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
     */
    window.infinite.Collection = function (collection, prototypes) {
        this.$collection = $(collection);
        this.internalCount = $(collection).children().length;
        this.$prototypes = prototypes;

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

            this.$collection.on('click', '.remove_item', function (e) {
                e.preventDefault();

                that.removeFromCollection($(this).parents('.item'));
            });
        },

        /**
         * Adds another row to the collection
         */
        addToCollection: function (prototype, values) {
            values = values || {};

            var row = $($.parseHTML(this._getPrototypeHtml(prototype)));
            this._fillRowWithValues(row, values);

            var event = $.Event('infinite_collection_add');
            event.collection = this.$collection;
            event.row = row;
            event.insertBefore = null;
            this.$collection.trigger(event);

            if (!event.isDefaultPrevented()) {
                if (event.insertBefore) {
                    row.insertBefore(event.insertBefore);
                } else {
                    this.$collection.append(row);
                }

                return row;
            }

            return false;
        },

        /**
         * Removes a supplied row from the collection.

         */
        removeFromCollection: function (row) {
            var event = $.Event('infinite_collection_remove');
            event.collection = this.$collection;
            event.row = row;
            this.$collection.trigger(event);

            if (!event.isDefaultPrevented()) {
                row.remove();
            }
        },

        /**
         * Retrieves the HTML from the prototype button, replacing __name__label__
         * and __name__ with an incremented counter value.
         *
         * TODO support customized prototype name
         * TODO add an extension point for the replacement of the label
         *
         * @private
         */
        _getPrototypeHtml: function (prototype) {
            var html = prototype.data('prototype');

            return html.replace(/__name__label__/gi, this.internalCount).replace(/__name__/gi, this.internalCount++);
        },

        /**
         * Fills a given row with default values.
         *
         * @private
         */
        _fillRowWithValues: function (row, values) {
            $.each(values, function (field, value) {
                var el = row.find(field);

                if (el.is('input, textarea, select')) {
                    el.val(value);
                } else {
                    el.text(value);
                }

                if (value && !el.is(':visible') && !el.is('.stay-hidden')) {
                    el.show();
                }
            });
        }
    };
}(window.jQuery));
