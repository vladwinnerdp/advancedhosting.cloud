<?php


defined('UNLIMITED_ELEMENTS_INC') or die('Restricted access');


	class UniteFunctionsWPUC{

		public static $urlSite;
		public static $urlAdmin;
		private static $db;
		private static $objAcfIntegrate;
		
		private static $arrTaxCache;
		
		const SORTBY_NONE = "none";
		const SORTBY_ID = "ID";
		const SORTBY_AUTHOR = "author";
		const SORTBY_TITLE = "title";
		const SORTBY_SLUC = "name";
		const SORTBY_DATE = "date";
		const SORTBY_LAST_MODIFIED = "modified";
		const SORTBY_RAND = "rand";
		const SORTBY_COMMENT_COUNT = "comment_count";
		const SORTBY_MENU_ORDER = "menu_order";
		const SORTBY_PARENT = "parent";
		const SORTBY_META_VALUE = "meta_value";
		const SORTBY_META_VALUE_NUM = "meta_value_num";
		
		const ORDER_DIRECTION_ASC = "ASC";
		const ORDER_DIRECTION_DESC = "DESC";
		
		const THUMB_SMALL = "thumbnail";
		const THUMB_MEDIUM = "medium";
		const THUMB_LARGE = "large";
		const THUMB_FULL = "full";
		
		const STATE_PUBLISHED = "publish";
		const STATE_DRAFT = "draft";
		
		
		/**
		 * 
		 * init the static variables
		 */
		public static function initStaticVars(){
			
			self::$urlSite = site_url();
			
			if(substr(self::$urlSite, -1) != "/")
				self::$urlSite .= "/";
			
			self::$urlAdmin = admin_url();			
			if(substr(self::$urlAdmin, -1) != "/")
				self::$urlAdmin .= "/";
				
		}
		
		
		/**
		 * get DB
		 */
		public static function getDB(){
			
			if(empty(self::$db))
				self::$db = new UniteCreatorDB();
				
			return(self::$db);
		}
		
		/**
		 * get acf integrate object
		 */
		public static function getObjAcfIntegrate(){
			
			if(empty(self::$objAcfIntegrate))
				self::$objAcfIntegrate = new UniteCreatorAcfIntegrate();
				
			return(self::$objAcfIntegrate);
		}
		
		
		public static function a_________POSTS_TYPES________(){}
		
		/**
		 * 
		 * return post type title from the post type
		 */
		public static function getPostTypeTitle($postType){
			
			$objType = get_post_type_object($postType);
						
			if(empty($objType))
				return($postType);

			$title = $objType->labels->singular_name;
			
			return($title);
		}
		
		
		/**
		 * 
		 * get post type taxomonies
		 */
		public static function getPostTypeTaxomonies($postType){
			
			$arrTaxonomies = get_object_taxonomies(array( 'post_type' => $postType ), 'objects');
					
			$arrNames = array();
			foreach($arrTaxonomies as $key=>$objTax){
				$name = $objTax->labels->singular_name;
				if(empty($name))
					$name = $objTax->labels->name;
				
				$arrNames[$objTax->name] = $objTax->labels->singular_name;
			}
			
			return($arrNames);
		}
		
		/**
		 * 
		 * get post types taxonomies as string
		 */
		public static function getPostTypeTaxonomiesString($postType){
			$arrTax = self::getPostTypeTaxomonies($postType);
			$strTax = "";
			foreach($arrTax as $name=>$title){
				if(!empty($strTax))
					$strTax .= ",";
				$strTax .= $name;
			}
			
			return($strTax);
		}
		
		/**
		 *
		 * get post types array with taxomonies
		 */
		public static function getPostTypesWithTaxomonies($filterPostTypes = array(), $fetchWithNoTax = true){
			
			$arrPostTypes = self::getPostTypesAssoc();
			
			$arrPostTypesOutput = array();
			
			foreach($arrPostTypes as $postType => $title){
				
				if(array_key_exists($postType, $filterPostTypes) == true)
					continue;
									
				$arrTaxomonies = self::getPostTypeTaxomonies($postType);
				
				if($fetchWithNoTax == false && empty($arrTaxomonies))
					continue;
					
				$arrType = array();
				$arrType["title"] = $title;
				$arrType["taxonomies"] = $arrTaxomonies;
				
				$arrPostTypesOutput[$postType] = $arrType;
			}

			
			return($arrPostTypesOutput);
		}
		
		
		/**
		 *
		 * get array of post types with categories (the taxonomies is between).
		 * get only those taxomonies that have some categories in it.
		 */
		public static function getPostTypesWithCats($arrFilterTypes = null){
			
			$arrPostTypes = self::getPostTypesWithTaxomonies();
			
			$arrOutput = array();
			foreach($arrPostTypes as $name => $arrPostType){
				
				if(array_key_exists($name, $arrFilterTypes) == true)
					continue;
									
				$arrTax = UniteFunctionsUC::getVal($arrPostType, "taxonomies");
				
				
				//collect categories
				$arrCats = array();
				foreach($arrTax as $taxName => $taxTitle){
					
					$cats = self::getCategoriesAssoc($taxName, false, $name);
					
					if(!empty($cats))
					foreach($cats as $catID=>$catTitle){
						
						if($taxName != "category"){
							$catID = $taxName."--".$catID;
							$catTitle = $catTitle." - [$taxTitle]";
						}
						
						$arrCats[$catID] = $catTitle;
					}
				}
				
				$arrPostType = array();
				$arrPostType["name"] = $name;
				$arrPostType["title"] = self::getPostTypeTitle($name);
				$arrPostType["cats"] = $arrCats;
				
				$arrOutput[$name] = $arrPostType;
			}
			
			
			return($arrOutput);
		}
		
		
		/**
		 *
		 * get array of post types with categories (the taxonomies is between).
		 * get only those taxomonies that have some categories in it.
		 */
		public static function getPostTypesWithCatIDs(){
			
			$arrTypes = self::getPostTypesWithCats();
			
			$arrOutput = array();
			
			foreach($arrTypes as $typeName => $arrType){
				
				$output = array();
				$output["name"] = $typeName;
				
				$typeTitle = self::getPostTypeTitle($typeName);
				
				//collect categories
				$arrCatsTotal = array();
				
				foreach($arrType as $arr){
					$cats = UniteFunctionsUC::getVal($arr, "cats");
					$catsIDs = array_keys($cats);
					$arrCatsTotal = array_merge($arrCatsTotal, $catsIDs);
				}
				
				$output["title"] = $typeTitle;
				$output["catids"] = $arrCatsTotal;
				
				$arrOutput[$typeName] = $output;
			}
			
			
			return($arrOutput);
		}
		
		
		
		/**
		 * 
		 * get all the post types including custom ones
		 * the put to top items will be always in top (they must be in the list)
		 */
		public static function getPostTypesAssoc($arrPutToTop = array(), $isPublicOnly = false){
			 
			$arrBuiltIn = array(
			 	"post"=>"post",
			 	"page"=>"page",
			 );
			 
			 $arrCustomTypes = get_post_types(array('_builtin' => false));
			 
			 
			 //top items validation - add only items that in the customtypes list
			 $arrPutToTopUpdated = array();
			 foreach($arrPutToTop as $topItem){
			 	if(in_array($topItem, $arrCustomTypes) == true){
			 		$arrPutToTopUpdated[$topItem] = $topItem;
			 		unset($arrCustomTypes[$topItem]);
			 	}
			 }
			 
			 $arrPostTypes = array_merge($arrPutToTopUpdated,$arrBuiltIn,$arrCustomTypes);
			 
			 //update label
			 foreach($arrPostTypes as $key=>$type){
				$arrPostTypes[$key] = self::getPostTypeTitle($type);
			 }
			 
			 //filter public only types
			 if($isPublicOnly == true)
			 	$arrPostTypes = self::filterPublicOnlyTypes($arrPostTypes);
			 
			 	
			 return($arrPostTypes);
		}
		
		
		/**
		 * get public only types from post types array
		 */
		public static function filterPublicOnlyTypes($arrPostTypes){
			
			if(empty($arrPostTypes))
				return($arrPostTypes);
						
			foreach($arrPostTypes as $type => $typeTitle){
				
				if($type == "post" || $type == "page"){
					continue;
				}
				
				$objType = get_post_type_object($type);
				
				if(empty($objType))
					continue;
				
				if($objType->publicly_queryable == false)
					unset($arrPostTypes[$type]);
			}
			
			return($arrPostTypes);
		}
		
		
		public static function a_______TAXANOMIES_______(){}
		
		
		/**
		 * get term data
		 */
		public static function getTermData($term){
			
			$data = array();
			$data["term_id"] = $term->term_id;
			$data["name"] = $term->name;
			$data["slug"] = $term->slug;
			$data["description"] = $term->description;
			
			$count = "";
			
			if(isset($term->count))
				$count = $term->count;
			
			$data["count"] = $count;

			//get link
			$link = get_term_link($term);
			$data["link"] = $link;
			
			return($data);
		}
		
		/**
		 * convert terms objects to data
		 */
		private static function getTermsObjectsData($arrTerms, $taxonomyName, $currentTermID = null){
			
			$arrTermData = array();
			
			if(empty($arrTerms))
				return(array());
			
			$counter = 0;
			foreach($arrTerms as $term){
								
				$termData = self::getTermData($term);
				
				$current = false;
				if($termData["term_id"] == $currentTermID)
					$current = true;
				
				$termData["iscurrent"] = $current;
				
				$slug = $termData["slug"];
				if(empty($slug))
					$slug = "{$taxonomyName}_{$counter}";
				
				$arrTermData[$slug] = $termData;					
			}
			
			return($arrTermData);
		}
		
		/**
		 * get current term ID
		 */
		public static function getCurrentTermID(){
			
			$term = get_queried_object();
			if(empty($term))
				return(null);
			
			if(!isset($term->term_id))
				return(null);
			
			return($term->term_id);
		}
		
		/**
		 * filter term objects by slugs
		 */
		private static function getTerms_filterBySlugs($arrTermObjects, $arrSlugs){
			
			if(empty($arrTermObjects))
				return($arrTermObjects);

			$arrSlugsAssoc = UniteFunctionsUC::arrayToAssoc($arrSlugs);
			$arrTermsNew = array();
			foreach($arrTermObjects as $term){
								
				if(isset($arrSlugsAssoc[$term->slug]))
					continue;
								
				$arrTermsNew[] = $term;
			}
			
			
			return($arrTermsNew);			
		}
		
		
		/**
		 * get terms
		 */
		public static function getTerms($taxonomy, $orderBy = null, $orderDir = null, $hideEmpty = false, $arrExcludeSlugs = null, $addArgs = null){
			
			//get current cat ID
			$currentTermID = self::getCurrentTermID();
			
			$hideEmpty = UniteFunctionsUC::strToBool($hideEmpty);
			
			$args = array();
			$args["hide_empty"] = $hideEmpty;
			$args["taxonomy"] = $taxonomy;
			$args["count"] = true;
			$args["number"] = 5000;
			
			if(!empty($orderBy)){
				$args["orderby"] = $orderBy;
				
				if(empty($orderDir))
					$orderDir = self::ORDER_DIRECTION_ASC;
				
				$args["order"] = $orderDir;
			}
			
			if(is_array($addArgs))
				$args = $args + $addArgs;
			
			HelperUC::addDebug("Terms Query", $args);
						
			$arrTermsObjects = get_terms($args);
			
			if(!empty($arrExcludeSlugs)){
				HelperUC::addDebug("Terms Before Filter:", $arrTermsObjects);
				HelperUC::addDebug("Exclude by:", $arrExcludeSlugs);
			}
			
			if(!empty($arrExcludeSlugs) && is_array($arrExcludeSlugs))
				$arrTermsObjects = self::getTerms_filterBySlugs($arrTermsObjects, $arrExcludeSlugs);
			
			$arrTerms = self::getTermsObjectsData($arrTermsObjects, $taxonomy, $currentTermID);
			
			return($arrTerms);
		}
		
		/**
		 * get post single taxonomy terms
		 */
		public static function getPostSingleTerms($postID, $taxonomyName){
			
			$arrTerms = wp_get_post_terms($postID, $taxonomyName);
			
			$arrTerms = self::getTermsObjectsData($arrTerms, $taxonomyName);
			
			return($arrTerms);
		}
		
		
		/**
		 * get post taxonomies
		 */
		public static function getPostTerms($post){
			
			if(empty($post))
				return(array());
			
			$postType = $post->post_type;
			$postID = $post->ID;
			
			if(empty($postID))
				return(array());
			
			//option 'objects' also available
			$arrTaxonomies = self::getPostTypeTaxomonies($postType);
			
			if(empty($arrTaxonomies))
				return($array);
			
			$arrDataOutput = array();
			
			foreach($arrTaxonomies as $taxName => $taxTitle){
				
				$arrTerms = wp_get_post_terms($postID, $taxName);
				
				$arrTermsData = self::getTermsObjectsData($arrTerms, $taxName);
				
				$arrDataOutput[$taxName] = $arrTermsData;
			}
			
			
			return($arrDataOutput);
						
		}
		
		/**
		 *
		 * get assoc list of the taxonomies
		 */
		public static function getTaxonomiesAssoc(){
			$arr = get_taxonomies();
			
			unset($arr["post_tag"]);
			unset($arr["nav_menu"]);
			unset($arr["link_category"]);
			unset($arr["post_format"]);
		
			return($arr);
		}
		
		
		
		/**
		 *
		 * get array of all taxonomies with categories.
		 */
		public static function getTaxonomiesWithCats(){
			
			if(!empty(self::$arrTaxCache))
				return(self::$arrTaxCache);
			
			$arrTax = self::getTaxonomiesAssoc();
			
			$arrTaxNew = array();
			foreach($arrTax as $key => $value){
				
				$arrItem = array();
				$arrItem["name"] = $key;
				$arrItem["title"] = $value;
				$arrItem["cats"] = self::getCategoriesAssoc($key);
				$arrTaxNew[$key] = $arrItem;
			}
			
			self::$arrTaxCache = $arrTaxNew;
			
			return($arrTaxNew);
		}
		
		
		public static function a_________CATEGORIES_AND_TAGS___________(){}

		
		/**
		 * check if category not exists and add it, return catID anyway
		 */
		public static function addCategory($catName){
			
			$catID = self::getCatIDByTitle($catName);
			if(!empty($catID))
				return($catID);
			
			$arrCat = array(
			  'cat_name' => $catName
			);
						
			$catID = wp_insert_category($arrCat);			
			if($catID == false)
				UniteFunctionsUC::throwError("category: $catName don't created");
			
			return($catID);
		}
		
		
		/**
		 * 
		 * get the category data
		 */
		public static function getCategoryData($catID){
			$catData = get_category($catID);
			if(empty($catData))
				return($catData);
				
			$catData = (array)$catData;			
			return($catData);
		}
		
		
		
		/**
		 * 
		 * get post categories by postID and taxonomies
		 * the postID can be post object or array too
		 */
		public static function getPostCategories($postID,$arrTax){
			
			if(!is_numeric($postID)){
				$postID = (array)$postID;
				$postID = $postID["ID"];
			}
				
			$arrCats = wp_get_post_terms( $postID, $arrTax);
			$arrCats = UniteFunctionsUC::convertStdClassToArray($arrCats);
			return($arrCats);
		}

		
		/**
		 *
		 * get post categories list assoc - id / title
		 */
		public static function getCategoriesAssoc($taxonomy = "category", $addNotSelected = false, $forPostType = null){
			
			if($taxonomy === null)
				$taxonomy = "category";
			
			$arrCats = array();
			
			if($addNotSelected == true)
				$arrCats["all"] = esc_html__("[All Categories]", "unlimited_elements");
			
			if(strpos($taxonomy,",") !== false){
				$arrTax = explode(",", $taxonomy);
				foreach($arrTax as $tax){
					$cats = self::getCategoriesAssoc($tax);
					$arrCats = array_merge($arrCats,$cats);
				}
		
				return($arrCats);
			}
			
			$args = array("taxonomy"=>$taxonomy);
			$args["hide_empty"] = false;
			$args["number"] = 5000;
			
			$cats = get_categories($args);
			
			foreach($cats as $cat){
									
				$numItems = $cat->count;
				$itemsName = "items";
				if($numItems == 1)
					$itemsName = "item";
		
				$title = $cat->name . " ($numItems $itemsName)";
		
				$id = $cat->cat_ID;
				$arrCats[$id] = $title;
			}
			return($arrCats);
		}
		
		/**
		 *
		 * get categories by id's
		 */
		public static function getCategoriesByIDs($arrIDs,$strTax = null){
		
			if(empty($arrIDs))
				return(array());
		
			if(is_string($arrIDs))
				$strIDs = $arrIDs;
			else
				$strIDs = implode(",", $arrIDs);
		
			$args = array();
			$args["include"] = $strIDs;
		
			if(!empty($strTax)){
				if(is_string($strTax))
					$strTax = explode(",",$strTax);
		
				$args["taxonomy"] = $strTax;
			}
		
			$arrCats = get_categories( $args );
		
			if(!empty($arrCats))
				$arrCats = UniteFunctionsUC::convertStdClassToArray($arrCats);
		
			return($arrCats);
		}
		
		
		/**
		 *
		 * get categories short
		 */
		public static function getCategoriesByIDsShort($arrIDs,$strTax = null){
			$arrCats = self::getCategoriesByIDs($arrIDs,$strTax);
			$arrNew = array();
			foreach($arrCats as $cat){
				$catID = $cat["term_id"];
				$catName = $cat["name"];
				$arrNew[$catID] =  $catName;
			}
		
			return($arrNew);
		}
		
		
		
		
		/**
		 *
		 * get post tags html list
		 */
		public static function getTagsHtmlList($postID,$before="",$sap=",",$after=""){
			
			$tagList = get_the_tag_list($before,",",$after,$postID);
			
			return($tagList);
		}

		
		/**
		 * get category by slug name
		 */
		public static function getCatIDBySlug($slug, $type = "slug"){
			
			$arrCats = get_categories(array("hide_empty"=>false));
			
			foreach($arrCats as $cat){
				$cat = (array)$cat;
				
				switch($type){
					case "slug":
						$catSlug = $cat["slug"];
					break;
					case "title":
						$catSlug = $cat["name"];
					break;
					default:
						UniteFunctionsUC::throwError("Wrong cat name");
					break;
				}
				
				$catID = $cat["term_id"];
				
				if($catSlug == $slug)
					return($catID);
			}
			
			return(null);
		}
		
		/**
		 * get category by title (name)
		 */
		public static function getCatIDByTitle($title){
			
			$catID = self::getCatIDBySlug($title,"title");
			
			return($catID);
		}
		
		public static function a________GENERAL_GETTERS________(){}
		
		
		/**
		 *
		 * get sort by with the names
		 */
		public static function getArrSortBy(){
			
			$arr = array();
			$arr[self::SORTBY_ID] = __("Post ID", "unlimited_elements");
			$arr[self::SORTBY_DATE] = __("Date", "unlimited_elements");
			$arr[self::SORTBY_TITLE] = __("Title", "unlimited_elements");
			$arr[self::SORTBY_SLUC] = __("Slug", "unlimited_elements");
			$arr[self::SORTBY_AUTHOR] = __("Author", "unlimited_elements");
			$arr[self::SORTBY_LAST_MODIFIED] = __("Last Modified", "unlimited_elements");
			$arr[self::SORTBY_COMMENT_COUNT] = __("Number Of Comments", "unlimited_elements");
			$arr[self::SORTBY_RAND] = __("Random", "unlimited_elements");
			$arr[self::SORTBY_NONE] = __("Unsorted", "unlimited_elements");
			$arr[self::SORTBY_MENU_ORDER] = __("Menu Order", "unlimited_elements");
			$arr[self::SORTBY_PARENT] = __("Parent Post", "unlimited_elements");
			$arr[self::SORTBY_META_VALUE] = __("Custom Field Value", "unlimited_elements");
			$arr[self::SORTBY_META_VALUE_NUM] = __("Custom Field Value (numeric)", "unlimited_elements");
			
			return($arr);
		}
		
		
		/**
		 *
		 * get array of sort direction
		 */
		public static function getArrSortDirection(){
			
			$arr = array();
			$arr[self::ORDER_DIRECTION_DESC] = __("Descending", "unlimited_elements");
			$arr[self::ORDER_DIRECTION_ASC] = __("Ascending", "unlimited_elements");
			
			return($arr);
		}
		
		/**
		 * get sort by term
		 */
		public static function getArrTermSortBy(){
			
			$arr = array();
			$arr["name"] = __("Name", "unlimited_elements");
			$arr["slug"] = __("Slug", "unlimited_elements");
			$arr["term_group"] = __("Term Group", "unlimited_elements");
			$arr["term_id"] = __("Term ID", "unlimited_elements");
			$arr["description"] = __("Description", "unlimited_elements");
			$arr["parent"] = __("Parent", "unlimited_elements");
			
			//$arr["count"] = "Count";
			
			return($arr);
		}
		
		private function a_______CUSTOM_FIELDS________(){}
		
		
		/**
		 * get keys of acf fields
		 */
		public static function getAcfFieldsKeys($postID, $objName = "post", $addPrefix = true){
			
			$objAcf = self::getObjAcfIntegrate();
			
			$arrKeys = $objAcf->getAcfFieldsKeys($postID, $objName, $addPrefix);
				
			return($arrKeys);
		}
		
		
		/**
		 * get post custom fields
		 * including acf
		 */
		public static function getPostCustomFields($postID, $addPrefixes = true){
			
			$prefix = null;
			if($addPrefixes == true)
				$prefix = "cf_";
			
			$isAcfActive = UniteCreatorAcfIntegrate::isAcfActive();
			
			
			//get acf
			if($isAcfActive){
				$objAcf = self::getObjAcfIntegrate();
				$arrCustomFields = $objAcf->getAcfFields($postID);
			}else{		//without acf - get regular custom fields
								
				$arrCustomFields = null;
				
				$isPodsExists = UniteCreatorPodsIntegrate::isPodsExists();
				if($isPodsExists){
					$objPods = UniteCreatorPodsIntegrate::getObjPodsIntegrate();
					$arrCustomFields = $objPods->getPodsFields($postID);
				}

				//handle toolset
				$isToolsetActive = UniteCreatorToolsetIntegrate::isToolsetExists();
				
				if($isToolsetActive == true && empty($arrCustomFields)){
					$objToolset = new UniteCreatorToolsetIntegrate();
					
					$arrCustomFields = $objToolset->getPostFieldsWidthData($postID);
				}
				
				if(empty($arrCustomFields))
					$arrCustomFields = self::getPostMeta($postID, false, $prefix);
				
			}
			
			if(empty($arrCustomFields)){
				$arrCustomFields = array();
				return($arrCustomFields);
			}
			
			
			return($arrCustomFields);
		}
		
		
		/**
		 * get post meta data
		 */
		public static function getPostMeta($postID, $getSystemVars = true, $prefix = null){
			
			$arrMeta = get_post_meta($postID);
			$arrMetaOutput = array();
			
			foreach($arrMeta as $key=>$item){

				//filter by key
				if($getSystemVars == false){
					$firstSign = $key[0];
					
					if($firstSign == "_")
						continue;
				}
				
				if(!empty($prefix))
					$key = $prefix.$key;
				
				if(is_array($item) && count($item) == 1)
					$item = $item[0];
					
				$arrMetaOutput[$key] = $item;
			}
			
			
			return($arrMetaOutput);
		}
		
		/**
		 * get pods meta keys
		 */
		public static function getPostMetaKeys_PODS($postID){
			
			$isPodsExists = UniteCreatorPodsIntegrate::isPodsExists();
			
			if($isPodsExists == false)
				return(array());
			
			$objPods = UniteCreatorPodsIntegrate::getObjPodsIntegrate();
			$arrCustomFields = $objPods->getPodsFields($postID);
			
			if(empty($arrCustomFields))
				return(array());
				
			$arrMetaKeys = array_keys($arrCustomFields);
			
			return($arrMetaKeys);
		}
		
		/**
		 * get post meta keys
		 */
		public static function getPostMetaKeys_TOOLSET($postID){
			
			$isToolsetExists = UniteCreatorToolsetIntegrate::isToolsetExists();			
			if($isToolsetExists == false)
				return(array());
			
			$objToolset = new UniteCreatorToolsetIntegrate();
			$arrFieldsKeys = $objToolset->getPostFieldsKeys($postID);
			if(empty($arrFieldsKeys))
				return($arrFieldsKeys);
			
			return($arrFieldsKeys);
		}
		
		/**
		 * get post meta data
		 */
		public static function getPostMetaKeys($postID, $prefix = null){
			
			$postMeta = get_post_meta($postID);
			
			if(empty($postMeta))
				return(array());
			
			$arrMetaKeys = array_keys($postMeta);
			
			$arrKeysOutput = array();
			foreach($arrMetaKeys as $key){
				
				$firstSign = $key[0];
				
				if($firstSign == "_")
					continue;
				
				if(!empty($prefix))
					$key = $prefix.$key;
				
				$arrKeysOutput[] = $key;				
			}
			
			
			return($arrKeysOutput);
		}
		
		
		
		public static function a__________POST_GETTERS__________(){}
		
		
		/**
		 *
		 * get single post
		 */
		public static function getPost($postID, $addAttachmentImage = false, $getMeta = false){
			
			$post = get_post($postID);
			if(empty($post))
				UniteFunctionsUC::throwError("Post with id: $postID not found");
		
			$arrPost = $post->to_array();
			
			if($addAttachmentImage == true){
				$arrImage = self::getPostAttachmentImage($postID);
				if(!empty($arrImage))
					$arrPost["image"] = $arrImage;
			}
		
			if($getMeta == true)
				$arrPost["meta"] = self::getPostMeta($postID);
		
			return($arrPost);
		}
		
		/**
		 * get post by name
		 */
		public static function getPostByName($name, $postType = null){
			
			if(!empty($postType)){
				$query = array(
					'name'=>$name,
					'post_type'=>$postType
				);			
				
				$arrPosts = get_posts($query);
				$post = $arrPosts[0];
				return($post);
			}
			
			//get only by name
			$postID = self::getPostIDByPostName($name);
			if(empty($postID))
				return(null);
			
			$post = get_post($postID);
						
			return($post);
		}
		
		
		/**
		 * get post children
		 */
		public static function getPostChildren($post){
			
			if(empty($post))
				return(array());
			
			$args = array();
			$args["post_parent"] = $post->ID;
			$args["post_type"] = $post->post_type;
						
			$arrPosts = get_posts($args);
						
			return($arrPosts);
		}
		
		
		/**
		 * get post id by post name
		 */
		public static function getPostIDByPostName($postName){
			
			$tablePosts = UniteProviderFunctionsUC::$tablePosts;
			
			$db = self::getDB();
			$response = $db->fetch($tablePosts, array("post_name"=>$postName));
			
			if(empty($response))
				return(null);
			
			$postID = $response[0]["ID"];
			
			return($postID);
		}
		
		
		/**
		 * get post id by name, using DB
		 */
		public static function isPostNameExists($postName){
			
			$tablePosts = UniteProviderFunctionsUC::$tablePosts;
			
			$db = self::getDB();
			$response = $db->fetch($tablePosts, array("post_name"=>$postName));
			
			$isExists = !empty($response);
			
			return($isExists);
		}
		
		/**
		 *
		 * get posts post type
		 */
		public static function getPostsByType($postType, $sortBy = self::SORTBY_TITLE, $addParams = array(),$returnPure = false){
			
			if(empty($postType))
				$postType = "any";
			
			$query = array(
					'post_type'=>$postType,
					'orderby'=>$sortBy
			);
			
			if($sortBy == self::SORTBY_MENU_ORDER)
				$query["order"] = self::ORDER_DIRECTION_ASC;
			
			$query["posts_per_page"] = 2000;	//no limit
			
			if(!empty($addParams))
				$query = array_merge($query, $addParams);	
			
				
			$arrPosts = get_posts($query);
			
			if($returnPure == true)
				return($arrPosts);
				
			foreach($arrPosts as $key=>$post){
				
				if(method_exists($post, "to_array"))
					$arrPost = $post->to_array();
				else
					$arrPost = (array)$post;
				
				$arrPosts[$key] = $arrPost;
			}
			
			return($arrPosts);
		}
		
		
		/**
		 * get taxanomy query
		 */
		public static function getPosts_getTaxQuery($category, $categoryRelation = null){
			
			if(empty($category))
				return(null);
			
			if($category == "all")
				return(null);
			
			if(is_array($category))
				$arrCategories = $category;
			else
				$arrCategories = explode(",", $category);
			
			$arrQuery = array();
			
			foreach($arrCategories as $cat){
				
				//check for empty category - mean all categories
				if($cat == "all" || empty($cat))
					continue;
				
				//set taxanomy name
				$taxName = "category";
				$catID = $cat;
				
				if(is_numeric($cat) == false){
					
					$arrTax = explode("--", $cat);
					if(count($arrTax) == 2){
						$taxName = $arrTax[0];
						$catID = $arrTax[1];
					}
				}
				
				//add the search item
				
				$arrSearchItem = array();
				$arrSearchItem["taxonomy"] = $taxName;
				$arrSearchItem["field"] = "id";
				$arrSearchItem["terms"] = $catID;
				$arrSearchItem["include_children"] = false;
				
				$arrQuery[] = $arrSearchItem;
			}

			if(empty($arrQuery))
				return(null);
			
			if(count($arrQuery) == 1)
				return($arrQuery);
				
			//check and add relation
			if($categoryRelation === "OR")
				$arrQuery["relation"] = "OR";
			
			return($arrQuery);			
		}
		
		/**
		 * update order by
		 */
		public static function updatePostArgsOrderBy($args, $orderBy){
			
			$arrOrderKeys = self::getArrSortBy();
			
			if(isset($arrOrderKeys[$orderBy])){
				$args["orderby"] = $orderBy;
				
				return($args);
			}
			
			switch($orderBy){
				case "price":
					$args["orderby"] = "meta_value_num";
					$args["meta_key"] = "_price";
				break;
			}
			
			return($args);
		}
		
		
		/**
		 * get posts arguments by filters
		 */
		public static function getPostsArgs($filters){
			
			$args = array();
			
			$category = UniteFunctionsUC::getVal($filters, "category");
			$categoryRelation = UniteFunctionsUC::getVal($filters, "category_relation");
			
			$arrTax = self::getPosts_getTaxQuery($category, $categoryRelation);
			
			$search = UniteFunctionsUC::getVal($filters, "search");
			if(!empty($search))
				$args["s"] = $search;
			
			$postType = UniteFunctionsUC::getVal($filters, "posttype");
				
			if(is_array($postType) && count($postType) == 1)
				$postType = $postType[0];
			
			$args["post_type"] = $postType;
			
			if(!empty($arrTax))
				$args["tax_query"] = $arrTax;
			
			
			//process orderby
			$orderby = UniteFunctionsUC::getVal($filters, "orderby");
				
			$args["orderby"] = $orderby;
			
			if($orderby == self::SORTBY_META_VALUE || $orderby == self::SORTBY_META_VALUE_NUM)
				$args["meta_key"] = UniteFunctionsUC::getVal($filters, "meta_key");
			
			$args["order"] = UniteFunctionsUC::getVal($filters, "orderdir");
			$args["posts_per_page"] = UniteFunctionsUC::getVal($filters, "limit");
			
			$postStatus = UniteFunctionsUC::getVal($filters, "status");
			if(!empty($postStatus))
				$args["post_status"] = $postStatus;

			//get exlude posts
			$excludeCurrentPost = UniteFunctionsUC::getVal($filters, "exclude_current_post");
			$excludeCurrentPost = UniteFunctionsUC::strToBool($excludeCurrentPost);
			
			if($excludeCurrentPost == true){
				$postID = get_the_ID();
				if(!empty($postID)){
					$args["post__not_in"] = array($postID);
				}
			}
			
			
			return($args);
		}
		
		/**
		 * get posts post type
		 */
		public static function getPosts($filters){

			$args = self::getPostsArgs($filters);
			
			$arrPosts = get_posts($args);
			
			if(empty($arrPosts))
				$arrPosts = array();
			
			
			
			return($arrPosts);
		}

		
		
		/**
		 * get page template
		 */
		public static function getPostPageTemplate($post){
			
			if(empty($post))
				return("");
			
			$arrPost = $post->to_array();
			$pageTemplate = UniteFunctionsUC::getVal($arrPost, "page_template");
			
			return($pageTemplate);
		}
		
		/**
		 * get edit post url
		 */
		public static function getUrlEditPost($postID, $encodeForJS = false){
			
			$context = "display";
			if($encodeForJS == false)
				$context = "normal";
			
			$urlEditPost = get_edit_post_link( $postID, $context); 
			
			return($urlEditPost);
		}
		
		
		/**
		 * check if current user can edit post
		 */
		public static function isUserCanEditPost($postID){
			
			$post = get_post($postID);
			
			if(empty($post))
				return(false);

			$postStatus = $post->post_status;
			if($postStatus == "trash")
				return(false);
			
			$postType = $post->post_type;
			
			$objPostType = get_post_type_object($postType);
			if(empty($objPostType))
				return(false);
			
			if(isset($objPostType->cap->edit_post) == false ){
				return false;
			}
			
			$editCap = $objPostType->cap->edit_post;
			
			$isCanEdit = current_user_can( $editCap, $postID );
			if($isCanEdit == false)
				return(false);
			
			$postsPageID = get_option( 'page_for_posts' );
			if($postsPageID === $postID)
				return(false);

			
			return(true);
		}
		
		public static function a__________POST_ACTIONS_________(){}
		
		
		
		/**
		 * update post type
		 */
		public static function updatePost($postID, $arrUpdate){
			
			if(empty($arrUpdate))
				UniteFunctionsUC::throwError("nothing to update post");
				
			$arrUpdate["ID"] = $postID;
			
			$wpError = wp_update_post( $arrUpdate ,true);
			
			if (is_wp_error($wpError)) {
    			UniteFunctionsUC::throwError("Error updating post: $postID");
			}
			
		}

		
		/**
		 * update post ordering
		 */
		public static function updatePostOrdering($postID, $ordering){
			
			$arrUpdate = array(
			      'menu_order' => $ordering,
			 );		
			
			self::updatePost($postID, $arrUpdate);
		}
		
		/**
		 * update post content
		 */
		public static function updatePostContent($postID, $content){
			
			$arrUpdate = array("post_content"=>$content);
			self::updatePost($postID, $arrUpdate);
		}
		
		/**
		 * update post page template attribute in meta
		 */
		public static function updatePageTemplateAttribute($pageID, $pageTemplate){
			
			update_post_meta($pageID, "_wp_page_template", $pageTemplate);
		}
		
		
		/**
		 * insert post
		 * params: [cat_slug, content]
		 */
		public static function insertPost($title, $alias, $params = array()){
			
			$catSlug = UniteFunctionsUC::getVal($params, "cat_slug");
			$content = UniteFunctionsUC::getVal($params, "content");
			$isPage = UniteFunctionsUC::getVal($params, "ispage");
			$isPage = UniteFunctionsUC::strToBool($isPage);
			
			$catID = null;
			if(!empty($catSlug)){
				$catID = self::getCatIDBySlug($catSlug);
				if(empty($catID))
					UniteFunctionsUC::throwError("Category id not found by slug: $slug");
			}
			
			$isPostExists = self::isPostNameExists($alias);
			
			if($isPostExists == true)
				UniteFunctionsUC::throwError("Post with name: <b> {$alias} </b> already exists");
			
			
			$arguments = array();
			$arguments["post_title"] = $title;
			$arguments["post_name"] = $alias;
			$arguments["post_status"] = "publish";
			
			if(!empty($content))
				$arguments["post_content"] = $content;
			
			if(!empty($catID))
				$arguments["post_category"] = array($catID);
			
			if($isPage == true)
				$arguments["post_type"] = "page";
			
			$postType = UniteFunctionsUC::getVal($params, "post_type");
			if(!empty($postType))
				$arguments["post_type"] = $postType;
			
			$newPostID = wp_insert_post($arguments, true);
			
			if(is_wp_error($newPostID)){
				$errorMessage = $newPostID->get_error_message();
				UniteFunctionsUC::throwError($errorMessage);
			}
			
			
			return($newPostID);
		}
		
		
		/**
		 * insert new page
		 */
		public static function insertPage($title, $alias, $params = array()){
			
			$params["ispage"] = true;
			
			$pageID = self::insertPost($title, $alias, $params);
			
			return($pageID);
		}
		
		
		/**
		 * delete all post metadata
		 */
		public static function deletePostMetadata($postID){
			
			$postID = (int)$postID;
			
			$tablePostMeta = UniteProviderFunctionsUC::$tablePostMeta;
			
			$db = self::getDB();
			$db->delete($tablePostMeta, "post_id=$postID");
		}
		
		/**
		 * duplicate post
		 */
		public static function duplicatePost($postID, $newTitle = null){
			
			$post = get_post($postID);
			if(empty($post))
				UniteFunctionsUC::throwError("Post now found");
			
			$current_user = wp_get_current_user();
			$new_post_author = $current_user->ID;			
			
			$postTitle = $post->post_title;
			if(!empty($newTitle))
				$postTitle = $newTitle;
			
			$args = array(
				'comment_status' => $post->comment_status,
				'ping_status'    => $post->ping_status,
				'post_author'    => $new_post_author,
				'post_content'   => $post->post_content,
				'post_excerpt'   => $post->post_excerpt,
				'post_name'      => $post->post_name,
				'post_parent'    => $post->post_parent,
				'post_password'  => $post->post_password,
				'post_status'    => $post->post_status,
				'post_title'     => $postTitle,
				'post_type'      => $post->post_type,
				'to_ping'        => $post->to_ping,
				'menu_order'     => $post->menu_order
			);				
			
			
			$newPostID = wp_insert_post( $args );
			
			if(empty($newPostID))
				UniteFunctionsUC::throwError("Can't duplicate post: $postID");
			
			
			//set all taxanomies to the new post (category, tags)
			$taxonomies = get_object_taxonomies($post->post_type);
			foreach ($taxonomies as $taxonomy) {
				$post_terms = wp_get_object_terms($postID, $taxonomy, array('fields' => 'slugs'));
				wp_set_object_terms($newPostID, $post_terms, $taxonomy, false);
			}

			//duplicate meta
			global $wpdb;
			
			//duplicate all post meta just in two SQL queries
			$post_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$postID");
			if (count($post_meta_infos)!=0) {
				$sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
				foreach ($post_meta_infos as $meta_info) {
					$meta_key = $meta_info->meta_key;
					if( $meta_key == '_wp_old_slug' ) continue;
					$meta_value = addslashes($meta_info->meta_value);
					$sql_query_sel[]= "SELECT $newPostID, '$meta_key', '$meta_value'";
				}
				$sql_query.= implode(" UNION ALL ", $sql_query_sel);
				$wpdb->query($sql_query);
			}
			
			
			return($newPostID);
		}
		
		/**
		 * delete multiple posts
		 */
		public static function deleteMultiplePosts($arrPostIDs){
			
			if(empty($arrPostIDs))
				return(false);
			
			if(is_array($arrPostIDs) == false)
				return(false);
			
			foreach($arrPostIDs as $postID)
				self::deletePost($postID);
			
		}
		
		
		/**
		 * delete post
		 */
		public static function deletePost($postID){
			
			wp_delete_post($postID, true);
			
		}
		
		public static function a__________ATTACHMENT________(){}
		
		/**
		 * get post thumb id from post id
		 */
		public static function getFeaturedImageID($postID){
			$thumbID = get_post_thumbnail_id( $postID );
			return($thumbID);
		}
		
		
		/**
		 *
		 * get attachment image url
		 */
		public static function getUrlAttachmentImage($thumbID, $size = self::THUMB_FULL){
			
			$arrImage = wp_get_attachment_image_src($thumbID, $size);
			if(empty($arrImage))
				return(false);
			
			$url = UniteFunctionsUC::getVal($arrImage, 0);
			return($url);
		}
		
		
		
		
		/**
		 * get attachment data
		 */
		public static function getAttachmentData($thumbID){
			
			if(is_numeric($thumbID) == false)
				return(null);
			
			$post = get_post($thumbID);
			if(empty($post))
				return(null);
			
			$title = wp_get_attachment_caption($thumbID);
			
			$item = array();
			$item["image_id"] = $post->ID;
			$item["image"] = $post->guid;
			
			if(empty($title))
				$title = $post->post_title;
			
			$urlThumb = self::getUrlAttachmentImage($thumbID,self::THUMB_MEDIUM);
			if(empty($urlThumb))
				$urlThumb = $post->guid;
			
			$item["thumb"] = $urlThumb;
			
			$item["title"] = $title;
			$item["description"] = $post->post_content;
			
			return($item);
		}
		
		
		/**
		 * get thumbnail sizes array
		 * mode: null, "small_only", "big_only"
		 */
		public static function getArrThumbSizes($mode = null){
			global $_wp_additional_image_sizes;
			
			$arrWPSizes = get_intermediate_image_sizes();
		
			$arrSizes = array();
		
			if($mode != "big_only"){
				$arrSizes[self::THUMB_SMALL] = "Thumbnail (150x150)";
				$arrSizes[self::THUMB_MEDIUM] = "Medium (max width 300)";
			}
		
			if($mode == "small_only")
				return($arrSizes);
		
			foreach($arrWPSizes as $size){
				$title = ucfirst($size);
				switch($size){
					case self::THUMB_LARGE:
					case self::THUMB_MEDIUM:
					case self::THUMB_FULL:
					case self::THUMB_SMALL:
						continue(2);
						break;
					case "ug_big":
						$title = esc_html__("Big", "unlimited_elements");
						break;
				}
		
				$arrSize = UniteFunctionsUC::getVal($_wp_additional_image_sizes, $size);
				$maxWidth = UniteFunctionsUC::getVal($arrSize, "width");
		
				if(!empty($maxWidth))
					$title .= " (max width $maxWidth)";
		
				$arrSizes[$size] = $title;
			}
		
			$arrSizes[self::THUMB_LARGE] = esc_html__("Large (max width 1024)", "unlimited_elements");
			$arrSizes[self::THUMB_FULL] = esc_html__("Full", "unlimited_elements");
		
			return($arrSizes);
		}
		
		
		/**
		 * Get an attachment ID given a URL.
		*
		* @param string $url
		*
		* @return int Attachment ID on success, 0 on failure
		*/
		public static function getAttachmentIDFromImageUrl( $url ) {
		
			$attachment_id = 0;
		
			$dir = wp_upload_dir();
		
			if ( false !== strpos( $url, $dir['baseurl'] . '/' ) ) { // Is URL in uploads directory?
		
				$file = basename( $url );
		
				$query_args = array(
						'post_type'   => 'attachment',
						'post_status' => 'inherit',
						'fields'      => 'ids',
						'meta_query'  => array(
								array(
										'value'   => $file,
										'compare' => 'LIKE',
										'key'     => '_wp_attachment_metadata',
								),
						)
				);
				
				$query = new WP_Query( $query_args );
		
				if ( $query->have_posts() ) {
		
					foreach ( $query->posts as $post_id ) {
		
						$meta = wp_get_attachment_metadata( $post_id );
		
						$original_file       = basename( $meta['file'] );
						$cropped_image_files = wp_list_pluck( $meta['sizes'], 'file' );
		
						if ( $original_file === $file || in_array( $file, $cropped_image_files ) ) {
							$attachment_id = $post_id;
							break;
						}
		
					}
		
				}
		
			}
		
			return $attachment_id;
		}		
		
		/**
		 * get attachment post title
		 */
		public static function getAttachmentPostTitle($post){
			
			if(empty($post))
				return("");
			
			$post = (array)$post;
						
			$title = UniteFunctionsUC::getVal($post, "post_title");
			$filename = UniteFunctionsUC::getVal($post, "guid");
			
			if(empty($title))
				$title = $filename;
			
			$info = pathinfo($title);
			$name = UniteFunctionsUC::getVal($info, "filename");
			
			if(!empty($name))
				$title = $name;
			
			return($title);
			
		}
		
		/**
		 * get attachment post alt
		 */
		public static function getAttachmentPostAlt($postID){
			
			$alt = get_post_meta($postID, '_wp_attachment_image_alt', true);
			
			return($alt);
		}
		
		public static function a___________USER_DATA__________(){}
		
		/**
		 * get keys of user meta
		 */
		public static function getUserMetaKeys(){
			
			$arrKeys = array(
				"first_name",
				"last_name",
				"description",
				
				"billing_first_name",
				"billing_last_name",
				"billing_company",
				"billing_address_1",
				"billing_address_2",
				"billing_city",
				"billing_postcode",
				"billing_country",
				"billing_state",
				"billing_phone",
				"billing_email",
				"billing_first_name",
				"billing_last_name",
				
				"shipping_company",
				"shipping_address_1",
				"shipping_address_2",
				"shipping_city",
				"shipping_postcode",
				"shipping_country",
				"shipping_state",
				"shipping_phone",
				"shipping_email",
			);
			
			return($arrKeys);
		}
		
		
		/**
		 * get user avatar keys
		 */
		public static function getUserAvatarKeys(){
			
			$arrKeys = array(
				"avatar_found",
				"avatar_url",
				"avatar_size"
			);
			
			return($arrKeys);
		}
		
		
		/**
		 * get user meta
		 */
		public static function getUserMeta($userID){
			
			$arrMeta = get_user_meta($userID,'',true);
						
			if(empty($arrMeta))
				return(null);
			
			$arrKeys = self::getUserMetaKeys();
			
			$arrOutput = array();
			foreach($arrKeys as $key){
				
				$metaValue = UniteFunctionsUC::getVal($arrMeta, $key);
				
				if(is_array($metaValue))
					$metaValue = $metaValue[0];
				
				$arrOutput[$key] = $metaValue;
			}
			
			return($arrOutput);
		}
		
		
		/**
		 * get user avatar data
		 */
		public static function getUserAvatarData($userID){
						
			$arrAvatar = get_avatar_data($userID);
			
			$hasAvatar = UniteFunctionsUC::getVal($arrAvatar, "found_avatar");
			$size = UniteFunctionsUC::getVal($arrAvatar, "size");
			$url = UniteFunctionsUC::getVal($arrAvatar, "url");
			
			$arrOutput = array();
			$arrOutput["avatar_found"] = $hasAvatar;
			$arrOutput["avatar_url"] = $url;
			$arrOutput["avatar_size"] = $size;
			
			return($arrOutput);
		}
		
		
		/**
		 * get user data by object
		 */
		public static function getUserData($objUser, $getMeta = false, $getAvatar = false){
			
			$userID = $objUser->ID;
			
			$userData = $objUser->data;
						
			$userData = UniteFunctionsUC::convertStdClassToArray($userData);
			
			$arrData = array();
			$arrData["id"] = UniteFunctionsUC::getVal($userData, "ID");
			
			$username = UniteFunctionsUC::getVal($userData, "user_nicename");
			
			$arrData["username"] = $username;
			
			$name = UniteFunctionsUC::getVal($userData, "display_name");
			if(empty($name))
				$name = $username;
			
			if(empty($name))
				$name = UniteFunctionsUC::getVal($userData, "user_login");
				
			$arrData["name"] = $name;
			
			$arrData["email"] = UniteFunctionsUC::getVal($userData, "user_email");

			if($getAvatar == true){
			
				$arrAvatar = self::getUserAvatarData($userID);
				if(!empty($arrAvatar))
					$arrData = $arrData + $arrAvatar;
			}
			
			//add role
			$arrRoles = $objUser->roles;
			
			$role = "";
			if(!empty($arrRoles))
				$role = implode(",",$arrRoles);
			
			$arrData["role"] = $role;
			
			$urlWebsite = UniteFunctionsUC::getVal($userData, "user_url");
			$arrData["website"] = $urlWebsite;
			
			//add meta
			if($getMeta == true){
				
				$arrMeta = self::getUserMeta($userID);
				if(!empty($arrMeta))
					$arrData = $arrData+$arrMeta;
			}
			
			return($arrData);
		}
		
		
		/**
		 * get user data by id
		 * if user not found, return empty data
		 */
		public static function getUserDataById($userID){
			
			$objUser = get_user_by("id", $userID);
			
			//if emtpy user - return empty
			if(empty($objUser)){
				
				$arrEmpty = array();
				$arrEmpty["id"] = "";
				$arrEmpty["name"] = "";
				$arrEmpty["email"] = "";
				
				return($arrEmpty);
			}
			
			$arrData = self::getUserData($objUser);

			
			return($arrData);
		}
		
		/**
		 * get roles as name/value array
		 */
		public static function getRolesShort($addAll = false){
			
			$objRoles = wp_roles();
						
			$arrShort = $objRoles->role_names;
			
			if($addAll == true){
				$arrAll["__all__"] = __("[All Roles]","unlimited_elements");
				$arrShort = $arrAll + $arrShort;
			}
			
			return($arrShort);
		}
		
		/**
		 * get menus list short - id / title
		 */
		public static function getMenusListShort(){
			
			$arrShort = array();
			
			$arrMenus = get_terms("nav_menu");
			
			if(empty($arrMenus))
				return(array());
				
			foreach($arrMenus as $menu){
				
				$menuID = $menu->term_id;
				$name = $menu->name;
				
				$arrShort[$menuID] = $name;				
			}
			
			return($arrShort);
		}
		
		
		public static function a___________OTHER_FUNCTIONS__________(){}
		
		
		/**
		 * check if archive location
		 */
		public static function isArchiveLocation(){
			
			if(( is_archive() || is_tax() || is_home() || is_search() ))
				return(true);
				
			if(class_exists("UniteCreatorElementorIntegrate")){
				$templateType = UniteCreatorElementorIntegrate::getCurrentTemplateType();
				if($templateType == "archive")
					return(true);
			}
			
			return(false);
		}
		
		
		/**
		 * get max menu order
		 */
		public static function getMaxMenuOrder($postType, $parentID = null){
			
			$tablePosts = UniteProviderFunctionsUC::$tablePosts;
			
			$db = self::getDB();
						
			$query = "select MAX(menu_order) as maxorder from {$tablePosts} where post_type='$postType'";
			
			if(!empty($parentID)){
				$parentID = (int)$parentID;
				$query .= " and post_parent={$parentID}";
			}
			
			$rows = $db->fetchSql($query);
		
			$maxOrder = 0;
			if(count($rows)>0)
				$maxOrder = $rows[0]["maxorder"];
		
			if(!is_numeric($maxOrder))
				$maxOrder = 0;
			
			return($maxOrder);
		}
		
		
		/**
		 *
		 * get wp-content path
		 */
		public static function getPathUploads(){
			
			if(is_multisite()){
				if(!defined("BLOGUPLOADDIR")){
					$pathBase = self::getPathBase();
					$pathContent = $pathBase."wp-content/uploads/";
				}else
					$pathContent = BLOGUPLOADDIR;
			}else{
				$pathContent = WP_CONTENT_DIR;
				if(!empty($pathContent)){
					$pathContent .= "/";
				}
				else{
					$pathBase = self::getPathBase();
					$pathContent = $pathBase."wp-content/uploads/";
				}
			}
		
			return($pathContent);
		}
		
		
		
		
		
		/**
		 *
		 * simple enqueue script
		 */
		public static function addWPScript($scriptName){
			wp_enqueue_script($scriptName);
		}
		
		/**
		 *
		 * simple enqueue style
		 */
		public static function addWPStyle($styleName){
			wp_enqueue_style($styleName);
		}
		
		
		/**
		 *
		 * check if some db table exists
		 */
		public static function isDBTableExists($tableName){
			global $wpdb;
		
			if(empty($tableName))
				UniteFunctionsUC::throwError("Empty table name!!!");
		
			$sql = "show tables like '$tableName'";
		
			$table = $wpdb->get_var($sql);
		
			if($table == $tableName)
				return(true);
		
			return(false);
		}
		
		/**
		 *
		 * validate permission that the user is admin, and can manage options.
		 */
		public static function isAdminPermissions(){
		
			if( is_admin() &&  current_user_can("manage_options") )
				return(true);
		
			return(false);
		}
		
		
		/**
		 * add shortcode
		 */
		public static function addShortcode($shortcode, $function){
		
			add_shortcode($shortcode, $function);
		
		}
		
		/**
		 *
		 * add all js and css needed for media upload
		 */
		public static function addMediaUploadIncludes(){
		
			self::addWPScript("thickbox");
			self::addWPStyle("thickbox");
			self::addWPScript("media-upload");
		
		}
		
		
		
		
		/**
		 * check if post exists by title
		 */
		public static function isPostExistsByTitle($title, $postType="page"){
			
			$post = get_page_by_title( $title, ARRAY_A, $postType );
			
			return !empty($post);
		}
		
		
		
		
		/**
		 * tells if the page is posts of pages page
		 */
		public static function isAdminPostsPage(){
			
			$screen = get_current_screen();
			$screenID = $screen->base;
			if(empty($screenID))
				$screenID = $screen->id;
			
			
			if($screenID != "page" && $screenID != "post")
				return(false);
			
			
			return(true);
		}
		
		
		/**
		 *
		 * register widget (must be class)
		 */
		public static function registerWidget($widgetName){
			add_action('widgets_init', create_function('', 'return register_widget("'.$widgetName.'");'));
		}
		
		
		/**
		 * get admin title
		 */
		public static function getAdminTitle($customTitle){
			
			global $title;
			
			if(!empty($customTitle))
				$title = $customTitle;
			else
				get_admin_page_title();
			
			$title = esc_html( strip_tags( $title ) );
			
			if ( is_network_admin() ) {
				/* translators: Network admin screen title. 1: Network name */
				$admin_title = sprintf( __( 'Network Admin: %s' ), esc_html( get_network()->site_name ) );
			} elseif ( is_user_admin() ) {
				/* translators: User dashboard screen title. 1: Network name */
				$admin_title = sprintf( __( 'User Dashboard: %s' ), esc_html( get_network()->site_name ) );
			} else {
				$admin_title = get_bloginfo( 'name' );
			}
			
			if ( $admin_title == $title ) {
				/* translators: Admin screen title. 1: Admin screen name */
				$admin_title = sprintf( __( '%1$s &#8212; WordPress' ), $title );
			} else {
				/* translators: Admin screen title. 1: Admin screen name, 2: Network or site name */
				$admin_title = sprintf( __( '%1$s &lsaquo; %2$s &#8212; WordPress' ), $title, $admin_title );
			}
			
			return($admin_title);
		}
		
	/**
	 * get action functions of some tag
	 */
	public static function getActionFunctionsKeys($tag){
		
		global $wp_filter;
		if(isset($wp_filter[$tag]) == false)
			return(array());
		
		$objFilter = $wp_filter[$tag];
		
		$arrFunctions = array();
		$arrCallbacks = $objFilter->callbacks;
		if(empty($arrCallbacks))
			return(array());
		
		foreach($arrCallbacks as $priority=>$callbacks){
			$arrKeys = array_keys($callbacks);
			
			foreach($arrKeys as $key){
				$arrFunctions[$key]	= true;
			}
			
		}
		
		return($arrFunctions);
	}
	
	/**
	 * clear filters from functions
	 */
	public static function clearFiltersFromFunctions($tag, $arrFunctionsAssoc){
		global $wp_filter;
		if(isset($wp_filter[$tag]) == false)
			return(false);
		
		if(empty($arrFunctionsAssoc))
			return(false);
			
		$objFilter = $wp_filter[$tag];
		
		$arrFunctions = array();
		$arrCallbacks = $objFilter->callbacks;
		if(empty($arrCallbacks))
			return(array());
		
		foreach($arrCallbacks as $priority=>$callbacks){
			$arrKeys = array_keys($callbacks);
			
			foreach($arrKeys as $key){
				if(isset($arrFunctionsAssoc[$key]))				
					unset($wp_filter[$tag]->callbacks[$priority][$key]);
			}
			
		}
			
	}
	
	/**
	 * get blog url
	 */
	public static function getUrlBlog(){
		
		//home page:
		
		$showOnFront = get_option( 'show_on_front' );
		if($showOnFront != "page"){
			$urlBlog = home_url();
			return($urlBlog);
		}
		
		//page is missing:
		
		$pageForPosts = get_option( 'page_for_posts' );
		if(empty($pageForPosts)){
			$urlBlog = home_url( '/?post_type=post' );
			return($urlBlog);
		}
			
		//some page:
		$urlBlog = self::getPermalink( $pageForPosts );
		
		return($urlBlog);  
	}
	
	/**
	 * get current page url
	 */
	public static function getUrlCurrentPage(){
		
		global $wp;
		$urlPage = home_url($wp->request);
		
		return($urlPage);
	}
	
	/**
	 * get permalist with check of https
	 */
	public static function getPermalink($post){
		
		$url = get_permalink($post);
		if(GlobalsUC::$is_ssl == true)
			$url = UniteFunctionsUC::urlToSsl($url);
		
		return($url);
	}
	
	
	/**
	 * tell wp plugins do not cache the page
	 */
	public static function preventCachingPage(){
		
		$arrNotCacheTags = array("DONOTCACHEPAGE","DONOTCACHEDB","DONOTMINIFY","DONOTCDN");
		
		foreach($arrNotCacheTags as $tag){
			if(defined( $tag ))
				continue;
				
			define($tag, true);			
		}
		
		nocache_headers();
	}
		
	
	
}	//end of the class
	
	//init the static vars
	UniteFunctionsWPUC::initStaticVars();
	
?>