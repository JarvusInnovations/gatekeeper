/*jshint undef: true, unused: true, browser: true, curly: true*/
/*global Ext*/
Ext.define('Site.page.Docs', {
    singleton: true,
    requires: [
        'Ext.util.Collection'
    ],

    config: {
        currentTocItem: null
    },


    // template methods
    constructor: function() {
        var me = this;

        me.toc = new Ext.util.Collection();

        Ext.onReady(me.onDocReady, me);
    },


    // config handlers
    updateCurrentTocItem: function(currentToc, oldCurrentToc) {
        if (oldCurrentToc) {
            oldCurrentToc.linkEl.removeCls('current');
        }

        if (currentToc) {
            currentToc.linkEl.addCls('current');
        }
    },
    

    // event handlers
    onDocReady: function() {
        var me = this,
            toc = me.toc,
            tocTops = me.tocTops = [],
            tocEl;

        me.tocEl = tocEl = Ext.getBody().down('.docs-toc');
        tocEl.select('a[href^="#"]').each(function(linkEl) {
            var targetId = linkEl.dom.hash.substr(1),
                targetEl = Ext.get(targetId),
                targetTop = targetEl && targetEl.getTop();

            if (!targetEl) {
                return;
            }

            toc.add({
                id: targetId,
                linkEl: Ext.get(linkEl),
                targetEl: targetEl,
                targetTop: targetTop
            });
            tocTops.push(targetTop);
        });

        me.syncTocFromScroll();
        Ext.get(document).on('scroll', 'onDocumentScroll', me);
    },
    
    onDocumentScroll: function(ev, t) {
        this.syncTocFromScroll();
    },


    // public methods
    getTocFromY: function(y) {
        var me = this,
            tocTops = me.tocTops,
            i = 1, tocLen = tocTops.length, tocTop;

        for (; i < tocLen; i++) {
            tocTop = tocTops[i];
            if (y < tocTop - 25) {
                return me.toc.getAt(i - 1);
            }
        }

        return null;
    },

    syncTocFromScroll: function() {
        var me = this,
            toc = me.getTocFromY(Ext.getBody().getScroll().top);

        me.setCurrentTocItem(toc);
    }
});