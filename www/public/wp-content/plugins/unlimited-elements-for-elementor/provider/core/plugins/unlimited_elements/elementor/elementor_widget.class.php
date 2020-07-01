<?php

defined('UNLIMITED_ELEMENTS_INC') or die('Restricted access');

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Scheme_Color;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Scheme_Typography;

class UniteCreatorElementorWidget extends Widget_Base {
	
    private $objAddon;
    
    private $isConsolidated = false;
    private $objCat, $arrAddons;
    private $isNoMemory = false;
    private $isNoMemory_addonName;
    
    const DEBUG_SETTINGS_VALUES = false;
    
    
    private function a_______INIT______(){}
    
    
    /**
     * set the addon
     */
    public function __construct($data = array(), $args = null) {
		    	
        $className = get_class($this);
    	
        if(strpos($className, "UCAddon_uccat_") === 0)
        	$this->initByCategory($className);
        else
        	$this->initByAddon($className);
        	
        
        parent::__construct($data, $args);
    }

    
    /**
	 * get help url
     */
	public function get_custom_help_url(){
		
		if(empty($this->objAddon))
			return(null);
		
		$link = $this->objAddon->getOption("link_resource");
		
		$link = trim($link);
		
		if(!empty($link)){
			$isValid = filter_var($link, FILTER_VALIDATE_URL);
			if($isValid == true)
				return($link);
		}
					
		$isPostListExists = $this->objAddon->isParamTypeExists(UniteCreatorDialogParam::PARAM_POSTS_LIST);
		
		if($isPostListExists == true)
			return(GlobalsUnlimitedElements::LINK_HELP_POSTSLIST);
		
		return(null);
	}
    
    
    /**
     * init by category
     */
    private function initByCategory($className){

    	$catName = str_replace("UCAddon_uccat_", "", $className);
        $catName = trim($catName);
    	
        $arrCache = UniteFunctionsUC::getVal(UniteCreatorElementorIntegrate::$arrCatsCache, $catName);
        if(empty($arrCache))
        	return(false);
        
        $this->isConsolidated = true;
        $this->objCat = $arrCache["objcat"];
        $this->arrAddons = $arrCache["addons"];
            	
    }
    
    
    /**
     * init by addon
     */
    private function initByAddon($className){
        
    	$addonName = str_replace("UCAddon_", "", $className);
        $addonName = trim($addonName);
        
        if(strpos($addonName, "_no_memory") !== false){
        	
        	$addonName = str_replace("_elementor", "", $addonName);
        	$addonName = str_replace("_no_memory", "", $addonName);
        	
        	$this->isNoMemory = true;
        	$this->isNoMemory_addonName = $addonName;
        	        	
        	return(false);
        }
        	
        
        if(empty($addonName))
        	UniteFunctionsUC::throwError("Widget name is empty");
    	
    	$this->objAddon = new UniteCreatorAddon();
        $this->objAddon->initByName($addonName);
    	
    }
    
    private function a______GETTERS____(){}
    
    
    /**
     * return category icon
     */
    public function get_icon() {
    	
    	if($this->isNoMemory == true)
    		return UniteCreatorElementorIntegrate::DEFAULT_ICON;
    	
    		
    	if($this->isConsolidated == true){
    		$icon = $this->objCat->getParam("icon");
    		    		
    		if(!empty($icon))
    			return($icon);
    	}else{
			
    		$fontIcon = $this->objAddon->getFontIcon();
    		if(!empty($fontIcon))
    			return($fontIcon);
    		
    		$catIcon = $this->objAddon->getCatIcon();
    		if(!empty($catIcon))
    			return($catIcon);
    		
    	}
    	
        return UniteCreatorElementorIntegrate::DEFAULT_ICON;
    }

    
    /**
     * get addon category
     */
    public function get_categories() {
    	
    	if($this->isConsolidated)
        	return array(UniteCreatorElementorIntegrate::ADDONS_CATEGORY_NAME);
    	
    	
    	$catID = $this->objAddon->getCatID();
    	if(empty($catID))
        	return array(UniteCreatorElementorIntegrate::ADDONS_CATEGORY_NAME);
        
        $catName = UniteCreatorElementorIntegrate::getCategoryName($catID);
        
        return array($catName);
    }
	
    
    /**
	 * put scripts
     */
    public function get_script_depends() {
    	    	
    	$isEditMode = $this->isEditMode();
    	 
    	if($isEditMode == true)
    		return(array());
    	
    	/*
    	
    	// - process from output
    	 
    	$arrScriptsHandles = $this->getScriptsDependsUC();
        return $arrScriptsHandles;
        */
    	
    	return(array());
    }
    
    
    /**
     * get widget name
     */
    public function get_name() {
		
    	if($this->isNoMemory == true){
    		$name = "ucaddon_".$this->isNoMemory_addonName;
    		
    		return($name);
    	}
    	
    	if($this->isConsolidated)
        	$name = "ucaddon_cat_".$this->objCat->getAlias();
    	else
        	$name = "ucaddon_".$this->objAddon->getAlias();    	
        
        return $name;
    }

    /**
     * get addon name
     */
    public function getAddonName(){
    	
    	$name = $this->objAddon->getName();
    	
    	return($name);
    }
    
    /**
     * get addon object
     */
    public function getObjAddon(){
    	
    	return($this->objAddon);
    }
    
    
    /**
     * get widget title
     */
    public function get_title() {
		
    	if($this->isNoMemory == true)
    		return($this->isNoMemory_addonName. "(no memory)");
    	
    		
    	if($this->isConsolidated)
    		$title = $this->objCat->getTitle();
    	else
        	$title = $this->objAddon->getTitle();
		
        return $title;
    }
    
    
    /**
     * get addon scripts depends
     */
    protected function getScriptsDependsUC(){
    	
    	if($this->isNoMemory == true)
    		return(array());
    	
    	
    	if($this->isConsolidated == true){
    		
    		$arrHandles = array();
    		foreach($this->arrAddons as $objAddon)
    			$arrHandles = $this->ucGetAddonDepents($objAddon, $arrHandles);
    		
    	}
    	else
    		$arrHandles = $this->ucGetAddonDepents($objAddon);
    	
    	$arrHandles = array_values($arrHandles);
    	
    	return($arrHandles);
    }
    
    private function a___________ADD_CONTROLS__________(){}
    
    
    /**
     * modify value before add by type
     */
    protected function modifyValueByTypeUC($type, $value){
    	
    	switch($type){
    		case UniteCreatorDialogParam::PARAM_IMAGE:
    			if(is_numeric($value))    				
    				$value = array("id"=>$value);
    			else
    				$value = array("url"=>$value);
    		break;
    		case UniteCreatorDialogParam::PARAM_LINK:
    			
    			$value = array("url"=>$value);
    		break;
    		case UniteCreatorDialogParam::PARAM_ICON:
    			
    			$value = UniteFontManagerUC::fa_convertIconTo5($value);
    		break;
    		case UniteCreatorDialogParam::PARAM_ICON_LIBRARY:
    			
    			$value = $this->getIconArrayValue($value);
    				    			
    		break;
    	}
    	
    	return($value);
    }
    
    
    /**
     * process data, set images as array
     */
    protected function modifyArrValuesUC($arrTypes, $arrValues){
    	
    	$arrData = array();
    	
    	foreach($arrValues as $paramName=>$value){
    		
    		$type = UniteFunctionsUC::getVal($arrTypes, $paramName);
    		
    		if(empty($type))
    			$arrData[$paramName] = $value;
    		else
    			$arrData[$paramName] = $this->modifyValueByTypeUC($type, $value);
    		
    	}
    	
    	return($arrData);
    }
    
