<?php
namespace Lara\Widgets\GoogleAnalytics;

/**
 * @package    Google Analytics by Lara - Pro
 * @author     Amr M. Ibrahim <mailamr@gmail.com>
 * @link       https://www.xtraorbit.com/
 * @copyright  Copyright (c) XtraOrbit Web development SRL 2016 - 2020
 */

if (!defined("ABSPATH"))
    die("This file cannot be accessed directly");

global $wpdb;
define ("lrgawidget_legacy_plugin_table", $wpdb->base_prefix . 'lrgawidget_pro_global_settings');
define ("lrgawidget_legacy_plugin_prefiex", "lrgapro-");

class PluginUpdater {

	private static function legacy_is_analytics($str){
		return (bool) preg_match('/^ua-\d{4,20}(-\d{1,10})?$/i', $str);
	}	
	
	public static function update($version){
		global $wpdb;
		if (version_compare($version, '3.0.0', '<')){
			$old_settings = array();
			$results = $wpdb->get_results ( "SELECT `name`, `value` FROM  `".lrgawidget_legacy_plugin_table."`", ARRAY_A );
			if (!empty($results)){
				foreach ($results as $setting) {
					$old_settings[$setting['name']] = $setting['value'];
				}			
			}

			$property_id = get_option('lrgawidget_property_id',"");
			if ( (!empty($property_id) && !self::legacy_is_analytics($property_id)) || (!empty($old_settings['property_id']) && !self::legacy_is_analytics($old_settings['property_id']))){
				$wpdb->query("TRUNCATE TABLE `".lrgawidget_legacy_plugin_table."`");
				if (!session_id()){session_start();}
				foreach ($_SESSION as $key => $value) {
					if(preg_match('/^lrgatmp_/s', $key)){
						unset($_SESSION[$key]);
					}
				}
				$property_id = "";
				$old_settings = array();
			}
			$wpdb->query("CREATE TABLE IF NOT EXISTS `".lrgawidget_legacy_plugin_table."_permissions` (`id` int(10) NOT NULL AUTO_INCREMENT, `site_id` int(10) NOT NULL, `blog_id` int(10) NOT NULL, `role_id` TEXT NOT NULL, `permissions` TEXT NOT NULL, PRIMARY KEY (`id`))");
			$db_columns= $wpdb->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '".lrgawidget_legacy_plugin_table."' ");
			if($db_columns == 3){
				$wpdb->query("ALTER TABLE `".lrgawidget_legacy_plugin_table."` ADD `site_id` int(10) NOT NULL AFTER `id`");
				$wpdb->query("ALTER TABLE `".lrgawidget_legacy_plugin_table."` ADD `blog_id` int(10) NOT NULL AFTER `site_id`");
				$wpdb->query("UPDATE `".lrgawidget_legacy_plugin_table."` SET `site_id`=1, `blog_id`=1");
			}
			
			if (!empty($property_id) && self::legacy_is_analytics($property_id)) {
				$wpdb->insert( lrgawidget_legacy_plugin_table, array( 'site_id' => 1, 'blog_id' => 1, 'name' => 'enable_universal_tracking', 'value' => 'on'));
			}else{
				if(!empty($old_settings)){
					$wpdb->insert( lrgawidget_legacy_plugin_table, array( 'site_id' => 1, 'blog_id' => 1, 'name' => 'enable_universal_tracking', 'value' => 'off'));
				}
			}
			delete_option('lrgawidget_property_id');
		}

		if (version_compare($version, '3.3.0', '<')){
			$blogs_settings = array();
			$results = $wpdb->get_results ( "SELECT CONCAT( `site_id`, '_', `blog_id` ) as `id`,`name`, `value` FROM  `".lrgawidget_legacy_plugin_table."`", ARRAY_A );
			if (!empty($results)){
				foreach ($results as $setting) {
					$blogs_settings[$setting['id']][$setting['name']] = $setting['value'];
				}
			}
			
			$wpdb->query("TRUNCATE TABLE `".lrgawidget_legacy_plugin_table."`");
			if (!empty($blogs_settings)){
				foreach ($blogs_settings as $id => $blog_settings) {
					list($site_id, $blog_id) = explode('_', $id);
					$wpdb->insert( lrgawidget_legacy_plugin_table, array( 'site_id' => $site_id, 'blog_id' => $blog_id, 'name' => "settings", 'value' => json_encode($blog_settings, JSON_FORCE_OBJECT)), array('%d','%d','%s','%s'));
				}
			}
			
			$blogs_permissions = array();
			$results = $wpdb->get_results ( "SELECT CONCAT( `site_id`, '_', `blog_id` ) as `id`,`role_id`, `permissions` FROM  `".lrgawidget_legacy_plugin_table."_permissions`", ARRAY_A );
			if (!empty($results)){
				foreach ($results as $permission) {
					$role_permissions = json_decode($permission['permissions'], true);
					$new_role_permissions = array();
					if(is_array($role_permissions)){
						foreach ($role_permissions as $role_permission) {
							$new_role_permissions[] = str_replace("lrgawidget_perm_","",$role_permission);
						}
					}
					$blogs_permissions[$permission['id']][$permission['role_id']] = $new_role_permissions;
				}
			}
			
			$wpdb->query("DROP TABLE `".lrgawidget_legacy_plugin_table."_permissions`");
			if (!empty($blogs_permissions)){
				foreach ($blogs_permissions as $id => $blog_permissions) {
					list($site_id, $blog_id) = explode('_', $id);
					$wpdb->insert( lrgawidget_legacy_plugin_table, array( 'site_id' => $site_id, 'blog_id' => $blog_id, 'name' => "permissions", 'value' => json_encode($blog_permissions, JSON_FORCE_OBJECT)) , array('%d','%d','%s','%s'));
				}
			}
			delete_option('lrgawidget_property_id');
			delete_network_option( 1, lrgawidget_legacy_plugin_prefiex.'version' );
		}
	}
}
?>