<?php
/**
 * @package Unlimited Elements
 * @author UniteCMS.net
 * @copyright (C) 2017 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ELEMENTS_INC') or die('Restricted access');

dmp("test");

//$font = new UniteFontManagerUC();
//$font->fetchIcons();


$webAPI = new UniteCreatorWebAPI();

$response = $webAPI->checkUpdateCatalog();

$lastAPIData = $webAPI->getLastAPICallData();
$arrCatalog = $webAPI->getCatalogData();

//$arrNames = $webAPI->getArrAddonNames($arrCatalog["catalog"]);

//dmp($arrCatalog);

dmp($lastAPIData);
dmp($arrCatalog);
exit();
