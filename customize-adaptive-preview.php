<?php
/**
 * Plugin Name: Customize Device Preview for RESS (Responsive Web Design + Server-Side Components)
 * Description: Replace responsive device preview in customizer with RESS device preview, refreshing preview with query param indicating the device being previewed so that server-side components for the device can be previewed.
 * Version: 0.1.0
 * Author: XWP
 * Author URI: https://make.xwp.co/
 *
 * Copyright (c) 2016 XWP (https://make.xwp.co/)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 *
 * @package Customize_Adaptive_Preview
 */

namespace Customize_Adaptive_Preview;

const DESKTOP_USER_AGENT = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.116 Safari/537.36';
const MOBILE_USER_AGENT = 'Mozilla/5.0 (Linux; Android 5.1.1; Nexus 6 Build/LYZ28E) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/48.0.2564.23 Mobile Safari/537.36';
const TABLET_USER_AGENT = 'Mozilla/5.0 (iPad; CPU OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1';

// Override user agent if query param specifies as early as possible.
if ( isset( $_GET['customize_previewed_device'] ) ) {
	if ( 'desktop' === $_GET['customize_previewed_device'] ) {
		$_SERVER['HTTP_USER_AGENT'] = wp_slash( DESKTOP_USER_AGENT );
	} elseif ( 'tablet' === $_GET['customize_previewed_device'] ) {
		$_SERVER['HTTP_USER_AGENT'] = wp_slash( TABLET_USER_AGENT );
	} elseif ( 'mobile' === $_GET['customize_previewed_device'] ) {
		$_SERVER['HTTP_USER_AGENT'] = wp_slash( MOBILE_USER_AGENT );
	}
}

/**
 * Init plugin.
 */
function init() {
	add_action( 'customize_controls_enqueue_scripts', __NAMESPACE__ . '\customize_controls_enqueue_scripts' );
	add_filter( 'jetpack_is_mobile', __NAMESPACE__ . '\filter_jetpack_is_mobile', 5, 3 );
}
add_action( 'plugins_loaded', __NAMESPACE__ . '\init' );

/**
 * Enqueue scripts for customizer controls (pane).
 */
function customize_controls_enqueue_scripts() {
	$version = false;
	if ( preg_match( '/Version:\s*(\S+)/', file_get_contents( __FILE__ ), $matches ) ) {
		$version = $matches[1];
	}
	$handle = 'customize-adaptive-preview-controls';
	$src = plugin_dir_url( __FILE__ ) . '/customize-adaptive-preview-controls.js';
	$deps = array( 'customize-controls' );
	wp_enqueue_script( $handle, $src, $deps, $version );
	wp_add_inline_script( $handle, 'CustomizeAdaptivePreview.init( wp.customize );', 'after' );
}

/**
 * Filter the value of jetpack_is_mobile before it is calculated.
 *
 * This is needed because the jetpack_is_mobile function sets
 *
 * @param bool|string $matches      Boolean if current UA matches $kind or not. If $return_matched_agent is true, should return the UA string.
 * @param string      $kind         Category of mobile device being checked.
 * @param bool        $return_agent Boolean indicating if the UA should be returned.
 * @returns bool|string Whether matches or user agent.
 */
function filter_jetpack_is_mobile( $matches, $kind, $return_agent ) {
	unset( $kind );
	if ( isset( $_GET['customize_previewed_device'] ) && 'mobile' === $_GET['customize_previewed_device'] ) {
		$matches = $return_agent ? MOBILE_USER_AGENT : true;
	}
	return $matches;
}

/**
 * Get current device.
 *
 * Obtain the device that is being used, whether by customizer preview or by device user agent.
 *
 * @return string Device.
 */
function get_current_device() {
	$previewed_device = 'desktop';

	if ( class_exists( 'Jetpack_User_Agent_Info' ) ) {
		if ( \Jetpack_User_Agent_Info::is_tablet() ) {
			$previewed_device = 'tablet';
		} elseif ( jetpack_is_mobile() ) {
			$previewed_device = 'mobile';
		}
	} elseif ( wp_is_mobile() ) {
		$previewed_device = 'mobile';
	}
	return $previewed_device;
}
