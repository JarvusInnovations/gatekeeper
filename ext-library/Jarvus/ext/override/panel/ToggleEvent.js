Ext.define('Jarvus.ext.override.panel.ToggleEvent', {
    override: 'Ext.panel.Panel',

    toggleCollapse: function() {
        var me = this,
            isExpanding = (me.collapsed || me.floatedFromCollapse),
            result;

        if (me.fireEvent('beforetoggle', me, isExpanding) === false) {
            return;
        }

        result = this.callParent(arguments);

        me.fireEvent('toggle', me, isExpanding, result);
    }
});