    /**
     * add font controls
     */
    protected function addFontControlsUC(){
    	  
            $arrFontsSections = $this->objAddon->getArrFontsParamNames();
	        $arrFontsParams = $this->objAddon->getArrFontsParams();
            
          	foreach($arrFontsSections as $name=>$title){
		         
          		$this->start_controls_section(
		                'section_styles_'.$name, array(
		                'label' => $title." ".esc_html__("Styles", "unlimited_elements"),
          				'tab' => "style"
		                 )
		         );
          			          	
	          	$arrParams = $arrFontsParams[$name];
	        		        	
	          	foreach($arrParams as $name => $param)
	          		$this->addElementorParamUC($param);
	          	
	          	$this->end_controls_section();
          	}
          	
    }
    
    
    /**
     * modify default items data, to make in elementor way
     */
    protected function modifyDefaultItemsDataUC($arrItemsData, $objAddon){
    	
    	$arrItemsTypes = $objAddon->getParamsTypes(true);
    	
    	foreach($arrItemsData as $key=>$arrData){    		
    		
    		$arrItemsData[$key] = $this->modifyArrValuesUC($arrItemsTypes, $arrData);
    	}
    	
    	return $arrItemsData;
    }
    
    
    /**
     * add items controls
     */
    protected function addItemsControlsUC($itemsType){
		
    	if($itemsType == "image")
    		return(false);
    	
         $this->start_controls_section(
                'section_items', array(
                'label' => esc_html__("Items", "unlimited_elements"),
                    )
          );
    	 
         $paramsItems = $this->objAddon->getProcessedItemsParams();

         $paramsItems = $this->addDynamicAttributes($paramsItems);
         
         
         $titleField = null;
         
         $arrFields = array();
         foreach($paramsItems as $param){
         	
         	$name = UniteFunctionsUC::getVal($param, "name");
         	if($name == "title")
         		$titleField = $name;
         	
         	$arrControl = $this->getControlArrayUC($param, true);
         	$arrFields[] = $arrControl;
         }
		 
         
         $arrItemsControl = array();
         $arrItemsControl["type"] = Controls_Manager::REPEATER;
         $arrItemsControl["fields"] = $arrFields;
         
         if(!empty($titleField))
         	$arrItemsControl["title_field"] = "{{{ $titleField }}}";
         
         
         //---- set default data
         
         $arrItemsData = $this->objAddon->getArrItemsForConfig();
         
         $arrItemsData = $this->modifyDefaultItemsDataUC($arrItemsData, $this->objAddon);

         if(empty($arrItemsData))
         	$arrItemsData = array();
         
         $arrItemsControl["default"] = $arrItemsData;
         
         $this->add_control('uc_items', $arrItemsControl);
		
         
         $this->end_controls_section();
    }
    
