var autoComplete = require('js-autocomplete/auto-complete.min.js');
var $ = require('jquery');

function htmlEscape(s) {
    return s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}

$(document.documentElement).on('focus', '.entity-search', function () {
    var that = $(this);

    if (that.data('autocomplete')) {
        return;
    }

    that.on('input', function () {
        that.prev().val('');
    });

    that.data('autocomplete', new autoComplete({
        selector: this,
        minChars: 2,
        source: function source(query, callback) {
            query = query.toLowerCase();
            $.get(that.attr('data-search-url') + '?query=' + encodeURIComponent(query), function (response) {
                callback(response.results);
            });
        },
        renderItem: function renderItem(result, query) {
            query = query.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
            var re = new RegExp("(" + query.trim().split(' ').join('|') + ")", "gi");
            return '<div class="autocomplete-suggestion" data-val="' + htmlEscape(result.name) + '" data-json="' + htmlEscape(JSON.stringify(result)) + '">' + htmlEscape(result.list_text || result.name).replace(re, "<b>$1</b>") + '</span>' + '</div>';
        },
        onSelect: function onSelect(e, search, suggestion) {
            e.preventDefault(); // If the user pressed enter, don't submit the form

            var term = JSON.parse($(suggestion).attr('data-json'));
            that.prev().val(term.id);
            that.val(term.name);
            that.trigger('entityselected', term);
        }
    }));
});
