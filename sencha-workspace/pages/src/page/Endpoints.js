/*jshint undef: true, unused: true, browser: true, curly: true*/
/*global Ext*/
Ext.define('Site.page.Endpoints', {
    singleton: true,
    requires: [
        'Site.Common',
        'Ext.util.Collection',
        'Ext.Ajax',
        'Ext.util.Format'
    ],

    responseTimeClsLevels: {
        good: 0,
        mid: 150,
        bad: 1000
    },

    // template methods
    constructor: function() {
        var me = this;

        me.endpoints = new Ext.util.Collection({
            listeners: {
                scope: me,
                sort: 'onSort'
            }
        });

        me.requestsRenderer = me.bytesRenderer = Ext.util.Format.numberRenderer('0,000');

        Ext.onReady(me.onDocReady, me);
    },


    // event handlers
    onDocReady: function() {
        var me = this,
            endpoints = me.endpoints,
            endpointsCt = me.endpointsCt = Ext.getBody().down('.endpoints'),
            checkedModeRadioEl;


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
                primaryValueCt = titleEl.appendChild({
                    cls: 'metric-primary-value',
                    cn: [{
                        tag: 'span',
                        cls: 'number',
                        html: '&mdash;'
                    },{
                        tag: 'span',
                        cls: 'unit'
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
                            cls: 'unit percent',
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
                primaryBarEl: titleEl.insertFirst({
                    cls: 'metric-primary-bar'
                }),
                primaryValueEl: primaryValueCt.down('.number'),
                primaryUnitEl: primaryValueCt.down('.unit')
            });
        });


        // wire filter field
        endpointsCt.down('input[type=search]').on('keyup', 'onFilterKeyUp', me);


        // wire mode radios
        endpointsCt.select('input[name=mode]', true).on('change', 'onModeChange', me);
        checkedModeRadioEl = endpointsCt.down('input[name=mode]:checked');
        me.mode = checkedModeRadioEl ? checkedModeRadioEl.getValue() : 'requests';


        // initialize sorters but suspend sort event until first load
        endpoints.suspendEvent('sort');
        endpoints.setSorters(function(a, b) {
            var mode = me.mode,
                aMetrics = a.lastMetrics,
                aValue = aMetrics && (mode == 'requests' ? aMetrics.requests : aMetrics.bytesTotal),
                bMetrics = b.lastMetrics,
                bValue = bMetrics && (mode == 'requests' ? bMetrics.requests : bMetrics.bytesTotal);

            if (aValue == bValue) {
                return 0;
            }

            return aValue > bValue ? -1 : 1;
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

    onModeChange: function(ev, t) {
        var me = this;

        me.mode = t.value;
        me.endpoints.sort();
        Ext.defer(me.renderMetrics, 1, me);
    },


    // internal methods
    loadMetrics: function(metricsUpdatedCallback, scope) {
        var me = this,
            endpoints = me.endpoints;

        Ext.Ajax.request({
            url: '/metrics/endpoints-current',
            headers: {
                Accept: 'application/json'
            },
            success: function(response) {
                var r = Ext.decode(response.responseText, true),
                    endpointsMetrics = r && r.data,
                    endpointsMetricsLen = endpointsMetrics && endpointsMetrics.length,
                    i = 0,
                    endpointMetrics;

                if (!endpointsMetrics) {
                    console.error('Failed to load endpoints');
                    return;
                }


                // update lastMetrics for each endpoint
                for (; i < endpointsMetricsLen; i++) {
                    endpointMetrics = endpointsMetrics[i];
                    endpointMetrics.bytesTotal = endpointMetrics.bytesCached + endpointMetrics.bytesExecuted;
                    endpoints.getByKey(endpointMetrics.EndpointID).lastMetrics = endpointMetrics;
                }


                // fire metricsUpdated callback before sorting
                Ext.callback(metricsUpdatedCallback, scope);


                // sort metrics
                endpoints.sort();


                // render metrics (deferred so reordering is flushed to DOM first, otherwise CSS transitions don't work)
                Ext.defer(me.renderMetrics, 1, me);


                // schedule next call
                if (!me.updatesPaused) {
                    Ext.defer(me.loadMetrics, 2000, me);
                }
            }
        });
    },

    renderMetrics: function() {
        var me = this,
            mode = me.mode,
            endpoints = me.endpoints,
            endpointsCount = endpoints.getCount(),
            primaryValueRenderer = mode == 'requests' ? me.requestsRenderer : me.bytesRenderer,
            responseTimeClsLevels = me.responseTimeClsLevels,
            responseTimeBarMax = Ext.Array.max(Ext.Object.getValues(responseTimeClsLevels)),
            responseTimeClasses = Ext.Object.getKeys(responseTimeClsLevels),
            totalRequests = 0,
            totalBytes = 0,
            maxResponseTime = 0,
            i, endpoint, metrics, responseTime, requests, bytes, cacheHitRatio, responseTimeCt;


        // initial loop to calculate totals
        for (i = 0; i < endpointsCount; i++) {
            endpoint = endpoints.getAt(i);
            metrics = endpoint.lastMetrics;
            totalRequests += metrics.requests;
            totalBytes += metrics.bytesTotal;
            maxResponseTime = Math.max(maxResponseTime, metrics.responseTime);
        }

        maxResponseTime = Math.min(maxResponseTime, responseTimeBarMax);


        // follow-up loop to render changes to DOM
        for (i = 0; i < endpointsCount; i++) {
            endpoint = endpoints.getAt(i);
            metrics = endpoint.lastMetrics;
            requests = metrics.requests;
            bytes = metrics.bytesTotal;

            // update primary column (requests or bytes)
            endpoint.primaryBarEl.setStyle('width',
                Math.round(
                    (
                        mode == 'requests' ?
                        requests / totalRequests :
                        bytes / totalBytes
                    ) * 100
                ) + '%'
            );
            endpoint.primaryValueEl.update(
                primaryValueRenderer(mode == 'requests' ? requests : bytes)
            );
            endpoint.primaryUnitEl.update(mode + ' / hour');

            // update response time column
            responseTime = metrics.responseTime;
            responseTimeCt = endpoint.responseTimeCt;
            endpoint.responseTimeBarEl.setStyle('height', Math.min(Math.round(responseTime / maxResponseTime * 100), 100) + '%');
            endpoint.responseTimeValueEl.update(responseTime || '&mdash;');
            responseTimeCt.removeCls(responseTimeClasses);
            if (responseTime) {
                responseTimeCt.addCls(me.getResponseTimeCls(responseTime));
            }

            // update cache hit ratio column
            cacheHitRatio = Math.round(
                (
                    mode == 'requests' ?
                    (requests ? metrics.responsesCached / requests : 0) :
                    (bytes ? metrics.bytesCached / bytes : 0)
                ) * 100
            );
            endpoint.cacheHitRatioBarEl.setStyle('height', cacheHitRatio + '%');
            endpoint.cacheHitRatioValueEl.update(cacheHitRatio.toString());
        }
    },

    getResponseTimeCls: function(responseTime) {
        var responseTimeClsLevels = this.responseTimeClsLevels,
            responseTimeClsLevel, returnLevel;

        for (responseTimeClsLevel in responseTimeClsLevels) {
            if (!responseTimeClsLevels.hasOwnProperty(responseTimeClsLevel)) {
                continue;
            }

            if (responseTime >= responseTimeClsLevels[responseTimeClsLevel]) {
                returnLevel = responseTimeClsLevel;
            } else {
                break;
            }
        }

        return returnLevel;
    }
});