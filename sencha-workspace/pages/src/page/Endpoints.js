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

    constructor: function() {
        var me = this;

        me.endpoints = new Ext.util.Collection();
        me.requestsRenderer = Ext.util.Format.numberRenderer('0,000');

        Ext.onReady(me.onDocReady, me);
    },

    onDocReady: function() {
        var me = this,
            endpoints = me.endpoints,
            endpointsCt = me.endpointsCt = Ext.getBody().down('.endpoints');


        // toggle expanded class when an endpoint is clicked
        Ext.getBody().on('click', function(ev, t) {
            var endpointEl = ev.getTarget('.endpoint', null, true);

            endpointEl.toggleCls('expanded');
        }, null, { delegate: '.endpoint' });


        // build list of endpoints
        endpointsCt.select('.endpoint', true).each(function(endpointEl) {
            var headerEl = endpointEl.down('header'),
                requestsValueCt = headerEl.insertFirst({
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
                responseTimeCt = headerEl.insertFirst({
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
                cacheHitRatioCt = headerEl.insertFirst({
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

            endpoints.add({
                id: parseInt(endpointEl.getAttribute('data-id'), 10),
                el: endpointEl,
                responseTimeBarEl: responseTimeCt.down('.metric-secondary-bar'),
                responseTimeValueEl: responseTimeCt.down('.metric-secondary-value .number'),
                cacheHitRatioBarEl: cacheHitRatioCt.down('.metric-secondary-bar'),
                cacheHitRatioValueEl: cacheHitRatioCt.down('.metric-secondary-value .number'),
                requestsBarEl: headerEl.insertFirst({
                    cls: 'metric-primary-bar'
                }),
                requestsValueEl: requestsValueCt.down('.number')
            });
        });


        // load initial metrics
        me.loadMetrics();
    },
    
    loadMetrics: function() {
        var me = this,
            requestsRenderer = me.requestsRenderer,
            endpoints = me.endpoints,
            endpointsCount = endpoints.getCount(),
            endpointsCt = me.endpointsCt;

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


                // sort metrics
                endpoints.sortItems(function(a, b) {
                    if (a.lastMetrics.requests == b.lastMetrics.requests) {
                        return 0;
                    }

                    return a.lastMetrics.requests > b.lastMetrics.requests ? -1 : 1;
                });


                // write new order to DOM
                // TODO: only move elements as needed instead of moving all of them? animate? use calculated top values instead of DOM ordering?
                for (i = 0; i < endpointsCount; i++) {
                    endpointsCt.appendChild(endpoints.getAt(i).el);
                }


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