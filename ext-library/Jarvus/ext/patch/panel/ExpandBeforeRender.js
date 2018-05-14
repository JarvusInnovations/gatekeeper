Ext.define('Jarvus.ext.patch.panel.ExpandBeforeRender', {
    override: 'Ext.panel.Panel',
    
    expand: function() {
        var me = this;
        
        if (me.rendered) {
            return me.callOverridden(arguments);
        } else {
            if (me.fireEvent('beforeexpand', me) !== false) {
                me.collapsed = false;
                me.fireEvent('expand', me);
            }

            return me;
        }
    }
});