/**
 * Fixes issue where a qtip set in a treepanel bleeds out into all other grid rendering
 * 
 * See http://www.sencha.com/forum/showthread.php?260302-4.2-Unwanted-data-qtip-injected-into-all-GridPanel-rows&p=1048779&viewfull=1#post1048779
 * Test case https://fiddle.sencha.com/#fiddle/6ia
 */
Ext.define('Jarvus.ext.patch.grid.ResetTipAttributes', {
    override: 'Ext.view.Table',
    
    renderRow: function() {
        var me = this;

        me.rowValues.rowAttr = {};

        return me.callParent(arguments);
    }
}, function() {
    //<debug>

    // 5.0.0.736 needs this and .970 doesn't, but neither reports itself as anything but "5"
    if (!Ext.getVersion().match('4.2.2.1144') && !Ext.getVersion().match('5')) {
        console.warn('This patch has not been tested with this version of ExtJS');
    }

    //</debug>
});