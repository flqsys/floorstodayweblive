<?php

/*
Plugin Name: Lara's Google Analytics (GA4) - Pro
Plugin URI: https://www.xtraorbit.com/wordpress-google-analytics-dashboard-widget/
Description: Full width Google Analytics dashboard widget for Wordpress admin interface, which also inserts latest Google Analytics (GA4) tracking code to your pages.
Version: 4.0.3
Author: XtraOrbit Web Development SRL
Author URI: https://www.xtraorbit.com/
License: SUSDL (Single Use / Single Domain License)
Text Domain: lara-google-analytics
Domain Path: /languages/
*/

/*
Single Use / Single Domain License (SUSDL)

All parts of this plugin (SOFTWARE) including, but not limited to algorithms,
PHP code, Javascript code or CSS code, texts, images and designs
are licensed according to this license, unless otherwise stated.

The SUSDL grants you a non-exclusive and non-transferrable right to use this
SOFTWARE subject to the following conditions:

You have been granted this license explicitly by the copyright holder.

You are granted one single use of the SOFTWARE on one single domain.

You may use the SOFTWARE for your own purposes or for a client, anyhow,
use of the SOFTWARE is limited to one domain only.

Use on multiple domains, including subdomains, requires you to obtain one
SUSDL for each domain or subdomain you wish to use the SOFTWARE on.

You must not directly or indirectly license, sub-license, rent, lend, sell,
resell, provide for free, reproduce or distribute by any means the SOFTWARE.
The same applies to derivative works based on the SOFTWARE.
The same applies to works based on reverse-engineered versions of this
SOFTWARE.

You must not create derivative works based on the SOFTWARE.

You must not reverse-engineer the SOFTWARE.

You must not incorporate the SOFTWARE in any work created for resale.

You must not use the SOFTWARE to provide services similar to those provided
by the SOFTWARE.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/


if (!defined("ABSPATH"))
    die("This file cannot be accessed directly");

define ("lrgawidget_plugin_version", "4.0.3");
define ("lrgawidget_plugin_prefiex", "lrgapro-");
define ("lrgawidget_plugin_scripts_version", "403");
define ("lrgawidget_plugin_dir", dirname(__FILE__ ) . DIRECTORY_SEPARATOR);
define ("lrgawidget_plugin_dir_url", plugin_dir_url( __FILE__ ));
define ("lrgawidget_plugin_dist_url", lrgawidget_plugin_dir_url . 'dist/');
define ("lrgawidget_plugin_plugins_url", lrgawidget_plugin_dir_url .'dist/plugins/');
define ("lrgawidget_plugin_main_file", basename( __FILE__ ));

global $wpdb;
define ("lrgawidget_plugin_table", $wpdb->base_prefix . 'lrgawidget_pro_global_settings');

register_activation_hook(__FILE__,'lrgawidget_activate');
register_uninstall_hook(__FILE__, 'lrgawidget_uninstall' );
add_action( 'init', 'lrgawidget_update' );
add_action( 'admin_init', 'lrgawidget_register_admin_actions' );

add_action( 'wp_logout', 'lrgawidget_logout' );
add_action( 'wp_head', 'lrgawidget_ga_code');

function lrgawidget_register_admin_actions() {
	require(lrgawidget_plugin_dir . 'core/system/wordpress/admin.actions.php');
}

function lrgawidget_ga_code(){
	if (!current_user_can('edit_posts')){
		require(lrgawidget_plugin_dir . 'core/system/wordpress/tracking.code.class.php');
		Lara\Widgets\GoogleAnalytics\TrackingCode::get_ga_code();
	}
} 

function lrgawidget_logout(){
	$session_token = wp_get_session_token();
	if (!empty($session_token)){
		delete_site_transient(lrgawidget_plugin_prefiex . $session_token);
	}	
}

function lrgawidget_activate() {
	require(lrgawidget_plugin_dir . 'core/system/wordpress/plugin.actions.class.php');
	Lara\Widgets\GoogleAnalytics\PluginActions::activate();	
}

function lrgawidget_update() {
	$options = get_network_option(1,lrgawidget_plugin_prefiex.'global_options', "{}");
	$global_options = json_decode($options, true);
	if (empty($global_options["version"])){
		$version = get_network_option(1,lrgawidget_plugin_prefiex.'version', '1.0');
	}else{
		$version = $global_options["version"];
	}

	if (version_compare($version, lrgawidget_plugin_version, '<')){
		require(lrgawidget_plugin_dir . 'core/system/wordpress/plugin.updater.class.php');
		Lara\Widgets\GoogleAnalytics\PluginUpdater::update($version);
		
		$options = get_network_option(1,lrgawidget_plugin_prefiex.'global_options', "{}");
		$global_options = json_decode($options, true);
		$global_options["version"] = lrgawidget_plugin_version;

		if (!is_multisite()){
			update_option(lrgawidget_plugin_prefiex.'global_options', json_encode($global_options, JSON_FORCE_OBJECT), 'yes' );
		}else{
			update_network_option(1, lrgawidget_plugin_prefiex.'global_options', json_encode($global_options, JSON_FORCE_OBJECT) );
		}			
	}
}

function lrgawidget_uninstall() {
	require(lrgawidget_plugin_dir . 'core/system/wordpress/plugin.actions.class.php');
	Lara\Widgets\GoogleAnalytics\PluginActions::uninstall();
}
?>