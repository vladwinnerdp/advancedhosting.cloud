<?php
/**
 * @package Unlimited Elements
 * @author UniteCMS.net
 * @copyright (C) 2017 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ELEMENTS_INC') or die('Restricted access');

class UniteCreatorAddonType_Shape_Divider extends UniteCreatorAddonType{
	
	
	/**
	 * init the addon type
	 */
	protected function initChild(){

		$this->typeName = GlobalsUC::ADDON_TYPE_SHAPE_DEVIDER;
		$this->textSingle = __("Divider", "unlimited_elements");
		$this->textPlural = __("Dividers", "unlimited_elements");
		$this->isSVG = true;
		$this->textShowType = $this->textSingle;
		$this->titlePrefix = $this->textSingle." - ";
		$this->isBasicType = false;
		$this->allowWebCatalog = true;
		$this->allowManagerWebCatalog = true;
		$this->catalogKey = $this->typeName;
		$this->browser_addEmptyItem = true;
	}
	
	
}
