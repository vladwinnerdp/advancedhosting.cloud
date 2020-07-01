<?php

/**
 * @package Unlimited Elements
 * @author UniteCMS.net
 * @copyright (C) 2017 Unite CMS, All Rights Reserved.
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ELEMENTS_INC') or die('Restricted access');

class UniteCreatorManagerAddonsWork extends UniteCreatorManager{
	
	const STATE_FILTER_CATALOG = "manager_filter_catalog";
	const STATE_FILTER_ACTIVE = "fitler_active_addons";
	const STATE_LAST_ADDONS_CATEGORY = "last_addons_cat";
	
	const FILTER_CATALOG_MIXED = "mixed";
	const FILTER_CATALOG_INSTALLED = "installed";
	const FILTER_CATALOG_WEB = "web";
	
	protected $numLocalCats = 0;
	private $filterAddonType = null;
	protected $objAddonType = null, $isLayouts = false, $enableActiveFilter = true, $enableEnterName = true;
	protected $enableSearchFilter = true;
	protected $enablePreview = true, $enableViewThumbnail = false, $enableMakeScreenshots = false;
	protected $enableDescriptionField = true, $enableEditGroup = false, $enableCopy = false;
	
	protected $textAddAddon, $textSingle, $textPlural, $textSingleLower, $textPluralLower;
	
	private $filterActive = "";
	private $showAddonTooltip = false, $showTestAddon = true;
	
	protected $filterCatalogState;
	protected $defaultFilterCatalog;
	protected $objBrowser;
	protected $urlBuy;
	protected $pluginName;
	protected $putUpdateCatalogButton = true;
	private $urlAjax;
	private $product;		//product for web api
	private $putItemButtonsType = "multiple";
	private $isInsideParent = false;
	
	
	/**
	 * construct the manager
	 */
	public function __construct(){
		
		$this->pluginName = GlobalsUC::PLUGIN_NAME;
		$this->urlAjax = GlobalsUC::$url_ajax;
		$this->hasHeaderLine = true;
		
	}
	
	/**
	 * set plugin name
	 */
	public function setPluginName($pluginName){
		
		$this->pluginName = $pluginName;
	}
	
	
	/**
	 * set filter active state
	 */
	public static function setStateFilterCatalog($filterCatalog, $addonType = ""){
		
		if(empty($filterCatalog))
			return(false);
		
		HelperUC::setState(self::STATE_FILTER_CATALOG, $filterCatalog);
		
	}
	
	/**
	 * get filter active statge
	 */
	protected function getStateFilterCatalog(){
		
		if(GlobalsUC::$enableWebCatalog == false)
			return(self::FILTER_CATALOG_INSTALLED);
		
		
		if($this->objAddonType->allowWebCatalog == false)
			return(self::FILTER_CATALOG_INSTALLED);
		
		$filterCatalog = HelperUC::getState(self::STATE_FILTER_CATALOG);
		if(empty($filterCatalog))
			$filterCatalog = $this->defaultFilterCatalog;
		
		
		return($filterCatalog);
	}
	
	
	/**
	 * set filter active state
	 */
	public static function setStateFilterActive($filterActive, $addonType = ""){
		
		if(empty($filterActive))
			return(false);
				
		HelperUC::setState(UniteCreatorManagerAddons::STATE_FILTER_ACTIVE, $filterActive);
		
	}
	
	/**
	 * get filter active statge
	 */
	public static function getStateFilterActive($addonType = ""){
		
		$filterActive = HelperUC::getState(UniteCreatorManagerAddons::STATE_FILTER_ACTIVE);
		
		return($filterActive);
	}
	
	
	private function a___________INIT________(){}
	
	/**
	 * validate that addon type is set
	 */
	protected function validateAddonType(){
		
		if(empty($this->objAddonType))
			UniteFunctionsUC::throwError("addons manager error: no addon type is set");
		
		if($this->objAddonType->isLayout != $this->isLayouts)
			UniteFunctionsUC::throwError("addons manager error: mismatch addon and layout types");
		
	}
	
	
	/**
	 * before init
	 */
	protected function beforeInit($addonType){
		
		$this->type = self::TYPE_ADDONS;
		$this->viewType = self::VIEW_TYPE_THUMB;
		$this->defaultFilterCatalog = self::FILTER_CATALOG_INSTALLED;
				
		$this->urlBuy = GlobalsUC::URL_BUY;
		$this->hasCats = true;
		
		if(emptY($this->filterAddonType))
			$this->setAddonType($addonType);
		
		$this->objBrowser = new UniteCreatorBrowser();
		$this->objBrowser->initAddonType($addonType);
		
		
	}
	 
	/**
	 * run after init
	 */
	protected function afterInit($addonType){
		
		$this->validateAddonType();
				
		$this->itemsLoaderText = esc_html__("Getting ","unlimited_elements").$this->textPlural;
		$this->textItemsSelected = $this->textPluralLower . esc_html__(" selected","unlimited_elements");
		
		if($this->enableActiveFilter == true)
			$this->filterActive = self::getStateFilterActive($addonType);
		
		$this->filterCatalogState = $this->getStateFilterCatalog();
		
		//set selected category
		$lastCatID = HelperUC::getState(self::STATE_LAST_ADDONS_CATEGORY);
		if(!empty($lastCatID))
			$this->selectedCategory = $lastCatID;
					
		UniteProviderFunctionsUC::doAction(UniteCreatorFilters::ACTION_MODIFY_ADDONS_MANAGER, $this);
		
	}
	
	/**
	 * init layout specific permissions
	 */
	protected function initByAddonType_layout(){
		
		$this->isLayouts = true;
		
		if($this->objAddonType->isLayout == false)
			return(false);
		
		$this->enableActiveFilter = false;
		$this->enableEnterName = false;
		$this->showTestAddon = false;
		$this->enablePreview = true;
		$this->enableViewThumbnail = false;
		$this->enableEditGroup = true;
		$this->enableCopy = true;
		
		$this->addClass = "uc-manager-layouts";
		
		
		//if($this->objAddonType->paramsSettingsType == "screenshot")
			//$this->enableMakeScreenshots = true;
		
	}
	
	
	/**
	 * init some settings by addon type
	 */
	protected function initByAddonType(){
				
		//svg permissions
		if($this->objAddonType->isSVG == true){
			$this->showTestAddon = false;
		}
		
		//layout permissions
		if($this->objAddonType->isLayout == true)
			$this->initByAddonType_layout();
		
		
		$single = $this->objAddonType->textSingle;
		$plural = 	$this->objAddonType->textPlural;
		
		$pluralLower = strtolower($plural);
		
		$this->textSingle = $single;
		$this->textPlural = $plural;
		$this->textSingleLower = strtolower($single);
		$this->textPluralLower = strtolower($plural);
		
		//set text
		$this->arrText["confirm_remove_addons"] = esc_html__("Are you sure you want to delete those {$pluralLower}?", "unlimited_elements");
		
		$objLayouts = new UniteCreatorLayouts();
		
		$this->arrOptions["is_layout"] = $this->isLayouts;
		$this->arrOptions["url_screenshot_template"] = $objLayouts->getUrlTakeScreenshot();
		
		$this->textAddAddon = esc_html__("Add ", "unlimited_elements").$single;
		
		//set default filter
		if($this->objAddonType->allowManagerWebCatalog == true)
			$this->defaultFilterCatalog = self::FILTER_CATALOG_MIXED;
		
		if(!empty($this->objAddonType->browser_urlBuyPro))
			$this->urlBuy = $this->objAddonType->browser_urlBuyPro;
		
		if($this->objAddonType->showDescriptionField == false)
			$this->enableDescriptionField = false;
			
		if($this->objAddonType->enableCategories == false)
			$this->hasCats = false;
		
		
	}
	
	
	/**
	 * set filter addon type to use only it
	 */
	public function setAddonType($addonType){
		
		$this->filterAddonType = $addonType;
		
		$this->objAddonType = UniteCreatorAddonType::getAddonTypeObject($addonType, $this->isLayouts);
						
		$this->initByAddonType();
	}
	
	
	/**
	 * set manager name
	 */
	public function setManagerNameFromData($data){
				
		$name = UniteFunctionsUC::getVal($data, "manager_name");
		$addontype = UniteFunctionsUC::getVal($data, "manager_addontype");
		if(empty($addontype))
			$addontype = UniteFunctionsUC::getVal($data, "addontype");
		
		$passData = UniteFunctionsUC::getVal($data, "manager_passdata");
		
		if(!empty($name))
			$this->setManagerName($name);
			
		if(!empty($passData) && is_array($passData)){
			$this->arrPassData = $passData;			
		}
		
		
		$this->init($addontype);
		
		$this->setProductFromData($data);
		
	}
	
	
	private function a__________ADDON_HTML_______(){}
	
	
	/**
	 * get addon admin html add
	 */
	protected function getAddonAdminAddHtml(UniteCreatorAddon $objAddon){
		
		$addHtml = "";
				
		$addHtml = UniteProviderFunctionsUC::applyFilters(UniteCreatorFilters::FILTER_MANAGER_ADDON_ADDHTML, $addHtml, $objAddon);
		
				
		return($addHtml);
	}
	
	
	/**
	 * get data of the admin html from addon
	 */
	private function getAddonAdminHtml_getDataFromAddon(UniteCreatorAddon $objAddon){
		
		$data = array();
		
		$objAddon->validateInited();
		
		$title = $objAddon->getTitle();
		
		$name = $objAddon->getNameByType();
		
		$description = $objAddon->getDescription();
		
		//set html icon
		$urlIcon = $objAddon->getUrlIcon();
		
		//get preview html
		$urlPreview = $objAddon->getUrlPreview();
		
		$itemID = $objAddon->getID();
		
		$isActive = $objAddon->getIsActive();
		
		$addHtml = $this->getAddonAdminAddHtml($objAddon);
		
		$fontIcon = $objAddon->getFontIcon();
				
		$data["title"] = $title;
		$data["name"] = $name;
		$data["description"] = $description;
		$data["url_icon"] = $urlIcon;
		$data["url_preview"] = $urlPreview;
		$data["id"] = $itemID;
		$data["is_active"] = $isActive;
		$data["font_icon"] = $fontIcon;
		$data["add_html"] = $addHtml;

		
		return($data);
	}
	
	/**
	 * get data from layout
	 */
	private function getAddonAdminHtml_getDataFromLayout(UniteCreatorLayout $objLayout){
		
		$data = array();
		
		$data["title"] = $objLayout->getTitle();
		$data["name"] = $objLayout->getName();
		$data["description"] = $objLayout->getDescription();
		$data["url_icon"] = $objLayout->getIcon();
		$data["url_preview"] = $objLayout->getPreviewImage(true);
		$data["url_preview_default"] = $objLayout->getDefaultPreviewImage();
		$data["id"] = $objLayout->getID();
		$data["is_active"] = true;		//no setting in layout yet
		$data["add_html"] = "";
		$data["url_edit"] = $objLayout->getUrlEditPost();
		$data["url_view_post"] = $objLayout->getUrlViewPost();
		$data["is_group"] = $objLayout->isGroup();
		
		
		return($data);
	}
	
	
	/**
	 * get add html of web addon
	 */
	private function getWebAddonData($addon){
				
		$isFree = $this->objBrowser->isWebAddonFree($addon); 
		
		$state = UniteCreatorBrowser::STATE_PRO;
		if($isFree == true)
			$state = UniteCreatorBrowser::STATE_FREE;
		
		$data = $this->objBrowser->getCatalogAddonStateData($state, false, null, $addon);
		
		return($data);
	}
	
	
	/**
	 * get addons or layout by type
	 */
	private function getCatAddonsOrLayouts($catID, $filterActive, $params = null){
		
		$isLayout = $this->objAddonType->isLayout;
		
		//UniteFunctionsUC::showTrace();
		
		if($isLayout == false){		//addons
			$objAddons = new UniteCreatorAddons();
			$addons = $objAddons->getCatAddons($catID, false, $filterActive, $this->filterAddonType, false, $params);
					
			return($addons);
		}
		
		//dmp($params);
		
		//layouts
		$objLayouts = new UniteCreatorLayouts();
		$arrLayouts = $objLayouts->getCatLayouts($catID, $this->objAddonType, false, $params);
		
				
		return($arrLayouts);
	}
	
	
	/**
	 * get web API
	 */
	private function getWebAPI(){
		
		$webAPI = new UniteCreatorWebAPI();
		
		if(!empty($this->product))
			$webAPI->setProduct($this->product);
					
		return($webAPI);
	}
	
	
	/**
	 * get category addons, objects or array from catalog
	 */
	private function getCatAddons($catID, $title = "", $isweb = false, $params = null){
		
		$filterType = $this->filterAddonType;
		$filterActive = self::getStateFilterActive($this->filterAddonType);
		
		$filterCatalog = $this->getStateFilterCatalog();
		
		$filterSearch = UniteFunctionsUC::getVal($params, "filter_search");
		$filterSearch = trim($filterSearch);
		
		//if category title match the search, then get all the addons
		if(!empty($filterSearch)){
			
			$isTitleMatch = UniteFunctionsUC::isStringContains($title, $filterSearch);
			
			if($isTitleMatch == true)
				unset($params["filter_search"]);
		}
		
				
		$addons = array();
		
		
		switch($filterCatalog){
			case self::FILTER_CATALOG_WEB:
			break;
			case self::FILTER_CATALOG_INSTALLED:
				if($isweb == false)
					$addons = $this->getCatAddonsOrLayouts($catID, $filterActive, $params);
				
				return($addons);
			break;
			case self::FILTER_CATALOG_MIXED:
				if($isweb == false)
					$addons = $this->getCatAddonsOrLayouts($catID, $filterActive, $params);
			break;
		}
		
		
		//mix with the catalog
				
		//get category title
		if(!empty($catID) && empty($title)){
			$objCategories = new UniteCreatorCategories();
			$arrCat = $objCategories->getCat($catID);
			$title = UniteFunctionsUC::getVal($arrCat, "title");
		}
		
		if(empty($title))
			return($addons);
		
		if($this->objAddonType->allowManagerWebCatalog == false)
			return($addons);
		
		
		$webAPI = $this->getWebAPI();
				
		$addons = $webAPI->mergeCatAddonsWithCatalog($title, $addons, $this->objAddonType, $params);
				
		
		return($addons);
	}
	
	/**
	 * get additional addhtml, function for override
	 */
	protected function getAddonAdminHtml_AddHtml($addHtml, $objAddon){
		
		
		return($addHtml);
	}
	
	/**
	 * get html addon
	 */
	public function getAddonAdminHtml($objAddon){
		
		$isLayout = false;
		
		if(is_array($objAddon))
			$data = $objAddon;
		else{
			
			$isLayout = $this->objAddonType->isLayout;
			
			if($this->objAddonType->isLayout == false)
				$data = $this->getAddonAdminHtml_getDataFromAddon($objAddon);
			else
				$data = $this->getAddonAdminHtml_getDataFromLayout($objAddon);
		}
		
		
		//--- prepare data
		
		$title = UniteFunctionsUC::getVal($data, "title");
		$name = UniteFunctionsUC::getVal($data, "name");
		$description = UniteFunctionsUC::getVal($data, "description");
		$urlIcon = UniteFunctionsUC::getVal($data, "url_icon");
		$urlPreview = UniteFunctionsUC::getVal($data, "url_preview");
		$urlPreviewDefault = UniteFunctionsUC::getVal($data, "url_preview_default");
		
		$itemID = UniteFunctionsUC::getVal($data, "id");
		$isActive = UniteFunctionsUC::getVal($data, "is_active");
		$addHtml = UniteFunctionsUC::getVal($data, "add_html");
		$isweb = UniteFunctionsUC::getVal($data, "isweb");
		$fontIcon = UniteFunctionsUC::getVal($data, "font_icon");
		$urlEdit = UniteFunctionsUC::getVal($data, "url_edit");
		$urlViewPost = UniteFunctionsUC::getVal($data, "url_view_post");
		$isGroup = UniteFunctionsUC::getVal($data, "is_group");
		$isGroup = UniteFunctionsUC::strToBool($isGroup);
		
				
		$liAddHTML = "";
		
		$state = null;
		
		if($isweb == true){
						
			$urlPreview = UniteFunctionsUC::getVal($data, "image");
			$isActive = true;
			$webData = $this->getWebAddonData($data);
			
			$addHtml .= $webData["html_state"];
			$addHtml .= $webData["html_additions"];
			$state = $webData["state"];
			
			$itemID = UniteFunctionsUC::getSerialID("webaddon");
			$liAddHTML = " data-itemtype='web' data-state='{$state}'";
		}
		
		UniteFunctionsUC::validateNotEmpty($itemID, "item id");
		
		$addHtml = $this->getAddonAdminHtml_AddHtml($addHtml, $objAddon);
		
		//put add html for layout
		if($this->isLayouts == true){
			
			//add group if available
			if($isGroup == true){
				
				$stateLabel = __("Template Kit","unlimited_elements");
				$htmlState = "<div class='uc-state-label uc-state-group'>
					<div class='uc-state-label-text'>{$stateLabel}</div>
				</div>";
				
				$addHtml .= $htmlState;
				$liAddHTML .= " data-isgroup='true'";
			}
			
		}
		
		//--- prepare output
				
		$title = htmlspecialchars($title);
		$name = htmlspecialchars($name);
		$description = htmlspecialchars($description);
		
		$descOutput = $description;
		
		$htmlPreview = "";
		
		$class = "uc-addon-thumbnail";
		$classThumb = "";
		$styleThumb = "";
		
		
		if(empty($urlPreview)){
			$classThumb = " uc-no-thumb";
			
			//replace by default preview
			if(!empty($urlPreviewDefault)){
				$classThumb = " uc-default-preview";
				$urlPreview = $urlPreviewDefault;
			}
		}

		
		if(!empty($urlPreview)){			
			$styleThumb = "style=\"background-image:url('{$urlPreview}')\"";
		}

		if($this->showAddonTooltip === true && !empty($urlPreview)){
			$urlPreviewHtml = htmlspecialchars($urlPreview);
			$htmlPreview = "data-preview='$urlPreviewHtml'";
		}
		
		
		if($isActive == false)
			$class .= " uc-item-notactive";
		
		if($isweb == true)
			$class .= " uc-item-web";
			
		$class = "class=\"{$class}\"";
		
		$addData = "";
		if(!empty($urlEdit)){
			$liAddHTML .= " data-urledit=\"$urlEdit\"";
		}
		
		if(!empty($urlViewPost))
			$liAddHTML .= " data-urlview=\"$urlViewPost\"";
		
		//set html output
		$htmlItem  = "<li id=\"uc_item_{$itemID}\" data-id=\"{$itemID}\" data-title=\"{$title}\" data-name=\"{$name}\" data-description=\"{$description}\" {$liAddHTML} {$htmlPreview} {$class} >";
		
		if($state == UniteCreatorBrowser::STATE_PRO){
			$urlBuy = $this->urlBuy;
						
			$htmlItem .= "<a class='uc-link-item-pro' href='$urlBuy' target='_blank'>";
		}
		
			
			
		//add icon to title
		if(!empty($fontIcon))
			$title = "<i class=\"$fontIcon\"></i> ".$title;
		
		//if svg type - set preview url as svg
		if($this->objAddonType->isSVG == true){
			
			$classThumb .= " uc-type-shape-devider";
			
			if($isweb == false){
				$urlPreview = null;
				
				$svgContent = $objAddon->getHtml();
				$urlPreview = UniteFunctionsUC::encodeSVGForBGUrl($svgContent);
			}
			
		}
			
			//output thumb
			$htmlItem .= "	<div class=\"uc-item-thumb{$classThumb} unselectable\" unselectable=\"on\" {$styleThumb}>";
			
			//draw item actions
			$actionEdit = "edit_addon";
			if($isLayout == true){
				$actionEdit = "edit_addon_blank";
				
				if($isGroup == true)
					$actionEdit = "edit_layout_group";
			}
			
			$urlIconEdit = GlobalsUC::$urlPluginImages."icon_item_edit.svg";
			$urlIconPreview = GlobalsUC::$urlPluginImages."icon_item_preview.svg";
			$urlIconDuplicate = GlobalsUC::$urlPluginImages."icon_item_duplicate.svg";
			$urlIconMenu = GlobalsUC::$urlPluginImages."icon_item_menu.svg";
			
			$textPreview = __("Preview ", "unlimited_textdomain").$this->textSingle;
			$textEdit = __("Edit ", "unlimited_textdomain").$this->textSingle;
			
			if($isGroup == true){
				$textPreview = __("Preview Template Kit", "unlimited_textdomain");
				$textEdit = __("Edit Template Kit", "unlimited_textdomain");
			}
			
			$textDuplicate = __("Duplicate ", "unlimited_textdomain").$this->textSingle;
			
			
			$htmlItem .= "<div class=\"uc-item-actions\">";
			
			$htmlItem .= "	<a href='javascript:void(0)' class='uc-item-action uc-item-action-edit uc-tip' onfocus='this.blur()' data-action='{$actionEdit}' title='{$textEdit}' ><img src='{$urlIconEdit}'></a>";
			
			if($isGroup == false){
				$htmlItem .= "	<a href='javascript:void(0)' class='uc-item-action uc-item-action-preview uc-tip' onfocus='this.blur()' data-action='preview_addon' title='$textPreview'><img src='{$urlIconPreview}'></a>";
				$htmlItem .= "	<a href='javascript:void(0)' class='uc-item-action uc-item-action-duplicate uc-tip' onfocus='this.blur()' data-action='duplicate_item' title='$textDuplicate'><img src='{$urlIconDuplicate}'></a>";
			}
			
			$htmlItem .= "	<a href='javascript:void(0)' class='uc-item-action uc-item-action-menu' onfocus='this.blur()' data-action='open_menu'><img src='{$urlIconMenu}'></a>";
			
			$htmlItem .= "	<div class='unite-clear'></div>";
			
			$htmlItem .= "</div>";
			
			$htmlItem .= "</div>";
			
			
			$htmlItem .= "	<div class=\"uc-item-title unselectable\" unselectable=\"on\">{$title}</div>";
			
			if($addHtml)
				$htmlItem .= $addHtml;
			
		
		if($state == UniteCreatorBrowser::STATE_PRO){
			$htmlItem .= "</a>";
		}
		
		$htmlItem .= "</li>";
		
		
		return($htmlItem);
	}
	
	
	/**
	 * get html of cate items
	 */
	public function getCatAddonsHtml($catID, $title = "", $isweb = false, $params = array()){
		
		$addons = $this->getCatAddons($catID, $title, $isweb, $params);
		
		$htmlAddons = "";
		
		foreach($addons as $addon){
			
			$html = $this->getAddonAdminHtml($addon);
			$htmlAddons .= $html;
		}
		
		return($htmlAddons);
	}
	
	
	/**
	 * get html of categories and items.
	 */
	public function getCatsAndAddonsHtml($catID, $catTitle = "", $isweb = false, $params = array()){
		
		$arrCats = $this->getArrCats($params);
		
		
		//change category if needed
		$arrCatsAssoc = UniteFunctionsUC::arrayToAssoc($arrCats, "id");
		
		if(isset($arrCatsAssoc[$catID]) == false){
			
			$catID = null;
			
			$firstCat = reset($arrCats);
			
			if(!empty($firstCat)){
				$catID = $firstCat["id"];
				$catTitle = $firstCat["title"];
				$isweb = UniteFunctionsUC::getVal($firstCat, "isweb");
				$isweb = UniteFunctionsUC::strToBool($isweb);
			}
		}
		
		
		$objCats = new UniteCreatorCategories();
		$htmlCatList = $this->getCatList($catID, null, $params);
		
		$htmlAddons = $this->getCatAddonsHtml($catID, $catTitle, $isweb, $params);
		
		$response = array();
		$response["htmlItems"] = $htmlAddons;
		$response["htmlCats"] = $htmlCatList;
	
		return($response);
	}
	
	
	/**
	 * set last selected category state
	 */
	private function setStateLastSelectedCat($catID){
		HelperUC::setState(self::STATE_LAST_ADDONS_CATEGORY, $catID);
	}
		
	
	/**
	 * set product from data
	 */
	private function setProductFromData($data){
		
		//get product
		$product = "";
		$passData = UniteFunctionsUC::getVal($data, "manager_passdata");
		if(empty($passData))
			return(false);
			
		$product = UniteFunctionsUC::getVal($passData, "product");
		
		if(empty($product))
			return(false);
		
		
		$this->product = $product;
		
		$this->objBrowser->setProduct($product);
		
	}
	
	
	/**
	 * get category items html
	 */
	public function getCatAddonsHtmlFromData($data){
				
		$this->validateAddonType();
				
		$catID = UniteFunctionsUC::getVal($data, "catID");
		$catTitle = UniteFunctionsUC::getVal($data, "title");
		$parentID = UniteFunctionsUC::getVal($data, "parent_id");
		
		$this->setProductFromData($data);
		
		$objAddons = new UniteCreatorAddons();
		
		$resonseCombo = UniteFunctionsUC::getVal($data, "response_combo");
		$resonseCombo = UniteFunctionsUC::strToBool($resonseCombo);
				
		$filterActive = UniteFunctionsUC::getVal($data, "filter_active");

		$filterSearch = UniteFunctionsUC::getVal($data, "filter_search");
		
		$filterSearch = trim($filterSearch);
		
		$isweb = UniteFunctionsUC::getVal($data, "isweb");
		$isweb = UniteFunctionsUC::strToBool($isweb);
		
		
		if($isweb == false && $catID != "all")
			UniteFunctionsUC::validateNumeric($catID,"category id");
		
		if(GlobalsUC::$enableWebCatalog == true){
			
			$filterCatalog = UniteFunctionsUC::getVal($data, "filter_catalog");
			self::setStateFilterCatalog($filterCatalog);
		}
		
		self::setStateFilterActive($filterActive);
		$this->setStateLastSelectedCat($catID);
		
		$params = array();
		
		if(!empty($filterSearch))
			$params["filter_search"] = $filterSearch;
		
		if(!empty($parentID)){
			$this->isInsideParent = true;
			$params["parent_id"] = $parentID;
		}
		
		if($resonseCombo == true){
			
			$response = $this->getCatsAndAddonsHtml($catID, $catTitle, $isweb, $params);
			
		}else{
			$itemsHtml = $this->getCatAddonsHtml($catID, $catTitle, $isweb, $params);
			$response = array("itemsHtml"=>$itemsHtml);
		}
		
		
		return($response);
	}
		
		
	private function a________DIALOGS________(){}
	
	
	/**
	 * put import addons dialog
	 */
	private function putDialogImportAddons(){
		
		$importText = esc_html__("Import ", "unlimited_elements").$this->textPlural;
		$textSelect = esc_html__("Select ","unlimited_elements") . $this->textPluralLower . __(" export zip file (or files)","unlimited_elements");
		$textLoader = esc_html__("Uploading ","unlimited_elements") . $this->textSingleLower. __(" file...", "unlimited_elements");
		$textSuccess = $this->textSingle . esc_html__(" Added Successfully", "unlimited_elements");
		
		$dialogTitle = $importText;
		
		$textOverwrite = esc_html__("Overwrite Existing ", "unlimited_elements").$this->textPlural;
		if($this->isLayouts == true)
			$textOverwrite = esc_html__("Overwrite Addons", "unlimited_elements");
		
		
		$nonce = "";
		if(method_exists("UniteProviderFunctionsUC", "getNonce"))
			$nonce = UniteProviderFunctionsUC::getNonce();
		?>
		
			<div id="dialog_import_addons" class="unite-inputs" title="<?php echo esc_attr($dialogTitle)?>" style="display:none;">
				
				<div class="unite-dialog-top"></div>
				
				<div class='dialog-import-addons-left'>
					
					<div class="unite-inputs-label">
						<?php echo esc_html($textSelect)?>:
					</div>
					
					<div class="unite-inputs-sap-small"></div>
					
					<form id="dialog_import_addons_form" action="<?php echo esc_attr($this->urlAjax)?>" name="form_import_addon" class="dropzone uc-import-addons-dropzone">
						<input type="hidden" name="action" value="<?php echo esc_attr($this->pluginName)?>_ajax_action">
						<input type="hidden" name="client_action" value="import_addons">
						
						<?php if(!empty($nonce)):?>
							<input type="hidden" name="nonce" value="<?php echo esc_attr($nonce)?>">
						<?php endif?>
						<script type="text/javascript">
							if(typeof Dropzone != "undefined")
								Dropzone.autoDiscover = false;
						</script>
					</form>	
						<div class="unite-inputs-sap-double"></div>
						
						<div class="unite-inputs-label">
							<?php esc_html_e("Import to Category", "unlimited_elements")?>:
							
						<select id="dialog_import_catname">
							<option value="autodetect" ><?php esc_html_e("[Autodetect]", "unlimited_elements")?></option>
							<option id="dialog_import_catname_specific" value="specific"><?php esc_html_e("Current Category", "unlimited_elements")?></option>
						</select>
							
						</div>
						
						<div class="unite-inputs-sap-double"></div>
						
						<div class="unite-inputs-label">
							<label for="dialog_import_check_overwrite">
							
								<input type="checkbox" checked="checked" id="dialog_import_check_overwrite"></input>
								
								<?php echo esc_html($textOverwrite) ?>:
								
							</label>
						</div>
						
				
				</div>
				
				<div id="dialog_import_addons_log" class='dialog-import-addons-right' style="display:none">
					
					<div class="unite-bold"> <?php echo esc_html($importText).esc_html__(" Log","unlimited_elements")?> </div>
					
					<br>
					
					<div id="dialog_import_addons_log_text" class="dialog-import-addons-log"></div>
				</div>
				
				<div class="unite-clear"></div>
				
				<?php 
					$prefix = "dialog_import_addons";
					$buttonTitle = $importText;
					$loaderTitle = $textLoader;
					$successTitle = $textSuccess;
					HelperHtmlUC::putDialogActions($prefix, $buttonTitle, $loaderTitle, $successTitle);
				?>
				
					
			</div>		
		<?php 
	}
	
	/**
	 * put quick edit dialog
	 */
	private function putDialogQuickEdit(){
		?>
			<!-- dialog quick edit -->
		
			<div id="dialog_edit_item_title"  title="<?php esc_html_e("Quick Edit","unlimited_elements")?>" style="display:none;">
			
				<div class="dialog_edit_title_inner unite-inputs mtop_20 mbottom_20" >
			
					<div class="unite-inputs-label-inline">
						<?php esc_html_e("Title", "unlimited_elements")?>:
					</div>
					<input type="text" id="dialog_quick_edit_title" class="unite-input-wide">
					
					
					<?php if($this->enableEnterName):?>
					<div class="unite-inputs-sap"></div>
							
					<div class="unite-inputs-label-inline">
						<?php esc_html_e("Name", "unlimited_elements")?>:
					</div>
					<input type="text" id="dialog_quick_edit_name" class="unite-input-wide">
					
					<?php else:?>
					
					<input type="hidden" id="dialog_quick_edit_name">
					
					<?php endif?>
					
					<div class="unite-inputs-sap"></div>
					
					<div class="unite-inputs-label-inline">
						<?php esc_html_e("Description", "unlimited_elements")?>:
					</div>
					
					<textarea class="unite-input-wide" id="dialog_quick_edit_description"></textarea>
					
				</div>
				
			</div>
		
		<?php 
	}

	
	/**
	 * put category edit dialog
	 */
	protected function putDialogEditCategory(){
		
		$prefix = "uc_dialog_edit_category";
		
		?>
			<div id="uc_dialog_edit_category" class="uc-dialog-edit-category" data-custom='yes' title="<?php esc_html_e("Edit Category","unlimited_elements")?>" style="display:none;" >
				
				<div class="unite-dialog-top"></div>
				
				<div class="unite-dialog-inner-constant">	
					<div id="<?php echo esc_attr($prefix)?>_settings_loader" class="loader_text"><?php esc_html_e("Loading Settings", "unlimited_elements")?>...</div>
					
					<div id="<?php echo esc_attr($prefix)?>_settings_content"></div>
					
				</div>
				
				<?php 
					$buttonTitle = esc_html__("Update Category", "unlimited_elements");
					$loaderTitle = esc_html__("Updating Category...", "unlimited_elements");
					$successTitle = esc_html__("Category Updated", "unlimited_elements");
					HelperHtmlUC::putDialogActions($prefix, $buttonTitle, $loaderTitle, $successTitle);
				?>
				
			</div>
		
		<?php
	}
	
	/**
	 * put category edit dialog
	 */
	protected function putDialogAddonProperties(){
		
		$prefix = "uc_dialog_addon_properties";
		
		$textTitle =  $this->textSingle.esc_html__(" Properties", "unlimited_elements");
		
		
		?>
			<div id="uc_dialog_addon_properties" class="uc-dialog-addon-properties" data-custom='yes' title="<?php echo esc_attr($textTitle)?>" style="display:none;" >
				
				<div class="unite-dialog-top"></div>
				
				<div class="unite-dialog-inner-constant">	
					<div id="<?php echo esc_attr($prefix)?>_settings_loader" class="loader_text uc-settings-loader"><?php esc_html_e("Loading Properties", "unlimited_elements")?>...</div>
					
					<div id="<?php echo esc_attr($prefix)?>_settings_content" class="uc-settings-content"></div>
					
				</div>
				
				<?php 
					$buttonTitle = esc_html__("Update ", "unlimited_elements").$this->textSingle;
					$loaderTitle = esc_html__("Updating...", "unlimited_elements");
					$successTitle = $this->textSingle.esc_html__(" Updated", "unlimited_elements");
					HelperHtmlUC::putDialogActions($prefix, $buttonTitle, $loaderTitle, $successTitle);
				?>			
				
			</div>
		
		<?php
	}
	
	
	/**
	 * put add addon dialog
	 */
	private function putDialogAddAddon(){
		
		$styleDesc = "";
		if($this->enableDescriptionField == false)
			$styleDesc = "style='display:none'";
		
		
		?>
			<!-- add addon dialog -->
			
			<div id="dialog_add_addon" class="unite-inputs" title="<?php echo esc_attr($this->textAddAddon)?>" style="display:none;">
			
				<div class="unite-dialog-top"></div>
			
				<div class="unite-inputs-label">
					<?php echo esc_html($this->textSingle).esc_html__(" Title", "unlimited_elements")?>:
				</div>
				
				<input type="text" id="dialog_add_addon_title" class="dialog_addon_input unite-input-100" />
				
				<?php if($this->enableEnterName):?>
				<div class="unite-inputs-sap"></div>
				
				<div class="unite-inputs-label">
					<?php echo esc_html($this->textSingle.__(" Name"))?>:
				</div>
				
				<input type="text" id="dialog_add_addon_name" class="dialog_addon_input unite-input-100" />
				
				<?php else:?>
				
				<input type="hidden" id="dialog_add_addon_name" value="" />
				
				<?php endif?>
				
				<?php 
					if($this->enableDescriptionField == false):		//description placeholder
					?>
					<div class="vert_sap30"></div>
					<?php 
					endif;
				?>
				
				<div class="unite-dialog-description-wrapper" <?php echo $styleDesc?>>
					
					<div class="unite-inputs-sap"></div>
					
					<div class="unite-inputs-label">
						<?php echo esc_html($this->textSingle).esc_html__(" Description")?>:
					</div>
					
					<textarea id="dialog_add_addon_description" class="dialog_addon_input unite-input-100" ></textarea>
				</div>
				
				<?php 
				
					$prefix = "dialog_add_addon";
					$buttonTitle = $this->textAddAddon;
					$loaderTitle = esc_html__("Adding ","unlimited_elements").$this->textSingle."...";
					$successTitle = $this->textSingle. esc_html__(" Added Successfully", "unlimited_elements");
					HelperHtmlUC::putDialogActions($prefix, $buttonTitle, $loaderTitle, $successTitle);
				?>
				
			</div>
		
		<?php 
	}	
	
	/**
	 * put preview addon dialog
	 */
	private function putDialogPreviewAddons(){
		
		$textPreviw = "Preview ".$this->textSingle;
		
		$textPreviw = htmlspecialchars($textPreviw);
		
		?>				
		
		<div id="uc_dialog_item_preview" title="<?php echo $textPreviw?>" style="display:none;">
			
			<iframe src="" width="100%" height="100%"  style="overflow-x: hidden;overflow-y:auto;">
			
		</iframe>
		
		</div>
		
		<?php 
	}
	
	private function a______MENUS_______(){}
	
	
	/**
	 * get single item menu
	 */
	protected function getMenuSingleItem(){
		
		$arrMenuItem = array();
		
		if($this->isLayouts == false){
			$arrMenuItem["edit_addon"] = esc_html__("Edit ","unlimited_elements").$this->textSingle;
			$arrMenuItem["edit_addon_blank"] = esc_html__("Edit In New Tab","unlimited_elements");
		}else{
			$arrMenuItem["edit_addon_blank"] = esc_html__("Edit ","unlimited_elements").$this->textSingle;			
		}
	
		if($this->enableEditGroup)
			$arrMenuItem["edit_layout_group"] = esc_html__("Edit Template Kit","unlimited_elements");
		
		if($this->enablePreview == true)
			$arrMenuItem["preview_addon"] = esc_html__("Preview","unlimited_elements");
		
		if($this->enableViewThumbnail)
			$arrMenuItem["preview_thumb"] = esc_html__("View Thumbnail","unlimited_elements");
		
		if($this->enableMakeScreenshots)
			$arrMenuItem["make_screenshots"] = esc_html__("Make Thumbnail","unlimited_elements");
		
			
		$arrMenuItem["quick_edit"] = esc_html__("Quick Edit","unlimited_elements");
		
		if($this->enableCopy == true)
			$arrMenuItem["copy"] = esc_html__("Copy","unlimited_elements");
		
			
		$arrMenuItem["remove_item"] = esc_html__("Delete","unlimited_elements");
		
		if($this->showTestAddon){
			$arrMenuItem["test_addon"] = esc_html__("Test ","unlimited_elements").$this->textSingle;
			$arrMenuItem["test_addon_blank"] = esc_html__("Test In New Tab","unlimited_elements");
		}	
		
		$arrMenuItem["export_addon"] = esc_html__("Export ","unlimited_elements").$this->textSingle;
		
		$arrMenuItem = UniteProviderFunctionsUC::applyFilters(UniteCreatorFilters::FILTER_MANAGER_MENU_SINGLE, $arrMenuItem);
		
		return($arrMenuItem);
	}

	
	
	/**
	 * get item field menu
	 */
	protected function getMenuField(){
		$arrMenuField = array();
				
		$arrMenuField["select_all"] = esc_html__("Select All","unlimited_elements");
		
		$arrMenuField = UniteProviderFunctionsUC::applyFilters(UniteCreatorFilters::FILTER_MANAGER_MENU_FIELD, $arrMenuField);
		
		return($arrMenuField);
	}
	
	
	
	/**
	 * get multiple items menu
	 */
	protected function getMenuMulitipleItems(){
		$arrMenuItemMultiple = array();
		$arrMenuItemMultiple["remove_item"] = esc_html__("Delete","unlimited_elements");
		
		if($this->enableMakeScreenshots == true)
			$arrMenuItemMultiple["make_screenshots"] = esc_html__("Make Thumbnails","unlimited_elements");
		
		$arrMenuItemMultiple = UniteProviderFunctionsUC::applyFilters(UniteCreatorFilters::FILTER_MANAGER_MENU_MULTIPLE, $arrMenuItemMultiple);
		
		return($arrMenuItemMultiple);
	}
	
	
	/**
	 * get category menu
	 */
	protected function getMenuCategory(){
	
		$arrMenuCat = array();
		$arrMenuCat["edit_category"] = esc_html__("Edit Category","unlimited_elements");
		$arrMenuCat["delete_category"] = esc_html__("Delete Category","unlimited_elements");
		
		
		$arrMenuCat = UniteProviderFunctionsUC::applyFilters(UniteCreatorFilters::FILTER_MANAGER_MENU_CATEGORY, $arrMenuCat);
		
		return($arrMenuCat);
	}
	
	private function a_______DATA______(){}
	
	
	/**
	 * filter categories without web addons
	 */
	private function filterCatsWithoutWeb($arrCats){
		
		foreach($arrCats as $key=>$cat){
			$isweb = UniteFunctionsUC::getVal($cat, "isweb");
			$isweb = UniteFunctionsUC::strToBool($isweb);
			if($isweb == true)
				continue;
			
			$numWebAddons = UniteFunctionsUC::getVal($cat, "num_web_addons");
			if($numWebAddons == 0)
				unset($arrCats[$key]);
		}
		
		return($arrCats);
	}
	
	
	/**
	 * get categories with catalog
	 */
	private function getCatsWithCatalog($filterCatalog, $params = array()){
		
		$objAddons = new UniteCreatorAddons();
		$webAPI = $this->getWebAPI();
		
		$arrCats = $objAddons->getAddonsWidthCategories(true, true, $this->filterAddonType, $params);
		
		
		$arrCats = $this->modifyLocalCats($arrCats);

		
		if($this->objAddonType->allowManagerWebCatalog == true)
			$arrCats = $webAPI->mergeCatsAndAddonsWithCatalog($arrCats, true, $this->objAddonType, $params);
		
		if($filterCatalog == self::FILTER_CATALOG_WEB)
			$arrCats = $this->filterCatsWithoutWeb($arrCats);
		
			
		return($arrCats);
	}
	
	
	/**
	 * modify local categories - create one if empty, and required
	 */
	protected function modifyLocalCats($arrCats){
		
		if(!empty($arrCats))
			return($arrCats);
		
		if($this->objAddonType->allowNoCategory == true)
			return($arrCats);

		//add default category
		
		$objCategory = new UniteCreatorCategory();
		$objCategory->addDefaultByAddonType($this->objAddonType);
		
		$arrCats = $this->objCats->getListExtra($this->objAddonType);
		
		return($arrCats);
	}
	
	
	/**
	 * get categories
	 */
	protected function getArrCats($params = array()){

		$filterCatalog = $this->getStateFilterCatalog();
		
		switch($filterCatalog){
			case self::FILTER_CATALOG_MIXED:
			case self::FILTER_CATALOG_WEB:
				$arrCats = $this->getCatsWithCatalog($filterCatalog, $params);
			break;
			default:	//installed type
				
				$filterSearch = UniteFunctionsUC::getVal($params, "filter_search");
				if(empty($filterSearch))
					$filterSearch = "";
				
				$filterSearch = trim($filterSearch);
				
				$catsParams = array();
				if(!empty($filterSearch))
					$catsParams["filter_search_addons"] = $filterSearch;
				
				$arrCats = $this->objCats->getListExtra($this->objAddonType, "","", false, $catsParams);
								
				$arrCats = $this->modifyLocalCats($arrCats);
				
			break;
		}
				
		
		
		return($arrCats);
	}
	
	
	/**
	 * get category list
	 */
	protected function getCatList($selectCatID = null, $arrCats = null, $params = array()){
		
		if($arrCats === null)
			$arrCats = $this->getArrCats($params);
					
		$htmlCatList = $this->objCats->getHtmlCatList($selectCatID, $this->objAddonType, $arrCats);
		
		
		return($htmlCatList);
	}
	
	
	/**
	 * get cat list from data
	 */
	public function getCatListFromData($data){
		
		$selectedCat = UniteFunctionsUC::getVal($data, "selected_catid");
		$filterActive = UniteFunctionsUC::getVal($data, "filter_active");
		$filterCatalog = UniteFunctionsUC::getVal($data, "filter_catalog");
				
		$typeDistinct = $this->objAddonType->typeNameDistinct;
		
		self::setStateFilterActive($filterActive, $typeDistinct);
		self::setStateFilterCatalog($filterCatalog, $typeDistinct);
		
		$htmlCats = $this->getCatList($selectedCat);
		
		$response = array();
		$response["htmlCats"] = $htmlCats;
		
		return($response);
	}
	
	
	/**
	 * get category settings from cat ID
	 */
	protected function getCatagorySettings(UniteCreatorCategory $objCat){
		
		$title = $objCat->getTitle();
		$alias = $objCat->getAlias();
		$params = $objCat->getParams();
		$catID = $objCat->getID();
		
		$settings = new UniteCreatorSettings();
		
		$settings->addStaticText("Category ID: <b>$catID</b>","some_name");
		$settings->addTextBox("category_title", $title, esc_html__("Category Title","unlimited_elements"));
		$settings->addTextBox("category_alias", $alias, esc_html__("Category Name","unlimited_elements"));
		$settings->addIconPicker("icon","",esc_html__("Category Icon", "unlimited_elements"));
		
		$settings = UniteProviderFunctionsUC::applyFilters(UniteCreatorFilters::FILTER_MANAGER_ADDONS_CATEGORY_SETTINGS, $settings, $objCat, $this->filterAddonType);
		
		$settings->setStoredValues($params);
		
		return($settings);
	}
	
	private function a______HEADER_LINE______(){}
	
	/**
	 * put catalog filters
	 */
	protected function putFiltersCatalog(){
		
		if(GlobalsUC::$enableWebCatalog == false)
			return(false);
		
		if($this->objAddonType->allowManagerWebCatalog == false)
			return(false); 
		
		$classActive = "class='uc-active'";
		
		$filterCatalog = $this->filterCatalogState;
		
		$addParams = "";
		if($filterCatalog == self::FILTER_CATALOG_INSTALLED)
			$addParams = " checked='checked'";
		
		?>
			<div class="uc-filter-set-wrapper uc-filter-set-checkbox">
				<label>
					<input id="uc_filter_catalog_installed" type="checkbox" data-state_active="<?php echo self::FILTER_CATALOG_INSTALLED?>" data-state_notactive="<?php echo self::FILTER_CATALOG_MIXED?>" <?php echo $addParams?>>
					<?php _e("Show Only Installed", "unlimited_elements")?>
				</label>
			</div>
			
		<?php 
	}
	
	/**
	 * put search filter
	 */
	protected function putFilterSearch(){
				
		$textPlaceholder = __("Search...","unlimited_elements");
		
		?>			
			<div class="uc-filters-set-search">
							
				<input id="uc_manager_addons_input_search" class="uc-filter-search-input" type="text" placeholder="<?php echo $textPlaceholder?>">
				
				<i id="uc_manager_addons_icon_search" class="fa fa-search uc-icon-search" title="<?php _e("Search Widget","unlimited_elements")?>"></i>
				
				<a id="uc_manager_addons_clear_search" href="javascript:void(0)" onfocus="this.blur()" class="uc-filter-button-clear" title="<?php _e("Clear Search","unlimited_elements")?>" style="display:none" >
					<i class="fa fa-times uc-icon-clear"></i>
				</a>
			</div>
		
		<?php 
		
	}
	
	
	/**
	 * put items filters links
	 */
	private function putItemsFilters_active(){
		
		$classActive = "class='uc-active'";
		$filter = $this->filterActive;
		if(empty($filter))
			$filter = "all";
					
		//show only if installed
		$style = "style='display:none'";
		if($this->filterCatalogState == "installed")
			$style = "";
		
		$arrFilter = array();
		$arrFilter["all"] = __("Show all states", "unlimited_elements");
		$arrFilter["active"] = __("Active state only","unlimited_elements");
		$arrFilter["not_active"] = __("Not active state only","unlimited_elements");
		
		$htmlSelect = HelperHtmlUC::getHTMLSelect($arrFilter, $filter, "id='uc_manager_filter_active' class='uc-select-filter-active'", true);
		
		?>
		<div class="uc-filter-set-wrapper uc-filter-set-active" <?php echo $style?>>
			
			<?php echo $htmlSelect ?>
			
		</div>
		<?php 
	}
	
	
	/**
	 * put filters - function for override
	 */
	private function putHeaderLineFilters(){
		
		?>
		
		<div class="uc-items-filters">
		
			<?php 
				if($this->enableActiveFilter)
					$this->putItemsFilters_active();
			?>
			
			<?php $this->putFiltersCatalog()?>
			
			<?php 
				if($this->enableSearchFilter == true)
					$this->putFilterSearch();
			?>
			
			<?php $this->putShortcode()?>
			
			<div class="unite-clear"></div>
			
		</div>
		
		<?php 
	}
	
	
	/**
	 * put html header line
	 * function for override
	 */
	protected function putHtmlHeaderLine(){
		
		$urlLogo = GlobalsUC::$urlPluginImages."logo_unlimited.svg";
		$logoWidth = "216";
		
		if(GlobalsUC::$isProVersion == true){
			$urlLogo = GlobalsUC::$urlPluginImages."logo_unlimited-pro.svg";
			$logoWidth = "256";
		}
		
		?>
		<div class="uc-manager-header-line">
			
			<img class="uc-manager-header-logo" src="<?php echo $urlLogo?>" width="<?php echo $logoWidth?>">
			
			<?php if(!empty($this->headerLineText)):?>
			<div class="uc-manager-header-text">
				<?php echo $this->headerLineText?>
			</div>
			<?php endif?>
			
			<div class="uc-manager-header-filters">
				<?php $this->putHeaderLineFilters()?>
			</div>
			
			<div class="unite-clear"></div>
			
		</div>
		
		<?php 
		
	}

		/**
	 * put after buttons html
	 */
	protected function putHtmlAfterButtons(){
		
		if($this->enableEditGroup == false)
			return(false);
		
		?>
		 	<div id="uc_manager_group" class="uc-manager-group">
		 		
		 		<a href="javascript:void(0)" class="uc-manager-group-back"><?php _e("Back To Category","unlimited_elements")?></a>
		 		
		 		<div class="uc-manager-group-text"><?php _e("Template Kit","unlimited_elements")?></div>
		 				 		
		 	</div>
		
		<?php 
		
	}
	
	private function a______STATUS_LINE______(){}
	
	/**
	 * add copy panel to status line
	 * 
	 */
	protected function putStatusLineOperationsAdditions(){
		
		if($this->enableCopy == true):
		?>
		<div class="item_operations_wrapper uc-bottom-copypanel" style="display:none">
			
			 <?php _e("Copied", "unlimited_elements")?>: <span class="uc-copypanel-addon"></span>
			 
			 <a class="unite-button-secondary button-disabled uc-button-copypanel-move" href="javascript:void(0)"><?php _e("Move Here","unlimited_elements")?></a>
			 <a class="unite-button-secondary uc-button-copypanel-cancel" href="javascript:void(0)"><?php _e("Cancel")?></a>
		 </div>
		
		<?php 
		endif;
		
	}
	
	
	private function a______OTHERS______(){}
	
	
	
	/**
	 * get addon type object
	 */
	public function getObjAddonType(){
		
		return($this->objAddonType);
	}
	
	/**
	 * return if layouts or addons type
	 */
	public function getIsLayoutType(){
		$this->validateAddonType();
		
		return($this->isLayouts);
	}
	
	
	/**
	 * get no items text
	 */
	protected function getNoItemsText(){
		
		$text = $this->objAddonType->textNoAddons;

		UniteFunctionsUC::validateNotEmpty($text,"text addon type");
		
		return($text);
	}
	
	
	/**
	 * get html categories select
	 */
	protected function getHtmlSelectCats(){
		
		if($this->hasCats == false)
			UniteFunctionsUC::throwError("the function ");
		
		$htmlSelectCats = $this->objCats->getHtmlSelectCats($this->filterAddonType);
		
		return($htmlSelectCats);
	}
	
	
	/**
	 * put content to items wrapper div
	 */
	protected function putListWrapperContent(){
		$addonType = $this->filterAddonType;
		if(empty($addonType))
			$addonType = "default";
		
		$filepathEmptyAddons = GlobalsUC::$pathProviderViews."empty_addons_text_{$addonType}.php";
		if(file_exists($filepathEmptyAddons) == false)
			return(false);
		
		?>
		<div id="uc_empty_addons_wrapper" class="uc-empty-addons-wrapper" style="display:none">
			
			<?php include $filepathEmptyAddons?>
			
		</div>
		<?php 
	}
	
	/**
	 * put buttons temp
	 * Enter description here ...
	 */
	protected function putItemsButtonsTemp(){
		/*
		 
 			<a data-action="select_all_items" type="button" class="unite-button-secondary button-disabled uc-button-item uc-button-select" data-textselect="<?php esc_html_e("Select All","unlimited_elements")?>" data-textunselect="<?php esc_html_e("Unselect All","unlimited_elements")?>"><?php esc_html_e("Select All","unlimited_elements")?></a>
	 		<a data-action="quick_edit" type="button" class="unite-button-secondary button-disabled uc-button-item uc-single-item"><?php esc_html_e("Quick Edit","unlimited_elements")?></a>
		 
		 */
	}
	
	
	/**
	 * put multiple buttons
	 */
	protected function putMultipleItemButtons(){
		?>
		 	<a data-action="remove_item" type="button" class="unite-button-secondary button-disabled uc-button-item uc-multiple-items"><?php esc_html_e("Delete","unlimited_elements")?></a>
		 	<a data-action="duplicate_item" type="button" class="unite-button-secondary button-disabled uc-button-item uc-multiple-items"><?php esc_html_e("Duplicate","unlimited_elements")?></a>
		
	 		<?php if($this->enableActiveFilter == true):?>
	 			
		 		<a data-action="activate_addons" type="button" class="unite-button-secondary button-disabled uc-button-item uc-notactive-item uc-multiple-items"><?php esc_html_e("Activate","unlimited_elements")?></a>
		 		<a data-action="deactivate_addons" type="button" class="unite-button-secondary button-disabled uc-button-item uc-active-item uc-multiple-items"><?php esc_html_e("Deactivate","unlimited_elements")?></a>
	 		
	 		<?php endif?>
		
		<?php 
	}
	
	
	/**
	 * put items buttons
	 */
	protected function putItemsButtons(){
		
		$textImport = esc_html__("Import ","unlimited_elements") . $this->textPlural;
		$textEdit = esc_html__("Edit ","unlimited_elements") . $this->textSingle;
		$textTest = "Test ".$this->textSingle;
		
		?>
			
			<?php 
			 UniteProviderFunctionsUC::doAction(UniteCreatorFilters::ACTION_MANAGER_ITEM_BUTTONS1);
			?>
 			<a data-action="add_addon" type="button" class="unite-button-primary button-disabled uc-button-item uc-button-add"><?php echo esc_html($this->textAddAddon)?></a> 
 			<a data-action="import_addon" type="button" class="unite-button-secondary button-disabled uc-button-item uc-button-add"><?php echo esc_html($textImport)?></a>
 			 			
 			<?php 
				if($this->putItemButtonsType == "multiple"){
					$this->putMultipleItemButtons();
					return(false);
				}
 			?>
 			
			<?php 
			 UniteProviderFunctionsUC::doAction(UniteCreatorFilters::ACTION_MANAGER_ITEM_BUTTONS2);
			?>
 							
		 		<a data-action="remove_item" type="button" class="unite-button-secondary button-disabled uc-button-item"><?php esc_html_e("Delete","unlimited_elements")?></a>
		 		<a data-action="edit_addon" type="button" class="unite-button-primary button-disabled uc-button-item uc-single-item"><?php echo esc_html($textEdit)?> </a>
		 		<a data-action="preview_addon" type="button" class="unite-button-secondary button-disabled uc-button-item uc-single-item"><?php esc_html_e("Preview", "unlimited_elements")?> </a>
	 			
		 		<?php if($this->showTestAddon):?>
		 		<a data-action="test_addon" type="button" class="unite-button-secondary button-disabled uc-button-item uc-single-item"><?php echo esc_html($textTest)?></a>
				<?php endif?>
						
				<?php 
				 UniteProviderFunctionsUC::doAction(UniteCreatorFilters::ACTION_MANAGER_ITEM_BUTTONS3);
				?>
			
				<?php if($this->enablePreview == true):?>
		 		
		 			<a data-action="preview_addon" type="button" class="unite-button-secondary button-disabled uc-button-item uc-single-item"><?php esc_html_e("Preview", "unlimited_elements")?> </a>
				
				<?php endif?>
							
	 		<?php if($this->enableActiveFilter == true):?>
	 			
		 		<a data-action="activate_addons" type="button" class="unite-button-secondary button-disabled uc-button-item uc-notactive-item"><?php esc_html_e("Activate","unlimited_elements")?></a>
		 		<a data-action="deactivate_addons" type="button" class="unite-button-secondary button-disabled uc-button-item uc-active-item"><?php esc_html_e("Deactivate","unlimited_elements")?></a>
	 		
	 		<?php endif?>
	 		
	 		<?php if($this->enableMakeScreenshots == true):?>
	 		
	 		<a data-action="make_screenshots" type="button" class="unite-button-secondary button-disabled uc-button-item uc-single-item"><?php esc_html_e("Make Thumb", "unlimited_elements")?> </a>
	 		<a data-action="make_screenshots" type="button" class="unite-button-secondary button-disabled uc-button-item uc-multiple-items"><?php esc_html_e("Make Thumbs", "unlimited_elements")?> </a>
	 		
	 		<?php endif?>
		<?php
	}
	
	/**
	 * get current layout shortcode template
	 */
	protected function getShortcodeTemplate(){
		
		$shortcodeTemplate = "{blox_page id=%id% title=\"%title%\"}";
		
		return($shortcodeTemplate);
	}
	
	
	/**
	 * put shortcode in the filters area
	 */
	protected function putShortcode(){
	
		if($this->objAddonType->enableShortcodes == false)
			return(false);
		
		$shortcodeTemplate = $this->getShortcodeTemplate();
		$shortcodeTemplate = htmlspecialchars($shortcodeTemplate);
		
		?>
		<div class="uc-single-item-related">
			<div class="uc-filters-set-title"><?php esc_html_e("Shortcode", "unlimited_elements")?>:</div>
			<div class="uc-filters-set-content"> <input type="text" readonly class="uc-filers-set-shortcode" data-template="<?php echo esc_attr($shortcodeTemplate)?>" value=""></div>
		</div>
		
		<?php 
		
	}
	
	
	/**
	 * get category settings html
	 */
	public function getCatSettingsHtmlFromData($data){
		
		$catID = UniteFunctionsUC::getVal($data, "catid");
		UniteFunctionsUC::validateNotEmpty($catID, "category id");
		
		$objCat = new UniteCreatorCategory();
		$objCat->initByID($catID);
		
		$settings = $this->getCatagorySettings($objCat);
		
		$output = new UniteSettingsOutputWideUC();
		$output->init($settings);
		
		ob_start();
		$output->draw("uc_category_settings");
		
		$htmlSettings = ob_get_contents();
		
		ob_end_clean();
		
		$response = array();
		$response["html"] = $htmlSettings;
		
		return($response);
	}
	
	/**
	 * 
	 * get properties html from data
	 */
	public function getAddonPropertiesDialogHtmlFromData($data){
		
		if($this->objAddonType->isLayout == false)
			UniteFunctionsUC::throwError("The addon type should be layouts for props");
		
		$layoutID = UniteFunctionsUC::getVal($data, "id");
		$objLayout = new UniteCreatorLayout();
		$objLayout->initByID($layoutID);
		
		$settings = $objLayout->getPageParamsSettingsObject();
		
		$htmlSettings = HelperHtmlUC::drawSettingsGetHtml($settings,"settings_addon_props");
		
		$output = array();
		$output["html"] = $htmlSettings;
		
		return($output);
	}
	
	
	
	
	

	/**
	 * put scripts
	 */
	private function putScripts(){
		
		$arrPlugins = UniteProviderFunctionsUC::applyFilters(UniteCreatorFilters::FILTER_MANAGER_ADDONS_PLUGINS, array());
				
		$script = "
			var g_ucManagerAdmin;
			
			jQuery(document).ready(function(){
				var selectedCatID = \"{$this->selectedCategory}\";
				g_ucManagerAdmin = new UCManagerAdmin();";
		
		if(!empty($arrPlugins)){
			foreach($arrPlugins as $plugin)
				$script .= "\n				g_ucManagerAdmin.addPlugin('{$plugin}');";
		}
		
		$script .= "
				g_ucManagerAdmin.initManager(selectedCatID);
			});
		";
		
		
		UniteProviderFunctionsUC::printCustomScript($script);
	}
	
	
	/**
	 * put preview tooltips
	 */
	protected function putPreviewTooltips(){
		?>
		<div id="uc_manager_addon_preview" class="uc-addon-preview-wrapper" style="display:none"></div>
		<?php 
	}
	
	/**
	 * get single item menu
	 */
	protected function getMenuSingleItemActions(){
		
		$arrMenuItem = array();
		$arrMenuItem["edit_addon_blank"] = esc_html__("Edit In New Tab","unlimited_elements");
		
		if($this->enableEditGroup)
			$arrMenuItem["edit_layout_group"] = esc_html__("Edit Template Kit","unlimited_elements");
		
		if($this->enableViewThumbnail)
			$arrMenuItem["preview_thumb"] = esc_html__("View Thumbnail","unlimited_elements");
		
		if($this->enableMakeScreenshots)
			$arrMenuItem["make_screenshots"] = esc_html__("Make Thumbnail","unlimited_elements");
		
		$arrMenuItem["quick_edit"] = esc_html__("Quick Edit","unlimited_elements");
		
		if($this->enableCopy == true)
			$arrMenuItem["copy"] = esc_html__("Copy","unlimited_elements");
		
		$arrMenuItem["remove_item"] = esc_html__("Delete","unlimited_elements");
		
		if($this->showTestAddon){
			$arrMenuItem["test_addon"] = esc_html__("Test ","unlimited_elements").$this->textSingle;
			$arrMenuItem["test_addon_blank"] = esc_html__("Test In New Tab","unlimited_elements");
		}	
		
		$arrMenuItem["export_addon"] = esc_html__("Export ","unlimited_elements").$this->textSingle;
		
		return($arrMenuItem);
	}
	
	
	/**
	 * put single item actions menu
	 */
	private function putMenuSingleItemActions(){
		
		$arrMenuItem = $this->getMenuSingleItemActions();
		
		if(!is_array($arrMenuItem))
			$arrMenuItem = array();
		
		$this->putRightMenu($arrMenuItem, "rightmenu_item_actions", "single_item_actions");
		
	}
	
	
	/**
	 * put additional html here
	 */
	protected function putAddHtml(){
		
		$this->putDialogQuickEdit();
		$this->putDialogAddAddon();
		$this->putDialogAddonProperties();
		$this->putDialogImportAddons();
		$this->putDialogPreviewAddons();
		
		$this->putMenuSingleItemActions();
		
		if($this->showAddonTooltip)
			$this->putPreviewTooltips();
		
		$this->putScripts();
	}
	
	
	/**
	 * put init items, will not run, because always there are cats
	 */
	protected function putInitItems(){
		
		if($this->hasCats == true)
			return(false);
		
		$htmlAddons = $this->getCatAddonsHtml(null);
		
		echo $htmlAddons;
	}
	
	
	/**
	 * 
	 * set the custom data to manager wrapper div
	 */
	protected function onBeforePutHtml(){
				
		$addonsType = $this->objAddonType->typeNameDistinct;
		
		$addHTML = "data-addonstype=\"{$addonsType}\"";
		
		$this->setManagerAddHtml($addHTML); 
	}
	
		
	
}