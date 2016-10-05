/* global jQuery */
/* exported CustomizeAdaptivePreview */
var CustomizeAdaptivePreview = ( function( $ ) {

	var component = {
		api: null
	};

	/**
	 * Add callbacks to previewUrl and previewedDevice values.
	 *
	 * @params {object} api Instance of wp.customize.
	 * @returns {void}
	 */
	component.init = function init( api ) {
		component.api = api;
		api.bind( 'ready', component.ready );
	};

	/**
	 * Add callbacks to previewUrl and previewedDevice values.
	 *
	 * @params {object} api Instance of wp.customize.
	 * @returns {void}
	 */
	component.ready = function ready() {
		component.api.previewer.previewUrl.setter( component.amendUrlWithPreviewedDevice );
		component.api.previewedDevice.bind( function() {
			var url = component.amendUrlWithPreviewedDevice( component.api.previewer.previewUrl.get() );
			component.api.previewer.previewUrl.set( url );
		} );
	};

	/**
	 * Parse query string.
	 *
	 * @param {string} queryString Query string.
	 * @returns {object}
	 */
	component.parseQueryString = function parseQueryString( queryString ) {
		var queryParams = {};
		_.each( queryString.split( '&' ), function( pair ) {
			var parts, name, value = null;
			parts = pair.split( '=', 2 );
			if ( parts[0] ) {
				name = decodeURIComponent( parts[0] );
				if ( ! _.isUndefined( parts[1] ) ) {
					value = decodeURIComponent( parts[1] );
				}
				queryParams[ name ] = value;
			}
		} );
		return queryParams;
	};

	/**
	 * Amend the given URL with a customize_previewed_device query parameter.
	 *
	 * @param {string} url URL.
	 * @returns {string} URL with customize_previewed_device query param amended.
	 */
	component.amendUrlWithPreviewedDevice = function amendUrlWithPreviewedDevice( url ) {
		var urlParser, queryParams;
		urlParser = document.createElement( 'a' );
		urlParser.href = url;
		queryParams = component.parseQueryString( urlParser.search.substr( 1 ) );
		queryParams.customize_previewed_device = component.api.previewedDevice.get();
		urlParser.search = $.param( queryParams );
		return urlParser.href;
	};

	return component;

} ( jQuery ));