    /**
     * get icon array value
     */
    private function getIconArrayValue($value){
    	
    	if(is_array($value))
    		return($value);
    				
    				
    	$value = UniteFontManagerUC::fa_convertIconTo5($value);
    	$library = UniteFontManagerUC::fa_getIconLibrary($value);
    			
    	$arrValue = array(
    		"library" => $library,
    		"value" => $value
    	);
    	
    	return($arrValue);
    }
    
    
    /**
     * get control array from param
     */
    protected function getControlArrayUC($param, $forItems = false){
    	
    	$type = UniteFunctionsUC::getVal($param, "type");
    	$title = UniteFunctionsUC::getVal($param, "title");
    	$name = UniteFunctionsUC::getVal($param, "name");
    	$description = UniteFunctionsUC::getVal($param, "description");
    	$defaultValue = UniteFunctionsUC::getVal($param, "default_value");
    	$value = $defaultValue;
    	$isMultiple = UniteFunctionsUC::getVal($param, "is_multiple");
    	$elementorCondition = UniteFunctionsUC::getVal($param, "elementor_condition");
		$placeholder = UniteFunctionsUC::getVal($param, "placeholder");
		$disabled = UniteFunctionsUC::getVal($param, "disabled");
    	
		$disabled = UniteFunctionsUC::strToBool($disabled);
		
    	$description = trim($description);
    	$placeholder = trim($placeholder);
    	
    	
    	if(isset($param["value"]))
    		$value = $param["value"];
    	
    	$arrControl = array();
    	    	
    	switch($type){
    		case UniteCreatorDialogParam::PARAM_TEXTFIELD:
    			$controlType = Controls_Manager::TEXT;
    			if($disabled === true){		//show disabled input raw html
					$arrControl['classes'] = "uc-elementor-control-disabled";
    			}
    				
    		break;
    		case UniteCreatorDialogParam::PARAM_COLORPICKER:
    			$controlType = Controls_Manager::COLOR;
    		break;
    		case UniteCreatorDialogParam::PARAM_NUMBER:
    			$controlType = Controls_Manager::NUMBER;
    			$unit = UniteFunctionsUC::getVal($param, "unit");
    			
    			if(!empty($unit))
    				$title .= " ($unit)";
    		break;
    		case UniteCreatorDialogParam::PARAM_RADIOBOOLEAN:
    			
    			$controlType = Controls_Manager::SWITCHER;
    			$trueValue = UniteFunctionsUC::getVal($param, "true_value");
    			
    			if($defaultValue == $trueValue)
    				$defaultValue = $trueValue;
    			else
    				$defaultValue = "";
				
    			$arrControl["label_on"] = UniteFunctionsUC::getVal($param, "true_name");
    			$arrControl["label_off"] = UniteFunctionsUC::getVal($param, "false_name");
    			$arrControl["return_value"] = $trueValue;
    			
    			
    		break;
    		case UniteCreatorDialogParam::PARAM_TEXTAREA:
    			$controlType = Controls_Manager::TEXTAREA;
    		break;
    		case UniteCreatorDialogParam::PARAM_CHECKBOX:
    			$controlType = Controls_Manager::SWITCHER;
    			$isChecked = UniteFunctionsUC::getVal($param, "is_checked");
    			$isChecked = UniteFunctionsUC::strToBool($isChecked);
    			$value = ($isChecked == true)?"yes":"no";
    			
    		break;
    		case UniteCreatorDialogParam::PARAM_DROPDOWN:
    			
    			if($isMultiple == true)
    				$controlType = Controls_Manager::SELECT2;
    			else
    				$controlType = Controls_Manager::SELECT;
    			
    		break;
    		case "select2":
    				$controlType = Controls_Manager::SELECT2;
    		break;
    		case "uc_select_special":
    			
    			$controlType = "uc_select_special";
    			
    		break;
    		case UniteCreatorDialogParam::PARAM_EDITOR:
    			$controlType = Controls_Manager::WYSIWYG;
    		break;
    		case UniteCreatorDialogParam::PARAM_ICON:
    			$controlType = Controls_Manager::ICON;
    		break;
    		case UniteCreatorDialogParam::PARAM_ICON_LIBRARY:
    			$controlType = Controls_Manager::ICONS;
    		break;
    		case UniteCreatorDialogParam::PARAM_IMAGE:
    			$controlType = Controls_Manager::MEDIA;
    		break;
    		case UniteCreatorDialogParam::PARAM_HR:
    			//$controlType = "uc_hr";
    			$controlType = Controls_Manager::DIVIDER;
    		break;
    		case UniteCreatorDialogParam::PARAM_AUDIO:
    			$controlType = "uc_mp3";
    		break;
    		case "uc_gallery":
    			$controlType = Controls_Manager::GALLERY;
    		break;
    		case UniteCreatorDialogParam::PARAM_LINK:
    			$controlType = Controls_Manager::URL;
    		break;
    		case UniteCreatorDialogParam::PARAM_SHAPE:
    			
    			$controlType = "uc_shape_picker";
    			//$controlType = Controls_Manager::ICON;
    		break;
    		case UniteCreatorDialogParam::PARAM_HIDDEN:
    			
    			$controlType = Controls_Manager::HIDDEN;
    		break;
    		case UniteCreatorDialogParam::PARAM_STATIC_TEXT:
    			$controlType = Controls_Manager::HEADING;
    		break;
    		case UniteCreatorDialogParam::PARAM_MARGINS:
    		case UniteCreatorDialogParam::PARAM_PADDING:
    			$controlType = Controls_Manager::DIMENSIONS;
    		break;
    		case UniteCreatorDialogParam::PARAM_BACKGROUND:    			
    			$controlType = Group_Control_Background::get_type();
    		break;    		
    		case UniteCreatorDialogParam::PARAM_SLIDER:
    			$controlType = Controls_Manager::SLIDER;
    		break;
    		case UniteCreatorDialogParam::PARAM_BORDER:    			
    			$controlType = Group_Control_Border::get_type();
    		break;
    		case UniteCreatorDialogParam::PARAM_DATETIME:
    			$controlType = Controls_Manager::DATE_TIME;
    		break;
    		case UniteCreatorDialogParam::PARAM_TEXTSHADOW:
    			$controlType = Group_Control_Text_Shadow::get_type();
    		break;
    		case UniteCreatorDialogParam::PARAM_BOXSHADOW:
    			$controlType = Group_Control_Box_Shadow::get_type();
    		break;
    		default:
    			
    			dmp("param not found");
    			dmp($param);
    			UniteFunctionsUC::throwError("Wrong param type: ".$type);
    		break;
    	}
    	
    	//add special params    	
    	$value = $this->modifyValueByTypeUC($type, $value);
    	
    	
    	if(empty($controlType)){
    		dmp("empty control param type");
    		dmp($param);
    		exit();
    	}
    	
    	$arrControl["type"] = $controlType;
    	$arrControl["label"] = $title;
    	
    	$arrControl["default"] = $value;
    	
    	
    	//add options
    	switch($type){
    		case "uc_select_special":
    			    			
    			$addParams = UniteFunctionsUC::getVal($param, "addparams");
    			$classAdd = UniteFunctionsUC::getVal($param, "classAdd");
				
    			if(!empty($classAdd))
    				$addParams .= " class={$classAdd}";

    			$addParams = str_replace("'", "", $addParams);
    			
				$arrControl["addparams"] = $addParams;
				
    		case "select2":
    		case UniteCreatorDialogParam::PARAM_DROPDOWN:
    		    
    			$options = UniteFunctionsUC::getVal($param, "options", array());
    			$options = array_flip($options);
    			$arrControl["options"] = $options;
    			
    			if($isMultiple == true)
    				$arrControl["multiple"] = true;
    		break;
    		case UniteCreatorDialogParam::PARAM_PADDING:
    		case UniteCreatorDialogParam::PARAM_MARGINS:
    			
    			$arrControl["size_units"] = array("px","%");
    			
    			$isResponsive = UniteFunctionsUC::getVal($param, "is_responsive");
    			$isResponsive = UniteFunctionsUC::strToBool($isResponsive);
    			
    			//set default value
    			$arrDefaultValue = array();
    			$arrDefaultValue["top"] = UniteFunctionsUC::getVal($param, "desktop_top");
    			$arrDefaultValue["bottom"] = UniteFunctionsUC::getVal($param, "desktop_bottom");
    			$arrDefaultValue["left"] = UniteFunctionsUC::getVal($param, "desktop_left");
    			$arrDefaultValue["right"] = UniteFunctionsUC::getVal($param, "desktop_right");
    			
    			
    			$unit = UniteFunctionsUC::getVal($param, "units");
    			if(!empty($unit))
    				$arrDefaultValue["unit"] = $unit;
    			
    			if($isResponsive == true){
    				
    				$arrTabletDefaults = array();
    				$arrTabletDefaults["top"] = UniteFunctionsUC::getVal($param, "tablet_top");
    				$arrTabletDefaults["bottom"] = UniteFunctionsUC::getVal($param, "tablet_bottom");
    				$arrTabletDefaults["left"] = UniteFunctionsUC::getVal($param, "tablet_left");
    				$arrTabletDefaults["right"] = UniteFunctionsUC::getVal($param, "tablet_right");
    				
    				$arrMobileDefaults = array();
    				$arrMobileDefaults["top"] = UniteFunctionsUC::getVal($param, "mobile_top");
    				$arrMobileDefaults["bottom"] = UniteFunctionsUC::getVal($param, "mobile_bottom");
    				$arrMobileDefaults["left"] = UniteFunctionsUC::getVal($param, "mobile_left");
    				$arrMobileDefaults["right"] = UniteFunctionsUC::getVal($param, "mobile_right");
    				
    				$arrControl["uc_responsive"] = true;
    				$arrControl["default"] = $arrDefaultValue;
    				$arrControl["desktop_default"] = $arrDefaultValue;
    				$arrControl["tablet_default"] = $arrTabletDefaults;
    				$arrControl["mobile_default"] = $arrMobileDefaults;
    				
    			}
    			else{
    				$arrControl["default"] = $arrDefaultValue;
    			}
    			
    			//set selector
    			$arrSelectors = array();
    			$selector = UniteFunctionsUC::getVal($param, "selector");
    			
    			$attribute = "margin";
    			if($type == UniteCreatorDialogParam::PARAM_PADDING)
    				$attribute = "padding";
    			
    		    				
    			if(!empty($selector)){
    				$selector = "{{WRAPPER}} $selector";
    				$selectorContent = $attribute.': {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};';
    				
    				$arrSelectors[$selector] = $selectorContent;
    			}
    			
    			if(!empty($arrSelectors))
    				$arrControl["selectors"] = $arrSelectors;
				
    			    				
    		break;
    		case UniteCreatorDialogParam::PARAM_BACKGROUND:
    			
    			unset($arrControl["default"]);
    			
    			$arrControl["name"] = $name;
    			$arrControl["types"] = array('classic', 'gradient', 'video');
    			
    			if(!empty($selector))
    				$arrControl["selector"] = $selector;
    			
    			//set defaults
    			$arrDefaults = array();
    			
    			$bgType = UniteFunctionsUC::getVal($param, "background_type");
    			
    			switch($bgType){
    				
    				case "solid":
    					
    					$color = UniteFunctionsUC::getVal($param, "solid_color");
    					
    					if(!empty($color)){
	    					$arrDefaults["background"] = array("default"=>"classic");
	    					$arrDefaults["color"] = array("default"=>$color);
    					}
    					
    				break;
    				case "gradient":
    					
    					$color1 = UniteFunctionsUC::getVal($param, "gradient_color1");
    					$color2 = UniteFunctionsUC::getVal($param, "gradient_color2");
    					
    					if(!empty($color1) && !empty($color2)){
	    					
    						$arrDefaults["background"] = array("default"=>"gradient");
	    					$arrDefaults["color"] = array("default"=>$color1);
	    					$arrDefaults["color_b"] = array("default"=>$color2);
    					}
    					   					
    				break;
    			}
    			
    			if(!empty($arrDefaults)){
    				
    				$arrControl["fields_options"] = $arrDefaults;
    			}
    			
    			
    		break;
    		case UniteCreatorDialogParam::PARAM_SLIDER:
    			
    			$units = UniteFunctionsUC::getVal($param, "units");
				
    			$rangeUnit = "px";
    			
    			switch($units){
    				case "px":
    					$arrControl["size_units"] = array("px");
    					$rangeUnit = "px";
    				break;
    				case "%":
    					$arrControl["size_units"] = array("%");
    					$rangeUnit = "%";
    				break;
    				case "em":
    					$arrControl["size_units"] = array("em");
    					$rangeUnit = "em";
    				break;
    				case "px_percent":
    					$arrControl["size_units"] = array("px","%");
    					$rangeUnit = "px";
    				break;
    				case "px_percent_em":
    				default:
    					$arrControl["size_units"] = array("px","%","em");
    					$rangeUnit = "px";
    				break;
    			}
    			
    			//set range
    			$arrRangeUnit = array(
    				"min"=>(int)UniteFunctionsUC::getVal($param, "min"),
    				"max"=>(int)UniteFunctionsUC::getVal($param, "max"),
    				"step"=>(int)UniteFunctionsUC::getVal($param, "step")
    			);
    			
    			$arrRange = array();
    			$arrRange[$rangeUnit] = $arrRangeUnit;
    			
    			$arrControl["range"] = $arrRange;
    			
    			$arrControl["default"] = array(
    				"size" => UniteFunctionsUC::getVal($param, "default_value"),
    				"unit" => $rangeUnit
    			);
    			
    			$selector = UniteFunctionsUC::getVal($param, "selector");
    			$selectorValue = UniteFunctionsUC::getVal($param, "selector_value");
    			
    			if(!empty($selector)){
    				$selector = "{{WRAPPER}} ".$selector;
    				$arrControl["selectors"][$selector] = $selectorValue;
    			}
    			
    			
    		break;
    		case UniteCreatorDialogParam::PARAM_TEXTSHADOW:
    			$arrControl["name"] = $name;
    		break;
    		case UniteCreatorDialogParam::PARAM_BOXSHADOW:
    			$arrControl["name"] = $name;
    		break;
    		case UniteCreatorDialogParam::PARAM_BORDER:
    			$arrControl["name"] = $name;
    		break;
    		case UniteCreatorDialogParam::PARAM_ICON_LIBRARY:
    			
    			$enableSVG = UniteFunctionsUC::getVal($param, "enable_svg");
    			$enableSVG = UniteFunctionsUC::strToBool($enableSVG);
    			
    			if($enableSVG == false){
	    			$arrControl["skin"] = "inline";
	    			$arrControl["exclude_inline_options"] = array("svg");
    			}
    			
    			$arrControl["default"] = $this->getIconArrayValue($value);
    			
    		break;
    	}
    	
    	//run single selector
    	switch($type){
    		case UniteCreatorDialogParam::PARAM_TEXTSHADOW:
    		case UniteCreatorDialogParam::PARAM_BOXSHADOW:
    		case UniteCreatorDialogParam::PARAM_BORDER:
    		case UniteCreatorDialogParam::PARAM_BACKGROUND:
    			
    			$selector = UniteFunctionsUC::getVal($param, "selector");
    			if(!empty($selector)){
    				$selector = "{{WRAPPER}} $selector";
    				
    				$arrControl["selector"] = $selector;
    			}
    			
    		break;
    	}
    	
    	if($forItems == true)
    		$arrControl["name"] = $name;
    	
    	//add description
    	if(!empty($description))
    		$arrControl["description"] = $description;
    	
    	if(!empty($placeholder))
    		$arrControl["placeholder"] = $placeholder;
    	
    	//add dynamic
    	$isAddDynamic = UniteFunctionsUC::getVal($param, "add_dynamic");
    	$isAddDynamic = UniteFunctionsUC::strToBool($isAddDynamic);
    	
    	if($isAddDynamic == true){
    		
    		$arrControl['dynamic'] = array(
				'active' => true
    		);
			
			$arrControl["recursive"] = true;
    	}
    	
    	//condition
    	if(!empty($elementorCondition)){
    		$arrControl["condition"] = $elementorCondition;
    	}
    	
    	
    	return($arrControl);
    }
    

