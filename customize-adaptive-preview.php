<?php
/**
 * Plugin Name: Customize Adaptive Preview
 * Description: Replace responsive device preview in customizer with adaptive preview, refreshing preview with query param indicating the device being previewed.
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

/**
 * Check for dependencies.
 */
function check_for_dependencies() {
	if ( ! function_exists( 'jetpack_is_mobile' ) ) {
		esc_html_e( 'Missing Jetpack dependency.', 'customize-adaptive-preview' );
		trigger_error( 'missing_jetpack_dependency', E_USER_ERROR );
	}
}
register_activation_hook( __FILE__, __NAMESPACE__ . '\check_for_dependencies' );

/**
 * Init plugin.
 */
function init() {
	if ( ! function_exists( 'jetpack_is_mobile' ) ) {
		add_action( 'admin_notices', __NAMESPACE__ . '\print_missing_dependency_admin_notice' );
		return;
	}
	add_action( 'after_setup_theme', __NAMESPACE__ . '\set_content_width' );
	add_action( 'customize_controls_enqueue_scripts', __NAMESPACE__ . '\customize_controls_enqueue_scripts' );
}
add_action( 'plugins_loaded', __NAMESPACE__ . '\init' );

/**
 * Print missing dependency admin notice.
 */
function print_missing_dependency_admin_notice() {
	?>
	<div class="error">
		<p><?php esc_html_e( 'Missing Jetpack dependency for Customize Adaptive Preview.', 'customize-adaptive-preview' ); ?></p>
	</div>
	<?php
}

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
 * Get current device.
 *
 * Obtain the device that is being used, whether by customizer preview or by device user agent.
 *
 * @return string Device.
 */
function get_current_device() {
	$previewed_device = null;
	if ( isset( $_GET['customize_previewed_device'] ) ) {
		$previewed_device = sanitize_key( wp_unslash( $_GET['customize_previewed_device'] ) );
	}
	if ( empty( $previewed_device ) ) {
		if ( jetpack_is_mobile( 'mobile' ) ) {
			$previewed_device = 'mobile';
		} elseif ( jetpack_is_mobile( 'tablet' ) ) {
			$previewed_device = 'tablet';
		} else {
			$previewed_device = 'desktop';
		}
	}
	return $previewed_device;
}

/**
 * Set content width.
 */
function set_content_width() {
	global $content_width;
	$previewed_device = get_current_device();
	if ( 'tablet' === $previewed_device ) {
		$content_width = 768;
	} elseif ( 'mobile' === $previewed_device ) {
		$content_width = 320;
	}
}
