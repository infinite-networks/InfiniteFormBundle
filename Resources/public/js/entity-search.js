$(function () {
    $(document.body).on('focus', '.entity-search', function() {
        var that = $(this);

        if (that.data('typeahead')) return;

        var url = that.attr('data-search-url');
        var timeout = null;
        var lastQuerySent = null;

        that.typeahead({
            source: function(typeahead, query) {
                that.prev().val('');

                if (timeout) clearTimeout(timeout);

                // Don't send a query for a blank string
                if (query == '') {
                    return [];
                }

                // Don't re-send a query if already displaying the results for that same query
                if (query == lastQuerySent && typeahead.shown) {
                    return null;
                }

                // Wait briefly before sending the request
                timeout = setTimeout(function() {
                    lastQuerySent = query;

                    $.ajax({
                        url: url,
                        data: {
                            substitute_for: that.data('substitute_for'),
                            query: query
                        },
                        success: function (data) {
                            if (query != lastQuerySent) return; // Ignore AJAX responses to old queries

                            for (var k in data.results) {
                                data.results[k].list_text = data.results[k].list_text || data.results[k].name;
                            }

                            typeahead.process(data.results);
                        }
                    });
                }, 150);
            },
            property: 'list_text',
            matcher: function(item) { return true; },
            onselect: function (selection) {
                that.prev().val(selection.id);
                that.val(selection.name);
                that.trigger('entityselected', selection);
            }
        });
    });
});