    /**
     * add typography control by param
     */
    protected function addTypographyByParamUC($param){
    	
    	$name = UniteFunctionsUC::getVal($param, "name");
    	$title = UniteFunctionsUC::getVal($param, "title");
    	
    	//get selectors
    	$selector1 = UniteFunctionsUC::getVal($param, "selector1");
    	$selector1 = trim($selector1);

    	$selector2 = UniteFunctionsUC::getVal($param, "selector2");
    	$selector2 = trim($selector2);
    	
    	$selector3 = UniteFunctionsUC::getVal($param, "selector3");
    	$selector3 = trim($selector3);
    	
    	//make selector string
    	
    	$selector = "";
    	
    	if(!empty($selector1))
    		$selector .= "{{WRAPPER}} $selector1";
    	
    		
    	if(!empty($selector2)){
    		
    		if(!empty($selector))
    			$selector .= ",";
    		
    		$selector .= "{{WRAPPER}} $selector2";
    	}
    	
    	if(!empty($selector3)){
    		
    		if(!empty($selector))
    			$selector .= ",";
    		
    		$selector .= "{{WRAPPER}} $selector3";
    	}
    	
    	//add the typography control
    	$controlName = HelperUC::convertTitleToHandle($name);
    	
    	$controlName = $name;
    	$controlName = str_replace(".", "_", $controlName);
    	
    	$arrControl = array();
    	$arrControl["name"] = $controlName;
    	$arrControl["selector"] = $selector;
    	$arrControl["scheme"] = Scheme_Typography::TYPOGRAPHY_3;
    	
    	if(!empty($title))
    		$arrControl["label"] = $title;
    	
    	
    	$this->add_group_control(Group_Control_Typography::get_type(), $arrControl);
    	
    }
    
    
    /**
     * add elementor param
     */
    protected function addElementorParamUC($param){
    	
    	$name = UniteFunctionsUC::getVal($param, "name");
    	$type = UniteFunctionsUC::getVal($param, "type");
    	    	
    	switch($type){
    		case UniteCreatorDialogParam::PARAM_INSTAGRAM:
    		case UniteCreatorDialogParam::PARAM_POST_TERMS:
    		case UniteCreatorDialogParam::PARAM_USERS:
    		case UniteCreatorDialogParam::PARAM_MENU:
    			
    			$settings = new UniteCreatorSettings();
    			  
    			$arrChildParams = $settings->getMultipleCreatorParams($param);
    			    			
    			foreach($arrChildParams as $childParam)
    				$this->addElementorParamUC($childParam);
    			
    		break;
    		case UniteCreatorDialogParam::PARAM_POSTS_LIST:
    			
    			$param["all_cats_mode"] = true;
    			
    			//add current posts settings
    			$templateType = UniteCreatorElementorIntegrate::$templateType;
    			$isArchiveLocation = UniteFunctionsWPUC::isArchiveLocation();
    			
    			/*
    			if($templateType == UniteCreatorElementorIntegrate::TEMPLATE_TYPE_ARCHIVE 
    			   || UniteCreatorElementorIntegrate::$isAjaxAction == true
    			   || $isArchiveLocation == true)
    			*/
    			
    			$param["add_current_posts"] = true;
    			
    			$settings = new UniteCreatorSettings();
    			  
    			$arrChildParams = $settings->getMultipleCreatorParams($param);
    			
    			foreach($arrChildParams as $childParam)
    				$this->addElementorParamUC($childParam);
    		break;
    		case UniteCreatorDialogParam::PARAM_TYPOGRAPHY:
    			$this->addTypographyByParamUC($param);
    		break;
    		case UniteCreatorDialogParam::PARAM_FONT_OVERRIDE:
    		break;
    		default:
    			$arrControl = $this->getControlArrayUC($param);
				    			
    			$type = UniteFunctionsUC::getVal($param, "type");
    			
    			switch($type){
    				case UniteCreatorDialogParam::PARAM_BACKGROUND:
    				case UniteCreatorDialogParam::PARAM_BORDER:
    				case UniteCreatorDialogParam::PARAM_TEXTSHADOW:
    				case UniteCreatorDialogParam::PARAM_BOXSHADOW:
    					
    					$groupType = $arrControl["type"];
    					
    					$values = $this->add_group_control($groupType, $arrControl);
    					
    				break;
    				default:
    					
    					//add control (responsive or not)
    					if(isset($arrControl["uc_responsive"])){
    						
    						unset($arrControl["uc_responsive"]);
    						$this->add_responsive_control($name, $arrControl);
    						
    					}else{
    						
    						$this->add_control($name, $arrControl);
    						
    					}
    					    					
    				break;
    			}
    		break;
    	}
    	
    }
    
