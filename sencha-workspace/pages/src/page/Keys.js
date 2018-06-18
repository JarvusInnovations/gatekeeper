/*jshint undef: true, unused: true, browser: true, curly: true*/
/*global Ext*/
Ext.define('Site.page.Keys', {
    extend: 'Site.abstractpage.TrafficStack',
    singleton: true,

    idProperty: 'KeyID',
    metricsUrl: '/metrics/keys-current'
});