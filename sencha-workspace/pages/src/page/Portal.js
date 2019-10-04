/*jshint undef: true, unused: true, browser: true, curly: true*/
/*global Ext, Jarvus*/
// @require-package jarvus-highlighter
Ext.define('Site.page.Portal', {
    singleton: true,
    requires: [
        'Site.Common',
        'Ext.util.Collection',
        'Jarvus.util.Highlighter'
    ],


    // template methods
    constructor: function() {
        var me = this;

        me.endpoints = new Ext.util.Collection();

        Ext.onReady(me.onDocReady, me);
    },


    // event handlers
    onDocReady: function() {
        var me = this,
            endpoints = me.endpoints,
            searchPlaceholder = 'Search APIs…',
            searchInputEl = me.searchInputEl = Ext.getBody().down('.api-search-input');


        // index endpoints
        Ext.select('.endpoint-list-item', true).each(function(endpointEl) {
            var pathEl = endpointEl.down('.endpoint-path'),
                titleEl = endpointEl.down('.endpoint-title'),
                descriptionEl = endpointEl.down('.endpoint-description');

            endpoints.add({
                id: parseInt(endpointEl.getAttribute('data-endpoint-id'), 10),
                endpointEl: endpointEl,
                pathEl: pathEl,
                pathText: pathEl.dom.textContent,
                titleEl: titleEl,
                titleText: titleEl.dom.textContent,
                descriptionEl: descriptionEl,
                descriptionText: descriptionEl.dom.textContent
            });
        });


        // wire search field
        if (searchInputEl) {
            searchInputEl.set({
                placeholder: searchPlaceholder
            });

            searchInputEl.on({
                focus: function() {
                    searchInputEl.set({
                        placeholder: ''
                    });
                },
                blur: function() {
                    searchInputEl.set({
                        placeholder: searchPlaceholder
                    });
                },
                keyup: {
                    buffer: 100,
                    fn: function() {
                        me.filterEndpoints(Ext.String.trim(searchInputEl.getValue()));
                    }
                }
            });
        }
    },


    // member methods
    filterEndpoints: function(query) {
        var me = this,
            queryRe = query && new RegExp('('+Ext.String.escapeRegex(query)+')', 'i');


        // suppress duplicate queries
        if (me.currentQuery == query) {
            return;
        }

        me.currentQuery = query;


        // apply filter and highlight matches
        me.endpoints.each(function(endpoint) {
            var match = false;

            Jarvus.util.Highlighter.removeHighlights(endpoint.endpointEl);

            if (query) {
                if (queryRe.test(endpoint.pathText)) {
                    match = true;
                    endpoint.pathEl.update(endpoint.pathText.replace(queryRe, '<mark>$1</mark>'));
                }
    
                if (queryRe.test(endpoint.titleText)) {
                    match = true;
                    endpoint.titleEl.update(endpoint.titleText.replace(queryRe, '<mark>$1</mark>'));
                }
    
                if (queryRe.test(endpoint.descriptionText)) {
                    match = true;
                    Jarvus.util.Highlighter.highlight(endpoint.descriptionEl, query);
                }
            } else {
                match = true;
            }

            endpoint.endpointEl.setStyle('display', match ? '' : 'none');
        });
    }
});