    /**
     * get addon depends
     */
    protected function ucGetAddonDepents(UniteCreatorAddon $objAddon, $arrHandles=array()){

    	$output = new UniteCreatorOutput();
    	$output->initByAddon($objAddon);
    	
    	$arrIncludes = $output->getProcessedIncludes(true, true, "js");
    	        
        foreach($arrIncludes as $arrInclude){
        	
        	$handle = UniteFunctionsUC::getVal($arrInclude, "handle");
        	
        	$arrHandles[$handle] = $handle;
        }
		
        return($arrHandles);
    	
    }
    
    /**
     * get gallery control
     */
    protected function getGalleryParamUC(){
    	
    	$param = array();
    	$param["type"] = "uc_gallery";
    	$param["title"] = __("Add Images","unlimited_elements");
    	$param["name"] = "uc_gallery_items";
    	$param["default_value"] = array();
    	$param["add_dynamic"] = true;
    	
    	return($param);
    }
    
    /**
     * add gallery param
     */
    protected function addGalleryControlUC(){
    	
    	$param = $this->getGalleryParamUC();
    	
    	$this->addElementorParamUC($param);
    	
    }
    
    /**
     * add edit html control
     */
    private function addEditAddonControl(){
    	
    	if($this->isConsolidated == true)
    		return(false);
    	
    	if(is_admin() == false)
    		return(false);
    	
    	if(class_exists("UniteProviderAdminUC") == false)
    		return(false);
    	
    	if(UniteProviderAdminUC::$isUserHasCapability == false)
    		return(false);
    	
    	$addonID =  $this->objAddon->getID();
    	
    	$urlEditAddon = HelperUC::getViewUrl_EditAddon($addonID, "", "tab=uc_tablink_html");

    	$html = "<button class='elementor-button elementor-button-default uc-button-edit-html' onclick='window.open(\"$urlEditAddon\")'>Edit Widget HTML</button>";
		
		$this->add_control(
			'html_button_gotoaddon',
			array(
				'type' => Controls_Manager::RAW_HTML,
				'label' => '',
				'raw' => $html
			)
		);
		
    }
    
    
    /**
     * add dynamic attribute
     */
    private function addDynamicAttributes($params){
    	
    	foreach($params as $index => $param){
    		
    		$type = UniteFunctionsUC::getVal($param, "type");
    	
	    	switch($type){
	    		case UniteCreatorDialogParam::PARAM_TEXTAREA:
	    		case UniteCreatorDialogParam::PARAM_TEXTFIELD:
	    		case UniteCreatorDialogParam::PARAM_LINK:
	    		case UniteCreatorDialogParam::PARAM_EDITOR:
	    		case UniteCreatorDialogParam::PARAM_IMAGE:
	    		break;
	    		default:
	    			continue(2);
	    		break;
	    	}
	    	
    		$param["add_dynamic"] = true;
    		
    		$params[$index] = $param;
    	}
    	
    	
    	return($params);    	
    }
    
    /**
     * add pagination controls
     */
    protected function addPaginationControls(){

    	$objPagination = new UniteCreatorElementorPagination();
    	$objPagination->addElementorSectionControls($this);
    	
    }
    
    
    /**
     * register controls with not consolidated addon
     */
   protected function ucRegisterControls_addon(){
                
   		 $isEnoughtMemory = UniteFunctionsUC::isEnoughtPHPMemory();
		 if($isEnoughtMemory == false){
			UniteCreatorElementorIntegrate::logMemoryUsage("Skip register controls (no memory): " . $this->objAddon->getName()." counter:".UniteCreatorElementorIntegrate::$counterControls, true);
			return(false);
		 }
	     
		 UniteCreatorElementorIntegrate::$counterControls++;
		
    	 UniteCreatorElementorIntegrate::logMemoryUsage("Register controls: ".$this->objAddon->getName()." counter: ".UniteCreatorElementorIntegrate::$counterControls);
   	
    	  $isItemsEnabled = $this->objAddon->isHasItems();
          $itemsType = $this->objAddon->getItemsType();
    	  
         $this->start_controls_section(
                'section_general', array(
                'label' => esc_html__("General", "unlimited_elements"),
                    )
          );
          
          $params = $this->objAddon->getProcessedMainParams();
          
          if($isItemsEnabled == true && $itemsType == "image")
          		$this->addGalleryControlUC();
          
          $options = $this->objAddon->getOptions();
          
          //$isDynamicAddon = UniteFunctionsUC::getVal($options, "dynamic_addon");
          //$isDynamicAddon = UniteFunctionsUC::strToBool($isDynamicAddon);

          //check and add dynamic
          //if($isDynamicAddon == true)
          
          //add dynamic to all the addons, not only dynamic
          $params = $this->addDynamicAttributes($params);          	
          
          $hasPostsList = false;
          $postListParam = null;
          
          foreach($params as $param){
          		
          		$type = UniteFunctionsUC::getVal($param, "type");
          		if($type == UniteCreatorDialogParam::PARAM_POSTS_LIST){
          			$hasPostsList = true;
          			$postListParam = $param;
          			continue;
          		}
          		
          		$this->addElementorParamUC($param);
          }
		  
          if($this->isConsolidated == false)
          	$this->addEditAddonControl();
          
          $this->end_controls_section();

          //add query controls section (post list) if exists
          if($hasPostsList == true){
          	
	          $this->start_controls_section(
	                'section_query', array(
	                'label' => esc_html__("Posts Query", "unlimited_elements"),
	              )
	          );
          	  
          	  $this->addElementorParamUC($postListParam);
	          
	          
	          $this->end_controls_section();
          	
          }
          		
          // --- add items
          
          if($isItemsEnabled)
          	$this->addItemsControlsUC($itemsType);
          
          	
          // --- add fonts
          
          $isFontsEnabled = $this->objAddon->isFontsPanelEnabled();
          if($isFontsEnabled == true)
          		$this->addFontControlsUC();
   		  
          //add pagination section if needed
          
          $isArchiveLocation = UniteCreatorElementorIntegrate::$templateType == UniteCreatorElementorIntegrate::TEMPLATE_TYPE_ARCHIVE;
          
          if($hasPostsList == true)
          		$this->addPaginationControls();
   			
   }

    private function a__________CONSOLIDATION_________(){}
   
    
    
