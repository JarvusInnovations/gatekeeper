/*jshint undef: true, unused: true, browser: true, curly: true*/
/*global Ext, google*/
Ext.define('Site.page.Status', {
    singleton: true,
    requires: [
        'Site.Common',
        'Ext.util.Collection',
        'Ext.Ajax'
    ],

    // template methods
    constructor: function() {
        var me = this,
            endpoints = me.endpoints = new Ext.util.Collection({
                keyFn: function(endpoint) {
                    return endpoint.ID;
                }
            });

        endpoints.add(window.SiteEnvironment && window.SiteEnvironment.gkEndpoints);

        Ext.onReady(me.onDocReady, me);
    },


    // event handlers
    onDocReady: function() {
        var me = this;

        if (!window.google) {
            window.alert('Failed to load Google charts library, charts will be unavailable');
            return;
        }

        google.load('visualization', '1', {
            packages: ['annotationchart', 'gauge'],
            callback: Ext.bind(me.onGoogleChartsReady, me)
        });
    },

    onGoogleChartsReady: function() {
        var me = this;

        me.initCacheStatus(function() {
            me.initTopRequestEndpoints(function() {
                me.initTopBytesEndpoints(function() {
                    me.initTopTimeEndpoints();
                });
            });
        });
    },


    // internal methods      
    bytes2megabytes: function(bytes) {
        return Math.round(bytes / 1024 / 1024);
    },

    initCacheStatus: function(callback) {
        var me = this,
            chart = new google.visualization.Gauge(
                Ext.fly('cache-status').appendChild({
                    cls: 'chart-gauge',
                    style: {
                        height: '300px'
                    },
                    html: 'Loading chart&hellip;'
                }, true)
            );

        Ext.Ajax.request({
            method: 'GET',
//            url: '/status/cache',
            url: 'http://developer.phila.gov/status/cache',
            withCredentials: true,
            headers: {
                Accept: 'application/json'
            },
            success: function(response) {
                var r = Ext.decode(response.responseText),
                    bytes2megabytes = me.bytes2megabytes,
                    totalBytes = r.total,
                    freeBytes = r.free,
                    responseBytes = r.responses,
                    usedBytes = totalBytes - freeBytes,
                    totalMegaBytes = bytes2megabytes(totalBytes);

                chart.draw(
                    google.visualization.arrayToDataTable([
                        ['Label', 'Value'],
                        ['Responses', bytes2megabytes(responseBytes)],
                        ['System', bytes2megabytes(usedBytes - responseBytes)],
                        ['Total', bytes2megabytes(usedBytes)]
                    ]),
                    {
                        max: totalMegaBytes,
                        redFrom: totalMegaBytes * 0.8, redTo: totalMegaBytes,
                        yellowFrom: totalMegaBytes * 0.6, yellowTo: totalMegaBytes * 0.8,
                        greenFrom: 0, greenTo: totalMegaBytes * 0.2,
                        minorTicks: 5
                    }
                );

                Ext.callback(callback, me);
            }
        });
    },

    initTopRequestEndpoints: function(callback) {
        var me = this,
            chart = new google.visualization.AnnotationChart(
                Ext.fly('top-endpoints-requests').appendChild({
                    cls: 'chart-timeline',
                    style: {
                        height: '400px'
                    },
                    html: 'Loading chart&hellip;'
                }, true)
            );

        Ext.Ajax.request({
            method: 'GET',
//            url: '/metrics/endpoints-historic',
            url: 'http://developer.phila.gov/metrics/endpoints-historic',
            withCredentials: true,
            params: {
                metrics: 'requests',
                'time-min': '1 month ago'
            },
            headers: {
                Accept: 'application/json'
            },
            success: function(response) {
                var endpoints = me.endpoints,
                    r = Ext.decode(response.responseText),
                    dataTable = new google.visualization.DataTable(),
                    records = r.data,
                    recordsLen = records.length,
                    i = 0,
                    pointsByTime = {},
                    endpointTotals = {},
                    topEndpoints = [],
                    record, time, endpointId, value, valuesByEndpoint, tableRow, topEndpointsLen;


                for (; i < recordsLen; i++) {
                    record = records[i];
                    time = record.Timestamp;
                    endpointId = record.EndpointID;
                    value = record.Value;

                    if (!(time in pointsByTime)) {
                        pointsByTime[time] = {};
                    }

                    pointsByTime[time][endpointId] = value;

                    if (!(endpointId in endpointTotals)) {
                        endpointTotals[endpointId] = 0;
                    }
                    
                    endpointTotals[endpointId] += value;
                }


                // build top endponits list
                topEndpoints = Ext.Object.getKeys(endpointTotals);
                topEndpoints = Ext.Array.sort(topEndpoints, function(a, b) {
                    a = endpointTotals[a];
                    b = endpointTotals[b];

                    if (a == b) {
                        return 0;
                    }
                    
                    return a < b ? 1 : -1;
                });
                topEndpoints = Ext.Array.slice(topEndpoints, 0, 5);
                topEndpointsLen = topEndpoints.length;

                // build columns list
                dataTable.addColumn('date', 'Date');
                for (i = 0; i < topEndpointsLen; i++) {
                    dataTable.addColumn('number', endpoints.getByKey(topEndpoints[i]).Title);
                }


                // load data
                for (time in pointsByTime) {
                    tableRow = [ new Date(time * 1000) ];
                    valuesByEndpoint = pointsByTime[time];

                    for (i = 0; i < topEndpointsLen; i++) {
                        tableRow.push(valuesByEndpoint[topEndpoints[i]] || 0);
                    }

                    dataTable.addRow(tableRow);
                }


                // render chart
                chart.draw(dataTable, {
                    displayZoomButtons: false,
                    numberFormats: '#,##0',
                    scaleFormat: '#,##0',
                    zoomStartTime: new Date(Date.now() - (1000 * 60 * 60 * 24 * 7)) // 1 week ago
                });


                Ext.callback(callback, me);
            }
        });
    },

    initTopBytesEndpoints: function(callback) {
        var me = this,
            chart = new google.visualization.AnnotationChart(
                Ext.fly('top-endpoints-bytes').appendChild({
                    cls: 'chart-timeline',
                    style: {
                        height: '400px'
                    },
                    html: 'Loading chart&hellip;'
                }, true)
            );

        Ext.Ajax.request({
            method: 'GET',
//            url: '/metrics/endpoints-historic',
            url: 'http://developer.phila.gov/metrics/endpoints-historic',
            withCredentials: true,
            params: {
                metrics: 'bytesExecuted',
                'time-min': '1 month ago'
            },
            headers: {
                Accept: 'application/json'
            },
            success: function(response) {
                var endpoints = me.endpoints,
                    r = Ext.decode(response.responseText),
                    dataTable = new google.visualization.DataTable(),
                    records = r.data,
                    recordsLen = records.length,
                    i = 0,
                    pointsByTime = {},
                    endpointTotals = {},
                    topEndpoints = [],
                    record, time, endpointId, value, valuesByEndpoint, tableRow, topEndpointsLen;


                for (; i < recordsLen; i++) {
                    record = records[i];
                    time = record.Timestamp;
                    endpointId = record.EndpointID;
                    value = record.Value;

                    if (!(time in pointsByTime)) {
                        pointsByTime[time] = {};
                    }

                    pointsByTime[time][endpointId] = value;

                    if (!(endpointId in endpointTotals)) {
                        endpointTotals[endpointId] = 0;
                    }
                    
                    endpointTotals[endpointId] += value;
                }


                // build top endponits list
                topEndpoints = Ext.Object.getKeys(endpointTotals);
                topEndpoints = Ext.Array.sort(topEndpoints, function(a, b) {
                    a = endpointTotals[a];
                    b = endpointTotals[b];

                    if (a == b) {
                        return 0;
                    }
                    
                    return a < b ? 1 : -1;
                });
                topEndpoints = Ext.Array.slice(topEndpoints, 0, 5);
                topEndpointsLen = topEndpoints.length;

                // build columns list
                dataTable.addColumn('date', 'Date');
                for (i = 0; i < topEndpointsLen; i++) {
                    dataTable.addColumn('number', endpoints.getByKey(topEndpoints[i]).Title);
                }


                // load data
                for (time in pointsByTime) {
                    tableRow = [ new Date(time * 1000) ];
                    valuesByEndpoint = pointsByTime[time];

                    for (i = 0; i < topEndpointsLen; i++) {
                        tableRow.push(valuesByEndpoint[topEndpoints[i]] || 0);
                    }

                    dataTable.addRow(tableRow);
                }


                // render chart
                chart.draw(dataTable, {
                    displayZoomButtons: false,
                    numberFormats: '#,##0',
                    scaleFormat: '#,##0',
                    zoomStartTime: new Date(Date.now() - (1000 * 60 * 60 * 24 * 7)) // 1 week ago
                });


                Ext.callback(callback, me);
            }
        });
    },

    initTopTimeEndpoints: function(callback) {
        var me = this,
            chart = new google.visualization.AnnotationChart(
                Ext.fly('top-endpoints-time').appendChild({
                    cls: 'chart-timeline',
                    style: {
                        height: '400px'
                    },
                    html: 'Loading chart&hellip;'
                }, true)
            );

        Ext.Ajax.request({
            method: 'GET',
//            url: '/metrics/endpoints-historic',
            url: 'http://developer.phila.gov/metrics/endpoints-historic',
            withCredentials: true,
            params: {
                metrics: 'responseTime',
                'time-min': '1 month ago'
            },
            headers: {
                Accept: 'application/json'
            },
            success: function(response) {
                var endpoints = me.endpoints,
                    r = Ext.decode(response.responseText),
                    dataTable = new google.visualization.DataTable(),
                    records = r.data,
                    recordsLen = records.length,
                    i = 0,
                    pointsByTime = {},
                    endpointTotals = {},
                    topEndpoints = [],
                    record, time, endpointId, value, valuesByEndpoint, tableRow, topEndpointsLen;


                for (; i < recordsLen; i++) {
                    record = records[i];
                    time = record.Timestamp;
                    endpointId = record.EndpointID;
                    value = record.Value;

                    if (!(time in pointsByTime)) {
                        pointsByTime[time] = {};
                    }

                    pointsByTime[time][endpointId] = value;

                    if (!(endpointId in endpointTotals)) {
                        endpointTotals[endpointId] = 0;
                    }
                    
                    endpointTotals[endpointId] += value;
                }


                // build top endponits list
                topEndpoints = Ext.Object.getKeys(endpointTotals);
                topEndpoints = Ext.Array.sort(topEndpoints, function(a, b) {
                    a = endpointTotals[a];
                    b = endpointTotals[b];

                    if (a == b) {
                        return 0;
                    }
                    
                    return a < b ? 1 : -1;
                });
                topEndpoints = Ext.Array.slice(topEndpoints, 0, 5);
                topEndpointsLen = topEndpoints.length;

                // build columns list
                dataTable.addColumn('date', 'Date');
                for (i = 0; i < topEndpointsLen; i++) {
                    dataTable.addColumn('number', endpoints.getByKey(topEndpoints[i]).Title);
                }


                // load data
                for (time in pointsByTime) {
                    tableRow = [ new Date(time * 1000) ];
                    valuesByEndpoint = pointsByTime[time];

                    for (i = 0; i < topEndpointsLen; i++) {
                        tableRow.push(valuesByEndpoint[topEndpoints[i]] || 0);
                    }

                    dataTable.addRow(tableRow);
                }


                // render chart
                chart.draw(dataTable, {
                    displayZoomButtons: false,
                    numberFormats: '#,##0',
                    scaleFormat: '#,##0',
                    zoomStartTime: new Date(Date.now() - (1000 * 60 * 60 * 24 * 7)) // 1 week ago
                });


                Ext.callback(callback, me);
            }
        });
    }
});