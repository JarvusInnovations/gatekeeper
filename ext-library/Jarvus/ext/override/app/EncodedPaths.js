/*jslint browser: true, undef: true, white: false, laxbreak: true *//*global Ext*/
Ext.define('Jarvus.ext.override.app.EncodedPaths', {
	override: 'Ext.app.Controller'
	
	
	/**
	 * URL-encode any characters that would could fail to pass through a hash path segment
	 
	 * @param {String} string The string to encode
	 * @return {String} The encoded string
	 */
	,encodeRouteComponent: function(string) {
		return encodeURIComponent(string||'').replace(/\(/g, '%28').replace(/\)/g, '%29').replace(/%20/g, '+');
	}
	
	/**
	 * URL-decode any characters that encodeRouteComponent encoded
	 
	 * @param {String} string The string to decode
	 * @return {String} The decoded string
	 */
	,decodeRouteComponent: function(string) {
		return decodeURIComponent((string||'').replace(/\+/g, ' '));
	}
});