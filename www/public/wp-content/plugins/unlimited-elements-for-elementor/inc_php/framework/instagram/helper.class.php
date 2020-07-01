<?php
/**
 * @package Unlimited Elements
 * @author UniteCMS.net
 * @copyright (C) 2017 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ELEMENTS_INC') or die('Restricted access');


class HelperInstaUC{

	
	/**
	 * convert title to handle
	 */
	public static function convertTitleToHandle($title, $removeNonAscii = true){
		
		$handle = strtolower($title);
	
		$handle = str_replace(array("ä", "Ä"), "a", $handle);
		$handle = str_replace(array("å", "Å"), "a", $handle);
		$handle = str_replace(array("ö", "Ö"), "o", $handle);
	
		if($removeNonAscii == true){
	
			// Remove any character that is not alphanumeric, white-space, or a hyphen
			$handle = preg_replace("/[^a-z0-9\s\_]/i", " ", $handle);
	
		}
	
		// Replace multiple instances of white-space with a single space
		$handle = preg_replace("/\s\s+/", " ", $handle);
		// Replace all spaces with underscores
		$handle = preg_replace("/\s/", "_", $handle);
		// Replace multiple underscore with a single underscore
		$handle = preg_replace("/\_\_+/", "_", $handle);
		// Remove leading and trailing underscores
		$handle = trim($handle, "_");
	
		return($handle);
	}
	
	
	/**
	 * convert number to textual representation
	 */
	public static function convertNumberToText($num){
		
		$x = round($num);
					
		$x_number_format = number_format($x);
		
		if($x < 10000)
			return($x_number_format);
		
		$x_array = explode(',', $x_number_format);
		$x_parts = array('k', 'm', 'b', 't');
		$x_count_parts = count($x_array) - 1;
				
		$x_display = $x_array[0];
				
		$x_display .= $x_parts[$x_count_parts - 1];
		
		return $x_display;
	}
	
	
	/**
	 * validate instagram user
	 */
	public static function validateInstance($user, $instance="user"){
	
		UniteFunctionsUC::validateNotEmpty($user,"instagram $instance");
	
		if(preg_match('/^[a-zA-Z0-9._]+$/', $user) == false)
			UniteFunctionsUC::throwError("The instagram $instance is incorrect");
	
	}
	
	
	/**
	 * sanitize insta user
	 */
	public static function sanitizeUser($user){
	
		$user = str_replace("@","",$user);
	
		return($user);
	}
	
	
	/**
	 * sanitize insta user
	 */
	public static function sanitizeTag($tag){
	
		$tag = str_replace("#","", $tag);
		
		return($tag);
	}
	
	
	/**
	 * cache response
	 * $cacheTimeSeconds - 600 sec - 10 min.
	 */
	public static function cacheResponse($cacheKey, $response, $cacheTimeSeconds = 600){
		
		if(empty($cacheTimeSeconds))
			$cacheTimeSeconds = 600;
	
		UniteProviderFunctionsUC::setTransient($cacheKey, $response, $cacheTimeSeconds);
	}
	
	
	/**
	 * get response from cache
	 */
	public static function getFromCache($cacheKey){
		
		$response = UniteProviderFunctionsUC::getTransient($cacheKey);
	
		return($response);
	}
	
	
	/**
	 * get simple remote url
	 */
	public static function getRemoteUrl($url, $arrHeaders = null, $params = null, $debug = false){
			
	        $curl = curl_init();
			
	        if(is_array($arrHeaders) == false)
				$arrHeaders = array();
	        							
	        //create get string
			$strGet = '';
			if(!empty($params)){
			foreach($params as $key=>$value){
				
				if(!empty($strGet))
					$strGet .= "&";
				
				if(is_array($value))
					$value = json_encode($value);
				
				$value = urlencode($value);
				$strGet .= "$key=$value";
			}
		   }
		    
		   
		   if(!empty($strGet))
		   	  $url = UniteFunctionsUC::addUrlParams($url, $strGet);
			

		   $curl_options = array(
	            CURLOPT_RETURNTRANSFER => true,
	            CURLOPT_HEADER => true,
	            CURLOPT_URL => $url,
	            CURLOPT_HTTPHEADER => $arrHeaders,
	            CURLOPT_SSL_VERIFYPEER => false,
	            CURLOPT_CONNECTTIMEOUT => 15,
	            CURLOPT_TIMEOUT => 60
	        );

		   if($debug == true){
		   		
		   		dmp($curl_options);
		   }
		   	
	        curl_setopt_array($curl, $curl_options);
			
	        $response = curl_exec($curl);
	        $arrInfo = curl_getinfo($curl);
			
	        curl_close($curl);
	        
	        if($debug == true){
	        	dmp($response);
	        	dmp($arrInfo);
	        	exit();
	        }
	        
			$code =  UniteFunctionsUC::getVal($arrInfo, "http_code");
			switch($code){
				case 200:
				case 400:
				break;
				default:
					$error = curl_error($curl);
					UniteFunctionsUC::throwError("request error: ".$error.", code: $code");
				break;
			}

			
			//cut the header 
			$headerSize = UniteFunctionsUC::getVal($arrInfo, "header_size");
			if(!empty($headerSize))
				$response = substr($response, $headerSize);
			
			return($response);
	}
	
	
	/**
	 * containing - cotnain the txtopen adn txtclose or not
	 */
	public static function getTextPart($contents, $txtOpen, $txtClose, $containing = false, $numTimes = 1){
	
		$pos1 = strpos($contents,$txtOpen);
		if($numTimes>1) {
			for($i=1;$i<$numTimes;$i++){
				$pos1 = strpos($contents,$txtOpen,$pos1+1);
			}
		}
	
		if($pos1 === FALSE)
			return(false);
	
		if($containing == false)
			$pos1 += strlen($txtOpen);
	
		$pos2 = strpos($contents,$txtClose,$pos1);
		if($pos2 === false)
			return(false);
	
		if($containing == true)
			$pos2 += strlen($txtClose);
	
		$trans = substr($contents,$pos1,$pos2-$pos1);
	
		$trans = trim($trans);
	
		return($trans);
	}
	
	
	/**
	 * convert stamp to date
	 */
	public static function stampToDate($stamp){
		
		if(is_numeric($stamp) == false)
			return("");
		
		$dateText = date("d F y, h:i", $stamp);
		
		return($dateText);
	}
	
	
	/**
	 * get time sinse the event
	 */
	public static function getTimeSince($time_stamp){
		
		
		$time_difference = strtotime('now') - $time_stamp;
		
		//year
		if ($time_difference >= 60 * 60 * 24 * 365.242199)
			return self::get_time_ago_string($time_stamp, 60 * 60 * 24 * 365.242199, 'y');
		
		//month
		if ($time_difference >= 60 * 60 * 24 * 30.4368499)
			return self::get_time_ago_string($time_stamp, 60 * 60 * 24 * 30.4368499, 'mon');
		
		//week
		if ($time_difference >= 60 * 60 * 24 * 7)
			return self::get_time_ago_string($time_stamp, 60 * 60 * 24 * 7, 'w');
		
		//day
		if ($time_difference >= 60 * 60 * 24)
			return self::get_time_ago_string($time_stamp, 60 * 60 * 24, 'd');
		
		//hour
		if($time_difference >= 60 * 60)
			return self::get_time_ago_string($time_stamp, 60 * 60, 'h');
		
		//minute
		return self::get_time_ago_string($time_stamp, 60, 'min');
	}
	
	
	/**
	 * get time ago string
	 */
	private static function get_time_ago_string($time_stamp, $divisor, $time_unit){
		
		$time_difference = strtotime("now") - $time_stamp;
		$time_units      = floor($time_difference / $divisor);
		
		settype($time_units, 'string');
		
		if ($time_units === '0')
			return '1' . $time_unit;
		
		return $time_units . $time_unit;
	}	
	
	
	
}