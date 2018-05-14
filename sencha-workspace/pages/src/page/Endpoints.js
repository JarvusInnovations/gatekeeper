/*jshint undef: true, unused: true, browser: true, curly: true*/
/*global Ext*/
Ext.define('Site.page.Endpoints', {
    extend: 'Site.abstractpage.TrafficStack',
    singleton: true,

    idProperty: 'EndpointID',
    metricsUrl: '/metrics/endpoints-current'
});