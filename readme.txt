=== Customizer Responsive Server-Side Components Device Preview ===
Contributors:      xwp, westonruter
Tags:              customizer, customize, responsive, rwd, adaptive, ress, preview
Requires at least: 4.5
Tested up to:      4.7-alpha
Stable tag:        0.1.0
License:           GPLv2 or later
License URI:       http://www.gnu.org/licenses/gpl-2.0.html

Extend device preview in customizer with previewing server-side components, ensuring the previewed device's user agent is set when the site refreshes

== Description ==

This plugin extends the responsive device preview functionality in the customizer which was added in 4.5 (see [#31195](https://core.trac.wordpress.org/ticket/31195)). In core when you change the previewed device it merely changes the dimensions of the preview, allowing you to simulate how the theme will appear on tablet or mobile screens. What it does not do, however, is cause the preview to reload with the User-Agent overridden for the current device being previewed. For fully responsive themes this core behavior is just fine. However, if you have an adaptive design for your theme you won't be able to see any server-side components that it may display when viewing a different device. For example, on mobile devices a theme may want to skip outputting the sidebar altogether to save on bandwidth.

This plugin will ensure that the preview is refreshed when the previewed device is changed in addition to changing the preview window size. It will pass a <code>customize_previewed_device</code> query parameter on the URL being previewed, and this parameter will be either <code>desktop</code>, <code>tablet</code>, or <code>mobile</code>. The plugin will override the <code>$_SERVER['HTTP_USER_AGENT']</code> to be a user agent representative of the supplied device type so that calls to <code>wp_is_mobile()</code>, <code>jetpack_is_mobile()</code>, and <code>Jetpack_User_Agent_Info::is_tablet()</code> will all return the expected values based on the current previewed device.

== Changelog ==

= 0.1.0 [2016-10-05] =

Initial release.
