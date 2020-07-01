<?php
/**
 * @package Unlimited Elements
 * @author UniteCMS.net / Valiano
 * @copyright (C) 2012 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ELEMENTS_INC') or die('Restricted access');
use Elementor\TemplateLibrary;


class UniteCreatorLayoutsExporterElementor extends UniteCreatorLayoutsExporter{
	
	const PREVIEW_IMAGE_KEY = "layout_preview_image_uc";
	
	const POST_TYPE_LAYOUTS_LIBRARY = "elementor_library";
	const POST_TYPE_PAGE = "page";
	
	private $exportPostID;
	private $txtLayout;
	private $layoutTitle;
	private $importPostType;
	private $arrLayoutContent;
	private $lastAddon;
	private $arrExportAddons;
	
	private $importedLayoutJsonFile;
	private $importedLayoutContent;
	
	
	/**
	 * create layout in db
	 */
	protected function createElementorLayoutInDB($arrLayout){
		
		$title = $arrLayout["title"];
		$name = sanitize_title($title);
		
		UniteFunctionsUC::validateNotEmpty($this->importPostType, "Import Post Type");
		
		$version = $arrLayout["version"];
		$templateType = $arrLayout["type"];
		$editMode = "builder";

		$arrContent = UniteFunctionsUC::getVal($arrLayout, "content");
		
		$arrPost = array();
		$arrPost["post_title"] = $title;
		$arrPost["post_name"] = $name;
		$arrPost["post_content"] = "elementor page";
		$arrPost["post_type"] = $this->importPostType;
		$arrPost["post_status"] = "publish";
		
		$postID = wp_insert_post($arrPost);
		
		if(empty($postID) || is_numeric($postID) == false)
			UniteFunctionsUC::throwError("post not inserted");
		
		//delete all meta
		UniteFunctionsWPUC::deletePostMetadata($postID);
		
		add_post_meta($postID, "_elementor_template_type", $templateType);
		add_post_meta($postID, "_elementor_edit_mode", $editMode);
		add_post_meta($postID, "_elementor_version", $version);
		
		$this->arrLayoutContent = $arrContent;
		
		$layoutData = json_encode($arrContent);
		$layoutDataValueInsert = wp_slash($layoutData);
		add_post_meta($postID, "_elementor_data", $layoutDataValueInsert);
		
		//delete css, elementor will create it again
		//delete_post_meta($postID, "_elementor_css");
		
		return($postID);
	}
	
	/**
	 * turn wp page to elementor layout
	 */
	protected function pageToElementorLayout($pageID, $arrLayout){
		
		$version = $arrLayout["version"];
		$templateType = $arrLayout["type"];
		$editMode = "builder";
		
		$arrPost = array();
		$arrPost["ID"] = $pageID;
		$arrPost["post_content"] = "elementor page";
		
		wp_update_post($arrPost);
		
		//set metadata
		UniteFunctionsWPUC::deletePostMetadata($pageID);
				
		add_post_meta($pageID, "_elementor_template_type", $templateType);
		add_post_meta($pageID, "_elementor_edit_mode", $editMode);
		add_post_meta($pageID, "_elementor_version", $version);
		
		$arrLayoutContent = UniteFunctionsUC::getVal($arrLayout, "content");
		
		$this->arrLayoutContent = $arrLayoutContent;
		$this->lastImportID = $pageID;
	}
	
	
	

	/**
	 * update layout in db
	 */
	protected function updateElementorLayoutInDB($arrLayout){
		
		$layoutData = json_encode($arrLayout);
		$layoutDataValueInsert = wp_slash($layoutData);
		update_post_meta($this->lastImportID, "_elementor_data", $layoutDataValueInsert);
	}
	
	
	
	
	/**
	 * add new layout by content
	 */
	protected function importElementorLayoutByContent($content, $layoutID = null){
		
		$arrLayout = @json_decode($content);
		
		if(empty($arrLayout))
			UniteFunctionsUC::throwError("Wrong file format");
		
		$arrLayout = UniteFunctionsUC::convertStdClassToArray($arrLayout);
		
		$objLayouts = new UniteCreatorLayouts();
		
		//set Title
		if(empty($layoutID)){
			$title = UniteFunctionsUC::getVal($arrLayout, "title");
			$arrLayout["title"] = $objLayouts->getUniqueTitle($title);
			$this->lastImportID = $this->createElementorLayoutInDB($arrLayout);
		}else{
			$this->lastImportID = $layoutID;
			$this->pageToElementorLayout($layoutID, $arrLayout);
		}
				
	}
	
	
	
	/**
	 * import layout txt
	 * $layoutID = existign layotu to import
	 */
	protected function importLayoutTxtFromZip($layoutID=null){
				
		$filepathLayout = $this->pathImportLayout."layout_data.json";
		
		UniteFunctionsUC::validateFilepath($filepathLayout,"layout_data.json");
				
		$content = file_get_contents($filepathLayout);
		
		$this->importElementorLayoutByContent($content, $layoutID);
			
	}
	
	
	/**
	 * import elementor layout
	 */
	public function importElementorLayout($arrTempFile, $isOverwriteAddons = true){
		
		dmp("import old way");
		exit();
		
		$this->addonsType = UniteCreatorElementorIntegrate::ADDONS_TYPE;
		$this->importPostType = self::POST_TYPE_LAYOUTS_LIBRARY;
		
		$this->import($arrTempFile, null, $isOverwriteAddons);
		$this->updateElementorLayoutAfterImport();
		
		$urlRedirect = admin_url("edit.php?post_type=elementor_library");
		
		header("location:".$urlRedirect);
		exit();
	}
	
	
	/**
	 * import vc zip file to some wp page
	 */
	public function importElementorZipToPage($filepath, $pageID, $isOverwriteAddons = false){
		
		$page = @get_post($pageID);
		if(empty($page))
			UniteFunctionsUC::throwError("page with id: $pageID not found");
					
		$this->addonsType = UniteCreatorElementorIntegrate::ADDONS_TYPE;
		
		$this->importZipFile($filepath, $pageID, $isOverwriteAddons);
		$this->updateElementorLayoutAfterImport();
		
	}
	
	/**
	 * set addon by widget type
	 */
	protected function setAddonByType($widgetType, $arrLayout){
		
		if(strpos($widgetType, "ucaddon_cat_") !== false)
			$addonName = $arrLayout["settings"]["uc_addon_name"];
		else
			$addonName = str_replace("ucaddon_", "", $widgetType);
		
		$objAddon = new UniteCreatorAddon();
		$objAddon->initByAlias($addonName, UniteCreatorElementorIntegrate::ADDONS_TYPE);
		
		$this->lastAddon = $objAddon;
	}
	
	
	function a________IMPORT_TEMPLATE_NEW_WAY______(){}
	
	/**
	 * get imported json file
	 */
	private function getElementorImportedJsonFile(){
		
		$arrFiles = UniteFunctionsUC::getFileList($this->pathImportLayout);
		
		if(empty($arrFiles))
			return(null);
			
		foreach($arrFiles as $file){
			$info = pathinfo($file);
			
			$ext = UniteFunctionsUC::getVal($info, "extension");
			if($ext == "json"){
				$urlFile = $this->pathImportLayout.$file;
				
				return($urlFile);
			}				
			
		}
		
		return(null);
	}
	
	
	/**
	 * rewrite import layout json file
	 */
	private function importElementorTemplateNew_rewriteJsonFile(){
		
		$jsonContent = json_encode($this->importedLayoutContent);
		
		UniteFunctionsUC::writeFile($jsonContent, $this->importedLayoutJsonFile);
	}
	
	
	/**
	 * get template ID from new imported
	 */
	private function importElementorTemplateNew_getTemplateID($response){
		
		if(empty($response))
			UniteFunctionsUC::throwError("Template not imported");
			
		$arrTemplate = $response[0];
		
		$templateID = UniteFunctionsUC::getVal($arrTemplate, "template_id");
		UniteFunctionsUC::validateNotEmpty($templateID, "template id");
		
		return($templateID);
	}
		
	
	/**
	 * import elementor template
	 */
	private function importElementorTemplateNew_importElementorTemplate(){
		
		$importedFilepath = $this->importedLayoutJsonFile;
		
		//get json file path
		$info = pathinfo($importedFilepath);
		$filename = $info["basename"];
		
		$objLocal = new Elementor\TemplateLibrary\Source_Local();
		
		$response = $objLocal->import_template( $filename, $importedFilepath );
		
		$newTemplateID = $this->importElementorTemplateNew_getTemplateID($response);
		
		return($newTemplateID);
	}
	
	/**
	 * prepare the json path and layout content from extracted layout
	 */
	private function importElementorTemplateNew_prepareLayoutImportContent(){
		
		$this->importedLayoutJsonFile = $this->getElementorImportedJsonFile();
		
		$jsonContent = file_get_contents($this->importedLayoutJsonFile);
		
		$this->importedLayoutContent = UniteFunctionsUC::jsonDecode($jsonContent);
				
	}
	
	
	/**
	 * import using elementor functions
	 * input - zip file from upload
	 */
	public function importElementorTemplateNew($arrTempFile, $isOverwriteAddons = true, $data = null){
		
		$this->addonsType = GlobalsUnlimitedElements::ADDONSTYPE_ELEMENTOR;
		
		$filepath = UniteFunctionsUC::getVal($arrTempFile, "tmp_name");
		$filename = UniteFunctionsUC::getVal($arrTempFile, "name");
		
		if(empty($filepath))
			UniteFunctionsUC::throwError("template filepath not found");
					
		$info = pathinfo($filename);
		
		$ext = UniteFunctionsUC::getVal($info, "extension");
		$ext = strtolower($ext);
		
		if($ext != "zip")
			UniteFunctionsUC::throwError("The file is not zip");
		
		$this->prepareImportFolders();
		$this->extractImportLayoutFile($filepath);

		//prepare the content and the json file path after extracted before import 
		$this->importElementorTemplateNew_prepareLayoutImportContent();
		
		//import addons
		$this->importLayoutAddons(true);
		
		//import images
		$this->importElementorTemplateNew_importImages();
		
		$this->importElementorTemplateNew_rewriteJsonFile();
				
		//import template
		$templateID = $this->importElementorTemplateNew_importElementorTemplate();
		
		return($templateID);
	}
	
	
	
	/**
	 * import elementor layout
	 */
	public function importElementorLayoutNew($arrTempFile, $isOverwriteAddons, $data){
		
		$templateID = $this->importElementorTemplateNew($arrTempFile, $isOverwriteAddons);
		
		if(empty($templateID))
			UniteFunctionsUC::throwError("Template not imported!");
		
		$post = get_post($templateID);
		
		if(empty($post)){
			$this->addLog(__("templates import failed", "unlimited_elements"));
			
			UniteFunctionsUC::throwError("Template post not created!");
		}
		
		$postTitle = $post->post_title;
		$this->addLog(__("Imported Template: ", "unlimited_elements").'<b>'. $postTitle.'</b>');
		
		//update to layout
		$arrUpdate = array();
		
		$postType = GlobalsUnlimitedElements::POSTTYPE_UNLIMITED_ELEMENS_LIBRARY;
		
		//change post type
		
		$arrUpdate["post_type"] = $postType;
		
		//change post parent
		$parentID = UniteFunctionsUC::getVal($data, "parentid");
		$parentID = (int)$parentID;
		if(!empty($parentID))		
			$arrUpdate["post_parent"] = $parentID;
		
		//set max menu order
		$maxOrder = UniteFunctionsWPUC::getMaxMenuOrder($postType, $parentID);
		$arrPost["menu_order"] = $maxOrder+1;
		
		//update the post
		UniteFunctionsWPUC::updatePost($templateID, $arrUpdate);
		
		//set category
		$catID = UniteFunctionsUC::getVal($data, "catid");
		
		if(!empty($catID))
		   add_post_meta($templateID, GlobalsProviderUC::META_KEY_CATID, $catID);
		
	}
	
	function a_______EXPORT_IMAGES______(){}
	
	/**
	 * check if it's image array or not
	 */
	protected function isImageArray($arr){
		
		if( count($arr) > 2 || empty($arr))
			return(false);
		
		if(isset($arr["url"]) || isset($arr["id"]))
			return(true);
		
		return(false);
	}
	
	
	/**
	 * modify layout image for export
	 */
	protected function modifyLayoutImageForExport($arrImage, $key=null){
		
		$urlImage = UniteFunctionsUC::getVal($arrImage, "url");
				
		$localFilename = $this->processConfigImage($urlImage);
		if(!empty($localFilename))
			$arrImage["url"] = self::KEY_LOCAL.$localFilename;

		$arrImage["id"] = "";
			
		return($arrImage);
	}
	
	
	/**
	 * go over layout items values, run modify func on every value
	 */
	protected function runOverLayoutImagesForExport($arrLayout, $modifyFunc){
		
		if(is_array($arrLayout) == false)
			return($arrLayout);

		foreach($arrLayout as $key=>$item){
			
			if(is_array($item) == false){
				
				continue;
			}
						
			$isImageItem = $this->isImageArray($item);
			
			if($isImageItem == true)
				$arrLayout[$key] = $modifyFunc($item, $key);
			else
				$arrLayout[$key] = $this->runOverLayoutImagesForExport($item, $modifyFunc);
		}
		
		return($arrLayout);
	}
	
	
	/**
	 * Enter description here ...
	 */
	private function addPostFeaturedImage(){
		
		$featuredImageID = UniteFunctionsWPUC::getFeaturedImageID($this->exportPostID);
		
		if(empty($featuredImageID))
			return(false);
		
		$this->processConfigImage($featuredImageID, "layout_preview_image_uc");
				
	}
	
	/**
	 * copy the images
	 */
	private function putLayoutImages_copyImages(){
		
		
		foreach($this->arrExportImages as $arrImage){
			$sourceFilepath = $arrImage["path"];
			
			if(is_file($sourceFilepath) == false)
				UniteFunctionsUC::throwError("Image file: $sourceFilepath not found!");
			
			$filename = $arrImage["save_filename"];
			$destFilepath = $this->pathExportLayoutImages.$filename;
						
			copy($sourceFilepath, $destFilepath);
		}
		
		
	}
	
	
	/**
	 * export images
	 */
	private function putLayoutImages_elementor($arrContent){
		
		$arrContent = $this->runOverLayoutImagesForExport($arrContent, array($this, "modifyLayoutImageForExport"));
		
		$this->addPostFeaturedImage();
		
		$this->putLayoutImages_copyImages();
				
		return($arrContent);
	}
	
	
	function a_______IMPORT_IMAGES______(){}
	
	
	/**
	 * import images
	 */
	protected function importElementorTemplateNew_importImages(){

		$this->importLayoutImages();
		
	}
	
	/**
	 * modify layout image for export
	 */
	protected function modifyLayoutImageForImport($arrImage, $key=null){
		
		if(is_string($arrImage)){
			$urlImage = $arrImage;
			
			$arrImageData = $this->getImportedImageData($urlImage);
			
			if(empty($arrImageData)){
				
				if(strpos($urlImage, self::KEY_LOCAL) !== false)
					UniteFunctionsUC::throwError("image not imported: ".$urlImage);
				
				return($urlImage);
			}
			
			$urlImage = UniteFunctionsUC::getVal($arrImageData, "urlfull");
			return($urlImage);
		}
				
		$id = UniteFunctionsUC::getVal($arrImage, "id");
		$url = UniteFunctionsUC::getVal($arrImage, "url");
		
		if(is_numeric($id))
			return($arrImage);
		
		//convert imported image
		$arrImageData = $this->getImportedImageData($url);
				
		if(!empty($arrImageData)){
			$arrImage["url"] = UniteFunctionsUC::getVal($arrImageData, "urlfull");
			$arrImage["id"] = UniteFunctionsUC::getVal($arrImageData, "imageid");
			return($arrImage);
		}
		
		//convert internal image (from assets path)
		/*
		if(empty($this->lastAddon))
			UniteFunctionsUC::throwError("should be addon inited for image convert");
		
		$urlAssets = $this->lastAddon->getUrlAssets();
		$arrImage["url"] = HelperUC::convertFromUrlAssets($url, $urlAssets);
		*/
		
		return($arrImage);
	}
	
	
	/**
	 * update layout at the end instead here, don't delete this function
	 */
	protected function importLayoutImages_updateLayout(){
		
		$arrContent = UniteFunctionsUC::getVal($this->importedLayoutContent, "content");
				
		$arrContent = $this->runOverLayoutImagesForExport($arrContent, array($this, "modifyLayoutImageForImport"));
		
		$this->importedLayoutContent["content"] = $arrContent;
		
	}
		
	
	
	function a_______EXPORT_ELEMENTOR_LAYOUT______(){}
	
	/**
	 * process export content
	 */
	protected function process_element_export_import_content( \Elementor\Controls_Stack $element, $method ) {
		
		$element_data = $element->get_data();

		if ( method_exists( $element, $method ) ) {
			// TODO: Use the internal element data without parameters.
			$element_data = $element->{$method}( $element_data );
		}

		foreach ( $element->get_controls() as $control ) {
			$control_class = \Elementor\Plugin::$instance->controls_manager->get_control( $control['type'] );

			// If the control isn't exist, like a plugin that creates the control but deactivated.
			if ( ! $control_class ) {
				return $element_data;
			}

			if ( method_exists( $control_class, $method ) ) {
				$element_data['settings'][ $control['name'] ] = $control_class->{$method}( $element->get_settings( $control['name'] ), $control );
			}

			// On Export, check if the control has an argument 'export' => false.
			if ( 'on_export' === $method && isset( $control['export'] ) && false === $control['export'] ) {
				unset( $element_data['settings'][ $control['name'] ] );
			}
		}
		
		return $element_data;
	}
	
	
	
	/**
	 * process export import content
	 */
	protected function process_export_import_content( $content, $method ) {
		
		return \Elementor\Plugin::$instance->db->iterate_data(
			$content, function( $element_data ) use ( $method ) {
				$element = \Elementor\Plugin::$instance->elements_manager->create_element_instance( $element_data );
				
				// If the widget/element isn't exist, like a plugin that creates a widget but deactivated
				if ( ! $element ) {
					return null;
				}

				return $this->process_element_export_import_content( $element, $method );
			}
		);
	}
	
	
	/**
	 * get elementor export data
	 */
	private function getElementorExportContent($template_id){
		
		$objLocal = new Elementor\TemplateLibrary\Source_Local();
		
		$template_data = $objLocal->get_data( array(
			'template_id' => $template_id,
		));
				
		$content = UniteFunctionsUC::getVal($template_data, "content");
		if(empty($content))
			$content = array();
		
		$content = $this->process_export_import_content( $content, 'on_export' );
		
		$template_data["content"] = $content;
		
		if ( get_post_meta( $template_id, '_elementor_page_settings', true ) ) {
			$page = \Elementor\SettingsManager::get_settings_managers( 'page' )->get_model( $template_id );
			
			$page_settings_data = $this->process_element_export_import_content( $page, 'on_export' );

			if ( ! empty( $page_settings_data['settings'] ) ) {
				$template_data['page_settings'] = $page_settings_data['settings'];
			}
		}
		
		return($template_data);
	}
	
	
	/**
	 * get elementor export data
	 */
	private function getElementorExportData($content, $templateID){
		
		$export_data = [
			'version' => \ELEMENTOR\DB::DB_VERSION,
			'title' => get_the_title( $templateID ),
			'type' => \Elementor\TemplateLibrary\Source_Local::get_template_type( $templateID ),
			'content' => $content
		];
		
				
		return [
			'name' => 'elementor-' . $templateID . '-' . date( 'Y-m-d' ) . '.json',
			'content' => wp_json_encode( $export_data ),
		];
	}
	
	
	/**
	 * get addons list from elementor content
	 */
	private function getAddonsListFromElementorContent($content){
		
		$this->arrExportAddons = array();
		
		\Elementor\Plugin::$instance->db->iterate_data(
			$content, function( $element_data ) {
				$element = \Elementor\Plugin::$instance->elements_manager->create_element_instance( $element_data );
				
				// If the widget/element isn't exist, like a plugin that creates a widget but deactivated
				if ( ! $element ) {
					return null;
				}

				$isUniteAddon = false;
				if($element instanceof UniteCreatorElementorWidget)
					$isUniteAddon = true;
				
				if($isUniteAddon == false)
					return(null);
				
				$addon = $element->getObjAddon();
				$this->arrExportAddons[] = $addon;
				
			}
		);
		
		return($this->arrExportAddons);
	}
	
	
	/**
	 * put layout file
	 */
	private function putLayoutFile_elementor($arrContent){
		
		//prepare the data
		$exportData = $this->getElementorExportData($arrContent, $this->exportPostID);
		
		$layoutStrContent = UniteFunctionsUC::getVal($exportData, "content");
		
		//set filepath		
		$filename = UniteFunctionsUC::getVal($exportData, "name");
		
		UniteFunctionsUC::validateNotEmpty($filename, "export filename");
		UniteFunctionsUC::validateNotEmpty($layoutStrContent, "layout content");
		
		$filepath = $this->pathExportLayout.$filename;
				
		UniteFunctionsUC::writeFile($layoutStrContent, $filepath);
		
	}
	
	
	/**
	 * export layout file
	 */
	private function exportElementorLayoutZip($arrContent, $exportName = null, $isReturnData = false){
		
		try{
			
			$arrAddons = $this->getAddonsListFromElementorContent($arrContent);
			
			$this->prepareExportFolders_layouts();
			$this->prepareExportFolders_layout();

			//$this->putLayoutImages();
			
			//get all vars
			$filename = $templateData["name"];
			
			if(!empty($exportName))
				$filename = $exportName.".json";
						
			$arrInfo = pathinfo($filename);
			$layoutName = $arrInfo["filename"];
			
			//pack
			$this->putLayoutAddons($arrAddons);
			$arrContent = $this->putLayoutImages_elementor($arrContent);
			
			$this->putLayoutFile_elementor($arrContent);
						
			//make zip
			$this->prepareExportZip($layoutName);
						
			$this->deleteExportLayoutFolder();
			
			if($isReturnData == true){
				$arrData = $this->getExportedFileData();
				
				return($arrData);
			}
			
			
			$this->downloadExportFile();
			exit();
			
		}catch(Exception $e){
	
			$prefix = "Export Template Error: ";
			
			$message = $prefix.$e->getMessage();
			
			dmp($e->getTraceAsString());
			
			echo esc_html($message);
			
			exit();
		}
	}
	
	
	/**
	 * export elementor post by id
	 */
	public function exportElementorPost($postID, $exportName = null, $isReturnData = false){
		
		$this->exportPostID = $postID;
		
		//if not inited - init
		if(empty($this->objLayout)){
			
			$post = get_post($postID);
			if(empty($post))
				UniteFunctionsUC::throwError("Elementor post not found: $postID");
			
			$objLayout = new UniteCreatorLayout();
			$objLayout->initByPost($post);
			
			$this->initByLayout($objLayout);
		}
		
		$templateData = $this->getElementorExportContent($postID);
		
		$content = $templateData["content"];
						
		$arrData = $this->exportElementorLayoutZip($content, $exportName, true);
		
		return($arrData);
	}
	
	
	/**
	 * export elementor inited layout
	 */
	public function exportElementorLayout($isReturnData = false){
		
		$this->validateInited();
		
		$postID = $this->objLayout->getID();
		$exportName = $this->objLayout->getExportLayoutName();
		
		$arrData = $this->exportElementorPost($postID, $exportName, $isReturnData);
		
		return($arrData);
	}
		
	
}