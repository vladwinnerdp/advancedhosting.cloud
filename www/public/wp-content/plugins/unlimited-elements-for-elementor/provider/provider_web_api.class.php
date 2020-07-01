<?php

/**
 * @package Unlimited Elements
 * @author UniteCMS.net
 * @copyright (C) 2017 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ELEMENTS_INC') or die('Restricted access');

class UniteCreatorWebAPI extends UniteCreatorWebAPIWork{

	
	/**
	 * construct
	 */
	public function __construct(){
		
		if(defined("UNLIMITED_ELEMENTS_UPRESS_URL_API"))
			self::$urlAPI = UNLIMITED_ELEMENTS_UPRESS_URL_API;
		
		//self::$urlAPI = GlobalsUC::URL_API;
							
		parent::__construct();
	}
	
	
	/**
	 * is active by freemius
	 */
	private function isFreemiusActive(){
        	
        $isActivated = HelperProviderUC::isActivatedByFreemius();
        
        return($isActivated);
	}
	
		
	
	/**
	 * is product active
	 */
	public function isProductActive($product = null){
		
		$isActive = $this->isFreemiusActive();
		
		if($isActive == true)
			return(true);
		
		$isActive = parent::isProductActive($product);
		
		return $isActive;
				
	}
	
	/**
	 * filter catalog addons for another platforms items
	 */
	protected function filterCatalogAddons($arrCatalogAddons){
		
		if(empty($arrCatalogAddons))
			return($arrCatalogAddons);
		
		$arrCatalogAddonsNew = array();
		foreach($arrCatalogAddons as $catName => $arrAddons){
			
			$arrAddonsNew = array();
			
			if(UniteCreatorWebAPI::IS_CATALOG_UNLIMITED == false)
				$catName = str_replace("Article", "Post", $catName);
			
			foreach($arrAddons as $addon){
				
				$title = UniteFunctionsUC::getVal($addon, "title");
				$name = UniteFunctionsUC::getVal($addon, "name");
				
				$titleLow = strtolower($title);
				
				if(strpos($titleLow, "joomla") !== false)
					continue;
				
				if(strpos($name, "joomla") !== false)
					continue;
				
				if(strpos($name, "k2_basic") !== false)
					continue;
				
				if($name == "article")
					continue;
				
				//rename
				if(UniteCreatorWebAPI::IS_CATALOG_UNLIMITED == false)
					$title = str_replace("Article", "Post", $title);
				
				$addon["title"] = $title;
				
				$arrAddonsNew[] = $addon;
			}
			
			$arrCatalogAddonsNew[$catName] = $arrAddonsNew;
		}
		
		
		return($arrCatalogAddonsNew);
	}
	
	
	/**
	 * get catalog array by addons type
	 */
	public function getCatalogArray($objAddonsType){
		
		$arrCatalogItems = parent::getCatalogArray($objAddonsType);
		
		if($objAddonsType->isLayout == true)
			return($arrCatalogItems);
			
		$arrCatalogItems = $this->filterCatalogAddons($arrCatalogItems);
		
		return($arrCatalogItems);
	}
	
	
	/**
	 * get catalog array
	 */
	protected function getCatalogArray_addons(){
		
		$arrCatalogAddons = parent::getCatalogArray_addons();
		
		$arrCatalogAddons = $this->filterCatalogAddons($arrCatalogAddons);
		
		return($arrCatalogAddons);		
	}
	
	
	/**
	 * modify data before request
	 */
	protected function modifyDataBeforeRequest($data){
		
		$data["platform"] = "wp";
		
		//get the right category name
		
		if(self::IS_CATALOG_UNLIMITED == false){
			$cat = UniteFunctionsUC::getVal($data, "cat");
			if(!empty($cat))
				$data["cat"] = str_replace("Post", "Article", $cat);			
		}
		
		return($data);
	}
	
	
	/**
	 * install from data
	 * redirect to wp back
	 */
	public function installCatalogPageFromData($data){
		
		$arrResponse = parent::installCatalogPageFromData($data);
		
		$pageID = $arrResponse["layoutid"];
		$params = UniteFunctionsUC::getVal($data, "params");
		
		$redirectToWP = UniteFunctionsUC::getVal($params, "redirect_to_wp_page");
		$redirectToWP = UniteFunctionsUC::strToBool($redirectToWP);
				
		if($redirectToWP == false)
			return($arrResponse);

		UniteFunctionsUC::validateNotEmpty($pageID, "page id");
		
		$urlRedirect = UniteFunctionsWPUC::getUrlEditPost($pageID);
		
		$arrResponse["url_redirect"] = $urlRedirect;
		
		return($arrResponse);
	}
		
}