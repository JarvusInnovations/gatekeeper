/*jslint browser: true, undef: true *//*global Ext*/
Ext.define('Site.widget.model.Ban', {
    extend: 'Site.widget.model.AbstractModel',
    singleton: true,
    alias: [
        'modelwidget.Ban'
    ],

    collectionTitleTpl: 'Bans',

    tpl: [
        '<a href="/bans/{ID}" class="link-model link-ban">',
            '<strong class="result-title">#{ID} &mdash; <tpl if="IP">IP: {[this.long2ip(values.IP)]}<tpl else>Key</tpl></strong> ',
            '<tpl if="ExpirationDate"><span class="result-info">Expires {[this.formatTimestamp(values.ExpirationDate)]}</strong></tpl>',
        '</a>',
        {
            long2ip: function(ip) {
                return [ip >>> 24, ip >>> 16 & 0xFF, ip >>> 8 & 0xFF, ip & 0xFF].join('.');
            },
            formatTimestamp: function(date) {
                return Ext.util.Format.date(new Date(date*1000));
            }
        }
    ]
});