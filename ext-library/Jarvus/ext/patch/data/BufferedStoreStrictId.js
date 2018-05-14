/**
 * Fixes issue where grouped+buffered stores fail to find correct record on hover/click due to string===int comparison
 * 
 * See http://www.sencha.com/forum/showthread.php?273049
 */
Ext.define('Jarvus.ext.patch.data.BufferedStoreStrictId', {
    override: 'Ext.data.Store',
    
    getByInternalId: function(internalId) {
        var result;

        if (this.buffered) {
            result = (this.snapshot || this.data).findBy(function(record) {
                return record.internalId == internalId; // changed from === to ==
            });
            //<debug>
            if (!result) {
                Ext.Error.raise('getByInternalId called for internalId that is not present in local cache');
            }
            //</debug>
        } else {
            result = this.data.get(internalId);
        }
        return result;
    }

}, function() {
    //<debug>
    if (!Ext.getVersion().match('4.2.2.1144')) {
        console.warn('This patch has not been tested with this version of ExtJS');
    }
    //</debug>
});