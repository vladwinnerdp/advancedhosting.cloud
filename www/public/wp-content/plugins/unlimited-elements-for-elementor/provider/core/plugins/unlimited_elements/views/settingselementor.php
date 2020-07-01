<?php
/**
 * @package Unlimited Elements
 * @author UniteCMS.net
 * @copyright (C) 2017 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ELEMENTS_INC') or die('Restricted access');


require HelperUC::getPathViewObject("settings_view.class");
	
class UniteCreatorViewElementorSettings extends UniteCreatorSettingsView{
	
	/**
	 * modify custom settings - function for override
	 */
	protected function modifyCustomSettings($objSettings){
		
		$objSettings = HelperProviderUC::modifyGeneralSettings_memoryLimit($objSettings);
		
		return($objSettings);
	}
	
	
	/**
	 * constructor
	 */
	public function __construct(){
		
		$this->headerTitle = esc_html__("General Settings", "unlimited_elements");
		$this->isModeCustomSettings = true;
		$this->customSettingsXmlFile = HelperProviderCoreUC_EL::$filepathGeneralSettings;
		$this->customSettingsKey = "unlimited_elements_general_settings";
		
		
		//set settings
		$this->display();
	}
	
	
}

new UniteCreatorViewElementorSettings();
