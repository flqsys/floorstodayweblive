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

class TrackingCode {
	private static $initialized  = false;
	private static $site_id;
	private static $blog_id;
	
	public static function initInstance() {

		if (self::$initialized){
			return;
		}

		self::$site_id       = self::current_site_id();
		self::$blog_id       = self::current_blog_id();
		self::$initialized  = true;
    }
	
	private static function is_analytics($str){
		return (bool) preg_match('/^g-[a-z0-9]+$/i', $str);
	}
	
	private static function current_site_id(){
		return is_multisite() ? get_current_site()->id : 1;
	}
	
	private static function current_blog_id(){
		return get_current_blog_id();
	}

	private static function get_settings(){
		global $wpdb;
		$results = array();
		$sql = $wpdb->prepare ( "SELECT `name`, `value` FROM  `".lrgawidget_plugin_table."`  WHERE `site_id` = %d AND `blog_id` = %d AND `name` = %s ", array(self::$site_id, self::$blog_id, "settings" ));
		$result = $wpdb->get_row( $sql , ARRAY_A );
		if ((empty($wpdb->last_error)) && is_array($result) && !empty($result["value"])){
			$results = json_decode($result["value"], true);
		}
		return $results;
	}
	
	public static function get_ga_code(){
		$settings = self::get_settings();
		if (!empty($settings["enable_ga4_tracking"]) && $settings["enable_ga4_tracking"] === "on"){
			if(!empty($settings["measurementId"]) && self::is_analytics($settings["measurementId"])){		
				$measurement_id = $settings["measurementId"];
?>

<!-- Lara's Google Analytics - https://www.xtraorbit.com/wordpress-google-analytics-dashboard-widget/ -->
<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo $measurement_id ?>"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '<?php echo $measurement_id ?>');
</script>

<?php
			}
		}
	}	
}
TrackingCode::initInstance();
?>