<?php


class UniteProviderCoreAdminUC_Elementor extends UniteProviderAdminUC{

	private $objFeedback;
	
	
	/**
	 * the constructor
	 */
	public function __construct($mainFilepath){
		
		$this->pluginName = GlobalsUnlimitedElements::PLUGIN_NAME;
		
		$this->textBuy = esc_html__("Activate Plugin", "unlimited_elements");
		
		$this->linkBuy = null;
		$this->coreAddonType = GlobalsUnlimitedElements::ADDONSTYPE_ELEMENTOR;
		$this->coreAddonsView = GlobalsUnlimitedElements::VIEW_ADDONS_ELEMENTOR;
		$this->pluginTitle = esc_html__("Unlimited Elements", "unlimited_elements");
		
		$this->arrAllowedViews = array(
			"addons_elementor",
			"licenseelementor", 
			"settingselementor", 
			"troubleshooting-overload",
			"troubleshooting-memory-usage",
			"troubleshooting-api-access",
			"testaddon",
			"addon",
			"addondefaults",
			"svg_shapes",
			"backgrounds",
			GlobalsUnlimitedElements::VIEW_TEMPLATES_ELEMENTOR,
			GlobalsUnlimitedElements::VIEW_CUSTOM_POST_TYPES,
			"test_settings"
		);
		
		HelperProviderCoreUC_EL::globalInit();
		
		//set permission
		$permission = HelperProviderCoreUC_EL::getGeneralSetting("edit_permission");
		
		if($permission == "editor")
			$this->setPermissionEditor();
		
		
		parent::__construct($mainFilepath);
	}
	
	
	/**
	 * modify category settings, add consolidate addons
	 */
	public function managerAddonsModifyCategorySettings($settings, $objCat, $filterType){
		
		if($filterType != UniteCreatorElementorIntegrate::ADDONS_TYPE)
			return($settings);
				
		$settings->updateSettingProperty("category_alias", "disabled", "true");
		$settings->updateSettingProperty("category_alias", "description", esc_html__("The category name is unchangable, because of the addons consolidation, if changed it could break the layout.", "unlimited_elements") );
				
		return($settings);
	}
		
	/**
	 * modify plugins view links
	 */
	public function modifyPluginViewLinks($arrLinks){
		
		if(GlobalsUC::$isProductActive == true)
			return($arrLinks);
		
		if(empty($this->linkBuy))
			return($arrLinks);
					
		$linkbuy = HelperHtmlUC::getHtmlLink($this->linkBuy, $this->textBuy,"","uc-link-gounlimited", true);
		
		$arrLinks["gounlimited"] = $linkbuy;
		
		return($arrLinks);
	}
	
	
	
	/**
	 * add admin menu links
	 */
	protected function addAdminMenuLinks(){
		
		$urlMenuIcon = HelperProviderCoreUC_EL::$urlCore."images/icon_menu.png";
				
		$mainMenuTitle = $this->pluginTitle;
		
		$this->addMenuPage($mainMenuTitle, "adminPages", $urlMenuIcon);
		
		$this->addSubMenuPage($this->coreAddonsView, __('Widgets for Elementor',"unlimited_elements"), "adminPages");
		
		if(GlobalsUC::$inDev == true){
			$this->addSubMenuPage(GlobalsUnlimitedElements::VIEW_TEMPLATES_ELEMENTOR, __('Templates',"unlimited_elements"), "adminPages");
/*			
			$this->addSubMenuPage(GlobalsUnlimitedElements::VIEW_SECTIONS_ELEMENTOR, __('Sections',"unlimited_elements"), "adminPages");
			$this->addSubMenuPage(GlobalsUnlimitedElements::VIEW_CUSTOM_POST_TYPES, __('Custom Post Types',"unlimited_elements"), "adminPages");
			$this->addSubMenuPage(GlobalsUnlimitedElements::VIEW_ICONS, __('SVG Shapes',"unlimited_elements"), "adminPages");
			$this->addSubMenuPage(GlobalsUnlimitedElements::VIEW_BACKGROUNDS, __('Section Backgrounds',"unlimited_elements"), "adminPages");
*/
		}
		
		$this->addSubMenuPage("settingselementor", __('General Settings',"unlimited_elements"), "adminPages");
		
		if(defined("UNLIMITED_ELEMENTS_UPRESS_VERSION")){
			
			if(GlobalsUC::$isProductActive == false && self::$view != GlobalsUnlimitedElements::VIEW_LICENSE_ELEMENTOR)
				HelperUC::addAdminNotice("The Unlimited Elements Plugin is not Active. Please activete it in license page.");
			
			$this->addSubMenuPage(GlobalsUnlimitedElements::VIEW_LICENSE_ELEMENTOR, __('Upress License',"unlimited_elements"), "adminPages");
		}
		
		$this->addLocalFilter("plugin_action_links_".$this->pluginFilebase, "modifyPluginViewLinks");
		
		//$isFsActivated = HelperProviderUC::isActivatedByFreemius();
		
		//if($isFsActivated == false)
			//$this->addSubMenuPage("licenseelementor", __('Old License Activation',"unlimited_elements"), "adminPages");
		
	}
	
	
	/**
	 * allow feedback on uninstall
	 */
	private function initFeedbackUninstall(){
		
		$this->objFeedback = new UnlimitedElementsFeedbackUC();
		
		$this->objFeedback->init();
		
	}
	
	
	
	/**
	 * init
	 */
	protected function init(){
		
		UniteProviderFunctionsUC::addFilter(UniteCreatorFilters::FILTER_MANAGER_ADDONS_CATEGORY_SETTINGS, array($this,"managerAddonsModifyCategorySettings"),10,3);
		
		if(GlobalsUnlimitedElements::ALLOW_FEEDBACK_ONUNINSTALL == true)
			$this->initFeedbackUninstall();
					
		parent::init();
	}
	
	
	
}