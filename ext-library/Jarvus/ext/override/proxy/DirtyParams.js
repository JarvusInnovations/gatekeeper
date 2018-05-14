/*jslint browser: true, undef: true *//*global Ext*/
Ext.define('Jarvus.ext.override.proxy.DirtyParams', {
    override: 'Ext.data.proxy.Server',

    extraParamsDirty: false,

    setExtraParam: function(name, value) {
        var extraParams = this.extraParams;

        if (extraParams[name] !== value) {
            this.markParamsDirty();
            extraParams[name] = value;
        }
    },

    markParamsDirty: function() {
        this.extraParamsDirty = true;
    },

    isExtraParamsDirty: function() {
        return this.extraParamsDirty;
    },

    buildRequest: function() {
        this.extraParamsDirty = false;
        return this.callParent(arguments);
    }
});