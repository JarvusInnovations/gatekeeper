/*jshint undef: true, unused: true, browser: true, curly: true*/
/*global Ext*/
Ext.define('Site.page.Keys', {
    extend: 'Site.abstract.TrafficStackPage',
    singleton: true,

    idProperty: 'EndpointID',
    metricsUrl: '/metrics/endpoints-current'
});