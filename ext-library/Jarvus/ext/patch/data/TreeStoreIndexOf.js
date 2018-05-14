/**
 * Fixes error when destroying a model attached to a TreeStore
 * 
 * See http://www.sencha.com/forum/showthread.php?268809
 */
Ext.define('Jarvus.ext.patch.data.TreeStoreIndexOf', {
    override: 'Ext.data.TreeStore',
    
    indexOf: function(record) {
        return null;
    }

}, function() {
    //<debug>
    if (!Ext.getVersion().match('4.2.2.1144')) {
        console.warn('This patch has not been tested with this version of ExtJS');
    }
    //</debug>
});