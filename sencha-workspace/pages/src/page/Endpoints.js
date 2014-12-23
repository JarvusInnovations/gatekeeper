/* jshint undef: true, unused: true, browser: true, quotmark: single, curly: true */
/* global Ext */
Ext.define('Site.page.Endpoints', {
    singleton: true,
    requires: [
        'Site.Common',
        'Ext.util.Collection',
        'Ext.Ajax',
        'Ext.util.Format'
    ],


    // template methods
    constructor: function() {
        var me = this;

        me.endpoints = new Ext.util.Collection({
            listeners: {
                scope: me,
                sort: 'onSort'
            }
        });

        me.requestsRenderer = Ext.util.Format.numberRenderer('0,000');

        Ext.onReady(me.onDocReady, me);
    },


    // event handlers
    onDocReady: function() {
        var me = this,
            endpoints = me.endpoints,
            endpointsCt = me.endpointsCt = Ext.getBody().down('.endpoints');


        // toggle expanded class when an endpoint is clicked
        Ext.getBody().on('click', function(ev, t) {
            if (ev.getTarget('a')) {
                return;
            }

            var endpointEl = ev.getTarget('.endpoint', null, true);

            endpointEl.toggleCls('expanded');
        }, null, { delegate: '.endpoint .summary' });


        // build list of endpoints
        endpointsCt.select('.endpoint', true).each(function(endpointEl) {
            var summaryEl = endpointEl.down('.summary'),
                titleEl =  endpointEl.down('.title'),
                requestsValueCt = titleEl.appendChild({
                    cls: 'metric-primary-value',
                    cn: [{
                        tag: 'span',
                        cls: 'number',
                        html: '&mdash;'
                    },{
                        tag: 'span',
                        cls: 'unit',
                        html: 'requests'
                    }]
                }),
                responseTimeCt = summaryEl.appendChild({
                    cls: 'metric-secondary-ct',
                    cn: [{
                        cls: 'metric-secondary-bar'
                    },{
                        cls: 'metric-secondary-value',
                        cn: [{
                            tag: 'span',
                            cls: 'number',
                            html: '&mdash;'
                        },{
                            tag: 'span',
                            cls: 'unit',
                            html: 'ms'
                        }]
                    }]
                }),
                cacheHitRatioCt = summaryEl.appendChild({
                    cls: 'metric-secondary-ct',
                    cn: [{
                        cls: 'metric-secondary-bar'
                    },{
                        cls: 'metric-secondary-value',
                        cn: [{
                            tag: 'span',
                            cls: 'number',
                            html: '&mdash;'
                        },{
                            tag: 'span',
                            cls: 'unit',
                            html: '%'
                        }]
                    }]
                });

            endpointEl.setVisibilityMode(Ext.Element.DISPLAY);

            endpoints.add({
                id: parseInt(endpointEl.getAttribute('data-id'), 10),
                el: endpointEl,
                title: titleEl.dom.innerText,
                responseTimeCt: responseTimeCt,
                responseTimeBarEl: responseTimeCt.down('.metric-secondary-bar'),
                responseTimeValueEl: responseTimeCt.down('.metric-secondary-value .number'),
                cacheHitRatioCt: cacheHitRatioCt,
                cacheHitRatioBarEl: cacheHitRatioCt.down('.metric-secondary-bar'),
                cacheHitRatioValueEl: cacheHitRatioCt.down('.metric-secondary-value .number'),
                requestsBarEl: titleEl.insertFirst({
                    cls: 'metric-primary-bar'
                }),
                requestsValueEl: requestsValueCt.down('.number')
            });
        });


        // wire filter field
        endpointsCt.down('input[type=search]').on('keyup', 'onFilterKeyUp', me);


        // initialize sorters but suspend sort event until first load
        endpoints.suspendEvent('sort');
        endpoints.setSorters(function(a, b) {
            var aMetrics = a.lastMetrics,
                aRequests = aMetrics && aMetrics.requests,
                bMetrics = b.lastMetrics,
                bRequests = bMetrics && bMetrics.requests;

            if (aRequests == bRequests) {
                return 0;
            }

            return aRequests > bRequests ? -1 : 1;
        });


        // load initial metrics
        me.loadMetrics(function() {
            endpoints.resumeEvent('sort');
        });
    },
    
    onSort: function(endpoints) {
        var me = this,
            endpointsCt = me.endpointsCt,
            endpointsCount = endpoints.getCount(),
            lastOrder = me.lastOrder,
            order = me.lastOrder = Ext.Array.pluck(endpoints.items, 'id'),
            orderUnchanged = true,
            i;


        // determine if order is the same and we can entirely skip rewriting the DOM
        if (order && lastOrder) {
            for (i = 0; i < endpointsCount; i++) {
                if (order[i] != lastOrder[i]) {
                    orderUnchanged = false;
                    break;
                }
            }

            if (orderUnchanged) {
                return;
            }
        }


        // write new order to DOM
        // TODO: only move elements as needed instead of moving all of them? animate? use calculated top values instead of DOM ordering?
        for (i = 0; i < endpointsCount; i++) {
            endpointsCt.appendChild(endpoints.getAt(i).el);
        }
    },

    onFilterKeyUp: function(ev, t) {
        var query = t.value,
            regexp = new RegExp(query, 'i');

        this.endpoints.each(function(endpoint) {
            endpoint.el.setVisible(regexp.test(endpoint.title));
        });
    },


    // internal methods
    loadMetrics: function(metricsUpdatedCallback, scope) {
        var me = this,
            requestsRenderer = me.requestsRenderer,
            endpoints = me.endpoints,
            endpointsCount = endpoints.getCount();

        Ext.Ajax.request({
            url: '/metrics/endpoints-current',
            headers: {
                Accept: 'application/json'
            },
            success: function(response) {
                var r = Ext.decode(response.responseText, true),
                    endpointsMetrics = r && r.data,
                    endpointsMetricsLen = endpointsMetrics && endpointsMetrics.length,
                    i, endpointMetrics, endpoint, totalRequests = 0, maxResponseTime = 0;

                if (!endpointsMetrics) {
                    console.error('Failed to load endpoints');
                    return;
                }


                // initial loop to calculate totals and update lastMetrics
                for (i = 0; i < endpointsMetricsLen; i++) {
                    endpointMetrics = endpointsMetrics[i];
                    totalRequests += endpointMetrics.requests;
                    maxResponseTime = Math.max(maxResponseTime, endpointMetrics.responseTime);
                    endpoints.getByKey(endpointMetrics.EndpointID).lastMetrics = endpointMetrics;
                }


                // fire metricsUpdated callback before sorting
                Ext.callback(metricsUpdatedCallback, scope);


                // sort metrics
                endpoints.sort();


                // update metrics (deferred so reordering is flushed to DOM first, otherwise CSS transitions don't work)
                Ext.defer(function() {
                    var i = 0,
                        endpoint, metrics, responseTime, requests, cacheHitRatio;

                    for (; i < endpointsCount; i++) {
                        endpoint = endpoints.getAt(i);
                        metrics = endpoint.lastMetrics;
                        requests = metrics.requests;

                        endpoint.requestsBarEl.setStyle('width', Math.round(requests / totalRequests * 100) + '%');
                        endpoint.requestsValueEl.update(requestsRenderer(requests));

                        responseTime = metrics.responseTime;
                        endpoint.responseTimeBarEl.setStyle('height', Math.round(responseTime / maxResponseTime * 100) + '%');
                        endpoint.responseTimeValueEl.update(responseTime);

                        cacheHitRatio = requests ? Math.round(metrics.responsesCached / requests * 100) : 0;
                        endpoint.cacheHitRatioBarEl.setStyle('height', cacheHitRatio + '%');
                        endpoint.cacheHitRatioValueEl.update(cacheHitRatio.toString());
                    }
                }, 1);


                // schedule next call
                if (!me.updatesPaused) {
                    Ext.defer(me.loadMetrics, 2000, me);
                }
            }
        });
    }
});