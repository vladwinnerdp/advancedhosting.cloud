<?php
/**
 * @package Unlimited Elements
 * @author UniteCMS.net
 * @copyright (C) 2017 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ELEMENTS_INC') or die('Restricted access');

class UniteCreatorParamsEditor{
	
	const TYPE_MAIN = "main";
	const TYPE_ITEMS = "items";
	
	private $type = null;
	private $isHiddenAtStart = false;
	private $isItemsType = false;
	
	
	/**
	 * validate that the object is inited
	 */
	private function validateInited(){
		if(empty($this->type))
			UniteFunctionsUC::throwError("UniteCreatorParamsEditor error: editor not inited");
	}
	
	
	/**
	 * output html of the params editor
	 */
	public function outputHtmlTable(){
		
		$this->validateInited();
		
		$style="";
		if($this->isHiddenAtStart == true)
			$style = "style='display:none'";
				
		?>
			<div id="attr_wrapper_<?php echo esc_attr($this->type) ?>" class="uc-attr-wrapper unite-inputs" data-type="<?php echo esc_attr($this->type)?>" <?php echo UniteProviderFunctionsUC::escAddParam($style)?> >
				
				<table class="uc-table-params unite_table_items">
					<thead>
						<tr>
							<th width="50px">
							</th>
							<th width="200px">
								<?php esc_html_e("Title", "unlimited_elements")?>
							</th>
							<th width="160px">
								<?php esc_html_e("Name", "unlimited_elements")?>
							</th>
							<th width="100px">
								<?php esc_html_e("Type", "unlimited_elements")?>
							</th>
							<th width="270px">
								<?php esc_html_e("Param", "unlimited_elements")?>
							</th>
							<th width="200px">
								<?php esc_html_e("Operations", "unlimited_elements")?>
							</th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
				
				<div class="uc-text-empty-params mbottom_20" style="display:none">
						<?php esc_html_e("No Attributes Found", "unlimited_elements")?>
				</div>
				
				<a class="uc-button-add-param unite-button-secondary" href="javascript:void(0)"><?php esc_html_e("Add Attribute", "unlimited_elements");?></a>
				
				<?php if($this->isItemsType):?>
				
				<a class="uc-button-add-imagebase unite-button-secondary mleft_10" href="javascript:void(0)"><?php esc_html_e("Add Image Base Fields", "unlimited_elements");?></a>
				
				<?php endif?>
			</div>
		
		<?php 
	}

	
	/**
	 * set hidden at start. must be run before init
	 */
	public function setHiddenAtStart(){
		$this->isHiddenAtStart = true;
	}
	
	
	/**
	 * 
	 * init editor by type
	 */
	public function init($type){
		
		switch($type){
			case self::TYPE_MAIN:
			break;
			case self::TYPE_ITEMS:
				$this->isItemsType = true;
			break;
			default:
				UniteFunctionsUC::throwError("Wrong editor type: {$type}");
			break;
		}
		
		
		$this->type = $type;
	}
	
	
}