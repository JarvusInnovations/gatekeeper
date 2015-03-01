/*jshint undef: true, unused: true, browser: true, curly: true*/
/*global Ext*/
Ext.define('Site.page.Portal', {
    singleton: true,
    requires: [
        'Site.Common',
        'Ext.util.Collection'
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

        me.endpoints.each(function(endpoint) {
            var match = false;

            me.removeHighlights(endpoint.endpointEl);

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
                    me.highlightQuery(endpoint.descriptionEl, query);
                }
            } else {
                match = true;
            }

            endpoint.endpointEl.setStyle('display', match ? '' : 'none');
        });
    },
    
    highlightQuery: function(containerEl, query) {
        var nodes = containerEl.dom.childNodes;

		function innerHighlight(node, pat) {
			var skip = 0;
			if (node.nodeType == 3) {
				var pos = node.data.toUpperCase().indexOf(pat);
				if (pos >= 0) {
					var spannode = document.createElement('mark')
						,middlebit = node.splitText(pos)
						,endbit = middlebit.splitText(pat.length)
						,middleclone = middlebit.cloneNode(true);
						
					spannode.appendChild(middleclone);
					middlebit.parentNode.replaceChild(spannode, middlebit);
					skip = 1;
				}
			} else if (node.nodeType == 1 && node.childNodes && !/(script|style)/i.test(node.tagName)) {
				for (var i = 0; i < node.childNodes.length; ++i) {
					i += innerHighlight(node.childNodes[i], pat);
				}
			}
			return skip;
		}

		if(nodes.length && query && query.length) {
			Ext.each(nodes, function(item) {
				innerHighlight(item, query.toUpperCase());
			});
		}
    },
    
    removeHighlights: function(containerEl) {
        var marks = containerEl.select('mark').elements;

		Ext.each(marks, function(item) {
			with (item.parentNode) {
				replaceChild(this.firstChild, this);
				normalize();
			}
		});
    }
});