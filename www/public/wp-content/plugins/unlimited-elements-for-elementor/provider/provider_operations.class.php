<?php
/**
 * @package Unlimited Elements
 * @author UniteCMS.net
 * @copyright (C) 2012 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ELEMENTS_INC') or die('Restricted access');


class ProviderOperationsUC extends UCOperations{

	
	/**
	 * get search text from data
	 */
	private function getSearchFromData($data){
		
		$type = UniteFunctionsUC::getVal($data, "_type");
		if($type != "query")
			return(null);
		
		$searchTerm = UniteFunctionsUC::getVal($data, "q");
		
		return($searchTerm);
	}
	
	
	/**
	 * get post list for select2
	 */
	public function getPostListForSelectFromData($data, $addNotSelected = false){
				
		$search = $this->getSearchFromData($data);
				
		$arrTypesAssoc = UniteFunctionsWPUC::getPostTypesAssoc(array(), true);
				
		$arrPostTypes = array_keys($arrTypesAssoc);
		
		if(empty($search))
			return(array());
		
		$filters = array();
		$filters["posts_per_page"] = 10;
		$filters["posttype"] = $arrPostTypes;
		$filters["status"] = "publish,draft";
		
		if(!empty($search))
			$filters["search"] = $search;
		
		$arrPosts = UniteFunctionsWPUC::getPosts($filters);
		
		if(empty($arrPosts))
			return(array());
		
		$arrResult = array();
		
		//add empty value
		if($addNotSelected == true){
			$arr = array();
			$arr["id"] = 0;
			$arr["text"] = __("[please select post]", "unlimited_elements");
			$arrResult[] = $arr;
		}
		
		foreach($arrPosts as $post){
			
			$postID = $post->ID;
			$postTitle = $post->post_title;
			$postType = $post->post_type;
			
			$postTypeTitle = UniteFunctionsUC::getVal($arrTypesAssoc, $postType);
			
			if(empty($postTypeTitle))
				$postTypeTitle = $postType;
			
			$title = $postTitle." - ($postTypeTitle)";
			
			$arr = array();
			$arr["id"] = $postID;
			$arr["text"] = $title;
			
			$arrResult[] = $arr;			
			
		}
		
		$arrOutput = array();
		$arrOutput["results"] = $arrResult;
		$arrOutput["pagination"] = array("more"=>false);
		
		return($arrOutput);
	}
	
	
}