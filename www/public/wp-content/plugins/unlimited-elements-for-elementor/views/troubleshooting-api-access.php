
<h1>Unlimited Elements - API Access Test</h1>

<br>

<?php

/**
 * check zip file request
 */
function checkZipFile(){
	
	//request single file
	$urlAPI = GlobalsUC::URL_API;
	
	$arrPost = array(
		"action"=>"get_addon_zip",
		"name"=>"blox_particles_logo",
		"cat"=>"Extras",
		"type"=>"addons",
		"catalog_date"=>"1563618449",
		"code"=>""
	);
	
	
	dmp("requesting widget zip from API");
	
	$response = UniteFunctionsUC::getUrlContents($urlAPI, $arrPost);
	
	if(empty($response))
		UniteFunctionsUC::throwError("Empty server response");
	
	$len = strlen($response);
	
	print_r("api response OK, recieve string size: $len");
	
}


/**
 * check zip file request
 */
function checkCatalogRequest(){
	
	//request single file
	$urlAPI = GlobalsUC::URL_API;
	
	$arrPost = array(
		"action"=>"check_catalog",
		"catalog_date"=>"1563618449",
		"include_pages"=>false,
		"domain"=>"localhost",
		"platform"=>"wp"	
	);
	
	dmp("requesting catalog check");
	
	$response = UniteFunctionsUC::getUrlContents($urlAPI, $arrPost);
	
	if(empty($response))
		UniteFunctionsUC::throwError("Empty server response");
	
	$len = strlen($response);
	
	print_r("api response OK, recieve string size: $len");
	
}

/**
 * various
 */
function checkVariousOptions(){
	
	dmp("checking file get contents");
	
	$urlAPI = GlobalsUC::URL_API;
	$response = file_get_contents($urlAPI);
	
	$len = strlen($response);
	
	dmp("file get contents OK, recieve string size: $len");
	
}

try{
	
	checkVariousOptions();
	
	echo "<br><br>";
	
	//checkCatalogRequest();
	
	echo "<br><br>";
	
	//checkZipFile();
	
	
}catch(Exception $e){
	echo $e->getMessage();
}

