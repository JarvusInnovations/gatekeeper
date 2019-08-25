/*jshint undef: true, unused: true, browser: true, curly: true*/
/*global Ext, Jarvus*/
// @require-package jarvus-highlighter
// @abstract
Ext.define('Site.abstractpage.TrafficStack', {
    requires: [
        'Site.Common',
        'Ext.util.Collection',
        'Ext.util.SorterCollection',
        'Ext.Ajax',
        'Ext.util.Format',
        'Jarvus.util.Highlighter'
    ],

    responseTimeClsLevels: {
        good: 0,
        mid: 150,
        bad: 1000
    },

    // template methods
    constructor: function() {
        var me = this;

        me.rows = new Ext.util.Collection({
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
            rows = me.rows,
            rowsCt = me.rowsCt = Ext.getBody().down('.trafficstack'),
            checkedModeRadioEl;


        // toggle expanded class when a row is clicked
        rowsCt.on('click', function(ev, t) {
            if (ev.getTarget('a, small')) {
                return;
            }

            ev.getTarget('.trafficstack-row', null, true).toggleCls('expanded');
        }, null, { delegate: '.trafficstack-row .summary' });


        // build list of rows
        rowsCt.select('.trafficstack-row', true).each(function(rowEl) {
            var summaryEl = rowEl.down('.summary'),
                titleEl =  rowEl.down('.title'),
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

            rowEl.setVisibilityMode(Ext.Element.DISPLAY);

            rows.add({
                id: parseInt(rowEl.getAttribute('data-id'), 10),
                el: rowEl,
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
        rowsCt.down('input[type=search]').on('keyup', 'onFilterKeyUp', me);


        // wire mode radios
        rowsCt.select('input[name=mode]', true).on('change', 'onModeChange', me);
        checkedModeRadioEl = rowsCt.down('input[name=mode]:checked');
        me.mode = checkedModeRadioEl ? checkedModeRadioEl.getValue() : 'requests';


        // initialize sorters but suspend sort event until first load
        rows.suspendEvent('sort');
        rows.setSorters(function(a, b) {
            var mode = me.mode,
                aMetric = a.lastMetric,
                aValue = aMetric && (mode == 'requests' ? aMetric.requests : aMetric.bytesTotal),
                bMetric = b.lastMetric,
                bValue = bMetric && (mode == 'requests' ? bMetric.requests : bMetric.bytesTotal);

            if (aValue == bValue) {
                return 0;
            }

            return aValue > bValue ? -1 : 1;
        });


        // load initial metrics
        me.loadMetrics(function() {
            rows.resumeEvent('sort');
        });
    },

    onSort: function(rows) {
        var me = this,
            rowsCt = me.rowsCt,
            rowsCount = rows.getCount(),
            lastOrder = me.lastOrder,
            order = me.lastOrder = Ext.Array.pluck(rows.items, 'id'),
            orderUnchanged = true,
            i;


        // determine if order is the same and we can entirely skip rewriting the DOM
        if (order && lastOrder) {
            for (i = 0; i < rowsCount; i++) {
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
        for (i = 0; i < rowsCount; i++) {
            rowsCt.appendChild(rows.getAt(i).el);
        }
    },

    onFilterKeyUp: function(ev, t) {
        var query = t.value,
            regexp = new RegExp(query, 'i'),
            rowEl, match;

        this.rows.each(function(row) {
            rowEl = row.el;
            match = query ? regexp.test(row.title) : true;

            rowEl.setVisible(match);

            Jarvus.util.Highlighter.removeHighlights(rowEl);

            if (query && match) {
                Jarvus.util.Highlighter.highlight(rowEl, query);
            }
        });
    },

    onModeChange: function(ev, t) {
        var me = this;

        me.mode = t.value;
        me.rows.sort();
        Ext.defer(me.renderMetrics, 1, me);
    },


    // internal methods
    loadMetrics: function(metricsUpdatedCallback, scope) {
        var me = this,
            idProperty = me.idProperty,
            rows = me.rows;

        Ext.Ajax.request({
            url: me.metricsUrl,
            headers: {
                Accept: 'application/json'
            },
            success: function(response) {
                var r = Ext.decode(response.responseText, true),
                    metrics = r && r.data,
                    metricsLen = metrics && metrics.length,
                    i = 0,
                    metric, row;

                if (!metrics) {
                    Ext.Logger.error('Failed to load endpoints');
                    return;
                }


                // update lastMetric for each endpoint
                for (; i < metricsLen; i++) {
                    metric = metrics[i];
                    row = rows.getByKey(metric[idProperty]);

                    metric.bytesTotal = metric.bytesCached + metric.bytesExecuted;

                    if (row) {
                        row.lastMetric = metric;
                    }
                }


                // fire metricsUpdated callback before sorting
                Ext.callback(metricsUpdatedCallback, scope);


                // sort metrics
                rows.sort();


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
            rows = me.rows,
            rowsCount = rows.getCount(),
            primaryValueRenderer = mode == 'requests' ? me.requestsRenderer : me.bytesRenderer,
            responseTimeClsLevels = me.responseTimeClsLevels,
            responseTimeBarMax = Ext.Array.max(Ext.Object.getValues(responseTimeClsLevels)),
            responseTimeClasses = Ext.Object.getKeys(responseTimeClsLevels),
            totalRequests = 0,
            totalBytes = 0,
            maxResponseTime = 0,
            i, row, metric, responseTime, requests, bytes, cacheHitRatio, responseTimeCt;


        // initial loop to calculate totals
        for (i = 0; i < rowsCount; i++) {
            row = rows.getAt(i);
            metric = row.lastMetric;

            if (!metric) {
                continue;
            }

            totalRequests += metric.requests;
            totalBytes += metric.bytesTotal;
            maxResponseTime = Math.max(maxResponseTime, metric.responseTime);
        }

        maxResponseTime = Math.min(maxResponseTime, responseTimeBarMax);


        // follow-up loop to render changes to DOM
        for (i = 0; i < rowsCount; i++) {
            row = rows.getAt(i);
            metric = row.lastMetric;

            if (!metric) {
                continue;
            }

            requests = metric.requests;
            bytes = metric.bytesTotal;

            // update primary column (requests or bytes)
            row.primaryBarEl.setStyle('width',
                Math.round(
                    (
                        mode == 'requests' ?
                        requests / totalRequests :
                        bytes / totalBytes
                    ) * 100
                ) + '%'
            );
            row.primaryValueEl.update(
                primaryValueRenderer(mode == 'requests' ? requests : bytes)
            );
            row.primaryUnitEl.update(mode + ' / hour');

            // update response time column
            responseTime = metric.responseTime;
            responseTimeCt = row.responseTimeCt;
            row.responseTimeBarEl.setStyle('height', Math.min(Math.round(responseTime / maxResponseTime * 100), 100) + '%');
            row.responseTimeValueEl.update(responseTime || '&mdash;');
            responseTimeCt.removeCls(responseTimeClasses);
            if (responseTime) {
                responseTimeCt.addCls(me.getResponseTimeCls(responseTime));
            }

            // update cache hit ratio column
            cacheHitRatio = Math.round(
                (
                    mode == 'requests' ?
                    (requests ? metric.responsesCached / requests : 0) :
                    (bytes ? metric.bytesCached / bytes : 0)
                ) * 100
            );
            row.cacheHitRatioBarEl.setStyle('height', cacheHitRatio + '%');
            row.cacheHitRatioValueEl.update(cacheHitRatio.toString());
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