   /**
    * add addons dropdown
    */
   protected function ucRegisterControls_cat_addAddonsDropdown($meta){

   		$arrOptions = array();
   		foreach($this->arrAddons as $objAddon){
   			$title = $objAddon->getTitle();
   			$name = $objAddon->getAlias();
   			$arrOptions[$name] = $title;
   		}
   		
   		$defaultValue = UniteFunctionsUC::getFirstNotEmptyKey($arrOptions);
   		
   		$meta =	UniteFunctionsUC::encodeContent($meta);
   		
   		$control = array();
   		$control["type"] = "uc_addon_selector";
   		$control["label"] = esc_html__("Select Style", "unlimited_elements");
   		$control["options"] = $arrOptions;
   		$control["default"] = $defaultValue;
   		$control["meta"] = $meta;
   		
   		
   		$this->add_control("uc_addon_name", $control);
   }
   
   
   /**
    * get param value
    */
   protected function getUCParamValue($param){
   	
    	$value = UniteFunctionsUC::getVal($param, "default_value");
    	    	
    	if(array_key_exists("value", $param))
    		$value = $param["value"];
   		
    	return($value);
   }
   
   
   /**
    * get consolidated params
    */
   protected function getConslidatedData(){
   		
   		$params = array();
		$paramsFonts = array();
   		$paramsItems = array();
   		$arrDefaultItemsData = array();
   		
   		
   		//params per addon
   		$meta_addonsParams = array();
   		$meta_addonParamsItems = array();
   		
   		$minItems = 10000;		//min default items
   		
   		foreach($this->arrAddons as $objAddon){
   			
   			//get addon data
   			$addonName = $objAddon->getAlias();
   			   				   			
	        $isFontsEnabled = $objAddon->isFontsPanelEnabled();
	    	$isItemsEnabled = $objAddon->isHasItems();
	        $itemsType = $objAddon->getItemsType();
	        
	        
   			$meta_addonsParams[$addonName] = array();
   			
   			//add gallery param
   			   			
   			//consolidate main params
   			$addonParams = $objAddon->getProcessedMainParams();
   			
   			if($isItemsEnabled == true && $itemsType == "image"){
   				$paramGallery = $this->getGalleryParamUC();
   				
   				array_unshift($addonParams, $paramGallery);
   			}
   			
   			foreach($addonParams as $param){
   				
   				$name = UniteFunctionsUC::getVal($param, "name");
				$meta_addonsParams[$addonName][] = $name;
   				
				$existingParam = UniteFunctionsUC::getVal($params, $name);
   				
				//set default value if exists
   				if(!empty($existingParam)){
   					$existingValue = $this->getUCParamValue($existingParam);
   					$newParamValue = $this->getUCParamValue($param);
   					if(empty($existingValue) && !empty($newParamValue))
   						$params[$name]["value"] = $newParamValue;
   				}
   				else
   				  $params[$name] = $param;
   			}
   			   			
   			
   			//----- consolidate fonts
   			
	        if($isFontsEnabled == true){
	            $arrFontsSections = $objAddon->getArrFontsParamNames();
		        $arrFontsParams = $objAddon->getArrFontsParams();
	        	
		        if(empty($arrFontsSections))
		        	$arrFontsSections = array();
		        
		        //consolidate font controls
		        foreach($arrFontsSections as $sectionName => $sectionTitle){
		        	
		        	$addonFontParams = $arrFontsParams[$sectionName];
		        	
		        	if(isset($paramsFonts[$sectionName]) == false){
		        		$paramsFonts[$sectionName] = array(
		        			"title"=>$sectionTitle,
		        			"params"=>$addonFontParams
		        		);
		        	}
		        	
		        	//update meta
		        	$meta_addonsParams[$addonName][] = "section_styles_".$sectionName;
		        	
		        	foreach($addonFontParams as $fontParam){
		        		$fontParamName = $fontParam["name"];
		        		$meta_addonsParams[$addonName][] = $fontParamName;
		        	}
		        			        	
		        }//foreach sections
		       	
	        }
	        
	        //----- consolidate items
	        
	        if($isItemsEnabled == true){
				$meta_addonsParams[$addonName][] = "uc_items";
				$meta_addonsParams[$addonName][] = "section_uc_items_consolidation";
	        }
	       	
	        if($isItemsEnabled == true && $itemsType != "image"){
	   			
	        	$meta_addonParamsItems[$addonName] = array();
	        	
	        	$addonParamsItems = $objAddon->getProcessedItemsParams();
	        	
	   			foreach($addonParamsItems as $paramItem){
	   				
	   				$name = UniteFunctionsUC::getVal($paramItem, "name");
					
	   				$meta_addonParamsItems[$addonName][] = $name;
					
					$existingParam = UniteFunctionsUC::getVal($paramsItems, $name);
					
					if(empty($existingParam))
						$paramsItems[$name] = $paramItem;
						
	   			}
   				
	        }
	        
	        //get default item data
	        if($isItemsEnabled == true){
		       	  		        
	        	$arrItemsData = $objAddon->getArrItemsForConfig();
	        	
	        	//dmp($arrItemsData);exit();
	        	
	        	if(!empty($arrItemsData)){
	        		
		         	$arrItemsData = $this->modifyDefaultItemsDataUC($arrItemsData, $objAddon);
		         	
		         	$numItems = count($arrItemsData);
		         	
		         	if($numItems < $minItems)
		         		$minItems = $numItems;
		         			         	
		         	foreach($arrItemsData as $index=>$item){
		         		
		         		//add
		         		if(!isset($arrDefaultItemsData[$index])){		
		         			$arrDefaultItemsData[$index] = $item;
		         		}else{	//update not existing
		         			foreach($item as $paramName=>$value){
		         				
		         				if(isset($arrDefaultItemsData[$index][$paramName]) == false)
		         					$arrDefaultItemsData[$index][$paramName] = $value;
		         					
		         			}
		         			
		         		}//else
		         	
		         	}//foreach
		         			         	
		         	
	        	}//if not empty defaults
	        	
	        }//is items enabled
	        
   		}//foreach addons

   		
   		
   		//cut by min items the defaults
   		if(!empty($arrDefaultItemsData)){
   			
   			if(count($arrDefaultItemsData) > $minItems)
   				$arrDefaultItemsData = array_slice($arrDefaultItemsData, 0, $minItems);
   		}
   		
   		
   		
   		$meta = array();
   		$meta["addon_params"] = $meta_addonsParams;
   		$meta["addon_params_items"] = $meta_addonParamsItems;
   		
   		$output = array();
   		$output["params"] = $params;
   		$output["params_fonts"] = $paramsFonts;
   		$output["params_items"] = $paramsItems;
   		$output["items_defaults"] = $arrDefaultItemsData;
   		$output["meta"] = $meta;
   		
		return($output);
   }
   
   
   
   /**
    * register consolidated font controls
    */
   protected function registerControls_cat_fonts($arrFontSections){
			   			
          	foreach($arrFontSections as $sectionName=>$section){
				
          		$title = $section["title"];
          		$arrParams = $section["params"];
          		
          		$this->start_controls_section(
		                'section_styles_'.$sectionName, array(
		                'label' => $title." ".esc_html__("Styles", "unlimited_elements"),
          				'tab' => "style"
		                 )
		         );
          			        		        	
	          	foreach($arrParams as $name => $param)
	          		$this->addElementorParamUC($param);
	          	
	          	
	          	$this->end_controls_section();
          	}
          	
          	//add fake section
          	
          	$this->start_controls_section(
		                'uc_section_styles_indicator', array(
		                'label' => "UC Styles",
          				'tab' => "style"
		                 )
		     );
		     
	   		$arrControl = array();
	    	$arrControl["type"] = "uc_hr";
	    	$arrControl["class"] = "uc_style_controls_hr";
    		$this->add_control("uc_style_controls_".$sectionName, $arrControl);
		     
		    $this->end_controls_section();
		         
   }
   
