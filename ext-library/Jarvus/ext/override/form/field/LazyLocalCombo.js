Ext.define('Jarvus.ext.override.form.field.LazyLocalCombo', {
    override: 'Ext.form.field.ComboBox',
    requires: ['Jarvus.ext.override.data.StoreIsLoaded'],

    lazyAutoLoad: true,

    doQuery: function() {
        this.doLazyLoad();
        this.callParent(arguments);
    },
    
    setValue: function(){
        this.callParent(arguments);
        this.doLazyLoad();
    },
    
    doLazyLoad: function() {
        var me = this,
            store = me.getStore();

        if (me.queryMode == 'local' && me.lazyAutoLoad && !store.isLoading() && !store.isLoaded()) {
            store.load();
        }
    }
});