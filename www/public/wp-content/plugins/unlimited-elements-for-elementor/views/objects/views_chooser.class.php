<?php
/**
 * @package Unlimited Elements
 * @author UniteCMS.net
 * @copyright (C) 2017 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ELEMENTS_INC') or die('Restricted access');


class UniteCreatorViewsChooser{
	
	protected $showButtons = true;
	protected $showHeader = true;
	protected $defaultIcon = "puzzle-piece";
	protected $arrPages = array();
	protected $headerTemplate = "header";
	protected $headerText;
	
	/**
	 * constructor
	 */
	public function __construct(){
		
		$this->initDefaults();
		$this->init();
		$this->putHtml();
	}
	
	
	/**
	 * init defaults
	 */
	protected function initDefaults(){
		
		$this->headerText = "My Pages List";
		
	}
	
	/**
	 * init the pages
	 */
	protected function init(){
		
		$urlAddons = helperUC::getViewUrl_Addons();
		$urlDividers = helperUC::getViewUrl_Addons(GlobalsUC::ADDON_TYPE_SHAPE_DEVIDER);
		$urlShapes = helperUC::getViewUrl_Addons(GlobalsUC::ADDON_TYPE_SHAPES);
		
		$urlSections = HelperUC::getViewUrl_LayoutsList(array(), GlobalsUC::ADDON_TYPE_LAYOUT_SECTION);
		
		$textAddons = esc_html__("My Addons", "unlimited_elements");
		$textDividers = esc_html__("Dividers", "unlimited_elements");
		$textShapes = esc_html__("Shapes", "unlimited_elements");
		$textSection = esc_html__("Sections", "unlimited_elements");
		$textPageTemplates = esc_html__("Page Templates", "unlimited_elements");
				
		$this->addPage($urlAddons, $textAddons);
		$this->addPage($urlDividers, $textDividers, "map");
		$this->addPage($urlShapes, $textShapes, "map");
		$this->addPage($urlSections, $textSection);
				
	}
	
	
		
	/**
	 * add page
	 */
	protected function addPage($url, $title, $icon=null){
		
		if(empty($icon))
			$icon = $this->defaultIcon;
		
		$this->arrPages[] = array(
			"url"=>$url,
			"title"=>$title,
			"icon"=>$icon);
		
	}
	
	/**
	 * put pages html
	 */
	protected function putHtmlPages(){
		
		if($this->showHeader == true){
			
			$headerTitle = $this->headerText;
			
			require HelperUC::getPathTemplate("header");
		}else
			require HelperUC::getPathTemplate("header_missing");
		
		
		?>
		<ul class='uc-list-pages-thumbs'>
		<?php 
		foreach($this->arrPages as $page){
			
			$url = $page["url"];
			$icon = $page["icon"];
			
			if(empty($icon))
				$icon = "angellist";
			
			$title = $page["title"];
				
			?>
			<li>				
				<a href="<?php echo esc_attr($url)?>">
					<i class="fa fa-<?php echo esc_attr($icon)?>"></i>
					<?php esc_html($title)?>
				</a>
			</li>
			<?php 
		}
		?>
		</ul>
		<?php 
	}
	
	
	/**
	 * constructor
	 */
	protected function putHtml(){
		
		$this->putHtmlPages();
		
	}

}

