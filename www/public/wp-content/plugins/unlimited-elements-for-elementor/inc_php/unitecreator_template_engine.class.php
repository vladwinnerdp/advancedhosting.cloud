<?php
/**
 * @package Unlimited Elements
 * @author UniteCMS.net
 * @copyright (C) 2017 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ELEMENTS_INC') or die('Restricted access');


class UniteCreatorTemplateEngineWork{
	
	protected $twig;
	protected $arrTemplates = array();
	protected $arrParams = null;
	protected $arrItems = array();
	protected $addon = null;
	protected $objParamsProcessor;
	
	
	/**
	 * init twig	
	 */
	public function __construct(){
		
		$this->objParamsProcessor = new UniteCreatorParamsProcessor();
		
	}
	
	
	public function a_____CUSTOM_FUNCTIONS____(){}
	
	
	/**
	 * output some item
	 */
	private function outputItem($index, $itemParams, $templateName, $sap, $newLine = true){
			
		$params = array_merge($this->arrParams, $itemParams);
		
		$htmlItem = $this->twig->render($templateName, $params);
		
		if(!empty($sap)){
			if($index != 0)
				echo UniteProviderFunctionsUC::escCombinedHtml($sap);
			echo UniteProviderFunctionsUC::escCombinedHtml($htmlItem);
		}else
			echo UniteProviderFunctionsUC::escCombinedHtml($htmlItem);
		
		if($newLine)
			echo "\n";
	}
	
	
	/**
	 * put items actually
	 */
	private function putItemsWork($templateName, $sap=null, $numItem=null){
		
		if(empty($this->arrItems))
		 	return(false);
		
		if($this->isTemplateExists($templateName) == false)
			return(false);
		
			
		if($numItem !== null){
			$itemParams = UniteFunctionsUC::getVal($this->arrItems, $numItem);
			if(empty($itemParams))
				return(false);
			
			$this->outputItem($numItem, $itemParams, $templateName, $sap, false);
			
			return(false);
		}

		//if sap, then no new line
		$newLine = empty($sap);
		
		foreach($this->arrItems as $index => $itemParams)
			$this->outputItem($index, $itemParams, $templateName, $sap, $newLine);
		
	}
	
	
	/**
	 * put items. input can be saporator or number of item, or null
	 */
	public function putItems($input = null, $templateName = "item"){
		
		$sap = null;
		$numItem = null;
		
		if($input !== null){
			if(is_numeric($input))
				$numItem = $input;
			else
				$sap = $input;
		}
		
		$this->putItemsWork($templateName, $sap, $numItem);
	}
	
	
	/**
	 * put items 2
	 */
	public function putItems2($input = null){
		$this->putItems($input, "item2");
	}
	
	/**
	 * put items 2
	 */
	public function putCssItems(){
		$this->putItems(null, "css_item");
	}
	
	
	/**
	 * put font override
	 */
	public function putFontOverride($name, $selector, $useID = false){
		
		$arrFonts = $this->addon->getArrFonts();
		
		if(empty($arrFonts))
			return(false);
		
		$cssSelector = "";
		if($useID == true)
			$cssSelector .= "#".$this->arrParams["uc_id"];
		
		if(!empty($cssSelector))
			$cssSelector .= " ".$selector;
		
		$fontKey = "uc_font_override_".$name;

		$arrFont = UniteFunctionsUC::getVal($arrFonts, $fontKey);
		
		if(empty($arrFont))
			return(false);
		
		$processor = new UniteCreatorParamsProcessor();
		$processor->init($this->addon);
		
		$css = $processor->processFont(null, $arrFont, true, $cssSelector, $fontKey);
		
		if(empty($css))
			return(false);
		
		echo UniteProviderFunctionsUC::escAddParam($css);
	}
	
	
	/**
	 * put font override
	 */
	public function putPostTags($postID){
		
		echo "no tag list for this platform";
	}
	
	
	/**
	 * put font override
	 */
	public function putPostMeta($postID, $key){
		
		echo "no meta for this platform";
	}
	
	/**
	 * put font override
	 */
	public function putAcfField($postID, $fieldname){
		
		echo "no acf available for this platform";
	}
	
	/**
	 * put post date
	 */
	public function putPostDate($postID, $dateFormat){
		
		echo "no custom date for this platform";
	}
	
	
	/**
	 * filter uc date, clear html first, then replace the date
	 */
	public function filterUCDate($dateStamp, $format = "d F Y, H:i"){
		
		$hasTags = false;
		$stamp = $dateStamp;
		
		if(is_numeric($dateStamp) == false){
			$hasTags = true;
			$stamp = strip_tags($dateStamp);
			$stamp = trim($stamp);
		}
		
		$strDate = date_i18n($format, $stamp);
		
		if($hasTags == true)
			$strDate = str_replace($stamp, $strDate, $dateStamp);

		return($strDate);
	}
	
	
	/**
	 * show item
	 */
	public function showItem($arrItem){
		dmp($arrItem);
	}
	
	
	/**
	 * get post get variable
	 */
	public function putPostGetVar($varName, $default=""){
		
		$varName = UniteProviderFunctionsUC::sanitizeVar($varName, UniteFunctionsUC::SANITIZE_KEY);
		
		$value = UniteFunctionsUC::getPostGetVariable($varName, $default , UniteFunctionsUC::SANITIZE_TEXT_FIELD);
		
		if(empty($value))
			$value = $default;
		
		echo UniteProviderFunctionsUC::escCombinedHtml($value);
	}
	
	
	/**
	 * convert date to type
	 */
	public function put_date_utc($strDate){
						
		$stamp = strtotime($strDate);
				
		$strUTC = gmdate('Y/m/d H:i:s', $stamp);

		echo UniteProviderFunctionsUC::escCombinedHtml($strUTC);
	}
	
	
	/**
	 * show data
	 */
	public function showData(){
				
		dmp("Params:");
		dmp($this->arrParams);
		
		dmp("Items:");
		dmp($this->arrItems);
		
	}
	
	/**
	 * show debug
	 */
	public function showDebug(){
		
		dmp("Showing Debug");
		
		$arrDebug = HelperUC::getDebug();
		
		if(empty($arrDebug)){
			dmp("no debug content found");
			return(false);
		}
		
		foreach($arrDebug as $item){
			
			$title = UniteFunctionsUC::getVal($item, "title");
			$content = UniteFunctionsUC::getVal($item, "content");
			
			$titleOutput = $title;
			if(!empty($content))
				$titleOutput = "<b>$title:</b>";
			
			dmp($titleOutput);
			dmp($content);
			
		}
		
	}
	
	/**
	 * get post tags
	 * Enter description here ...
	 */
	public function getPostTags($postID){
		
		$errorPrefix = "getPostTags function error: ";
		
		if(empty($postID))
			UniteFunctionsUC::throwError("$errorPrefix - no postID argument found");
		
		$arrTerms = UniteFunctionsWPUC::getPostSingleTerms($postID, "post_tag");
		
		if(empty($arrTerms))
			return(array());
		
		$objParamsProcessor = new UniteCreatorParamsProcessor();
		
		$arrTagsOutput = $objParamsProcessor->modifyArrTermsForOutput($arrTerms);
		
		return($arrTagsOutput);
	}
	
	/**
	 * print some variable
	 */
	public function printVar($var){
		
		dmp($var);
	}
	
	
	/**
	 * do some wp action, function for override
	 */
	public function do_action($tag, $param = null, $param2 = null, $param3=null){
		
		UniteFunctionsUC::throwError("The do_action() function exists only in PRO version of the plugin");
		
	}

	
	/**
	 * do some wp
	 */
	public function apply_filters($tag, $value = null, $param1 = null, $param2=null){
		
		UniteFunctionsUC::throwError("The apply_filters() function exists only in PRO version of the plugin");
		
		
		return($value);
	}
	
	/**
	 * filter truncate
	 * preserve - preserve word
	 * separator - is the ending
	 */
	public function filterTruncate($value, $length = 100, $preserve = false, $separator = '...'){
		
		$value = strip_tags($value);
		
		$charset = $this->twig->getCharset();
		
        if (mb_strlen($value, $charset) > $length) {
            if ($preserve) {
                // If breakpoint is on the last word, return the value without separator.
                if (false === ($breakpoint = mb_strpos($value, ' ', $length, $charset))) {
                    return $value;
                }

                $length = $breakpoint;
            }

            return rtrim(mb_substr($value, 0, $length, $charset)).$separator;
        }
        
        return $value;
	}
	
	/**
	 * run filter wp autop
	 * 
	 */
	public function filterWPAutop($text, $br = true){
		
		return wpautop($text, $br);
	}
	
	/**
	 * get post terms
	 */
	public function getPostTerms($postID, $taxonomy){
		
		$post = get_post($postID);
		
		if(empty($post)){
			return(array());
		}
		
		$arrTerms = UniteFunctionsWPUC::getPostSingleTerms($postID, $taxonomy);
		
		//get single category
		if(empty($arrTerms))
			return(array());
		
		$arrTermsOutput = $this->objParamsProcessor->modifyArrTermsForOutput($arrTerms);
		
		return($arrTermsOutput);
	}
	
	/**
	 * function for override
	 */
	protected function initTwig_addExtraFunctionsPro(){
		//function for override
	}
	
	/**
	 * put woo commerce filter
	 */
	public function putWooFilter($filterName = null){
		
		if(empty($filterName))
			UniteFunctionsUC::throwError("putWooFilter error: No Filter provided");
		
		$isActive = UniteCreatorWooIntegrate::isWooActive();
		
		$objWooIntegrate = UniteCreatorWooIntegrate::getInstance();

		$objWooIntegrate->putHtmlFilter($filterName);
		
	}
	
	/**
	 * get post author
	 */
	public function getPostAuthor($authorID){
				
		$arrUserData = UniteFunctionsWPUC::getUserDataById($authorID);
		
		return($arrUserData);		
	}
	
	
	/**
	 * add extra functions to twig
	 */
	protected function initTwig_addExtraFunctions(){
		
		//add extra functions
		
		$putItemsFunction = new Twig_SimpleFunction('put_items', array($this,"putItems"));
		$putItemsFunction2 = new Twig_SimpleFunction('put_items2', array($this,"putItems2"));
		$putCssItemsFunction = new Twig_SimpleFunction('put_css_items', array($this,"putCssItems"));
		$putFontOverride = new Twig_SimpleFunction('put_font_override', array($this,"putFontOverride"));
		$putPostTagsFunction = new Twig_SimpleFunction('putPostTags', array($this,"putPostTags"));
		$putPostMetaFunction = new Twig_SimpleFunction('putPostMeta', array($this,"putPostMeta"));
		$putACFFieldFunction = new Twig_SimpleFunction('putAcfField', array($this,"putAcfField"));
		$putShowFunction = new Twig_SimpleFunction('show', array($this,"showItem"));
		$putPostDateFunction = new Twig_SimpleFunction('putPostDate', array($this,"putPostDate"));
		$putPostGetVar = new Twig_SimpleFunction('putPostGetVar', array($this,"putPostGetVar"));
		$convertDate = new Twig_SimpleFunction('put_date_utc', array($this,"put_date_utc"));
		$putShowDataFunction = new Twig_SimpleFunction('showData', array($this,"showData"));
		$putShowDebug = new Twig_SimpleFunction('showDebug', array($this,"showDebug"));
		$getPostTags = new Twig_SimpleFunction('getPostTags', array($this,"getPostTags"));
		$printVar = new Twig_SimpleFunction('printVar', array($this,"printVar"));
		$doAction = new Twig_SimpleFunction('do_action', array($this,"do_action"));
		$applyFilters = new Twig_SimpleFunction('apply_filters', array($this,"apply_filters"));
		$getPostTerms = new Twig_SimpleFunction('getPostTerms', array($this,"getPostTerms"));
		$getPostAuthor = new Twig_SimpleFunction('getPostAuthor', array($this,"getPostAuthor"));
		$putWooFilter = new Twig_SimpleFunction('putWooFilter', array($this,"putWooFilter"));
		
		$filterTruncate = new Twig_SimpleFilter("truncate", array($this, "filterTruncate"));
		$filterWPAutop = new Twig_SimpleFilter("wpautop", array($this, "filterWPAutop"));
		$filterUCDate = new Twig_SimpleFilter("ucdate", array($this, "filterUCDate"));
		
		
		//add extra functions
		$this->twig->addFunction($putItemsFunction);
		$this->twig->addFunction($putItemsFunction2);
		$this->twig->addFunction($putCssItemsFunction);
		$this->twig->addFunction($putFontOverride);
		$this->twig->addFunction($putPostTagsFunction);
		$this->twig->addFunction($putPostMetaFunction);
		$this->twig->addFunction($putACFFieldFunction);
		$this->twig->addFunction($putShowFunction);
		$this->twig->addFunction($putPostDateFunction);
		$this->twig->addFunction($putPostGetVar);
		$this->twig->addFunction($convertDate);
		$this->twig->addFunction($putShowDataFunction);
		$this->twig->addFunction($putShowDebug);
		$this->twig->addFunction($getPostTags);
		$this->twig->addFunction($printVar);
		$this->twig->addFunction($getPostTerms);
		$this->twig->addFunction($getPostAuthor);
		$this->twig->addFunction($putWooFilter);
		
		
		//add filters
		$this->twig->addFilter($filterTruncate);
		$this->twig->addFilter($filterWPAutop);
		$this->twig->addFilter($filterUCDate);
		
		
		//pro functions
		$this->twig->addFunction($doAction);
		$this->twig->addFunction($applyFilters);
		
		
		$this->initTwig_addExtraFunctionsPro();
		
	}
	
	
	public function a_____OTHER_FUNCTIONS_____(){}
	
	
	/**
	 * init twig
	 */
	private function initTwig(){
		
		if(empty($this->arrTemplates))
			UniteFunctionsUC::throwError("No templates found");
		
		$loader = new Twig_Loader_Array($this->arrTemplates);
		
		$arrOptions = array();
		$arrOptions["debug"] = true;
		
		$this->twig = new Twig_Environment($loader, $arrOptions);
		$this->twig->addExtension(new Twig_Extension_Debug());
		
		$this->initTwig_addExtraFunctions();
		
	}
	
	
	/**
	 * validate that not inited
	 */
	private function validateNotInited(){
		if(!empty($this->twig))
			UniteFunctionsUC::throwError("Can't add template or params when after rendered");
	}

	
	/**
	 * validate that all is inited
	 */
	private function validateInited(){
				
		if($this->arrParams === null){
			UniteFunctionsUC::throwError("Please set the params");
		}		
		
	}
	
	
	/**
	 * return if some template exists
	 * @param $name
	 */
	private function isTemplateExists($name){
		
		$isExists = array_key_exists($name, $this->arrTemplates);
		
		return($isExists);
	}
	
	
	/**
	 * add template
	 */
	public function addTemplate($name, $html){
		$this->validateNotInited();
		if(isset($this->arrTemplates[$name]))
			UniteFunctionsUC::throwError("template with name: $name already exists");
		
		$this->arrTemplates[$name] = $html;
	}
	
	
	/**
	 * add params
	 */
	public function setParams($params){
		
		$this->arrParams = $params;
	}
	
	
	/**
	 * set items
	 * @param $arrItems
	 */
	public function setArrItems($arrItems){
		
		$this->arrItems = $arrItems;
		
		$numItems = 0;
		if(is_array($arrItems))
			$numItems = count($arrItems);
		
		//add number of items
		$this->arrParams["uc_num_items"] = count($arrItems);
		
	}
	
	
	/**
	 * set fonts array
	 */
	public function setArrFonts($arrFonts){
		$this->arrFonts = $arrFonts;
	}
	
	
	/**
	 * get rendered html
	 * @param $name
	 */
	public function getRenderedHtml($name){
		
		UniteFunctionsUC::validateNotEmpty($name);
		$this->validateInited();
		if(array_key_exists($name, $this->arrTemplates) == false)
			UniteFunctionsUC::throwError("Template with name: $name not exists");
		
		if(empty($this->twig))
			$this->initTwig();
		
		$output = $this->twig->render($name, $this->arrParams);
		
		return($output);
	}
	
	
	/**
	 * set addon
	 */
	public function setAddon($addon){
		
		$this->addon = $addon;
	}
	
}