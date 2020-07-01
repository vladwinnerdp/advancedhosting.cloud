<?php

/**
 * @package Unlimited Elements
 * @author UniteCMS http://unitecms.net
 * @copyright Copyright (c) 2016 UniteCMS
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

//no direct accees
defined ('UNLIMITED_ELEMENTS_INC') or die ('restricted aceess');

class UniteCreatorWooIntegrate{
	
	const POST_TYPE_PRODUCT = "product";
	private $currency;
	private $currencySymbol;
	private $urlCheckout;
	private $urlCart;
	private $urlSite;
	private $urlCurrentPage;
	
	private $isInited = false;
	
	private static $instance;
	
	
	/**
	 * constructor
	 */
	public function __construct(){
		
		$this->init();
	}
	
	
	/**
	 * init if not inited
	 */
	private function init(){
		
		if(self::isWooActive() == false)
			return(false);
		
		if($this->isInited == true)
			return(false);
			
		//init
		$this->currency = get_woocommerce_currency();
    	$this->currencySymbol = get_woocommerce_currency_symbol($this->currency);
    	$this->urlCheckout = wc_get_checkout_url();
    	$this->urlCart = wc_get_cart_url();
    	$this->urlSite = home_url();
		$this->urlCurrentPage = UniteFunctionsWPUC::getUrlCurrentPage();
    	    	
    	$this->isInited = true;
		
    	/*
		global $wp;
		echo home_url($wp->request);
    	*/    	
    	
	}
	
	
	/**
	 * return if acf plugin activated
	 */
	public static function isWooActive(){
		
		if(class_exists('WooCommerce'))
			return(true);
		
		return(false);
	}
	
	/**
	 * check and init instance
	 */
	public static function getInstance(){
		
		if(empty(self::$instance))
			self::$instance = new UniteCreatorWooIntegrate();
		
		
		return(self::$instance);
	}
	
	/**
	 * add add to cart data
	 */
	private function addAddToCartData($arrProduct, $productID, $productSku){
		
		$params = "add-to-cart={$productID}";
		
		$urlAddCart = UniteFunctionsUC::addUrlParams($this->urlCurrentPage, $params);
		
    	$arrProduct["woo_link_addcart_cart"] = UniteFunctionsUC::addUrlParams($this->urlCart, $params);
    	$arrProduct["woo_link_addcart_checkout"] = UniteFunctionsUC::addUrlParams($this->urlCheckout, $params);
    	    	
    	//add html ajax add to cart
    	$addCartAttributes = "href=\"{$urlAddCart}\" data-quantity=\"1\" class=\"uc-button-addcart product_type_simple add_to_cart_button ajax_add_to_cart\" data-product_id=\"{$productID}\" data-product_sku=\"{$productSku}\" rel=\"nofollow\"";
    	
    	$arrProduct["woo_addcart_ajax_attributes"] = $addCartAttributes;
    	
		return($arrProduct);
	}
	
	
	/**
	 * get product data
	 */
	private function getProductData($productID){
		
		if(function_exists("wc_get_product") == false)
			return(null);
					
		//wc_get_ac
    	$objInfo = wc_get_product($productID);
		
    	if(empty($objInfo))
    		return(null);
				
    	$arrData = $objInfo->get_data();
		
    	$arrProperties = array(
    		"sku",
    		"price",
    		"regular_price",
    		"sale_price",
    		"stock_quantity",
    		"stock_status",
    		"weight",
    		"length",
    		"width",
    		"height",
    		"height",
    		"average_rating",
    		"review_count"
    	);
    	
    	$productSku = UniteFunctionsUC::getVal($arrData, "sku");
    	$salePrice = UniteFunctionsUC::getVal($arrData, "sale_price");
    	
    	$arrProduct = array();
    	foreach($arrProperties as $propertyName){
    		
    		$value = UniteFunctionsUC::getVal($arrData, $propertyName);
    		if(is_array($value) == true)
    			continue;
    		
    		$arrProduct["woo_".$propertyName] = $value;
    	}
    	    	
    	$arrProduct["woo_currency"] = $this->currency;
    	$arrProduct["woo_currency_symbol"] = $this->currencySymbol;
		
    	//put add to cart link
    	$arrProduct = $this->addAddToCartData($arrProduct, $productID, $productSku);
    	
    	
    	return($arrProduct);
	}
	
	
	/**
	 * get woo data by type
	 */
	private function getWooData($postType, $postID){
		
		if(self::isWooActive() == false)
			return(null);
		
		switch($postType){
			case self::POST_TYPE_PRODUCT:
				$arrData = $this->getProductData($postID);
				
				return($arrData);
			break;
			default:
				return(null);
			break;
		}
		
	}
	
	/**
	 * get woo commerce data by type
	 */
	public static function getWooDataByType($postType, $postID){

		$objInstance = self::getInstance();

		$response = $objInstance->getWooData($postType, $postID);
		
		return($response);
	}
	
	/**
	 * get keys by post id
	 */
	private function getWooKeys($postID){
		
		if(self::isWooActive() == false)
			return(null);
		
		$post = get_post($postID);
		if(empty($post))
			return(null);
		
		$postType = $post->post_type;
		
		$arrData = self::getWooDataByType($postType, $postID);
		if(empty($arrData))
			return(false);
		
		$arrKeys = array_keys($arrData);
		
		
		return($arrKeys);
		
	}
	
	
	/**
	 * get woo keys by post id
	 */
	public static function getWooKeysByPostID($postID){
		
		$instance = self::getInstance();
		
		$response = $instance->getWooKeys($postID);
		
		return($response);
	}
	
	/**
	 * put filters js
	 */
	private function putHtmlFiltersJS(){
		
		UniteProviderFunctionsUC::addjQueryInclude();
		
		$urlScriptFile = GlobalsUC::$url_assets_internal."js/uc_woocommerce.js";
		
		HelperUC::addScriptAbsoluteUrl($urlScriptFile, "uc_woo_integrate");
	}
	
	
	/**
	 * put html filter - order
	 */
	private function putHtmlFilter_order($params){
				
		$arrOptions = array();
		$arrOptions["name"] = __("Product Name","unlimited_elements");
		$arrOptions["price"] = __("Price","unlimited_elements");
				
		$name = "uc_order";
		
		$value = UniteFunctionsUC::getPostGetVariable($name, "", UniteFunctionsUC::SANITIZE_KEY);
		
		
		$htmlSelect = HelperHtmlUC::getHTMLSelect($arrOptions, $value, "name='{$name}' class='uc-woo-filter uc-woo-filter-order'", true);
		
		?>
		<form class="uc-woocommerce-ordering" method="get">
			
			<?php echo $htmlSelect?>
			
		</form>
		
		<?php 
		
		$this->putHtmlFiltersJS();
	}
	
	
	/**
	 * put html filter
	 */
	public function putHtmlFilter($filterName, $params = null){
		
		switch($filterName){
			case "order":
				$this->putHtmlFilter_order($params);
			break;
			default:
				UniteFunctionsUC::throwError("putWooFilter error: filter $filterName not exists");
			break;
		}
		
	}
	
}