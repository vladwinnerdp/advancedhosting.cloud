<?php
/**
 * @package Unlimited Elements
 * @author UniteCMS.net
 * @copyright (C) 2017 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ELEMENTS_INC') or die('Restricted access');


class UniteCreatorAddonViewChildParams{
	
	const PARAM_PREFIX = "[param_prefix]";
	const PARENT_NAME = "[parent_name]";
	
	
	/**
	 * create child param
	 */
	protected function createChildParam($param, $type = null, $addParams = false){
		
		$arr = array("name"=>$param, "type"=>$type);
				
		
		switch($type){
			case UniteCreatorDialogParam::PARAM_IMAGE:
				$arr["add_thumb"] = true;
				$arr["add_thumb_large"] = true;
			break;
		}
		
		if(!empty($addParams))
			$arr = array_merge($arr, $addParams);
		
		return($arr);
	}

	/**
	 * create add param
	 */
	private function createAddParam($param = null,$addParams = array()){
		
		if(empty($addParams)){
			
			$addParams = array(
				"rawvisual"=>true,
			);
			
			if(!empty($param)){
				if($param == "|raw")
					$param = self::PARENT_NAME."|raw";
				else
					$param = self::PARENT_NAME."_".$param;
			}
			
		}
		
		$type = null;
		
		$arr = array("name"=>$param, "type"=>$type);
		$arr = array_merge($arr, $addParams);
		
		return($arr);
	}
	
	/**
	 * create child param
	 */
	protected function createChildParam_code($key, $text, $noslashes = false){
		
	    $arguments = array(
		    	"raw_insert_text" => $text, 
		    	"rawvisual"=>true,
	    	);		
		
	    if($noslashes == true)
	    	 $arguments["noslashes"] = true;
	    
	    	
	    $arr = $this->createChildParam($key, null, $arguments);		
		
		return($arr);
	}
	
	/**
	 * get code example php params
	 */
	protected function getCodeExamplesParams_php($arrParams){
		
			$key = "Run PHP Function (pro)";
			$text = "
			
{# This functionality exists only in the PRO version #}
{# run any wp action, and any custom PHP function. Use add_action to create the actions. \n The function support up to 3 custom params #}
\n
{{ do_action('some_action') }}
{{ do_action('some_action','param1','param2','param3') }}
";
		
			$arrParams[] = $this->createChildParam_code($key, $text);
		
			$key = "Data From PHP (pro)";
			$text = "
{# This functionality exists only in the PRO version #}			
{# apply any WordPress filters, and any custom PHP function. Use apply_filters to create the actions. \n The function support up to 2 custom params #}
\n
{% set myValue = apply_filters('my_filter') }}
{% set myValue = apply_filters('my_filter',value, param2, param3) }}

";
			$arrParams[] = $this->createChildParam_code($key, $text);
		
		
		return($arrParams);
	}
	
	/**
	 * get post child params
	 */
	public function getChildParams_codeExamples(){
		
		$arrParams = array();
		
		//----- show data --------
		
		$key = "showData()";
		$text = "
{# This function will show all data in that you can use #} \n 
{{showData()}}
";
		
		$arrParams[] = $this->createChildParam_code($key, $text);
		
		//---- show debug -----
		
		$key = "showDebug()";
		$text = "{# This function show you some debug (with post list for example) #} \n 
					{{showDebug()}}";
		
		//------ if empty ------
		
		$key = "IF Empty";
		$text = "
{% if some_attribute is empty %}
	<!-- put your empty case html -->   
{% else %} 
	<!-- put your not empty html here -->   
{% endif %}	
";
		
		$arrParams[] = $this->createChildParam_code($key, $text);
		
		//----- simple if ------
		
		$key = "IF";
		$text = "
{% if some_attribute == \"some_value\" %}
	<!-- put your html here -->   
{% endif %}	
";
		$arrParams[] = $this->createChildParam_code($key, $text);
		
		
		//----- if else ------
		
		$key = "IF - Else";
		$text = "
{% if product_stock == 0 %}
	<!-- not available html -->   
{% else %}
	<!-- available html -->
{% endif %}
";
		$arrParams[] = $this->createChildParam_code($key, $text);
		
		
		//----- complex if ------
		
		$key = "IF - Else - Elseif";
		$text = "
{% if product_stock == 0 %}
	<!-- put your 0 html here -->   
{% elseif product_stock > 0 and product_stock < 20 %}
	<!-- put your 0-20 html here -->   
{% elseif product_stock >= 20 %}
	<!-- put your >20 html here -->   
{% endif %}	
";

		$arrParams[] = $this->createChildParam_code($key, $text);


		//----- for in (loop) ------
		
		$key = "For In (loop)";
		$text = "
{% for product in woo_products %}
	
	<!-- use attributes inside the product, works if the product is array -->   
	<span> {{ product.title }} </span>
	<span> {{ product.price }} </span>
	
{% endfor %}	
";
		
		$arrParams[] = $this->createChildParam_code($key, $text);
		
		//----- html output raw filter ------
		
		$key = "HTML Output - |raw";
		$text = "
{# use the raw filter for printing attribute with html tags#}
{{ some_attribute|raw }}
";
		
		$arrParams[] = $this->createChildParam_code($key, $text);

		//----- truncate text filter ------
		
		$key = "Truncate Text Filter - |truncate";
		$text = "
{# use the truncate filter for lower the text length. arguments are: (numchars, preserve(true|false), separator=\"...\")#}
{{ some_attribute|truncate }}
{{ some_attribute|truncate(50) }}
{{ some_attribute|truncate(100, true) }}
{{ some_attribute|truncate(150, true, \"...\") }}
";
		
		$arrParams[] = $this->createChildParam_code($key, $text);
		
		
		//----- default value ------
		
		$key = "Default Value";
		$text = "
{# use the default value filter in case that no default value provided (like in acf fields) #}
{{ cf_attribute|default('text in case that not defined') }}
";

		$arrParams[] = $this->createChildParam_code($key, $text);

		
		$arrParams = $this->getCodeExamplesParams_php($arrParams);
		
		
		return($arrParams);
	}
	

	/**
	 * get post child params
	 */
	public function getChildParams_codeExamplesJS(){
		
		$arrParams = array();
		
		//----- show data --------
		
		$key = "jQuery(document).ready()";
		$text = " 
jQuery(document).ready(function(){

	/* put your code here, a must wrapper for every jQuery enabled widget */

});
		";
		$arrParams[] = $this->createChildParam_code($key, $text);
		
		
		return($arrParams);
	}
	
	
	/**
	 * create category child params
	 */
	public function createChildParams_category($arrParams){
		
		$arrParams[] = $this->createChildParam("category_id");
		$arrParams[] = $this->createChildParam("category_name");
		$arrParams[] = $this->createChildParam("category_slug");
		$arrParams[] = $this->createChildParam("category_link");
		
		//create categories array foreach
		
		$strCode = "";
		$strCode .= "{% for cat in [param_prefix].categories %}\n";
										
		$strCode .= "	<span> {{cat.id}} , {{cat.name}} , {{cat.slug}} , {{cat.description}}, {{cat.link}} </span> <br>\n ";
		
		$strCode .= "{% endfor %}\n";


	    $arrParams[] = $this->createChildParam("categories", null, array("raw_insert_text" => $strCode));		
		
		
		return($arrParams);
	}
	
	private function __________POST_FIELDS_________(){}
	
	
	/**
	 * add custom fields
	 */
	protected function addCustomFieldsParams($arrParams, $postID){
		
		if(empty($postID))
			return($arrParams);
		
		$isAcfExists = UniteCreatorAcfIntegrate::isAcfActive();
		
		$prefix = "cf_";
			
		//take from pods
		$isPodsExists = UniteCreatorPodsIntegrate::isPodsExists();
		
		$takeFromACF = true;
		if($isPodsExists == true){
			$arrMetaKeys = UniteFunctionsWPUC::getPostMetaKeys_PODS($postID);
			if(!empty($arrMetaKeys))
				$takeFromACF = false;
		}
		
		//take from toolset
		$isToolsetExists = UniteCreatorToolsetIntegrate::isToolsetExists();
		if($isToolsetExists == true){
			
			$objToolset = new UniteCreatorToolsetIntegrate();
			$arrMetaKeys = $objToolset->getPostFieldsKeys($postID);
			$takeFromACF = false;
		}
		
		
		//acf custom fields
		if($isAcfExists == true && $takeFromACF == true){
			
			$arrMetaKeys = UniteFunctionsWPUC::getAcfFieldsKeys($postID);
			$title = "acf field";
			
			if(empty($arrMetaKeys))
				return($arrParams);
			
			$firstKey = UniteFunctionsUC::getFirstNotEmptyKey($arrMetaKeys);
			
			foreach($arrMetaKeys as $key=>$type){
				
				//complex code (repeater) 
				
				if(is_array($type)){
					
					$strCode = "";
					$strCode .= "{% for item in [param_prefix].{$key} %}\n";
					
					$typeKeys = array_keys($type);
					
					foreach($typeKeys as $postItemKey){
												
						$strCode .= "<span> {{item.$postItemKey}} </span>\n";
					}
					
					$strCode .= "{% endfor %}\n";
					
				    $arrParams[] = $this->createChildParam($key, null, array("raw_insert_text"=>$strCode));
					
				    continue;
				}
				
				//array code 
				
				if($type == "array"){
					
					$strCode = "";
					$strCode .= "{% for value in [param_prefix].{$key} %}\n";
					$strCode .= "<span> {{item}} </span>\n";
					$strCode .= "{% endfor %}\n";
					
				    $arrParams[] = $this->createChildParam($key, null, array("raw_insert_text"=>$strCode));
					
					continue;
				}
				
				if($type == "empty_repeater"){
					
					$strText = "<!-- Please add some values to this field repeater in demo post in order to see the fields here -->";
				    $arrParams[] = $this->createChildParam($key, null, array("raw_insert_text"=>$strText));
					
					continue;
				}
				
				//simple param
				
				$arrParams[] = $this->createChildParam($key);
			}
			
			
		}else{	//regular custom fields
			
			//should be $arrMetaKeys from pods
			
			if(empty($arrMetaKeys))
				$arrMetaKeys = UniteFunctionsWPUC::getPostMetaKeys($postID, "cf_");
							
			$title = "custom field";
			
			if(empty($arrMetaKeys))
				return($arrParams);
			
			$firstKey = $arrMetaKeys[0];
				
			foreach($arrMetaKeys as $key)
				$arrParams[] = $this->createChildParam($key);
			
		}
		
		
		//add functions
		$arrParams[] = $this->createChildParam("$title example with default",null,array("raw_insert_text"=>"{{ [param_prefix].$firstKey|default('default text') }}"));

		
		return($arrParams);
	}
	
	/**
	 * add post terms function
	 */
	private function getChildParams_post_addTerms($arrParams){
		
		$strCode = "";
		$strCode .= "{% set terms = getPostTerms([param_prefix].id, \"post_tag\") %}\n";
		
		$strCode .= "{% for term in terms %}\n\n";
		$strCode .= "	{{term.id}}, {{term.name}}, {{term.slug}}\n";
		$strCode .= "	{{printVar(term)}}\n";
		$strCode .= "{% endfor %}\n";
		
		
	    $arrParams[] = $this->createChildParam("putPostTerms", null, array("raw_insert_text"=>$strCode));
		
		return($arrParams);
	}

	
	
	/**
	 * add post terms function
	 */
	private function getChildParams_post_addAuthor($arrParams){
		
		$strCode = "";
		$strCode .= "{% set author = getPostAuthor([param_prefix].author_id) %}\n\n";
		$strCode .= "{{author.id}} {{author.name}} {{author.email}}\n";
		
	    $arrParams[] = $this->createChildParam("getPostAuthor", null, array("raw_insert_text"=>$strCode));
		
		return($arrParams);
	}
	
	/**
	 * check and add woo post params
	 */
	private function checkAddWooPostParams($postID, $arrParams){
		
		$arrKeys = UniteCreatorWooIntegrate::getWooKeysByPostID($postID);
		
		if(empty($arrKeys))
			return($arrParams);
		
		foreach($arrKeys as $key){			
			$arrParams[] = $this->createChildParam($key);
		}
		
		return($arrParams);
	}
	
	
	/**
	 * get post child params
	 */
	public function getChildParams_post($postID = null, $arrAdditions = array()){
						
		$arrParams = array();
		$arrParams[] = $this->createChildParam("id");
		$arrParams[] = $this->createChildParam("title",UniteCreatorDialogParam::PARAM_EDITOR);
		$arrParams[] = $this->createChildParam("alias");
		$arrParams[] = $this->createChildParam("content", UniteCreatorDialogParam::PARAM_EDITOR);
		$arrParams[] = $this->createChildParam("content|wpautop", UniteCreatorDialogParam::PARAM_EDITOR);
		$arrParams[] = $this->createChildParam("intro", UniteCreatorDialogParam::PARAM_EDITOR);
		$arrParams[] = $this->createChildParam("link");
		$arrParams[] = $this->createChildParam("date",null,array("raw_insert_text"=>"{{[param_name]|ucdate(\"d F Y, H:i\")|raw}}"));
		
		//ucdate replaces
		//$arrParams[] = $this->createChildParam("postdate",null,array("raw_insert_text"=>"{{putPostDate([param_prefix].id,\"d F Y, H:i\")}}"));
		
		$arrParams[] = $this->createChildParam("tagslist",null,array("raw_insert_text"=>"{{putPostTags([param_prefix].id)}}"));		
		$arrParams = $this->getChildParams_post_addTerms($arrParams);
		$arrParams = $this->getChildParams_post_addAuthor($arrParams);
		
		if(!empty($postID))
			$arrParams = $this->checkAddWooPostParams($postID, $arrParams);
		
		$arrParams[] = $this->createChildParam("image", UniteCreatorDialogParam::PARAM_IMAGE);
		
		//add post additions
		if(empty($arrAdditions))
			return($arrParams);
				
		foreach($arrAdditions as $addition){
			
			switch($addition){
				case GlobalsProviderUC::POST_ADDITION_CATEGORY:
					
					$arrParams = $this->createChildParams_category($arrParams);
					
				break;
				case GlobalsProviderUC::POST_ADDITION_CUSTOMFIELDS:
					
					if(!empty($postID))
						$arrParams = $this->addCustomFieldsParams($arrParams, $postID);
					
				break;
			}
		}
		
		
		return($arrParams);
	}
	
	private function __________POST_FIELDS_END_________(){}
	
	
	/**
	 * get term code
	 */
	private function getTermCode($itemName, $parentName){
		
		$strCode = "";
		$strCode .= "{% for $itemName in $parentName %}\n";
		$strCode .= "\n";
		$strCode .= "	Term ID: {{{$itemName}.id}} <br>\n ";
		$strCode .= "	Name: {{{$itemName}.name|raw}} <br>\n ";
		$strCode .= "	Slug: {{{$itemName}.slug}} <br>\n ";
		$strCode .= "	Description: {{{$itemName}.description}} <br>\n ";
		$strCode .= "	Link: {{{$itemName}.link}} <br>\n ";
		$strCode .= "	Num posts: {{{$itemName}.num_posts}} <br>\n ";
		$strCode .= "	Is Current: {{{$itemName}.iscurrent}} <br>\n ";
		$strCode .= "	Selected Class: {{{$itemName}.class_selected}} <br>\n ";
		$strCode .= "	<hr>\n";
		$strCode .= "\n";
		
		$strCode .= "{% endfor %}\n";
		
		return($strCode);
	}
	
	
	/**
	 * get term code
	 */
	private function getUsersCode($itemName, $parentName){
		
		$strCode = "";
		$strCode .= "{% for $itemName in $parentName %}\n";
		$strCode .= "\n";
		$strCode .= "	User ID: {{{$itemName}.id}} <br>\n ";
		$strCode .= "	Username: {{{$itemName}.username}} <br>\n ";
		$strCode .= "	Name: {{{$itemName}.name|raw}} <br>\n ";
		$strCode .= "	Email: {{{$itemName}.email}} <br>\n ";
		$strCode .= "	Role: {{{$itemName}.role}} <br>\n ";
		
		$strCode .= "\n";
		$strCode .= "	<hr>\n";
		
		$strCode .= "	# ---- Avatar Fields: ----- \n\n";
		
		$arrAvatarKeys = UniteFunctionsWPUC::getUserAvatarKeys();
		
		foreach($arrAvatarKeys as $key){
			$title = UniteFunctionsUC::convertHandleToTitle($key);
		
			$strCode .= "	$title: {{{$itemName}.{$key}}} <br>\n ";
		}
		
		$strCode .= "\n";
		$strCode .= "	<hr>\n";
		
		$strCode .= "	# ---- User Meta Fields: ----- \n\n";
		
		$arrMetaKeys = UniteFunctionsWPUC::getUserMetaKeys();
		
		foreach($arrMetaKeys as $key){
			$title = UniteFunctionsUC::convertHandleToTitle($key);
		
			$strCode .= "	$title: {{{$itemName}.{$key}}} <br>\n ";
		}
		
		
		$strCode .= "\n";
		
		$strCode .= "{% endfor %}\n";
		
		return($strCode);
	}
	
	
	/**
	 * get post child params
	 */
	public function getAddParams_terms(){
		
		$arrParams = array();
		
		$strCode = $this->getTermCode("term", "[parent_name]");
		
		$arrParams[] = $this->createChildParam_code("[parent_name]_output", $strCode);
		
				
		return($arrParams);
	}

	/**
	 * get users child params
	 */
	public function getAddParams_users(){
		
		$arrParams = array();
		
		$strCode = $this->getUsersCode("user", "[parent_name]");
		
		$arrParams[] = $this->createChildParam_code("[parent_name]_output", $strCode);
		
		return($arrParams);
	}
	
	
	/**
	 * get post child params
	 */
	public function getAddParams_link(){

		$arrParams = array();
			
		$arrParams[] = $this->createAddParam();
		$arrParams[] = $this->createAddParam("html_attributes|raw");
		
		return($arrParams);
	}

	/**
	 * get post child params
	 */
	public function getAddParams_slider(){

		$arrParams = array();
		
		$arrParams[] = $this->createAddParam();
		$arrParams[] = $this->createAddParam("nounit");
		
		return($arrParams);
	}
	
	/**
	 * get post child params
	 */
	public function getAddParams_menu(){

		$arrParams = array();
		
		$arrParams[] = $this->createAddParam("|raw");
		
		return($arrParams);
	}
	
	
	/**
	 * icon library add params
	 */
	public function getAddParams_iconLibrary(){
		
		$arrParams = array();
		
		$arrParams[] = $this->createAddParam(null);
		$arrParams[] = $this->createAddParam("html|raw");

		
		return($arrParams);
	}
	
	
	
}

