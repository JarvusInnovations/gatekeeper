/**
 * Fixes issue where tagfield fails to refilter store when value changes for filterPickList==true
 * 
 * See http://www.sencha.com/forum/showthread.php?288294-tagfield-config-filterPickList-implementation-partly-broken&p=1053510
 * Test case https://fiddle.sencha.com/#fiddle/7ga
 */
Ext.define('Jarvus.ext.patch.form.field.TagFieldFilterPickList', {
    override: 'Ext.form.field.Tag',
    
    onValueStoreRemove: function() {
        // do nothing, onValueStoreChange will handle it...
    },
    
    onValueStoreChange: function() {
        var me = this;

        if (me.filterPickList) {
            me.store.filter(me.selectedFilter);
        }

        me.applyMultiselectItemMarkup();
    }
}, function() {
    //<debug>
    if (!Ext.getVersion().match('5')) {
        console.warn('This patch has not been tested with this version of ExtJS');
    }
    //</debug>
});