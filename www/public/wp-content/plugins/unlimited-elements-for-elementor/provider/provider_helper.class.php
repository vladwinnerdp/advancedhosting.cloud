<?php

class HelperProviderUC{
	
	/**
	 * is activated by freemis
	 */
	public static function isActivatedByFreemius(){
        
		global $unl_fs;
		
        if(isset($unl_fs) == false)
        	return(false);
        	
        $isActivated = $unl_fs->is_paying();
        	
        return($isActivated);
	}
    
	
	/**
	 * get post addditions array from options
	 */
	public static function getPostAdditionsArray_fromAddonOptions($arrOptions){
		
		$arrAdditions = array();
		
		$enableCustomFields = UniteFunctionsUC::getVal($arrOptions, "dynamic_post_enable_customfields");
		$enableCustomFields = UniteFunctionsUC::strToBool($enableCustomFields);

		$enableCategory = UniteFunctionsUC::getVal($arrOptions, "dynamic_post_enable_category");
		$enableCategory = UniteFunctionsUC::strToBool($enableCategory);
		
		/*
		$enableTaxonomies = UniteFunctionsUC::getVal($this->addonOptions, "dynamic_post_enable_taxonomies");
		$enableTaxonomies = UniteFunctionsUC::strToBool($enableTaxonomies);
		*/
		
		if($enableCustomFields == true)
			$arrAdditions[] = GlobalsProviderUC::POST_ADDITION_CUSTOMFIELDS;
		
		if($enableCategory == true)
			$arrAdditions[] = GlobalsProviderUC::POST_ADDITION_CATEGORY;
		
		
		return($arrAdditions);
	}
	
	
	/**
	 * get post data additions
	 */
	public static function getPostDataAdditions($addCustomFields, $addCategory){
		
		$arrAdditions = array();
		
		$addCustomFields = UniteFunctionsUC::strToBool($addCustomFields);
		$addCategory = UniteFunctionsUC::strToBool($addCategory);
		
		if($addCustomFields == true)
			$arrAdditions[] = GlobalsProviderUC::POST_ADDITION_CUSTOMFIELDS;
		
		if($addCategory == true)
			$arrAdditions[] = GlobalsProviderUC::POST_ADDITION_CATEGORY;
		
		return($arrAdditions);
	}
	
    /**
     * get white label settings
     */
    public static function getWhiteLabelSettings(){
        
        $activateWhiteLabel = HelperUC::getGeneralSetting("activate_white_label");
        $activateWhiteLabel = UniteFunctionsUC::strToBool($activateWhiteLabel);
        
        if($activateWhiteLabel == false)
            return(null);
            
        $whiteLabelText = HelperUC::getGeneralSetting("white_label_page_builder");
        if(empty($whiteLabelText))
            return(null);
                
            $whiteLabelSingle = HelperUC::getGeneralSetting("white_label_single");
            if(empty($whiteLabelSingle))
                return(null);
                    
            $arrSettings = array();
            $arrSettings["plugin_text"] = trim($whiteLabelText);
            $arrSettings["single"] = trim($whiteLabelSingle);
                    
           return($arrSettings);
    }
    
	
	/**
	 * modify memory limit setting
	 */
	public static function modifyGeneralSettings_memoryLimit($objSettings){
		
		//modify memory limit
		
		$memoryLimit = ini_get('memory_limit');
		$htmlLimit = "<b> {$memoryLimit} </b>";
				
		$setting = $objSettings->getSettingByName("memory_limit_text");
		if(empty($setting))
			UniteFunctionsUC::throwError("Must be memory limit troubleshooter setting");
		
		$setting["text"] = str_replace("[memory_limit]", $htmlLimit, $setting["text"]);
		$objSettings->updateArrSettingByName("memory_limit_text", $setting);
		
		
		return($objSettings);
	}
	
	
	/**
	 * add all post types
	 */
	private static function modifyGeneralSettings_postType(UniteSettingsUC $objSettings){
		
		$arrPostTypes = UniteFunctionsWPUC::getPostTypesAssoc();
		
		if(count($arrPostTypes) <= 2)
			return($objSettings);
		
		unset($arrPostTypes["elementor_library"]);
		unset($arrPostTypes["uc_layout"]);
		unset($arrPostTypes[GlobalsProviderUC::POST_TYPE_LAYOUT]);
		
		$arrPostTypes = array_flip($arrPostTypes);
		
		$objSettings->updateSettingItems("post_types", $arrPostTypes);
		
		
		return($objSettings);
	}
	
	
	/**
	 * modify general settings
	 */
	private static function modifyGeneralSettings(UniteSettingsUC $objSettings){
		
		//update memory limit
		
		$objSettings = self::modifyGeneralSettings_postType($objSettings);
		
		
		return($objSettings);
	}
	
	
	/**
	 * set general settings
	 */
	public static function setGeneralSettings(UniteCreatorSettings $objSettings){
		
		//add general settings
		
		//add platform related settings
		
		$arrSettingsFilepaths = array();
		
		$filepathGeneral = GlobalsUC::$pathProvider."settings/general_settings.xml";
		UniteFunctionsUC::validateFilepath($filepathGeneral, "Provider general settings");
		
		$arrSettingsFilepaths[] = $filepathGeneral;
				
		$arrSettingsFilepaths = UniteProviderFunctionsUC::applyFilters(UniteCreatorFilters::FILTER_GET_GENERAL_SETTINGS_FILEPATH, $arrSettingsFilepaths);
					
		foreach($arrSettingsFilepaths as $filepath){
			UniteFunctionsUC::validateFilepath($filepath, "plugin related settings xml file");
			$objSettings->addFromXmlFile($filepath);
		}
					
		$objSettings = self::modifyGeneralSettings($objSettings);
				
		return($objSettings);
	}
	
	
	/**
	 * check if layout editor plugin exists, or exists addons for it
	 */
	public static function isLayoutEditorExists(){
		
		$classExists = class_exists("LayoutEditorGlobals");
		if($classExists == true)
			return(true);
	
		return(false);
	}
	
	
	/**
	 * register widgets 
	 */
	public static function registerWidgets(){
		
		//register_widget("Blox_WidgetLayout");
				
	}
	
