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

class Permissions{
	private $laraStockPermissions;
	private $userRoles;
	private $userPermissions;
	private $output  = array();
	private $errors  = array();	
	
	function __construct(){

		$this->laraStockPermissions =  array(
											array( "id"      => "permissions",
												   "name"    => __('Permissions', 'lara-google-analytics'),
												   "icon"    => "fas fa-user-lock",
												   "type"    => "checkbox",
												   "default" => "",
												   "permissions" => array(array("name" => "perm",     "label" => __('Super Administrator [Change Permissions]', 'lara-google-analytics')),
																		  array("name" => "admin",    "label" => __('Administrator [Change Settings]', 'lara-google-analytics'))
																		  )
													),		
											array( "id"      => "tabs",
												   "name"    => __('Tabs', 'lara-google-analytics'),
												   "icon"    => "far fa-chart-bar",
												   "type"    => "checkbox",
												   "default" => "",
												   "permissions" => array(array("name" => "daterange",    "label" => __('Change Date Range', 'lara-google-analytics')),
																		  array("name" => "graph_options","label" => __('Edit Graph Options', 'lara-google-analytics')),	
																		  array("name" => "sessions",     "label" => __('Graph', 'lara-google-analytics')),
																		  array("name" => "realtime",     "label" => __('Real Time', 'lara-google-analytics')),
																		  array("name" => "countries",    "label" => __('Countries', 'lara-google-analytics')),
																		  array("name" => "browsers",     "label" => __('Browsers', 'lara-google-analytics')),
																		  array("name" => "languages",    "label" => __('Languages', 'lara-google-analytics')),
																		  array("name" => "os",           "label" => __('Operating Systems', 'lara-google-analytics')),
																		  array("name" => "devices",      "label" => __('Devices', 'lara-google-analytics')),
																		  array("name" => "screenres",    "label" => __('Screen Resolutions', 'lara-google-analytics')),
																		  array("name" => "keywords",     "label" => __('Keywords', 'lara-google-analytics')),
																		  array("name" => "sources",      "label" => __('Sources', 'lara-google-analytics')),
																		  array("name" => "pages",        "label" => __('Pages', 'lara-google-analytics'))
													)
												),
											array( "id"      => "ecommerce",
												   "name"    => __('eCommerce Graphs', 'lara-google-analytics'),
												   "icon"    => "fas fa-store",
												   "type"    => "radio",
												   "default" => "ecom_woo",
												   "permissions" => array(array("name" => "ecom_woo",  "label" => __('WooCommerce', 'lara-google-analytics')." [".__('beta', 'lara-google-analytics')."]"),
																		  array("name" => "ecom_edd",  "label" => __('Easy Digital Downloads', 'lara-google-analytics')." [".__('comming soon', 'lara-google-analytics')."]")
														)
													),
													
											);
		$this->userRoles        = $this->getCurrentUserRoles();
		$this->userPermissions  = $this->setCurrentUserPermissions();
	}
	
	private function getRoles(){
		return SystemBootStrap::getRoles();
	}
	
	public function getCurrentBlogRolesPermissions(){
		if (in_array("perm", $this->userPermissions)){
			$return = array();
			$return['group_permissions'] = $this->laraStockPermissions;
			$return['roles'] = $this->getRoles();

			foreach ($return['roles'] as $role) {
				$return['role_permissions'][$role['id']] = $this->getRolePermissions($role['id']);
			}
			$this->output = $return;
		}else{$this->errors[] = __('You do not have permission to access this page', 'lara-google-analytics');}
		$this->jsonOutput();
	}	

	private function getCurrentUserRoles(){
		return SystemBootStrap::getCurrentUserRoles();
	}	
	
	private function getRolePermissions($roleID){
		$permissions = array();
		$results = DataStore::database_get("permissions", $roleID);
		if(!empty($results)){
			$permissions = $results;
		}
		return $permissions;
	}	

	private function setCurrentUserPermissions(){
		$permissions = array();
		foreach ($this->userRoles as $roleID) {
			$rolePermissions = $this->getRolePermissions($roleID);
			if (($roleID === "administrator") && empty($rolePermissions)){
				$rolePermissions = $this->getStockPermissions(false);
				$this->setRolePermissions("administrator", $rolePermissions);
			}
			$permissions = array_merge($permissions,$rolePermissions);
		}
		if(in_array("administrator", $this->userRoles)){
			array_push($permissions, "perm", "admin");
			ErrorHandler::setDebugMode(true);
		}

		$permissions = array_unique($permissions);
		DataStore::$RUNTIME["permissions"] = $permissions;
		return $permissions;
	}

	private function getStockPermissions($all = true){
		$results = array();
		foreach ($this->laraStockPermissions as $group){
			if (($all === false) && ($group["type"] === "radio")){
				$results[] = $group['default'];
			}else{
				foreach ($group['permissions'] as $permission){
					$results[] = $permission['name'];
				}			
			}
		}
		return $results;
	}	
	
	private function setRolePermissions($roleID, $permissions){
		if (empty($permissions)){
			DataStore::database_delete("permissions", $roleID);
		}else{
			DataStore::database_set("permissions", $roleID, $permissions);
		}

	}
	
	public function setCurrentBlogRolesPermissions($rolesPermissions){
		if (in_array("perm", $this->userPermissions)){
			if (!empty($rolesPermissions) && is_array($rolesPermissions)){

				$roles = array_map(function ($ar) {return $ar['id'];}, $this->getRoles());
				$stockPermissions = $this->getStockPermissions();

				foreach ($roles as $roleID){
					$rolePermissions = array();
					if ($roleID === "administrator"){
						array_push($rolePermissions, "perm", "admin");
					}
					
					if(!empty($rolesPermissions[$roleID]) && is_array($rolesPermissions[$roleID])){
						foreach ($rolesPermissions[$roleID] as $permission){
							if(in_array($permission, $stockPermissions)){
								$rolePermissions[] = $permission;
							}
						}					
					}
					$this->setRolePermissions($roleID, $rolePermissions);
				}
			}
		}
		DataStore::purge_cache("lrga_");
		$this->jsonOutput();
	}	

	private function jsonOutput(){
		if (empty($this->errors)){
			$this->output['status'] = "done";
			OutputHandler::jsonOutput($this->output);
		}else{ 
			if(in_array("administrator", $this->userRoles)){ErrorHandler::setDebugMode(true);}
			ErrorHandler::FatalError(__('Fatal Error', 'lara-google-analytics'),__('Could not get or set widget permissions .. please contact an administrator', 'lara-google-analytics'),10001,$this->errors); 
		}
		exit();
	}	

}

?>