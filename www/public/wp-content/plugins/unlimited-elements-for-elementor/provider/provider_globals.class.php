<?php

defined('UNLIMITED_ELEMENTS_INC') or die('Restricted access');

class GlobalsProviderUC{
	
	const ENABLE_FREEMIUS = true;
	
	public static $pluginName = "";
	
	const META_KEY_LAYOUT_DATA = "_unelements_layout_data";
	const META_KEY_LAYOUT_PARAMS = "_unelements_layout_params";
	
	const META_KEY_BLOX_PAGE = "_unelements_page_enabled";
	const META_KEY_CATID = "_unelements_catid";
	const META_KEY_LAYOUT_TYPE = "_unelements_layout_type";

	
	const PAGE_TEMPLATE_LANDING_PAGE = "blox_landing_page";
	const POST_TYPE_LAYOUT = "unelements_library";
	
	const SHORTCODE_LAYOUT = "unlimited_layout";
	
	const ACTION_RUN_ADMIN = "unitecreator_run_admin";
	const ACTION_RUN_FRONT = "unitecreator_run_front";
	
	public static $arrFilterPostTypes = array(		//filter post types that will not show
				"elementor_library", 
				"unelements_library", 
				"wpcf7_contact_form",
				"_pods_pod",
				"_pods_field",
				"_pods_template",
				"wp-types-group",
				"wp-types-user-group",
				"wp-types-term-group",
				"elementor_font",
				"elementor_icons"
	);
	
	const POST_ADDITION_CUSTOMFIELDS = "customfields";
	const POST_ADDITION_CATEGORY = "category";
	
	
	/**
	 * init globals
	 */
	public static function initGlobals(){
		
		self::$arrFilterPostTypes = UniteFunctionsUC::arrayToAssoc(self::$arrFilterPostTypes);
		
	}
	
}

GlobalsProviderUC::initGlobals();