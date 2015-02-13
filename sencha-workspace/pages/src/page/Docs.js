/*jshint undef: true, unused: true, browser: true, curly: true*/
/*global Ext, Prism*/
Ext.define('Site.page.Docs', {
    singleton: true,
    requires: [
        'Ext.util.Collection',
        'Ext.String',
        'Ext.XTemplate'
    ],

    config: {
        currentTocItem: null
    },

    tryItOutTpl: [
        '<h1>Response ({status_code}):</h1>',
        '{status_reason}',
        '<dl>',
        '   <tpl foreach="headers">',
        '       <dt>{$}</dt>',
        '       <dd>{.}</dd>',
        '   </tpl>',
        '</dl>',
        '<h2>Body</h2>',
        '<pre class="response-body language-{language}">{body}</pre>'
    ],
    
    pathParamRe: /\{(\w+)\}/,
    numberTypeRe: /number|integer/,
    
    httpErrorReasons: {
        400: 'Bad Request',
        401: 'Unauthorized',
        402: 'Payment Required',
        403: 'Forbidden',
        404: 'Not Found',
        405: 'Method Not Allowed',
        406: 'Not Acceptable',
        407: 'Proxy Authentication Required',
        408: 'Request Timeout',
        409: 'Conflict',
        410: 'Gone',
        411: 'Length Required',
        412: 'Precondition Failed',
        413: 'Payload Too Large',
        414: 'URI Too Long',
        415: 'Unsupported Media Type',
        416: 'Range Not Satisfiable',
        417: 'Expectation Failed',
        422: 'Unprocessable Entity',
        423: 'Locked',
        424: 'Failed Dependency',
        425: 'Unassigned',
        426: 'Upgrade Required',
        427: 'Unassigned',
        428: 'Precondition Required',
        429: 'Too Many Requests',
        430: 'Unassigned',
        431: 'Request Header Fields Too Large',
        500: 'Internal Server Error',
        501: 'Not Implemented',
        502: 'Bad Gateway',
        503: 'Service Unavailable',
        504: 'Gateway Timeout',
        505: 'HTTP Version Not Supported',
        506: 'Variant Also Negotiates',
        507: 'Insufficient Storage',
        508: 'Loop Detected',
        509: 'Unassigned',
        510: 'Not Extended',
        511: 'Network Authentication Required'
    },

    // template methods
    constructor: function() {
        Ext.onReady(this.onDocReady, this);
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
        var me = this;

        me.initializeToc();
        me.initializeTryItOut();

        Ext.get(document).on('scroll', 'onDocumentScroll', me);
    },
    
    onDocumentScroll: function(ev, t) {
        this.syncTocFromScroll();
    },


    // public methods
    initializeToc: function() {
        var me = this,
            toc = me.toc = new Ext.util.Collection(),
            tocTops = me.tocTops = [];

        Ext.getBody().down('.docs-toc').select('a[href^="#"]').each(function(linkEl) {
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
    },

    initializeTryItOut: function() {
        var me = this,
            tryItOutTpl = Ext.XTemplate.getTpl(me, 'tryItOutTpl'),
            docsCt = Ext.getBody().down('.endpoint-docs'),
            apiSchemes = docsCt.getAttribute('data-schemes').split(','),
            apiHost = docsCt.getAttribute('data-host'),
            apiBasePath = docsCt.getAttribute('data-basepath'),
            paramCollectionSeperators = {
                csv: ',',
                ssv: ' ',
                tsv: '\t',
                pipes: '|'
            },
            paramInputTypes = {
                number: 'number',
                integer: 'number',
                string: 'text',
                file: 'file',
                'boolean': 'checkbox'
            };

        // wire test consoles
        Ext.select('.endpoint-path-method', true).each(function(pathMethodEl) {
            var btn = pathMethodEl.appendChild({
                    tag: 'button',
                    html: 'Try it out!'
                }),
                method = pathMethodEl.getAttribute('data-method'),
                path = pathMethodEl.up('.endpoint-path').getAttribute('data-path'),
                containerEl = pathMethodEl.appendChild({
                    cls: 'response-container'
                }),
                tableEl = pathMethodEl.down('.endpoint-path-method-parameters table');
                
            tableEl.down('thead td').insertSibling({tag: 'td', html: 'Value'}, 'after');

            tableEl.select('tbody > tr').each(function(rowEl) {
                var type = rowEl.getAttribute('data-type'),
                    inputConfig = {
                        tag: 'input',
                        type: paramInputTypes[type]
                    },
                    collectionFormat;
                
                if (type === 'array') {
                    // how will we differentiate between types of the items, they take the same arguments as the parameter type. it may make more sense to allow you to [+] and [-] objects
                    // we could run the same validation routines possibly.
                    inputConfig.placeholder = ['item1', 'item2', '...'].join((paramCollectionSeperators[rowEl.getAttribute('data-collectionformat') || 'csv'])); 
                }
                
                rowEl.down('td').insertSibling(inputConfig, 'after');
            });

            btn.on('click', function (ev, t) {
                var parameters = {
                        path: {},
                        query: {}
                    }, 
                    language = '',
                    firstErrorEl;
                
                pathMethodEl.select('.endpoint-path-method-parameters tbody > tr').each(function(rowEl) {
                    var inputEl = rowEl.down('input'),
                        isEmpty = (inputEl.dom.value === ''),
                        paramIn = rowEl.getAttribute('data-in'),
                        val;
                        
                    if (isEmpty && (rowEl.getAttribute('data-required') || paramIn === 'path')) {
                        inputEl.addCls('invalid');
                        
                        if (!firstErrorEl) {
                            firstErrorEl = Ext.get(inputEl);
                        }
                    } else if (!isEmpty) {
                        val = inputEl.dom.value;
                        
                        if (rowEl.getAttribute('data-type') === 'boolean') {
                            val = inputEl.dom.checked ? 'true': 'false';    
                        }
                        
                        parameters[paramIn][rowEl.getAttribute('data-name')] = val;
                    }
                });
                
                if (firstErrorEl) {
                    firstErrorEl.dom.focus();
                
                    pathMethodEl.on('blur', function(ev, t) {
                        Ext.fly(t).toggleCls('invalid', t.value === '');
                    }, null, { delegate: 'input.invalid' });
                    
                    return;
                }
                
                Ext.Ajax.request({
                    method: method,
                    url: apiSchemes[0] + '://' + apiHost + apiBasePath + me.populatePlaceholders(path, parameters.path),
                    params: parameters.query,
                    callback: function(options, success, response) {
                        var headers = response.getAllResponseHeaders(),
                            body = response.responseText;
                            
                        if (/(x|ht)ml/.test(headers['content-type'])) {
                            language = 'markup';
                            body = me.prettyPrintXML(body);
                        } else if (headers['content-type'].indexOf('json') !== -1) {
                            try {
                                body = JSON.stringify(JSON.parse(body), null, '   ');
                                language = 'javascript';
                            } catch(e) {
                                body = response.responseText;
                            }
                        }
                        
                        if (language !== '') {
                            body = Prism.highlight(body, Prism.languages[language]);
                        } else {
                            body = Ext.util.Format.htmlEncode(body);
                        }
                        
                        tryItOutTpl.overwrite(containerEl, {
                            body: body,
                            headers: headers,
                            language: language,
                            status_code: response.status,
                            status_reason: success ? (response.statusText || 'OK') : (response.statusText || me.httpErrorReasons[response.status] || 'Error')
                        });
                    },
                    disableCaching: true
                });
            });
        });
    },

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
    },
    
    populatePlaceholders: function (template, values) {
        var pathParamRe = this.pathParamRe,
            match;

        while ((match = pathParamRe.exec(template)) !== null) {
            template = template.replace(match[0], values[match[1]]);
        }

        return template;
    },

    prettyPrintXML: function formatXml(xml) {
        var formatted = '',
            reg = /(>)(<)(\/*)/g,
            pad = 0,
            nodes;
            
        xml = xml.toString().replace(reg, '$1\r\n$2$3');
        nodes = xml.split('\r\n');
        
        nodes.forEach(function(node) {
            var indent = 0,
                paddinng;
            
            if (node.match(/.+<\/\w[^>]*>$/)) {
                indent = 0;
            } else if (node.match(/^<\/\w/)) {
                if (pad !== 0) {
                    pad -= 1;
                }
            } else if (node.match(/^<\w[^>]*[^\/]>.*$/)) {
                indent = 1;
            } else {
                indent = 0;
            }
    
            formatted += Ext.String.repeat('   ', pad) + node + '\r\n';
            pad += indent;
        });
        
        return formatted;
    }
});