	/**
	 * on plugins loaded, load textdomains
	 */
	public static function onPluginsLoaded(){

		//dmp(GlobalsUC::$pathWPLanguages);exit();
		
		load_plugin_textdomain( "unlimited_elements", FALSE, GlobalsUC::$pathWPLanguages );
		
	}
	
	
	/**
	 * global init function that common to the admin and front
	 */
	public static function globalInit(){
				
		add_filter(UniteCreatorFilters::FILTER_MODIFY_GENERAL_SETTINGS, array("HelperProviderUC", "setGeneralSettings") );
		
		add_action('widgets_init', array("HelperProviderUC","registerWidgets"));
		
		add_action("plugins_loaded",array("HelperProviderUC", "onPluginsLoaded"));
		
		//add_action("wp_loaded", array("HelperProviderUC", "onWPLoaded"));
				
	}
	
	
	/**
	 * on plugins loaded call plugin
	 */
	public static function onPluginsLoadedCallPlugins(){
		
		do_action("addon_library_register_plugins");
		
		UniteProviderFunctionsUC::doAction(UniteCreatorFilters::ACTION_EDIT_GLOBALS);
		
	}
	
	
	/**
	 * register plugins
	 */
	public static function registerPlugins(){
				
		add_action("plugins_loaded", array("HelperProviderUC","onPluginsLoadedCallPlugins"));
		
	}
	
	
	/**
	 * output custom styles
	 */
	public static function outputCustomStyles(){
	    
	    $arrStyles = UniteProviderFunctionsUC::getCustomStyles();
	    if(!empty($arrStyles)){
	        echo "\n<!--   Unlimited Elements Styles  --> \n";
	        
	        echo "<style type='text/css'>";
	        
	        foreach ($arrStyles as $style){
	            echo UniteProviderFunctionsUC::escCombinedHtml($style)."\n";
	        }
	        
	        echo "</style>\n";
	    }
	    
	}
	
	
	/**
	 * print custom scripts
	 */
	public static function onPrintFooterScripts($isFront = false, $scriptType = "all"){
		
		//print custom styles
		if($scriptType != "js"){
			
			self::outputCustomStyles();
		}
		
		//print inline admin html
		
		if($isFront == false){
			
			//print inline html
			$arrHtml = UniteProviderFunctionsUC::getInlineHtml();
			if(!empty($arrHtml)){
				foreach($arrHtml as $html){
					echo UniteProviderFunctionsUC::escCombinedHtml($html);
				}
			}
			
		}
			
		//print custom JS script
		
		if($scriptType != "css"){
		
			$arrScrips = UniteProviderFunctionsUC::getCustomScripts();
			if(!empty($arrScrips)){
				echo "\n<!--   Unlimited Elements Scripts  --> \n";
				
				echo "<script type='text/javascript'>\n";
				foreach ($arrScrips as $script){
					echo UniteProviderFunctionsUC::escCombinedHtml($script)."\n";
				}
				echo "</script>";
			}
			
		}
	
	}
	
	
}