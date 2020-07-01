<?php
/**
 * @package Unlimited Elements
 * @author UniteCMS.net / Valiano
 * @copyright (C) 2012 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ELEMENTS_INC') or die('Restricted access');

class HelperProviderCoreUC_EL{
	
   	
	public static $pathCore;
	public static $urlCore;
	public static $filepathGeneralSettings;
	public static $operations;
	
	
	/**
	 * register post types of elementor library
	 */
	public static function registerPostType_UnlimitedLibrary(){
				
		$arrLabels = array(
						'name' => __( 'Unlimited Elements Library' ,"unlimited_elements"),
						'singular_name' => __( 'Unlimited Elements Library' ,"unlimited_elements"),
						'add_new_item' => __( 'Add New Template' ,"unlimited_elements"),
						'edit_item' => __( 'Edit Template' ,"unlimited_elements"),
						'new_item' => __( 'New Template' ,"unlimited_elements"),
						'view_item' => __( 'View Template' ,"unlimited_elements"),
						'view_items' => __( 'View Template' ,"unlimited_elements"),
						'search_items' => __( 'Search Template' ,"unlimited_elements"),
						'not_found' => __( 'No Template Found' ,"unlimited_elements"),
						'not_found_in_trash' => __( 'No Template found in trash' ,"unlimited_elements"),
						'all_items' => __( 'All Templates' ,"unlimited_elements")
				);
		
		$arrSupports = array(
			"title",
		//	"editor",
			"author",
			"thumbnail",
			"revisions",
			"page-attributes",
		);
		
		$arrPostType =	array(
							'labels' => $arrLabels,
							'public' => true,
							'rewrite' => false,
							
							'show_ui' => true,
							'show_in_menu' => false,		//set to true for show
							'show_in_nav_menus' => false,	//set to true for show
		
							'exclude_from_search' => true,
							'capability_type' => 'post',
							'hierarchical' => true,
							'description' => __("Unlimited Elements Template", "unlimited_elements"),
							'supports' => $arrSupports,
							//'show_in_admin_bar' => true		
					);
		
		register_post_type( GlobalsUnlimitedElements::POSTTYPE_UNLIMITED_ELEMENS_LIBRARY, $arrPostType);
				
	}
	
	
	/**
	 * process param value by type
	 */
	public static function processParamValueByType($value, $type, $param){
		    		
    		switch($type){
    			
    			case UniteCreatorDialogParam::PARAM_RADIOBOOLEAN:
    			    
    				$trueValue = UniteFunctionsUC::getVal($param, "true_value");
    				$falseValue = UniteFunctionsUC::getVal($param, "false_value");
					
    				switch($value){
    					case $trueValue:		//don't change true or false
    					case $falseValue:
    					break;
    					case "yes":
    						$value = $trueValue;
    					break;
    					default:
    						$value = $falseValue;
    					break;
    				}
    				
    				
    			break;
    		}
    	
    		
		return($value);
	}
	
	
	/**
	 * get general settings values
	 */
	public static function getGeneralSettingsValues(){
		
		$arrValues = self::$operations->getCustomSettingsObjectValues(self::$filepathGeneralSettings, GlobalsUnlimitedElements::GENERAL_SETTINGS_KEY);
		
		return($arrValues);
	}
	
	
	/**
	 * get general setting value
	 */
	public static function getGeneralSetting($name){
		
		$arrSettings = self::getGeneralSettingsValues();
		if(isset($arrSettings[$name]) == false)
			UniteFunctionsUC::throwError("Setting: $name does not exists in unlimited elements");
		
		$value = $arrSettings[$name];
		
		return($value);
	}
	
	
	/**
	 * add constant data to addon output
	 */
	public static function addOutputConstantData($data){
		
		$data["uc_platform_title"] = "Elementor Page Builder";
		$data["uc_platform"] = "elementor";
		
		return($data);
	}
	
	
	/**
	 * run on init action
	 */
	public static function onInitAction(){
		
		//register templates
		if(GlobalsUC::$inDev == true){
			self::registerPostType_UnlimitedLibrary();
			add_post_type_support( GlobalsUnlimitedElements::POSTTYPE_UNLIMITED_ELEMENS_LIBRARY, 'elementor' );
		}
		
	}
	
	
	/**
	 * global init
	 */
	public static function globalInit(){
				
		self::$operations = new UCOperations();
		
		add_filter(UniteCreatorFilters::FILTER_ADD_ADDON_OUTPUT_CONSTANT_DATA ,array("HelperProviderCoreUC_EL","addOutputConstantData"));
		
		//set path and url
		self::$pathCore = dirname(__FILE__)."/";
		self::$urlCore = HelperUC::pathToFullUrl(self::$pathCore);
		
		self::$filepathGeneralSettings = self::$pathCore."settings/general_settings_el.xml";
		
		GlobalsProviderUC::$pluginName = "unlimited_elementor";
		
		GlobalsUC::$currentPluginTitle = GlobalsUnlimitedElements::PLUGIN_TITLE;
		
		add_action("init", array("HelperProviderCoreUC_EL", "onInitAction"));
		
	}
	
}