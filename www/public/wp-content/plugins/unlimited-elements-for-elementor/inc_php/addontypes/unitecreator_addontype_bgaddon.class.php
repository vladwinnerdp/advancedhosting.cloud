<?php
/**
 * @package Unlimited Elements
 * @author UniteCMS.net
 * @copyright (C) 2017 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ELEMENTS_INC') or die('Restricted access');

class UniteCreatorAddonType_BGAddon extends UniteCreatorAddonType{
	
	
	
	/**
	 * init the addon type
	 */
	protected function init(){
		 
		$this->typeName = GlobalsUC::ADDON_TYPE_BGADDON;
		$this->textSingle = __("BG Addon", "unlimited_elements");
		$this->textPlural = __("BG Addons", "unlimited_elements");
		$this->textShowType = $this->textSingle;
		$this->titlePrefix = $this->textSingle." - ";
		$this->isBasicType = false;
		$this->allowWebCatalog = true;
		$this->allowManagerWebCatalog = true;
		$this->catalogKey = $this->typeName;
		$this->allowNoCategory = false;
		$this->defaultCatTitle = "Main";
		
	}
	
	
}
