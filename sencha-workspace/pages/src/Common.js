/*jslint browser: true, undef: true *//*global Ext*/
Ext.define('Site.Common', {
    singleton: true,
    requires: [
        'Site.widget.Search',
        'Site.widget.model.Person',
        'Site.widget.model.Endpoint',
        'Site.widget.model.Key',
        'Site.widget.model.Ban'
    ],

    constructor: function() {
        Ext.onReady(this.onDocReady, this);
    },

    onDocReady: function() {
        var me = this,
            body = Ext.getBody();

        // site search
        me.siteSearch = Ext.create('Site.widget.Search', {
            searchForm: body.down('.search-form.site-search')
        });
    }
});