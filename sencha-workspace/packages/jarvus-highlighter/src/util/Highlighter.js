/* jshint undef: true, unused: true, browser: true, quotmark: single, curly: true */
/* global Ext */
Ext.define('Jarvus.util.Highlighter', {
    singleton: true,

    /**
     * Wraps every occurance of a specified string with <mark> tags
     * @param {Ext.Element} containerEl The container to search within for occurances of given string
     * @param {String} string The string to find and highlight
     */
    highlight: function(containerEl, string) {
        var stringLength = string.length,
            nodes = Ext.getDom(containerEl).childNodes,
            nodeIndex = 0, nodesLength = nodes.length;

        string = string.toUpperCase();

        if (nodesLength && stringLength) {
            for (; nodeIndex < nodesLength; nodeIndex++) {
                _innerHighlight(nodes[nodeIndex], string);
            }
        }

        function _innerHighlight(node, string) {
            var skip = 0,
                childNodeIndex = 0,
                stringPosition, markNode, middleBit, middleClone;

            if (node.nodeType == 3) {
                stringPosition = node.data.toUpperCase().indexOf(string);

                if (stringPosition >= 0) {
                    markNode = document.createElement('mark');
                    middleBit = node.splitText(stringPosition);
                    middleBit.splitText(stringLength);
                    middleClone = middleBit.cloneNode(true);

                    markNode.appendChild(middleClone);
                    middleBit.parentNode.replaceChild(markNode, middleBit);
                    skip = 1;
                }
            } else if (node.nodeType == 1 && node.childNodes && !/(script|style)/i.test(node.tagName)) {
                for (; childNodeIndex < node.childNodes.length; childNodeIndex++) {
                    childNodeIndex += _innerHighlight(node.childNodes[childNodeIndex], string);
                }
            }

            return skip;
        }
    },

    /**
     * Remove all <mark> elements
     * @param {Ext.Element} containerEl The container to search within for occurances of given string
     */
    removeHighlights: function(containerEl) {
        var markNodes = Ext.getDom(containerEl).querySelectorAll('mark'),
            markNodesLength = markNodes.length,
            markNodeIndex = 0,
            markNode, parentNode;

        for (; markNodeIndex < markNodesLength; markNodeIndex++) {
            markNode = markNodes[markNodeIndex];
            parentNode = markNode.parentNode;
            parentNode.replaceChild(markNode.firstChild, markNode);
            parentNode.normalize();
        }
    }
});