   /**
    * register category items consolidated
    */
   protected function registerControls_cat_items($paramsItems, $itemsDefaults){
		 
         $this->start_controls_section(
                'section_uc_items_consolidation', array(
                'label' => esc_html__("Items", "unlimited_elements"),
                    )
          );

         $arrFields = array();
         foreach($paramsItems as $param){
         	
         	$arrControl = $this->getControlArrayUC($param, true);
         	$arrFields[] = $arrControl;
         }
         
         $arrItemsControl = array();
         $arrItemsControl["type"] = Controls_Manager::REPEATER;
         $arrItemsControl["fields"] = $arrFields;
         
         if(empty($itemsDefaults))
         	$itemsDefaults = array();
         
         	
         $arrItemsControl["default"] = $itemsDefaults;
         $this->add_control('uc_items', $arrItemsControl);
         
         $this->end_controls_section();
   }
   
   
   /**
    * register addon by some category
    */
    protected function ucRegisterControls_cat(){
    	    	
    	$data = $this->getConslidatedData();
                
        $meta = $data["meta"];
        $params = $data["params"];
        $paramsFonts = $data["params_fonts"];
    	$paramsItems = $data["params_items"];
    	$itemsDefaults = $data["items_defaults"];
        
        //register general controls
        
    	$this->start_controls_section(
                'section_general', array(
                'label' => esc_html__("General", "unlimited_elements"),
                    )
          );
    	
    	$this->ucRegisterControls_cat_addAddonsDropdown($meta);
    		
        foreach($params as $param)
          		$this->addElementorParamUC($param);
    	          		
    	$this->end_controls_section();
    	
    	//register font controls
    	if(!empty($paramsFonts))
    		$this->registerControls_cat_fonts($paramsFonts);
    	
    	//register items controls
    	if(!empty($paramsItems))
    		$this->registerControls_cat_items($paramsItems, $itemsDefaults);
    		
    }
    
    /**
     * test controls
     */
    private function registerControlsTest(){
    	
         $this->start_controls_section(
                'section_general', array(
                'label' => "General"
                    )
          );
    	
    	
    	$this->add_control("title",[
    		"type"=>"text",
    		"label"=>"Title",
    		"default"=>"This is some title"    	
    	]);
    	
    	$this->end_controls_section();
	}
    
    /**
	* register controls
    */
    protected function _register_controls() {

    	//$this->registerControlsTest();
    	//return(false);
    	
    	try{
          
    	  if($this->isConsolidated == true){
    	  		    	  	
    	  		$this->ucRegisterControls_cat();
    	  	
    	  }else{
    	  		
    	  		$this->ucRegisterControls_addon();
    	  	
    	  }
    		
    	  
    	}catch(Exception $e){
    		
    		HelperHtmlUC::outputException($e);
    		exit();
    	}
        
    }
    
    
    private function a________RENDER_RELATED__________(){}
    
    
    /**
     * get settings values
     */
    protected function getSettingsValuesUC(){
    	
		$arrSettings = $this->get_settings_for_display();
		
		if(self::DEBUG_SETTINGS_VALUES === true)
			dmp($arrSettings);
		
    	$arrValues = array();
    	foreach($arrSettings as $key=>$value){
    		
    		if(empty($key))
    			continue;
    		
    		if($key == "_id"){
    			$arrValues["elementor_id"] = $value;
    			continue;
    		}
    		
    		if($key[0] == "_")
    			continue;
    		
    		$arrValues[$key] = $value;
    	}
    	
    	return($arrValues);
    }
    
    /**
     * parse font key
     */
    protected function parseFontKey($key){
    	
    	if(strpos($key, "ucfont_") !== 0)
			UniteFunctionsUC::throwError("Wrong font key: $key");
    	
		$key = substr($key, strlen("ucfont_"));
		
		$arrKey = explode("__", $key);
		
		if(count($arrKey) != 2)
			UniteFunctionsUC::throwError("Wrong font key, no __ delimiter: $key");
		
		$output = array();
		$output["param_name"] = $arrKey[0];
		$output["font_name"] = $arrKey[1];
		
		return($output);
    }
    
    
    /**
     * get fonts from settings values
     */
    protected function getArrFonts($arrValues){
    	
    	$arrFonts = array();
    	$arrEnabled = array();
    	
    	foreach($arrValues as $key=>$value){
    		
    		if(strpos($key, "ucfont_") !== 0)
    			continue;
    		
    		$arrParsed = $this->parseFontKey($key);
    		
    		$paramName = $arrParsed["param_name"];
    		$fontName = $arrParsed["font_name"];
    		
    		if($fontName == "fonts-enabled"){
    			if($value == "on" || $value == "yes" || $value === true)
    				$arrEnabled[$paramName] = true;
    				
    			continue;
    		}
    		
    		if(!isset($arrFonts[$paramName]))
    			$arrFonts[$paramName] = array();
    		
    		$arrFonts[$paramName][$fontName] = $value;
    					
    	}
    	    	
    	//prepare output
    	$arrFontsOutput = array();
    	
    	if(empty($arrEnabled))
    		return($arrFontsOutput);
    	
    	foreach($arrEnabled as $paramName=>$nothing){
    		
    		if(isset($arrFonts[$paramName]))
    			$arrFontsOutput[$paramName] = $arrFonts[$paramName];
    	}
    	
    	
    	return($arrFontsOutput);
    }
    
    
    /**
     * modify image value from array to regular
     */
    protected function modifyImageValueUC($arrValue){
    	    	
    	if(is_array($arrValue) == false)
    		return($arrValue);
		
    	$isLink = $this->isValueIsLink($arrValue);
    	if($isLink == true)
    		return($arrValue);
    	
    	if(array_key_exists("url", $arrValue) == false && array_key_exists("id", $arrValue) == false)
    		return($arrValue);
    	
    	$id = UniteFunctionsUC::getVal($arrValue, "id");
    	if(!empty($id))
    		return($id);
    	
    	$url = UniteFunctionsUC::getVal($arrValue, "url");
		
    	return($url);
    }
    
    
    /**
     * modify items values array
     */
    protected function modifyArrItemsParamsValuesUC($arrItems, $itemsType){
    	
    	if(empty($arrItems))
    		return(array());
    	
    	foreach($arrItems as $itemIndex=>$arrItem){
    		
    		if($itemsType == "image"){	//modify image base
    			
    			try{
	    			$imageValue = $this->modifyImageValueUC($arrItem);
	    			$arrData = UniteFunctionsWPUC::getAttachmentData($imageValue);
    			}catch(Exception $e){
    				$arrData = array();
    			}
    			
    			if(empty($arrData)){
    				$arrData = array();
    				$arrData["title"] = "";
    				$arrData["image"] = "";
    				$arrData["thumb"] = "";
    				$arrData["description"] = "";
    			}
    			
    			$arrItems[$itemIndex] = $arrData;

    			
    		}else{		//modify regular repeater
	    		
    			foreach($arrItem as $key=>$value){
		    		
	    			if(is_array($value))
		    			$arrItems[$itemIndex][$key] = $this->modifyImageValueUC($value);
		    		
	    		}
    			
    		}
    		    		
    	}
    	
    	
    	return($arrItems);
    }
    

    /**
     * check all the switchers params, if value not found, set false value
     */
    protected function modifyValuesBySwitchers($arrValues, $objAddon){
    	
    	$paramsSwitchers = $objAddon->getParams(UniteCreatorDialogParam::PARAM_RADIOBOOLEAN);
    	
    	if(empty($paramsSwitchers))
    		return($arrValues);
    	
    	foreach($paramsSwitchers as $param){
    		
    		$name = UniteFunctionsUC::getVal($param, "name");
    		$value = UniteFunctionsUC::getVal($arrValues, $name);
    		    		
    		if(empty($value)){
    			$falseValue = UniteFunctionsUC::getVal($param, "false_value");
    			$arrValues[$name] = $falseValue;
    		}
    		    		
    	}
    	
    	return($arrValues);
    }
    
