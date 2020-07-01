<?php
/**
 * @package Unlimited Elements
 * @author UniteCMS.net
 * @copyright (C) 2017 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ELEMENTS_INC') or die('Restricted access');

class UniteCreatorActivationViewProvider extends UniteCreatorActivationView{
	
	const ENABLE_STAND_ALONE = true;
	
	/**
	 * init by envato
	 */
	private function initByEnvato(){
				
		$this->textGoPro = esc_html__("Activate Blox Pro", "unlimited_elements");
		
		if(self::ENABLE_STAND_ALONE == true)
			$this->textGoPro = esc_html__("Activate Blox Pro - Envato", "unlimited_elements");
		
		$this->textPasteActivationKey = esc_html__("Paste your envato purchase code here <br> from the pro version item", "unlimited_elements");
		$this->textPlaceholder = esc_html__("xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx","unlimited_elements");
		
		$this->textLinkToBuy = null; 
		$this->urlPricing = null;
		
		$this->textDontHave = esc_html__("We used to sell this product in codecanyon.net <br> Activate from this screen only if you bought it there.","unlimited_elements");
		
		$this->textActivationFailed = esc_html__("You probably got your purchase code wrong", "unlimited_elements");
		$this->codeType = self::CODE_TYPE_ENVATO;
		$this->isExpireEnabled = false;
		
		
		if(self::ENABLE_STAND_ALONE == true){
			
			$urlRegular = HelperUC::getViewUrl("license");
			$htmlLink = HelperHtmlUC::getHtmlLink($urlRegular, esc_html__("Activate With Blox Key", "unlimited_elements"),"","blue-text");
			
			$this->textSwitchTo = esc_html__("Don't have Envato Activation Key? ","unlimited_elements").$htmlLink;
		}
		
		$this->textDontHaveLogin = null;
		
	}
	
	
	/**
	 * init by blox wp
	 */
	private function initByBloxWP(){
		
		$urlEnvato = HelperUC::getViewUrl("license","envato=true");
		$htmlLink = HelperHtmlUC::getHtmlLink($urlEnvato, esc_html__("Activate With Envato Key", "unlimited_elements"),"","blue-text");
		
		$this->urlPricing = "http://blox-builder.com/go-pro/";
		$this->textSwitchTo = esc_html__("Have Envato Market Activation Key? ","unlimited_elements").$htmlLink;
		
	}
	
	
	
	/**
	 * init the variables
	 */
	public function __construct(){
				
		parent::__construct();
		
		$this->textGoPro = esc_html__("Activate Blox Pro", "unlimited_elements");
		$this->writeRefreshPageMessage = false;
		
		$isEnvato = UniteFunctionsUC::getGetVar("envato", "", UniteFunctionsUC::SANITIZE_KEY);
		$isEnvato = UniteFunctionsUC::strToBool($isEnvato);
		
		if(self::ENABLE_STAND_ALONE == false)
			$isEnvato = true;
		
		if($isEnvato == true)
			$this->initByEnvato();
		else
			$this->initByBloxWP();
			
	}
	
		
}