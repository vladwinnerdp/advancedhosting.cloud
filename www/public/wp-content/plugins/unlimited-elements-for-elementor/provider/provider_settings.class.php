<?php
/**
 * @package Unlimited Elements
 * @author UniteCMS.net
 * @copyright (C) 2012 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ELEMENTS_INC') or die('Restricted access');

class UniteCreatorSettings extends UniteCreatorSettingsWork{

	
	/**
	 * add settings provider types
	 */
	protected function addSettingsProvider($type, $name,$value,$title,$extra ){
		
		$isAdded = false;
		
		return($isAdded);
	}
	
	
	/**
	 * show taxanomy
	 */
	private function showTax(){
										
		$showTax = UniteFunctionsUC::getGetVar("maxshowtax", "", UniteFunctionsUC::SANITIZE_NOTHING);
		$showTax = UniteFunctionsUC::strToBool($showTax);
		
		if($showTax == true){
			
			$args = array("taxonomy"=>"");
			$cats = get_categories($args);
			
			$arr1 = UniteFunctionsWPUC::getTaxonomiesWithCats();
			
			
			$arrPostTypes = UniteFunctionsWPUC::getPostTypesAssoc();
			$arrTax = UniteFunctionsWPUC::getTaxonomiesWithCats();
			$arrCustomTypes = get_post_types(array('_builtin' => false));
			
			$arr = get_taxonomies();
			
			$taxonomy_objects = get_object_taxonomies( 'post', 'objects' );
   			dmp($taxonomy_objects);
   			
			dmp($arrCustomTypes);
			dmp($arrPostTypes);
			exit();
		}
		
	}
	
	
	/**
	 * get categories from all post types
	 */
	protected function getCategoriesFromAllPostTypes($arrPostTypes){
		
		if(empty($arrPostTypes))
			return($array);

		$arrAllCats = array();
		$arrAllCats[__("All Categories", "unlimited_elements")] = "all";
		
		foreach($arrPostTypes as $name => $arrType){
		
			if($name == "page")
				continue;
			
			$postTypeTitle = UniteFunctionsUC::getVal($arrType, "title");
			
			$cats = UniteFunctionsUC::getVal($arrType, "cats");
			
			if(empty($cats))
				continue;
			
			foreach($cats as $catID => $catTitle){
				
				if($name != "post")
					$catTitle = $catTitle." ($postTypeTitle type)";
				
				$arrAllCats[$catTitle] = $catID;
				
			}
			
		}
		
		
		return($arrAllCats);
	}
	
	/**
	 * get taxonomies array for terms picker
	 */
	private function addPostTermsPicker_getArrTaxonomies($arrPostTypesWithTax){
		
		$arrAllTax = array();
		
		
		//make taxonomies data
		$arrTaxonomies = array();
		foreach($arrPostTypesWithTax as $typeName => $arrType){
			
			$arrItemTax = UniteFunctionsUC::getVal($arrType, "taxonomies");
			
			$arrTaxOutput = array();
			
			//some fix that avoid double names
			$arrDuplicateValues = UniteFunctionsUC::getArrayDuplicateValues($arrItemTax);
			
			foreach($arrItemTax as $slug => $taxTitle){
				
				$isDuplicate = array_key_exists($taxTitle, $arrDuplicateValues);
				
				//some modification for woo
				if($taxTitle == "Tag" && $slug != "post_tag")
					$isDuplicate = true;
				
				if(isset($arrAllTax[$taxTitle]))
					$isDuplicate = true;
					
				if($isDuplicate == true)
					$taxTitle = UniteFunctionsUC::convertHandleToTitle($slug);
				
				$taxTitle = ucwords($taxTitle);
				
				$arrTaxOutput[$slug] = $taxTitle;
				
				$arrAllTax[$taxTitle] = $slug;
			}
			
			if(!empty($arrTaxOutput))
				$arrTaxonomies[$typeName] = $arrTaxOutput;
		}
		
		$response = array();
		$response["post_type_tax"] = $arrTaxonomies;
		$response["taxonomies_simple"] = $arrAllTax;
		
		
		return($response);
	}

	
	/**
	 * add users picker
	 */
	protected function addUsersPicker($name,$value,$title,$extra){
		
		$arrRoles = UniteFunctionsWPUC::getRolesShort(true);
		
		//----- roles in -------
		
		$params = array();
		$params["origtype"] = UniteCreatorDialogParam::PARAM_DROPDOWN;
		
		if(!empty($arrRoles))
			$arrRoles = array_flip($arrRoles);
		
		$role = UniteFunctionsUC::getVal($value, $name."_role");
		if(empty($role))
			$role = UniteFunctionsUC::getArrFirstValue($arrRoles);
		
		$params["is_multiple"] = true;
		$params["placeholder"] = __("All Roles", "unlimited_elements");
		//$params["description"] = __("Get all the users if leave empty", "unlimited_elements");
		
		$this->addMultiSelect($name."_role", $arrRoles, __("Select Roles", "unlimited_elements"), $role, $params);
		
		
		//-------- exclude roles ---------- 
		
		$arrRoles = UniteFunctionsWPUC::getRolesShort();
		
		$params = array();
		$params["origtype"] = UniteCreatorDialogParam::PARAM_DROPDOWN;
		
		if(!empty($arrRoles))
			$arrRoles = array_flip($arrRoles);
		
		$roleExclude = UniteFunctionsUC::getVal($value, $name."_role_exclude");
		
		$params["is_multiple"] = true;
		
		$this->addMultiSelect($name."_role_exclude", $arrRoles, __("Exclude Roles", "unlimited_elements"), $roleExclude, $params);
		
		
		
	}

	/**
	 * add users picker
	 */
	protected function addMenuPicker($name, $value, $title, $extra){
		
		$arrMenus = UniteFunctionsWPUC::getMenusListShort();
		
		$menuID = UniteFunctionsUC::getVal($value, $name."_id");
		
		if(empty($menuID))
			$menuID = UniteFunctionsUC::getFirstNotEmptyKey($arrMenus);
					
		$arrMenus = array_flip($arrMenus);
		
		$params = array();
		$params["origtype"] = UniteCreatorDialogParam::PARAM_DROPDOWN;
		
		$this->addSelect($name."_id", $arrMenus, __("Select Menu", "unlimited_elements"), $menuID, $params);
		
		//add depth
		$arrDepth = array();
		$arrDepth["0"] = __("All Depths", "unlimited_elements");
		$arrDepth["1"] = __("1", "unlimited_elements");
		$arrDepth["2"] = __("2", "unlimited_elements");
		$arrDepth["3"] = __("3", "unlimited_elements");
		
		$arrDepth = array_flip($arrDepth);
		$depth = UniteFunctionsUC::getVal($value, $name."_depth", "0");
				
		$this->addSelect($name."_depth", $arrDepth, __("Max Depth", "unlimited_elements"), $depth, $params);
		
	}
	
	
	/**
	 * add post terms settings
	 */
	protected function addPostTermsPicker($name, $value, $title, $extra){
		
		$arrPostTypesWithTax = UniteFunctionsWPUC::getPostTypesWithTaxomonies(GlobalsProviderUC::$arrFilterPostTypes, false);
		
		$taxData = $this->addPostTermsPicker_getArrTaxonomies($arrPostTypesWithTax);
		
		
		$arrPostTypesTaxonomies = $taxData["post_type_tax"];
		
		$arrTaxonomiesSimple = $taxData["taxonomies_simple"];
				
		//----- add post types ---------
		
		//prepare post types array
		
		$arrPostTypes = array();
		foreach($arrPostTypesWithTax as $typeName => $arrType){
			
			$title = UniteFunctionsUC::getVal($arrType, "title");
			if(empty($title))
				$title = ucfirst($typeName);
			
			$arrPostTypes[$title] = $typeName;
		}
		
		$postType = UniteFunctionsUC::getVal($value, $name."_posttype");
		if(empty($postType))
			$postType = UniteFunctionsUC::getArrFirstValue($arrPostTypes);
		
		$params = array();
		
		$params[UniteSettingsUC::PARAM_CLASSADD] = "unite-setting-post-type";
		$dataTax = UniteFunctionsUC::encodeContent($arrPostTypesTaxonomies);
		
		$params[UniteSettingsUC::PARAM_ADDPARAMS] = "data-arrposttypes='$dataTax' data-settingtype='select_post_taxonomy' data-settingprefix='{$name}'";
		$params["datasource"] = "post_type";
		$params["origtype"] = "uc_select_special";
		
		$this->addSelect($name."_posttype", $arrPostTypes, __("Select Post Type", "unlimited_elements"), $postType, $params);
		
		//---------- add taxonomy ---------
				
		$params = array();
		$params["datasource"] = "post_taxonomy";
		$params[UniteSettingsUC::PARAM_CLASSADD] = "unite-setting-post-taxonomy";
		$params["origtype"] = UniteCreatorDialogParam::PARAM_DROPDOWN;
		
		$arrTax = UniteFunctionsUC::getVal($arrPostTypesTaxonomies, $postType, array());
		
		if(!empty($arrTax))
			$arrTax = array_flip($arrTax);
				
		$taxonomy = UniteFunctionsUC::getVal($value, $name."_taxonomy");
		if(empty($taxonomy))
			$taxonomy = UniteFunctionsUC::getArrFirstValue($arrTax);
				
		$this->addSelect($name."_taxonomy", $arrTaxonomiesSimple, __("Select Taxonomy", "unlimited_elements"), $taxonomy, $params);
		
		//---------- add exclude ---------
		$params = array();
		$params["origtype"] = UniteCreatorDialogParam::PARAM_TEXTFIELD;
		$params["placeholder"] = "Example: cat1,cat2";
		$params["description"] = "To exclude, enter comma saparated term slugs";
		
		$exclude = UniteFunctionsUC::getVal($value, $name."_exclude");
		
		$this->addTextBox($name."_exclude", $exclude, __("Exclude Terms", "unlimited_elements"), $params);
		
		
		
		// --------- add order by -------------
		
		$arrOrderBy = UniteFunctionsWPUC::getArrTermSortBy();
		$arrOrderBy = array_flip($arrOrderBy);
		
		$orderBy = UniteFunctionsUC::getVal($value, $name."_orderby", "name");
		
		$params = array();
		$params["origtype"] = UniteCreatorDialogParam::PARAM_DROPDOWN;
		
		$this->addSelect($name."_orderby", $arrOrderBy, __("Order By", "unlimited_elements"), $orderBy, $params);
		
		//--------- add order direction -------------
		
		$arrOrderDir = UniteFunctionsWPUC::getArrSortDirection();
		
		$orderDir = UniteFunctionsUC::getVal($value, $name."_orderdir", UniteFunctionsWPUC::ORDER_DIRECTION_ASC);
		
		$arrOrderDir = array_flip($arrOrderDir);
		
		$params = array();
		$params["origtype"] = UniteCreatorDialogParam::PARAM_DROPDOWN;
		
		$this->addSelect($name."_orderdir", $arrOrderDir, __("Order Direction", "unlimited_elements"), $orderDir, $params);
		
		//--------- add hide empty -------------
		
		$hideEmpty = UniteFunctionsUC::getVal($value, $name."_hideempty", "no_hide");
		
		
		$arrHide = array();
		$arrHide["no_hide"] = "Don't Hide";
		$arrHide["hide"] = "Hide";
		$arrHide = array_flip($arrHide);
		
		$params = array();
		$params["origtype"] = UniteCreatorDialogParam::PARAM_DROPDOWN;
		
		$this->addSelect($name."_hideempty", $arrHide, __("Hide Empty", "unlimited_elements"), $hideEmpty, $params);
		
		
		//add hr
		$params = array();
		$params["origtype"] = UniteCreatorDialogParam::PARAM_HR;
		
		$this->addHr("post_terms_sap", $params);
		
		
	}
	
	
	/**
	 * add background settings
	 */
	protected function addBackgroundSettings($name, $value, $title, $param){
		
		$arrTypes = array();
		$arrTypes["none"] = __("No Background", "unlimited_elements");
		$arrTypes["solid"] = __("Solid", "unlimited_elements");
		$arrTypes["gradient"] = __("Gradient", "unlimited_elements");
		
		$arrTypes = array_flip($arrTypes);
		
		$type = UniteFunctionsUC::getVal($param, "background_type", "none");
		
		$this->addRadio($name."_type", $arrTypes, "Background Type", $type);
		
		$solid = UniteFunctionsUC::getVal($param, "solid_color");
		$gradient1 = UniteFunctionsUC::getVal($param, "gradient_color1");
		$gradient2 = UniteFunctionsUC::getVal($param, "gradient_color2");
		
		$this->addHr();
		
		//solid color
		$this->startBulkControl($name."_type", "show", "solid");
		
			$this->addColorPicker($name."_color_solid", $solid, __("Solid Color", "unlimited_elements"));
		
		$this->endBulkControl();
		
		//gradient color
		$this->startBulkControl($name."_type", "show", "gradient");
		
			$this->addColorPicker($name."_color_gradient1", $gradient1, __("Gradient Color1", "unlimited_elements"));
			$this->addColorPicker($name."_color_gradient2", $gradient2, __("Gradient Color2", "unlimited_elements"));
		
		$this->endBulkControl();
		
	}
	
	
	/**
	 * add post list picker
	 */
	protected function addPostsListPicker($name,$value,$title,$extra){
		
		$simpleMode = UniteFunctionsUC::getVal($extra, "simple_mode");
		$simpleMode = UniteFunctionsUC::strToBool($simpleMode);
		
		$allCatsMode = UniteFunctionsUC::getVal($extra, "all_cats_mode");
		$allCatsMode = UniteFunctionsUC::strToBool($allCatsMode);
		
		$addCurrentPosts = UniteFunctionsUC::getVal($extra, "add_current_posts");
		$addCurrentPosts = UniteFunctionsUC::strToBool($addCurrentPosts);
		
		$arrPostTypes = UniteFunctionsWPUC::getPostTypesWithCats(GlobalsProviderUC::$arrFilterPostTypes);
				
		$isWpmlExists = UniteCreatorWpmlIntegrate::isWpmlExists();
		
		/*
		if($isWpmlExists == true){
			
			$objWpmlIntegrate = new UniteCreatorWpmlIntegrate();
			
			$arrLanguages = $objWpmlIntegrate->getLanguagesShort(true);
			$activeLanguege = $objWpmlIntegrate->getActiveLanguage();
		}
		*/
		
		$arrGlobalElementorCondition = array();
		
		//fill simple types
		$arrTypesSimple = array();
		
		if($simpleMode)
			$arrTypesSimple = array("Post"=>"post","Page"=>"page");
		else{
			
			foreach($arrPostTypes as $arrType){
				
				$postTypeName = UniteFunctionsUC::getVal($arrType, "name");
				$postTypeTitle = UniteFunctionsUC::getVal($arrType, "title");
				
				if(isset($arrTypesSimple[$postTypeTitle]))
					$arrTypesSimple[$postTypeName] = $postTypeName;
				else
					$arrTypesSimple[$postTypeTitle] = $postTypeName;
			}
			
		}
		
		
		//----- posts source ----
		//UniteFunctionsUC::showTrace();
		
		$arrGlobalElementorCondition = array();
		$arrCustomOnlyCondition = array();
		$arrRelatedOnlyCondition = array();
		
		
		if($addCurrentPosts == true){
			
			$arrCurrentElementorCondition = array(
				$name."_source" => "current",
			);

			$arrGlobalElementorCondition = array(
				$name."_source!" => "current",
			);
			
			$arrCustomOnlyCondition = array(
				$name."_source" => "custom",
			);
			
			$arrRelatedOnlyCondition = array(
				$name."_source" => "related",
			);
			
			$arrNotInRelatedCondition = array(
				$name."_source!" => "related",
			);
			
			
			$params = array();
			$params["origtype"] = UniteCreatorDialogParam::PARAM_DROPDOWN;
			//$params["description"] = esc_html__("Choose the source of the posts list", "unlimited_elements");
			
			$source = UniteFunctionsUC::getVal($value, $name."_source", "custom");
			$arrSourceOptions = array();
			$arrSourceOptions[__("Current Query Posts", "unlimited_elements")] = "current";
			$arrSourceOptions[__("Custom Posts", "unlimited_elements")] = "custom";
			$arrSourceOptions[__("Related Posts", "unlimited_elements")] = "related";
			
			
			$this->addSelect($name."_source", $arrSourceOptions, esc_html__("Posts Source", "unlimited_elements"), $source, $params);
			
			//-------- add static text - current --------
			
			$params = array();
			$params["origtype"] = UniteCreatorDialogParam::PARAM_STATIC_TEXT;
			$params["description"] = esc_html__("Choose the source of the posts list", "unlimited_elements");
			$params["elementor_condition"] = $arrCurrentElementorCondition;
			
			$this->addStaticText("The current posts are being used in archive pages", $name."_currenttext", $params);

			//-------- add static text - related --------
			
			$params = array();
			$params["origtype"] = UniteCreatorDialogParam::PARAM_STATIC_TEXT;
			$params["elementor_condition"] = $arrRelatedOnlyCondition;
			
			$this->addStaticText("The related posts are being used in single page. Posts from same post type and terms", $name."_relatedtext", $params);
			
		}
		
		
		//----- post type -----
		
		$postType = UniteFunctionsUC::getVal($value, $name."_posttype", "post");
		
		$params = array();
		
		if($simpleMode == false){
			$params["datasource"] = "post_type";
			$params[UniteSettingsUC::PARAM_CLASSADD] = "unite-setting-post-type";
			
			$dataCats = UniteFunctionsUC::encodeContent($arrPostTypes);
			
			$params[UniteSettingsUC::PARAM_ADDPARAMS] = "data-arrposttypes='$dataCats' data-settingtype='select_post_type' data-settingprefix='{$name}'";
		}
		
		$params["origtype"] = "uc_select_special";
		//$params["description"] = esc_html__("Select which Post Type or Custom Post Type you wish to display", "unlimited_elements");
		
		if(!empty($arrGlobalElementorCondition))
			$params["elementor_condition"] = $arrCustomOnlyCondition;
		
		$params["is_multiple"] = true;
		
		$this->addMultiSelect($name."_posttype", $arrTypesSimple, esc_html__("Post Types", "unlimited_elements"), $postType, $params);
		
		
		//----- add categories -------
		
		$arrCats = array();
		
		if($simpleMode == true){
			
			$arrCats = $arrPostTypes["post"]["cats"];
			$arrCats = array_flip($arrCats);
			$firstItemValue = reset($arrCats);
			
		}else if($allCatsMode == true){
			
			$arrCats = $this->getCategoriesFromAllPostTypes($arrPostTypes);
			$firstItemValue = reset($arrCats);
			
		}else{
			$firstItemValue = "";
		}
		
		
		$category = UniteFunctionsUC::getVal($value, $name."_category", $firstItemValue);
		
		$params = array();
		
		if($simpleMode == false){
			$params["datasource"] = "post_category";
			$params[UniteSettingsUC::PARAM_CLASSADD] = "unite-setting-post-category";
		}
		
		$params["origtype"] = UniteCreatorDialogParam::PARAM_DROPDOWN;
		$params["is_multiple"] = true;
		
		//$params["description"] = esc_html__("Filter Posts by Specific Term", "unlimited_elements");
		
		if(!empty($arrGlobalElementorCondition))
			$params["elementor_condition"] = $arrCustomOnlyCondition;
		
		
		$this->addMultiSelect($name."_category", $arrCats, esc_html__("Include By Term", "unlimited_elements"), $category, $params);

		
		// --------- Include by term relation -------------
		
		$params = array();
		$params["origtype"] = UniteCreatorDialogParam::PARAM_DROPDOWN;
		
		if(!empty($arrGlobalElementorCondition))
			$params["elementor_condition"] = $arrCustomOnlyCondition;
		
		$relation = UniteFunctionsUC::getVal($value, $name."_category_relation", "AND");
		
		$arrRelationItems = array();
		$arrRelationItems["And"] = "AND";
		$arrRelationItems["Or"] = "OR";
		
		$this->addSelect($name."_category_relation", $arrRelationItems, __("Include By Term Relation", "unlimited_elements"), $relation, $params);
		
		
		// --------- add exclude by -------------
		
		$arrExclude = array();
		$arrExclude["current_post"] = __("Current Post", "unlimited_elements");
		
		$params = array();
		$params["origtype"] = UniteCreatorDialogParam::PARAM_DROPDOWN;
		$params["is_multiple"] = true;
		
		if(!empty($arrGlobalElementorCondition))
			$params["elementor_condition"] = $arrNotInRelatedCondition;
		
		//$params["description"] = esc_html__("Exclude Posts By", "unlimited_elements");
		
		$arrExclude = array_flip($arrExclude);
		
		$arrExcludeValues = "";
		
		$category = UniteFunctionsUC::getVal($value, $name."_category", $firstItemValue);
		
		$this->addMultiSelect($name."_excludeby", $arrExclude, __("Exclude By", "unlimited_elements"), $arrExcludeValues, $params);
		
		
		//------- max items --------
		
		$params = array("unit"=>"posts");
		$maxItems = UniteFunctionsUC::getVal($value, $name."_maxitems", 10);
		$params["origtype"] = UniteCreatorDialogParam::PARAM_TEXTFIELD;
		$params["placeholder"] = __("All Posts","unlimited_elements");
		
		//$params["description"] = "Enter how many Posts you wish to display, -1 for unlimited";
		
		if(!empty($arrGlobalElementorCondition))
			$params["elementor_condition"] = $arrGlobalElementorCondition;
		
		$this->addTextBox($name."_maxitems", $maxItems, esc_html__("Max Posts", "unlimited_elements"), $params);
		
		
		//----- orderby --------
		
		$arrOrder = UniteFunctionsWPUC::getArrSortBy();
		$arrOrder = array_flip($arrOrder);
		
		$arrDir = UniteFunctionsWPUC::getArrSortDirection();
		$arrDir = array_flip($arrDir);
				
		//---- orderby1
		
		$params = array();
		
		//$params[UniteSettingsUC::PARAM_ADDFIELD] = $name."_orderdir1";
		
		$orderBY = UniteFunctionsUC::getVal($value, $name."_orderby", UniteFunctionsWPUC::SORTBY_ID);
		$params["origtype"] = UniteCreatorDialogParam::PARAM_DROPDOWN;
		//$params["description"] = esc_html__("Select how you wish to order posts", "unlimited_elements");
		
		if(!empty($arrGlobalElementorCondition))
			$params["elementor_condition"] = $arrGlobalElementorCondition;
		
		
		$this->addSelect($name."_orderby", $arrOrder, __("Order By", "unlimited_elements"), $orderBY, $params);
		
		
		
		//--- meta value param -------
		
		$params = array();
		$params["origtype"] = UniteCreatorDialogParam::PARAM_TEXTFIELD;
		$params["class"] = "alias";
		
		$arrCondition = $arrGlobalElementorCondition;
		$arrCondition[$name."_orderby"] = array(UniteFunctionsWPUC::SORTBY_META_VALUE, UniteFunctionsWPUC::SORTBY_META_VALUE_NUM);
		
		$params["elementor_condition"] = $arrCondition;
		
		$this->addTextBox($name."_orderby_meta_key1", "" , __("&nbsp;&nbsp;Custom Field Name","unlimited_elements"), $params);

		$this->addControl($name."_orderby", $name."_orderby_meta_key1", "show", UniteFunctionsWPUC::SORTBY_META_VALUE.",".UniteFunctionsWPUC::SORTBY_META_VALUE_NUM);
		
		//---- order dir -----
		
		$params = array();
		$params["origtype"] = UniteCreatorDialogParam::PARAM_DROPDOWN;
		//$params["description"] = esc_html__("Select order direction. Descending A-Z or Accending Z-A", "unlimited_elements");
		
		if(!empty($arrGlobalElementorCondition))
			$params["elementor_condition"] = $arrGlobalElementorCondition;
		
		$orderDir1 = UniteFunctionsUC::getVal($value, $name."_orderdir1", UniteFunctionsWPUC::ORDER_DIRECTION_DESC );
		$this->addSelect($name."_orderdir1", $arrDir, __("&nbsp;&nbsp;Order By Direction", "unlimited_elements"), $orderDir1, $params);
		
		$params = array();
		$params["origtype"] = UniteCreatorDialogParam::PARAM_HR;
		
		$this->addHr("", $params);
		
		//---- query id -----
		
		$isPro = GlobalsUC::$isProVersion;
		
		$params = array();
		$params["origtype"] = UniteCreatorDialogParam::PARAM_TEXTFIELD;
		if($isPro == true){
			
			$title = __("Query ID", "unlimited_elements");
			$params["description"] = __("Give your Query unique ID to been able to filter it in server side using add_filter() function","unlimited_elements");
			
		}else{		//free version
			
			$params["description"] = __("Give your Query unique ID to been able to filter it in server side using add_filter() function. This feature exists in a PRO Version only","unlimited_elements");
			$title = __("Query ID (pro)", "unlimited_elements");
			$params["disabled"] = true;
		}
		
		$queryID = UniteFunctionsUC::getVal($value, $name."_queryid");
		
		$this->addTextBox($name."_queryid", $queryID, $title, $params);
		
		
	}
	
	
	
	
	
}