    /**
     * return if the value is a link
     */
    private function isValueIsLink($arrValue){
    	
    	if(is_array($arrValue) == false)
    		return(false);
    		
    	if(array_key_exists("is_external", $arrValue) == false)
    		return(false);
    	
    	if(array_key_exists("nofollow", $arrValue) == false)
    		return(false);
    	
    	return(true);
    }
        
    
    /**
     * get main param values from values
     */
    protected function getArrMainParamValuesUC($arrValues, $objAddon){
    			    	
    	$arrValues = $this->modifyValuesBySwitchers($arrValues, $objAddon);
    	
    	foreach($arrValues as $key => $value){
    		
    		
    		if(strpos($key, "ucfont_") === 0){
    			unset($arrValues[$key]);
    			continue;
    		}
    		
    		if($key == "uc_items"){
    			unset($arrValues[$key]);
    			continue;
    		}
    		
    		if(is_array($value)){
    			
    			$arrValues[$key] = $this->modifyImageValueUC($value);
    		}
    		
    	}
    	
    	
    	return($arrValues);
    }

    /**
     * check if edit mode
     */
    protected function isEditMode(){

    	$isEditMode = HelperUC::isElementorEditMode();
    	
    	return($isEditMode);
    }
    
    /**
     * put addon not exist error message
     */
    private function putAddonNotExistErrorMesssage(){
    	
    	$addonName = $this->get_name();
    	
    	echo "<span style='color:red'>$addonName widget not exists</span>";
    }

    /**
     * get pagination extra html
     */
    private function getExtraWidgetHTML_pagination($arrValues, UniteCreatorAddon $objAddon){
    	
    	$arrPostListParam = $objAddon->getParamByType(UniteCreatorDialogParam::PARAM_POSTS_LIST);
    	if(empty($arrPostListParam))
    		return("");
    	
    	$paginationType = UniteFunctionsUC::getVal($arrValues, "pagination_type");
    	    	
    	if(empty($paginationType))
    		return("");
    	
    	$templateType = UniteCreatorElementorIntegrate::$templateType;
    	
    	$isArchivePage = UniteFunctionsWPUC::isArchiveLocation();
    	   
    	if($isArchivePage == false)
    		return(false);
    	
		$objPagination = new UniteCreatorElementorPagination();
		$htmlPagination = $objPagination->getHTMLPaginationByElementor($arrValues);
    		    	
		return($htmlPagination);
    }
    
    /**
     * get extra widget html
     */
    private function getExtraWidgetHTML($arrValues, UniteCreatorAddon $objAddon){
    	    	
    	$htmlPagination = $this->getExtraWidgetHTML_pagination($arrValues, $objAddon);
    	
    	return($htmlPagination);
    }
    
    
    /**
     * render by addon
     */
    protected function ucRenderByAddon($objAddon){
    	try{
    		
	    	if(empty($objAddon)){
	    		$this->putAddonNotExistErrorMesssage();
	    		return(false);
	    	}
	    	
	    	$arrValues = $this->getSettingsValuesUC();
	   		
	    	HelperUC::addDebug("widget values", $arrValues);
	    	
	    	$arrFonts = $this->getArrFonts($arrValues);
	    	
	    	//get items
	    	$hasItems = $objAddon->isHasItems();
	    	$itemsType = $objAddon->getItemsType();
	    	
	    	if($hasItems == true){
	    		
	    		if($itemsType == "image")
	    			$arrItems = UniteFunctionsUC::getVal($arrValues, "uc_gallery_items");
	    		else 
	    			$arrItems = UniteFunctionsUC::getVal($arrValues, "uc_items");
	    			    			
	    		$arrItems = $this->modifyArrItemsParamsValuesUC($arrItems, $itemsType);
	    		
	    	}
	   		
	    		
	    	$arrMainParamValues = $this->getArrMainParamValuesUC($arrValues, $objAddon);
	    	
	    	
	    	//check if inside editor
	        $isEditMode = $this->isEditMode();
	        
	    	$objAddon->setParamsValues($arrMainParamValues);
	    	$objAddon->setArrFonts($arrFonts);
	    	    	
	    	if($hasItems == true)
	    		$objAddon->setArrItems($arrItems);
	    	
	        $output = new UniteCreatorOutput();
	        $output->initByAddon($objAddon);
			
	        $cssFilesPlace = HelperProviderCoreUC_EL::getGeneralSetting("css_includes_to");
			if($isEditMode == true)
				$cssFilesPlace = "body";
			
	        if($cssFilesPlace == "footer")
	        	$output->processIncludes("css");
	        
	        //decide if the js will be in footer
	        $scriptsHardCoded = false;
	        
	        $isInFooter = HelperProviderCoreUC_EL::getGeneralSetting("js_in_footer");
	        $isInFooter = UniteFunctionsUC::strToBool($isInFooter);
			
	        if ($isInFooter == false)
	            $scriptsHardCoded = true;
			
	        if($isEditMode == true)
	            $scriptsHardCoded = true;
	
	        
	        $putCssIncludesInBody = ($cssFilesPlace == "body") ? true : false;
			
	        $params = array();
	        
	        if($isEditMode == true){
				$arrIncludes = $output->getProcessedIncludes(true, false, "js");
	        	$jsonIncludes = UniteFunctionsUC::jsonEncodeForClientSide($arrIncludes);
	        	
	        	if(empty($arrIncludes))
	        		$params["wrap_js_timeout"] = true;
	        	else{
	        		$params["wrap_js_start"] = "window.parent.g_objUCElementorEditorAdmin.ucLoadJSAndRun(window, {$jsonIncludes}, function(){";
	        		$params["wrap_js_end"] = "});";
	        	}
	        	
	        }else{
	        	
	        	$output->processIncludes("js");
	        }
	                
	        $htmlOutput = $output->getHtmlBody($scriptsHardCoded, $putCssIncludesInBody,true,$params);
	        	        
        	echo UniteProviderFunctionsUC::escCombinedHtml($htmlOutput);
	        
	        $htmlExtra = $this->getExtraWidgetHTML($arrValues, $objAddon);
        	
	        if(!empty($htmlExtra))
	        	echo $htmlExtra;
        	
    	}catch(Exception $e){
    		
    		HelperHtmlUC::outputException($e);
    		
    	}
                
    }
    
    
    /**
     * render the HTML
     */    
    protected function render() {
		
    	if($this->isNoMemory == true){
    		echo "no memory to render ".$this->isNoMemory_addonName." widget. <br> Please increase memory_limit in php.ini";	
    		return(false);
    	}
    	
    	if($this->isConsolidated == false){
    		
    		$this->ucRenderByAddon($this->objAddon);
    		
    	}else{		//for consolidated, find the right addon
    		
    		$arrSettings = $this->get_settings();
    		$addonName = UniteFunctionsUC::getVal($arrSettings, "uc_addon_name");
			
    		try{
	    		$objAddon = new UniteCreatorAddon();
	    		$objAddon->initByAlias($addonName, UniteCreatorElementorIntegrate::ADDONS_TYPE);
    			$this->ucRenderByAddon($objAddon);
	    		
    		}catch(Exception $e){
    			echo "error render widget: $addonName";
    			HelperHtmlUC::outputException($e);
    		}
    		
    	}
    	
    }
    
    
    /**
     * render HTML with backbone.js for elementor admin
     */ 
    /* 
    protected function content_template() {
    	
    	echo "content template";
    	
    }
    */
	

}
