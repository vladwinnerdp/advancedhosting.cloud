<?php
/**
 * @package Unlimited Elements
 * @author UniteCMS.net
 * @copyright (C) 2017 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ELEMENTS_INC') or die('Restricted access');


if($this->showHeader)
	$this->putHeaderHtml();
else
	require HelperUC::getPathTemplate("header_missing");

$slot1AddHtml = "";
if($this->isTestData1 == false)
	$slot1AddHtml = "style='display:none'";


$styleShow = "";
$styleHide = "style='display:none'";

$urlBack = HelperUC::getViewUrl_Addons($addonType);
if(!empty($objAddonType->addonView_urlBack))
	$urlBack = $objAddonType->addonView_urlBack;

	$textEditThis = esc_html__("Edit This ", "unlimited_elements"). $this->textSingle;
	
	$textBackTo = esc_html__("Back To ", "unlimited_elements"). $this->textPlural .esc_html__(" List", "unlimited_elements")

?>

<div id="uc_testaddon_wrapper" class="uc-testaddon-wrapper">

<?php if($this->showToolbar):?>

<div class="uc-testaddon-panel">
		
		<a href="<?php echo esc_attr($urlEditAddon)?>" class="unite-button-secondary" ><?php echo $textEditThis?></a>
		<a class="unite-button-secondary uc-button-cat-sap" href="<?php echo esc_attr($urlBack)?>"><?php esc_html_e($textBackTo, "unlimited_elements");?></a>
		
		<a id="uc_button_preview" href="javascript:void(0)" class="unite-button-secondary" <?php echo UniteProviderFunctionsUC::escAddParam($isPreviewMode?$styleHide:$styleShow)?>><?php esc_html_e("To Preview", "unlimited_elements")?></a>
		<a id="uc_button_close_preview" href="javascript:void(0)" class="unite-button-secondary" <?php echo UniteProviderFunctionsUC::escAddParam($isPreviewMode?$styleShow:$styleHide)?>><?php esc_html_e("Hide Preview", "unlimited_elements")?></a>
		
		<a id="uc_button_preview_tab" href="javascript:void(0)" class="unite-button-secondary uc-button-cat-sap"><?php esc_html_e("Preview New Tab", "unlimited_elements")?></a>
		
		<span id="uc_testaddon_slot1" class="uc-testaddon-slot" <?php echo UniteProviderFunctionsUC::escAddParam($slot1AddHtml)?>>
			<a id="uc_testaddon_button_restore" href="javascript:void(0)" class="unite-button-secondary"><?php esc_html_e("Restore Data", "unlimited_elements")?></a>
			<span id="uc_testaddon_loader_restore" class="loader-text" style="display:none"><?php esc_html_e("loading...")?></span>
			<a id="uc_testaddon_button_delete" href="javascript:void(0)" class="unite-button-secondary"><?php esc_html_e("Delete Data", "unlimited_elements")?></a>
			<span id="uc_testaddon_loader_delete" class="loader-text" style="display:none"><?php esc_html_e("deleting...")?></span>
		</span>
		
		<a id="uc_testaddon_button_save" href="javascript:void(0)" class="unite-button-secondary"><?php esc_html_e("Save Data", "unlimited_elements")?></a>
		<span id="uc_testaddon_loader_save" class="loader-text" style="display:none"><?php esc_html_e("saving...")?></span>
		
		<a id="uc_testaddon_button_clear" href="javascript:void(0)" class="unite-button-secondary"><?php esc_html_e("Clear", "unlimited_elements")?></a>
	
</div>

<?php endif; ?>

<form name="form_test_addon">

<?php 

	//put helper editor if needed
	
	if($isNeedHelperEditor)
		UniteProviderFunctionsUC::putInitHelperHtmlEditor();

    $addonConfig->putHtmlFrame(); 
?>

</form>

</div>

<script type="text/javascript">

	jQuery(document).ready(function(){

		var objTestAddonView = new UniteCreatorTestAddon();
		objTestAddonView.init();

		<?php if($isPreviewMode == true):?>
		jQuery("#uc_button_preview").trigger("click");
		<?php endif?>
		
	});
	
		
</script>

			



