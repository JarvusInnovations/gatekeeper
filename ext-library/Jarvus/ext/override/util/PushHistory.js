/*jslint browser: true, undef: true, white: false, laxbreak: true *//*global Ext*/

/**
 * Provides {@link #method-pushPath} for controllers
 */
Ext.define('Jarvus.ext.override.util.PushHistory', {
    override: 'Ext.util.History'

    ,pageTitleSeparator: ' &mdash; '

    /**
     * Silently push a given path to the address bar without triggering a routing event.
     * This is useful to call after a user has _already_ entered a UI state and the current address
     * _may_ need to be synchronized. If the given path was already in the address bar this method
     * has no effect.
     *
     * @param {String/String[]/Ext.data.Model} url The url path to push
     */
    ,pushPath: function(url, title) {
        var me = this
            ,titleDom = me.pageTitleDom
            ,titleBase = me.pageTitleBase;

        if(title) {
            if(!titleDom) {
                titleDom = me.pageTitleDom = document.querySelector('title');
                titleBase = me.pageTitleBase = titleDom.innerHTML;
            }

            titleDom.innerHTML = title + me.pageTitleSeparator + titleBase;
        }

        Ext.util.History.add(url, true);
    }
});