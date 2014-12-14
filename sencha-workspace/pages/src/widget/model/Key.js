/*jslint browser: true, undef: true *//*global Ext*/
Ext.define('Site.widget.model.Key', {
    extend: 'Site.widget.model.AbstractModel',
    singleton: true,
    alias: [
        'modelwidget.Gatekeeper\\Key'
    ],

    collectionTitleTpl: 'Keys',

    tpl: [
        '<a href="/keys/{Key}" class="link-model link-key">',
            '<strong class="result-title">{OwnerName:htmlEncode}</strong> ',
            '<span class="result-info">{Key}</strong>',
        '</a>'
    ]
});