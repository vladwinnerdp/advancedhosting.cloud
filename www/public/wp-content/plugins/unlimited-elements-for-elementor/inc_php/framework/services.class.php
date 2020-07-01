<?php
/**
 * @package Unlimited Elements
 * @author UniteCMS.net
 * @copyright (C) 2017 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ELEMENTS_INC') or die('Restricted access');


	// advanced settings class. adds some advanced features
	class UniteServicesUC{
		
		
		/**
		 * get instagram data
		 */
		public function getInstagramData($user, $maxItems = null){
						
			$pathAPI = GlobalsUC::$pathPlugin."inc_php/framework/instagram/include_insta_api.php";
			require_once($pathAPI);
			
			$api = new InstagramAPINewUC();
			
			$response = $api->getItemsData($user,null,null,$maxItems);
						
			return($response);
		}
		
		
	}
