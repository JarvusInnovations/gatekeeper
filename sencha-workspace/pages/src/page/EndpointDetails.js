/*jshint undef: true, unused: true, browser: true, curly: true*/
/*global Ext*/
Ext.define('Site.page.EndpointDetails', {
    singleton: true,
    requires: [
        'Ext.Ajax'
    ],

    // template methods
    constructor: function() {
        var me = this;

        Ext.onReady(me.onDocReady, me);
    },


    // event handlers
    onDocReady: function() {
        var me = this,
            docsCt = me.docsCt = Ext.get('endpoint-docs'),
            uploadForm = me.uploadForm = docsCt.down('.swagger-uploader');

        // setup query expand toggle for request tables
        // TODO: move to a global library somewhere, maybe use delegate body listener
        Ext.select('.col-options', true).on('click', function(ev, t) {
            var opt = Ext.get(t);
            opt.radioCls('selected');
            opt.up('table').toggleCls('query-expand');
        }, null, { delegate: '.col-option' });


        // setup dropping swagger file
        if (uploadForm) {
            docsCt.on({
                scope: me,
                dragover: me.onDocsDragOver,
                dragleave: me.onDocsDragLeave,
                drop: me.onDocsDrop
            });
        }
    },

    onDocsDragOver: function(ev, t) {
        ev.stopEvent();
        ev.browserEvent.dataTransfer.dropEffect = 'copy';
        this.docsCt.addCls('file-drag-over');
    },

    onDocsDragLeave: function(ev, t) {
        this.docsCt.removeCls('file-drag-over');
    },

    onDocsDrop: function(ev, t) {
        var me = this,
            docsCt = me.docsCt,
            xhr = new XMLHttpRequest(),
            file = ev.event.dataTransfer.files[0];

        ev.stopEvent();
        docsCt.removeCls('file-drag-over');

        if (!file.name.match(/\.ya?ml$/)) {
            window.alert('Dropped file must end with .yaml');
            return;
        }

        docsCt.addCls('uploading');

        xhr.open('PUT', me.uploadForm.dom.action);
        xhr.onload = Ext.bind(me.onDocsUploadFinished, me);
//        xhr.onprogress = Ext.bind(me.onDocsUploadProgress, me);
        xhr.send(file);
    },

    onDocsUploadFinished: function(e) {
        this.docsCt.removeCls('uploading');

        if(e.currentTarget.status == 201 || e.currentTarget.status == 204) {
            window.alert('Upload successful!');
            location.reload();
        } else {
            window.alert('Failed to upload file');
        }
    }
});