Ext.define('Jarvus.ext.override.data.StoreIsLoaded', {
    override: 'Ext.data.Store',

    constructor: function() {
        var me = this;
        
        me.callParent(arguments);

        me.on('load', function() {
            me.loaded = true;
        });
    },

    loadData: function() {
        this.callParent(arguments);
        this.loaded = true;
    },

    /**
     * Returns true if the Store is currently performing a load operation
     * @return {Boolean} True if the Store is currently loading
     */
    isLoading: function() {
        return Boolean(this.loading);
    },

    /**
     * Returns true if the Store has been loaded.
     * @return {Boolean} True if the Store has been loaded
     */
    isLoaded: function() {
        return Boolean(this.loaded);
    }
});