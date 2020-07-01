<?php

/**
 * @package Unlimited Elements
 * @author UniteCMS.net
 * @copyright (C) 2017 Unite CMS, All Rights Reserved.
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ELEMENTS_INC') or die('Restricted access');

class UniteCreatorManagerAddons extends UniteCreatorManagerAddonsWork{

	
	/**
	 * get current layout shortcode template
	 */
	protected function getShortcodeTemplate(){
		
		$shortcode = GlobalsProviderUC::SHORTCODE_LAYOUT;
		
		$shortcodeTemplate = "[$shortcode id=%id% title=\"%title%\"]";
		
		return($shortcodeTemplate);
	}
	
	
	/**
	 * construct the manager
	 */
	public function __construct(){
		
		parent::__construct();
		
		$urlLicense = HelperUC::getViewUrl(GlobalsUC::VIEW_LICENSE);
		$this->urlBuy = $urlLicense;
		
	}
	
	
}