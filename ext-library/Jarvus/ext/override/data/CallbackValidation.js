/*jslint browser: true, undef: true *//*global Ext*/
/**
 * Provides a "callback" validation type that accepts a custom function
 */
Ext.define('Jarvus.ext.override.data.CallbackValidation', {
    override: 'Ext.data.validations',

    callback: function(config, value) {
        return config.validate(value, config);
    }
}, function() {
    //<debug>
    if (!Ext.getVersion().match('4.2.2.1144')) {
        console.warn('This override has not been tested with this framework version');
    }
    //</debug>
});