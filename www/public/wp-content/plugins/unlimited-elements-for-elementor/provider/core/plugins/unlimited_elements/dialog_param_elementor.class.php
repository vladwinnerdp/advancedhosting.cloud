<?php
/**
 * @package Unlimited Elements
 * @author UniteCMS.net
 * @copyright (C) 2017 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ELEMENTS_INC') or die('Restricted access');

class UniteCreatorDialogParamElementor extends UniteCreatorDialogParam{

	
	/**
	 * put menu param
	 */
	protected function putMenuParam(){
		
		$settings = new UniteCreatorSettings();
		
		$settings->addTextBox("menu_class","uc-list-menu","Menu Class",array("description"=>"The class on menu ul element","unlimited_elements"));
		$settings->addTextBox("before","","Html Before Link",array("description"=>"The html that are put before link if needed","unlimited_elements"));
		$settings->addTextBox("after","","Html After Link",array("description"=>"The html that are put after the link if needed","unlimited_elements"));
		
		
		$objOutput = new UniteSettingsOutputWideUC();
		$objOutput->init($settings);
		$objOutput->draw("menu_param_settings", false);
		
		?>
		<div class="unite-inputs-sap-double"></div>
		
		<i> 
			* <?php _e("Information about menu classes you can find here", "unlimited_elements")?>: 
			
			<a href="https://developer.wordpress.org/reference/functions/wp_nav_menu/" target="_blank"><?php _e("WP Menu Reference","unlimited_elements")?></a>
			
		</i>
		<?php 
	}
	
	
	/**
	 * put users param
	 */
	protected function putUsersParam(){
		
		$checkID = "get_meta";
		$checkIDAvatar = "get_avatar";
		
		esc_html_e("Use this attribute to get the WP Users List", "unlimited_elements");
		
		?>
		<br>
		<br>
		
		<label for="<?php echo esc_attr($checkID)?>" >
			<input id="<?php echo esc_attr($checkID)?>" type="checkbox" class="uc-param-checkbox"  name="<?php echo $checkID?>">
			<?php _e("Include User Meta Data", "unlimited_elements")?>
		</label>
		
		<div class="unite-inputs-sap"></div>
		
		<label for="<?php echo esc_attr($checkIDAvatar)?>" >
			<input id="<?php echo esc_attr($checkIDAvatar)?>" type="checkbox" class="uc-param-checkbox"  name="<?php echo $checkIDAvatar?>">
			<?php _e("Include Avatar", "unlimited_elements")?>
		</label>
		
		<?php 
		
	}
	
	
	/**
	 * put icon library parameter
	 */
	protected function putIconLibraryParam(){
		
		$checkID = "check_put_svg";
		
		?>
		<div class="unite-inputs-label">
			<?php esc_html_e("Default Value", "unlimited_elements")?>:
		</div>
		
		
		<input type="text" name="default_value"  value="">
		
		<div class="unite-inputs-sap-double"></div>
		
		<label for="<?php echo esc_attr($checkID)?>" >
			<input id="<?php echo esc_attr($checkID)?>" type="checkbox" class="uc-param-checkbox"  name="enable_svg">
			<?php _e("Enable SVG Select", "unlimited_elements")?>
		</label>
		
		
		<?php 
		
	}
	
	/**
	 * add selector html to the params
	 */
	private function addHtmlSelector(){
		?>		
		<div class="unite-inputs-sap"></div>
		
		<div class="unite-inputs-label">
			<?php esc_html_e("CSS Selector", "unlimited_elements")?>:
		</div>
				
		<input type="text" name="selector"  value="" placeholder="<?php _e("Example","unlimited_elements")?> .my-price">
		
		<div class="unite-inputs-sap"></div>
		
		<i><?php _e("* This attribute generate css only within the css selectors, it don't have placeholder in the widget editor","unlimited_elements")?></i>
		
		
		<?php
	}

	
	/**
	 * add selector html to the params
	 */
	private function addHtmlSelectorNameValue($selectorPlaceholder = "", $selectorValuePlaceholder = "", $value = ""){
		
		if(empty($selectorPlaceholder))
			$selectorPlaceholder = "Example .my-price";
		
		?>		
		<div class="unite-inputs-sap"></div>
		
		<div class="unite-inputs-label">
			<?php esc_html_e("CSS Selector", "unlimited_elements")?>:
		</div>
				
		<input type="text" name="selector"  value="" placeholder="<?php echo $selectorPlaceholder?>">
		
		<div class="unite-inputs-sap"></div>
		
		<div class="unite-inputs-label">
			<?php esc_html_e("CSS Selector Value", "unlimited_elements")?>:
		</div>
		
		<input type="text" name="selector_value" data-initval="<?php echo $value?>" value="<?php echo $value?>" placeholder="<?php echo $selectorValuePlaceholder?>">
		
		
		<?php
	}
		
	
	/**
	 * slider param
	 */
	protected function putSliderParam(){
		
		$arrUnits = array();
		$arrUnits["px"] = "PX";
		$arrUnits["%"] = "%";
		$arrUnits["em"] = "EM";
		$arrUnits["px_percent"] = __("PX, %","unlimited_elements");
		$arrUnits["px_percent_em"] = __("PX, %, EM","unlimited_elements");
		
		$arrUnits = array_flip($arrUnits);
		
		$objSettings = new UniteCreatorSettings();
		
		$params = array();
		$params["class"] = "number";
		
		$objSettings->addTextBox("default_value","20",__("Default Value","unlimited_elements"),$params);
		$objSettings->addTextBox("min","1",__("Min","unlimited_elements"),$params);
		$objSettings->addTextBox("max","100",__("Max","unlimited_elements"),$params);
		$objSettings->addTextBox("step","1",__("Step","unlimited_elements"),$params);
		
		$objSettings->addSelect("units", $arrUnits, __("Units", "unlimited_elements"),"px");

		$objOutput = new UniteSettingsOutputWideUC();
		$objOutput->init($objSettings);
		
		?>
		
		<div class="unite-inputs-label">
			<?php esc_html_e("Default Value", "unlimited_elements")?>:
		</div>
		
		<?php $objOutput->drawSingleSetting("default_value"); ?>
		
		<div class="unite-inputs-sap"></div>
		
		<div class="params-dialog-table">
		
			<!-- Min -->
			<div class="params-table-item">
				<div class="unite-inputs-label">
					<?php esc_html_e("Min", "unlimited_elements")?>:
				</div>
				
				<?php $objOutput->drawSingleSetting("min"); ?>
			</div>
			
			<!-- Max -->
			<div class="params-table-item">
	
				<div class="unite-inputs-label">
					<?php esc_html_e("Max", "unlimited_elements")?>:
				</div>
			
			<?php $objOutput->drawSingleSetting("max"); ?>
			</div>
			
			<!-- Step -->
			<div class="params-table-item">
				
				<div class="unite-inputs-label">
					<?php esc_html_e("Step", "unlimited_elements")?>:
				</div>
			
			<?php $objOutput->drawSingleSetting("step"); ?>
			</div>

			<div class="params-table-item">
			
				<div class="unite-inputs-label">
					<?php esc_html_e("Units", "unlimited_elements")?>:
				</div>
				
				<?php $objOutput->drawSingleSetting("units"); ?>
				
			</div>
		
		</div>
		
		<?php $this->addHtmlSelectorNameValue("Example .box", "example - width: {{SIZE}}{{UNIT}};", "width: {{SIZE}}{{UNIT}};") ?>

		<div class="unite-inputs-sap"></div>
		
		<i><?php _e("* You can use those placeholders: {{SIZE}} &nbsp; {{UNIT}} &nbsp; {{CURRENT_ITEM}}","unlimited_elements")?></i>

		
		<?php
	}

	
	/**
	 * background param
	 */
	protected function putBackgroundParam(){
		
		$arrSelect = array();
		$objSettings = new UniteCreatorSettings();
		
		$params = array();
		$params[UniteSettingsUC::PARAM_CLASSADD] = "uc-text-colorpicker";
		
		$objSettings->addColorPicker("solid_color", "", "Solid Color", $params);
		
		$objSettings->addColorPicker("gradient_color1", "", "Gradient Color 1", $params);
		$objSettings->addColorPicker("gradient_color2", "", "Gradient Color 2", $params);
		
		
		$objOutput = new UniteSettingsOutputWideUC();
		$objOutput->init($objSettings);
		
		?>
			<div class='uc-paramsdialog-radio-chooser uc-radioset-wrapper' data-defaultchecked="none">
				
				<label>
					<?php _e("None","unlimited_elements")?>:
					<input type="radio" name="background_type" class="uc-param-radio uc-control" data-controlled-selector=".uc-controls-none" value="none" >
				</label>
				
				<label>
					<?php _e("Solid","unlimited_elements")?>:
					<input type="radio" name="background_type" class="uc-param-radio uc-control" data-controlled-selector=".uc-controls-solid" value="solid" >
				</label>
				
				<label>
					<?php _e("Gradient","unlimited_elements")?>
					<input type="radio" name="background_type" class="uc-param-radio uc-control" data-controlled-selector=".uc-controls-gradient" value="gradient">
				</label>
				
			</div>

			<div class="uc-controls-none">
															
			</div>

			
			<div class="uc-controls-solid" style="display:none">
				
				<div class="vert_sap5"></div>
				
				<label><?php _e("Solid Color","unlimited_elements")?></label>
				
				<?php 
				$objOutput->drawSingleSetting("solid_color");
				?>
			</div>
			
			<div class="vert_sap5"></div>
			
			<div class="uc-dialogparam-horlist uc-controls-gradient" style="display:none">
				
				<label>
					<?php _e("Gradient Color1", "unlimited_elements")?>
					
					<?php $objOutput->drawSingleSetting("gradient_color1"); ?>
					
				</label>
				<label>
					<?php _e("Gradient Color2", "unlimited_elements")?>
				
					<?php $objOutput->drawSingleSetting("gradient_color2");?>
				
				</label>
			
			</div>
			
			<div class="vert_sap20"></div>
			<hr>
		<?php 
		
		$this->addHtmlSelector();
		
	}
	
	/**
	 * function for override
	 */
	protected function putBorderParam(){
		
		$this->addHtmlSelector();
	}
	
	
	/**
	 * function for override
	 */
	protected function putDateTimeParam(){
		
		?>
		
		<div class="unite-inputs-label">
			<?php echo __("Date Time", "unlimited_elements")?>:
		</div>
		
		<input type="text" name="default" value="" placeholder="YYYY-mm-dd HH:ii">
		
		<div class="unite-inputs-sap"></div>
		
		<i><?php _e("* The default value can be empty as well","unlimited_elements")?></i>
		
		
		<?php 	
	}
	
	/**
	 * function for override
	 */
	protected function putTextShadowParam(){

		$this->addHtmlSelector();
			
	}
	
	/**
	 * function for override
	 */
	protected function putBoxShadowParam(){
		
		$this->addHtmlSelector();
				
	}
	
	
	/**
	 * put dimentions param
	 * type can be padding or margin
	 */
	protected function putDimentionsParam($type = ""){
		
		$title = __("Margins","unlimited_elements");
		if($type == "padding")
			$title = "Padding";
		
		$extra = array();
		$extra["output_names"] = true;
		
		$objSettings = new UniteCreatorSettings();
		
		$objSettings->addDimentionsSetting("desktop", "", "Dimentions", $extra);
		$objSettings->addDimentionsSetting("tablet", "", "Tablet", $extra);
		$objSettings->addDimentionsSetting("mobile", "", "Mobile", $extra);
		
		$objOutput = new UniteSettingsOutputWideUC();
		$objOutput->init($objSettings);
			
		$checkID = "check_dimentions_{$type}_is_responsive";
		
		?>
		
		<label for="<?php echo esc_attr($checkID)?>">
			<input id="<?php echo esc_attr($checkID)?>" type="checkbox" class="uc-param-checkbox uc-control" data-controlled-selector=".uc-responsive-controls,.uc-label-desktop" name="is_responsive">
			<?php _e("Responsive Control", "unlimited_elements")?>
		</label>
		
		<div class="unite-inputs-sap"></div>
		
		<div class="unite-inputs-label">
			<?php echo $title.__(" Default Values", "unlimited_elements")?>:
		</div>
		
		<div class="unite-inputs-sap"></div>
		
		<div class="unite-inputs-label uc-label-desktop" style="display:none">
			<?php esc_html_e("Desktop", "unlimited_elements")?>:
		</div>
		
		<?php 
		$objOutput->drawSingleSetting("desktop");
		?>
				
		
		<div class="uc-responsive-controls" style="display:none">
				
				<div class="unite-inputs-sap"></div>
		
				<div class="unite-inputs-label">
					<?php esc_html_e("Tablet", "unlimited_elements")?>:
				</div>
				
				<?php 
				$objOutput->drawSingleSetting("tablet");
				?>
				
				<div class="unite-inputs-sap"></div>
				
				<div class="unite-inputs-label">
					<?php esc_html_e("Mobile", "unlimited_elements")?>:
				</div>
								
				<?php 
				$objOutput->drawSingleSetting("mobile");
				?>
		</div>
		
		<?php $this->addHtmlSelector()?>
		
		<?php
	}	
	
	
	/**
	 * put elementor typography param field
	 */
	protected function putTypographyParamField(){
		?>
		
		<!-- selector 1 -->
		
		<div class="unite-inputs-label">
			
			<?php esc_html_e("CSS Selector", "unlimited_elements")?>:
		</div>		
		
		<input type="text" name="selector1" value="">
		
		<!-- selector 2 -->
		
		<div class="unite-inputs-sap"></div>
						
		<div class="unite-inputs-label">
			
			<?php esc_html_e("CSS Selector 2 (optional)", "unlimited_elements")?>:
		</div>		
		
		<input type="text" name="selector2" value="">
		
		<!-- selector 3 -->
		
		<div class="unite-inputs-sap"></div>
		
		<div class="unite-inputs-label">
			
			<?php esc_html_e("CSS Selector 3 (optional)", "unlimited_elements")?>:
		</div>		
		
		<input type="text" name="selector3" value="">
		
		<div class="unite-dialog-description-right">
			* <?php esc_html_e("The selector that the typography field will be related to. Can be related to several html tags.", "unlimited_elements")?>
		</div>
		
		<?php 
	}
	
	/**
	 * put param content
	 */
	protected function putParamFields($paramType){
	
		switch($paramType){
			
			case self::PARAM_TYPOGRAPHY:
				$this->putTypographyParamField();
			break;
			default:
				parent::putParamFields($paramType);
			break;
		}
	
	}
	
	
	/**
	 * init by addon type
	 * function for override
	 */
	protected function initByAddonType($addonType){
		
		if($addonType != GlobalsUnlimitedElements::ADDONSTYPE_ELEMENTOR)
			return(false);
		
		$this->option_putAdminLabel = false;
				
	}
	
	
}