"use strict";

/**
 * browser object
 */
function UniteCreatorGridPanel(){
	
	var g_objPanel, g_objBody, g_objHead;
	var t = this, g_objPaneGridSettings, g_objButtonHide, g_objGridBuilderIframeWrapper;
	var g_objButtonShow, g_objButtonClose, g_objHeaderLink, g_objBuffer;
	var g_objSettings = {}, g_paneNames = {};
	
	
	this.events = {
			SETTINGS_CHANGE: "panel_settings_change",
			SETTINGS_HTML_LOADED: "settings_html_loaded",
			AFTER_OPEN_SETTINGS: "after_open_settings",
			HEAD_MOUSEOVER: "head_mouseover",
			HEAD_MOUSEOUT: "head_mouseout",
			PANEL_MOUSEOUT: "mouse_mouseout",
			PANEL_MOUSEOVER: "mouse_mouseover",
			ACTION_BUTTON_CLICK: "action_button_click",
			SWITCH_PANE: "switch_pane",
			SHOW: "show_panel",
			HIDE: "hide_panel"
	};
	
	var g_options = {
			allow_undock: false
	};
	
	var g_temp = {
			isInited:false,
			bodyMarginTop:1,
			bodyMarginBottom:0,
			bodyHeight: null,
			headHeight:null,
			bottomHeight:null,
			baseWidth: 250,
			baseTop: 50,
			animation_speed: 500,
			minWidth: 200,
			isDocked: true,
			isHidden: false,
			enableTriggerChange: true,
			command_after_load: null,		//run command after load settings
			pane_enable_trigger_change: true	//disable trigger change by passing params
	};
	
	if(!g_ucAdmin)
		var g_ucAdmin = new UniteAdminUC();
	
	/**
	 * validate inited
	 */
	function validateInited(){
		
		g_ucAdmin.validateDomElement(g_objPanel, "grid panel");
		
	}
	
	/**
	 * validate that pane exists
	 */
	function validatePane(name){
		
		validateInited();
		g_ucAdmin.validateObjProperty(g_objSettings, name, "pane");
		
	}
		
	
	/**
	 * set setting values
	 */
	function setSettingsValues(paneName, values, noclear){
		
		var isClear = false;
		if(typeof values == "undefined")
			isClear = true;
		
		validatePane(paneName);
		
		g_temp.enableTriggerChange = false;
		
		var objSettings = g_ucAdmin.getVal(g_objSettings, paneName);
		
		if(objSettings){
			if(isClear == true)
				g_objSettings[paneName].clearSettingsInit();
			else
				g_objSettings[paneName].setValues(values, noclear);
		}
		
		g_temp.enableTriggerChange = true;
	}
	
	
	/**
	 * set single setting value
	 */
	function setSingleSettingValue(paneName, settingName, settingValue){
		
		validatePane(paneName);
		
		g_temp.enableTriggerChange = false;
		
		var objSettings = g_ucAdmin.getVal(g_objSettings, paneName);
		
		objSettings.setSingleSettingValue(settingName, settingValue);
		
		g_temp.enableTriggerChange = true;
		
	}

	
	
	/**
	 * external alias
	 */
	this.setSettingsValues = function(paneName, values){
		setSettingsValues(paneName, values);
	};
	
	
	/**
	 * update some setting if active
	 */
	this.setSettingValueIfActive = function(paneName, objectID, settingName, settingsValue){
		
		validatePane(paneName);
		
		var isActive = t.isExactPaneActive(paneName, objectID);
		if(isActive == false)
			return(false);
		
		setSingleSettingValue(paneName, settingName, settingsValue);
		
	};
	
	
	/**
	 * get active pane name
	 */
	this.getActivePaneName = function(){
		var objPane = getActivePane();
		var paneName = objPane.data("name");
		
		return(paneName);
	};
	
	
	/**
	 * get settings values
	 */
	function getSettingsValues(paneName){
		validatePane(paneName);
		
		var values = g_objSettings[paneName].getSettingsValues();
		
		return(values);
	}
	
	
	
	/**
	 * external alias
	 */
	this.getSettingsValues = function(paneName){
		return getSettingsValues(paneName);
	};
	
	
	
	/**
	 * check if pane active and has the same id
	 */
	this.isExactPaneActive = function(paneName, objectID){
		
		if(!objectID)
			var objectID = null;
		
		var data = getActivePaneData();
		if(paneName == data.name && objectID == data.objectID)
			return(true);
		
		return(false);
	};
	
	
	/**
	 * get settings and active id datga
	 */
	this.getPaneData = function(paneName){
		
		var objPane = getPaneByName(paneName);
		
		var output = {};
		output.objectID = objPane.data("objectid");
		output.settings = getSettingsValues(paneName);
		
		return(output);
	};
	
	/**
	 * get active pane data
	 */
	this.getActivePaneData = function(){
		
		var objData = getActivePaneData(null, true);
		return(objData);
	}
	
	/**
	 * get active pane
	 */
	function getActivePane(){
		
		var objPane = g_objPanel.find(".uc-grid-panel-pane.uc-current-pane");
		
		g_ucAdmin.validateDomElement(objPane, "current pane");
		
		return(objPane);
	}
	
	
	/**
	 * get active pane
	 */
	function getActivePaneData(objPane, isGetSettings){
		
		if(!objPane)
			var objPane = getActivePane();
			
		g_ucAdmin.validateDomElement(objPane, "current pane");
		
		if(objPane.length != 1)
			throw new Error("There should be only one active pane");
		
		var paneName = objPane.data("name");
		var objectID = objPane.data("objectid");
		if(!objectID)
			objectID = null;
		
		var paneParams = objPane.data("pane_params");
		
		var output = objPane.data("options");
		if(!output)
			output = {};
		
		output["name"] = paneName;
		output["objectID"] = objectID;
		output["pane_params"] = paneParams;
		
		if(isGetSettings === true)
			output["settings"] = getSettingsValues(paneName);
		
		return(output);
	}
	
	
	/**
	 * get pane by name
	 */
	function getPaneByName(paneName){
		
		var paneClass = ".uc-grid-panel-pane.uc-pane-" + paneName;
		
		var objPane = g_objPanel.find(paneClass);
		g_ucAdmin.validateDomElement(objPane, paneClass);
		 
		return(objPane);
	}
	
	
	/**
	 * get pane settings object
	 */
	function getPaneSettingsObject(objPane){
		var name = objPane.data("name");
		var objSettings = g_objSettings[name];
		if(!objSettings)
			throw new Error("Settings object not found! "+ name);
		
		return(objSettings);
	}
	
	
	/**
	 * switch pane
	 */
	function switchPane(paneName, customTitle, params){
		
		var objPane = null;
		
		if(!paneName){
			var objPane = getActivePane();
			var paneName = objPane.data("name");
		}
		
		var classCurrentPane = "uc-current-pane";
		var classChildPane = "uc-child-pane";
		
		if(!objPane)
			objPane = getPaneByName(paneName);
		
		var objData = getActivePaneData(objPane);
		
		
		if(objPane.hasClass(classCurrentPane) == false){
			var otherPanes = g_objPanel.find(".uc-grid-panel-pane").not(objPane);
			
			otherPanes.hide().removeClass(classCurrentPane);
			objPane.show().addClass(classCurrentPane);
		}
		
		//set child pane class
		var isChildPane = g_ucAdmin.getVal(params, "is_child_pane");
		if(isChildPane == true)
			objPane.addClass(classChildPane);
		else
			objPane.removeClass(classChildPane);
		
		//set header
		var title = objPane.data("title");
		
		if(!customTitle)
			customTitle = g_ucAdmin.getVal(params,"custom_title");
		
		if(customTitle){
			title = customTitle;
			params.custom_title = customTitle;
		}
		
		//save params
		objPane.data("pane_params", params);
		
		var prefix = g_ucAdmin.getVal(params, "add_title_prefix");
		if(prefix)
			title = prefix+" "+title;
		
		var replaceTitle = g_ucAdmin.getVal(params, "replace_title");
		if(replaceTitle)
			title = replaceTitle;
		
		g_ucAdmin.validateNotEmpty(title, "pane title");

		var className = "uc-grid-panel-head uc-panetype-"+paneName;
		g_objHead.attr("class", className);
				
		var objTitleText = g_objHead.find(".uc-grid-panel-head-text");
		objTitleText.html(title);
		
		//hide header if no head
		var isNoHead = g_ucAdmin.getVal(objData, "no_head");
		
		if(isNoHead === true)
			hideHeader();
		else
			showHeader();
		
		//prepare header link
		var editLink = g_ucAdmin.getVal(params, "header_edit_link");
		if(editLink){
			g_objHeaderLink.addClass("uc-link-active");
			g_objHeaderLink.attr("href", editLink);
		}else{
			g_objHeaderLink.removeClass("uc-link-active");
		}
		
		//trigger event
		triggerEvent(t.events.SWITCH_PANE, paneName);
		
		return(objPane);
	}
	
	
	
	/**
	 * back to parent pane
	 */
	function backToParentPane(){
		
		var objPaneData = getActivePaneData();
		var paneParams = objPaneData["pane_params"];
			
		var parentPaneName = g_ucAdmin.getVal(paneParams, "parent_pane");
		
		g_ucAdmin.validateNotEmpty(parentPaneName, "parent pane name");
		var objParentPane = getPaneByName(parentPaneName);
		
		var parentPaneParams = objParentPane.data("pane_params");
			
		switchPane(parentPaneName, null, parentPaneParams);
		
	}
	
	/**
	 * is current pane is child
	 */
	function isActivePaneIsChildPane(){
		
		var objPane = getActivePane();
		
		if(objPane.hasClass("uc-child-pane"))
			return(true);
		else
			return(false);
	}
	
	
	function _________MOVEMENT_________(){}
	
	
	/**
	 * hide panel header
	 */
	function hideHeader(){
		
		if(g_objPanel.hasClass("uc-no-head"))
			return(false);
		
		g_objPanel.addClass("uc-no-head");
	}
	
	
	/**
	 * show panel header
	 */
	function showHeader(){
		
		if(g_objPanel.hasClass("uc-no-head") == false)
			return(false);
		
		g_objPanel.removeClass("uc-no-head");
		
	}
	
	
	/**
	 * return if the header is visible
	 */
	function isHeaderVisible(){
		
		if(g_objPanel.hasClass("uc-no-head") == true)
			return(false);
		else
			return(true);
	}
	
	
	/**
	 * hide panel in docked state
	 */
	function hidePanelDocked(){
		
		var width = g_objPanel.width();
		
		var objCss = {};
		objCss["top"] = g_temp.baseTop+"px";
		objCss["left"] = -width+"px";
		
		//just for case
		if(g_objButtonShow)
			g_objButtonShow.hide();
		
		g_objPanel.animate(objCss, g_temp.animation_speed,null,function(){
			
			if(g_objButtonShow)
				g_objButtonShow.toggle("slide");
			
		});	
		
		setBuilderSize(0, true);
	}
	
	
	/**
	 * hide panel undocked state
	 */
	function hidePanelUndocked(){
		
		if(g_objButtonShow)
			g_objButtonShow.hide();
		
		g_objPanel.fadeOut(g_temp.animation_speed,function(){
			
			if(g_objButtonShow)
				g_objButtonShow.toggle("slide");
			
			g_objPanel.hide();
		});
		
	}
	
	
	
	/**
	 * hide panel in side
	 */
	function hidePanel(){
		
		//hide tinymce panel if exists
		jQuery(".mce-floatpanel").hide();
		
		if(g_temp.isDocked == true)
			hidePanelDocked();
		else
			hidePanelUndocked();
		
		g_temp.isHidden = true;
		
		triggerEvent(t.events.HIDE);
	}
	
	
	
	/**
	 * show the panel
	 */
	function showPanel(){
		
		if(g_temp.isHidden == false)
			return(false);
		
		if(g_objPanel.is(":visible") == false){
			g_objPanel.show();
			setPanelSizes();
		}
		
		var width = g_objPanel.width();
				
		//set init position
		var objCss = {};
		objCss["top"] = g_temp.baseTop+"px";
		objCss["left"] = -width+"px";
		g_objPanel.css(objCss);
		
		
		var objCss = {};
		objCss["top"] = g_temp.baseTop+"px";
		objCss["left"] = "0px";
						
		if(g_objButtonShow){
			if(g_objButtonShow.is(":visible"))
				g_objButtonShow.toggle("slide");
		}
		
		g_objPanel.animate(objCss, g_temp.animation_speed);
		
		setBuilderSize(width, true);
				
		g_temp.isHidden = false;
		
		triggerEvent(t.events.SHOW);
	}
	
	
	
	/**
	 * check is docked or not by position
	 */
	function isDocked(ui){
		
		var bottom = g_objPanel.css("bottom");
		
		if(bottom != "0px" && bottom != "0" && bottom != 0)
			return(false);
		
		if(ui){
			var left = ui.position.left;
			var top = ui.position.top;
		}
		else{
			var pos = g_objPanel.position();
			
			var top = pos.top;
			var left = pos.left;
			
		}
		
		if(left != 0)
			return(false);
		
		if(top != g_temp.baseTop)
			return(false);
		
		return(true);
	}
	
	
	/**
	 * undock the panel
	 */
	function undock(){
		
		g_temp.isDocked = false;
		
		setBuilderSize(0, true);
	}
	
	
	/**
	 * check if docked state changed, if do, change var and run events
	 */
	function handleDockedState(ui){
		
		if(g_temp.isDocked == false)
			return(false);
		
		var docked = isDocked();
		
		if(docked == false)
			undock();
		
	}
	
	/**
	 * set builder size
	 */
	function setBuilderSize(panelWidth, isAnimation){
		
		var objCssBuilder = {"padding-left":panelWidth+"px"};
		
		if(isAnimation)
			g_objGridBuilderIframeWrapper.animate(objCssBuilder, g_temp.animation_speed);	
		else
			g_objGridBuilderIframeWrapper.css(objCssBuilder);
	}
	
	
	/**
	 * set settings max height
	 */
	function setSettingsSize(objSettings){
		if(!objSettings)
			return(false);
		
		objSettings.setAccordionMaxHeight(g_temp.bodyHeight);
	}
	
	
	/**
	 * set panel max height
	 */
	function setPanelSizes(){
		
		setBodySize();
		
		//set settings size
		jQuery.each(g_objSettings, function(index, objSettings){
			setSettingsSize(objSettings);
		});
		
	}
	
	
	/**
	 * set body size
	 */
	function setBodySize(){
		
		var panelHeight = g_objPanel.height();
		var headHeight = g_temp.headHeight;
		var isHeadVisible = isHeaderVisible();
				
		if(isHeadVisible == false)
			headHeight = 0;
		else if(headHeight === null){
			headHeight = g_objHead.height();
		}
		
		var bottomHeight = g_temp.bottomHeight;
		if(bottomHeight === null){
			var objBottomPanel = g_objPanel.find(".uc-grid-panel-bottom");
			if(objBottomPanel.length)
				bottomHeight = objBottomPanel.height();
			else
				bottomHeight = 0;
			
			g_temp.bottomHeight = bottomHeight;
		}
				
		var bodyHeight = panelHeight - headHeight - g_temp.bodyMarginTop - g_temp.bodyMarginBottom - bottomHeight;
		var bodyPosY = headHeight + g_temp.bodyMarginTop;
		
		
		//set body height
		var isFirstSet = g_temp.bodyHeight === null;
		
		var bodyCss = {};
		
		if(isFirstSet == true){
			bodyCss["top"] = bodyPosY+"px";
			bodyCss["display"] = "block";
		}
		
		bodyCss["height"] = bodyHeight+"px";
		
		g_objBody.css(bodyCss);
		
		g_temp.bodyHeight = bodyHeight;
		
	}
	
	
	/**
	 * load pane data
	 */
	function loadPaneData(objPane, data){
				
		g_temp.enableTriggerChange = false;
		
		var objLoader = objPane.children(".uc-grid-panel-pane-loader");
		g_ucAdmin.validateDomElement(objLoader, "pane loader");
		
		destroyPane(objPane);
		
		var objContent = objPane.children(".uc-grid-panel-pane-content");
		
		//check for html settings
		var htmlSettings = g_ucAdmin.getVal(data, "html_settings");
				
		if(htmlSettings){
			
			objContent.html(htmlSettings);
			initPaneSettingsObject(objPane);
			
			triggerEvent(t.events.AFTER_OPEN_SETTINGS, objPane);
			
		}else{		//html settings don't found, load settings
			
			objLoader.show();
			
			data.is_inside_grid = true;
			
			var action = objPane.data("action");
			g_ucAdmin.ajaxRequest(action, data, function(response){
				
				objLoader.hide();
				var htmlSettings = response.html;
				var paneData = getActivePaneData(objPane);
				var eventData = {"object_id":paneData.objectID, "html_settings":htmlSettings};
				
				triggerEvent(t.events.SETTINGS_HTML_LOADED, eventData);
				
				objContent.html(htmlSettings);
				initPaneSettingsObject(objPane);
				
				triggerEvent(t.events.AFTER_OPEN_SETTINGS, objPane);
				
			});
			
		}
		
		g_temp.enableTriggerChange = true;
		
	}
	
	
	/**
	 * set pane data after open
	 */
	function setPaneData(objPane, objValues, paneName){
				
		var paneAction = objPane.data("action");
				
		if(paneAction){
			loadPaneData(objPane, objValues);
		}else{
			
			if(!paneName)
				paneName = objPane.data("name");
			
			setSettingsValues(paneName, objValues);
			
			triggerEvent(t.events.AFTER_OPEN_SETTINGS, objPane);
			
		}
		
	}
	
	
	/**
	 * blink active panel for a minute
	 */
	function blink(){
		
		var classAnimation = "unite-animation-blink";
		g_objHead.addClass(classAnimation);
		
		setTimeout(function(){
			g_objHead.removeClass(classAnimation);
		},1000);
		
	}
	
	/**
	 * open the panel
	 */
	this.open = function(paneName, objValues, objectID, customTitle, params){
		
		if(!objValues && objValues !== null)
			objValues = {};
		
		if(!objectID)
			objectID = "";
		
		//check if current panel active and opened
		var isActive = t.isExactPaneActive(paneName, objectID);
		if(isActive == true){
			
			if(g_temp.isHidden == true)
				showPanel();
			else
				blink();
			
			return(false);
		}
			
		g_temp.pane_enable_trigger_change = true;
		
		//settings by params
		if(params){
			var command = g_ucAdmin.getVal(params, "command");
			if(command)
				g_temp.command_after_load = command;
			
			var disable_trigger_change = g_ucAdmin.getVal(params, "disable_trigger_change");
			if(disable_trigger_change === true)
				g_temp.pane_enable_trigger_change = false;
				
		}
		
		var objPane = switchPane(paneName, customTitle, params);
		var paneObjectID = objPane.data("objectid");
		
		if((!paneObjectID || paneObjectID !== objectID) && objValues !== null)
			setPaneData(objPane, objValues, paneName);
		
		objPane.data("objectid", objectID);
		
		if(g_temp.isHidden == true)
			showPanel();
		
	};
	
	/**
	 * tells if the panel is visible
	 */
	this.isVisible = function(){
		
		return(!g_temp.isHidden);
	};
	
	
	/**
	 * toggle
	 */
	this.toggle = function(paneName, objValues, objectID, customTitle, params){
		
		if(g_temp.isHidden == true)
			this.open(paneName, objValues, objectID, customTitle, params);
		else{
			
			var isActive = t.isExactPaneActive(paneName, objectID);
			
			if(isActive == true)
				hidePanel();
			else
			  this.open(paneName, objValues, objectID, customTitle, params);
		}
	};
	
	/**
	 * hide panel
	 */
	this.close = function(){
		hidePanel();
	}
	
	/**
	 * hide panel if the pane with id is active
	 */
	this.hideIfActive = function(objectID){
		
		var data = getActivePaneData();
		if(objectID == data.objectID)
			hidePanel();
	};
	
	function _____BUFFER_____(){}
	
	/**
	 * copy settings
	 */
	function copySettings(){
		
		var paneData = getActivePaneData();
		var paneName = paneData.name;
		var objValues = getSettingsValues(paneName);
		var objectID = paneData["objectID"];
		
		g_objBuffer.store(paneName, objectID, null, objValues);
	}
	
	/**
	 * paste settings
	 */
	function pasteSettings(){
		
		var paneData = getActivePaneData();
		var activePaneName = paneData.name;
		
		var storedType = g_objBuffer.getStoredType();
		if(storedType != activePaneName)
			return(false);
		
		var storedData = g_objBuffer.getStoredContent();
		if(!storedData)
			return(false);
		
		var objSettingsValues = g_ucAdmin.getVal(storedData,"content_object");
		
		if(!objSettingsValues)
			return(false);
		
		t.setSettingsValues(activePaneName, objSettingsValues);
		
		triggerActiveSettingsChange();
	}
	
	
	function _____EVENTS_____(){}
	
	
	/**
	 * on action button click
	 */
	function onActionButtonClick(){
				
		var objButton = jQuery(this);
		
		var action = objButton.data("action");
		if(!action){
			trace(objButton);
			throw new Error("No action found for button");			
		}
		
		var params = objButton.data("params");
		if(!params)
			params = {};
		
		var actionParam = objButton.data("actionparam");
		if(actionParam)
			params["action_param"] = actionParam;
		
		var isInternal = checkInternalButtonAction(action, params);
		
		if(isInternal == false)
			triggerEvent(t.events.ACTION_BUTTON_CLICK, [action, params]);
		
	}
	
	
	/**
	 * handle child pane change
	 */
	function handleChildPaneChange(paneParams){
		
		var parentPaneName = paneParams["parent_pane"];
		var activePaneData = getActivePaneData(null, true);
		var childPaneName = activePaneData.name;
		
		if(childPaneName != "addon-settings"){
			throw new Error("The child pane can be now only addon-settings");
		}
		
		//set parent setting
		var objParentSettings = g_objSettings[parentPaneName];
		var parentSettingName = paneParams["parent_setting_name"];
		var parentChangingSettingName = paneParams["parent_changing_setting_name"];
		var addonData = paneParams["addon_data"];
		
		var settingsChild = activePaneData.settings;
		var objAddonConfig = new UniteCreatorAddonConfig();
		var addonDataNew = objAddonConfig.getAddonDataFromSettingsValues(settingsChild);
		addonDataNew = objAddonConfig.setNewAddonData(addonData, addonDataNew);
		addonDataNew["output"] = null;
				
		objParentSettings.setSingleSettingValue(parentSettingName, addonDataNew);
		
		//trigger parent event
		var objParentPane = getPaneByName(parentPaneName);
		var parentPaneData = getActivePaneData(objParentPane);
		
		
		var sendParams = {};
		sendParams["object_name"] = parentPaneName;
		sendParams["is_instant"] = false;		
		sendParams["object_id"] = parentPaneData["objectID"];
		sendParams["name"] = parentChangingSettingName;
		
		triggerEvent(t.events.SETTINGS_CHANGE, sendParams);
		
	}
	
	
	/**
	 * on setting change
	 */
	function onSettingChange(objectName, params, isInstant){
		
		if(g_temp.enableTriggerChange == false)
			return(true);
		
		if(g_temp.pane_enable_trigger_change == false)
			return(true);
		
		if(g_temp.isHidden == true)
			return(true);
		
		if(!params)
			params = {};
		
		if(!isInstant)
			var isInstant = false;

		params["object_name"] = objectName;
		params["is_instant"] = isInstant;		
		
		//add object ID
		var objPane = getPaneByName(objectName);
		var data = getActivePaneData(objPane);
		
		var paneParams = data.pane_params;
		var isChildPane = g_ucAdmin.getVal(paneParams, "is_child_pane");
				
		//handle child pane
		if(isChildPane === true){
			
			var parentPaneName = paneParams["parent_pane"];
			
			if(isInstant == false)
				handleChildPaneChange(paneParams);
			
			
		}else{
			
			params["object_id"] = data["objectID"];
			
			triggerEvent(t.events.SETTINGS_CHANGE, params);
		}
					
	}
	
	/**
	 * trigger current settings change
	 */
	function triggerActiveSettingsChange(){
		
		var data = getActivePaneData();
		
		var params = {};
		params["object_name"] = data.name;
		params["object_id"] = data.objectID;
		
		triggerEvent(t.events.SETTINGS_CHANGE, params);
	}
	
	
	/**
	 * on resize
	 */
	function onResizableResize(event, ui){
		
		var panelWidth = ui.size.width;
		
		//ui.size.height = ui.originalSize.height;
		
		if(g_temp.isDocked)
			setBuilderSize(panelWidth);
		
		handleDockedState(ui);
	}
	
	
	/**
	 * init settings object events
	 */
	function initEvents_settings(objSettings, paneName){
		
		if(!objSettings)
			return(false);
		
		//change
		objSettings.setEventOnChange(function(event, params){
			
			onSettingChange(paneName, params);
			
		});
		
		//instant change
		objSettings.onEvent(objSettings.events.INSTANT_CHANGE, function(event, params){
			
			onSettingChange(paneName, params, true);
			
		});
		
		
		/**
		 * open child panel
		 */
		objSettings.onEvent(objSettings.events.OPEN_CHILD_PANEL, function(event, params){
			
			var paneName = params["pane_name"];
			var sendData = g_ucAdmin.getVal(params, "send_data");
			var title = g_ucAdmin.getVal(params, "panel_title");
			var panelData = g_ucAdmin.getVal(params, "panel_data");
			var settingName = g_ucAdmin.getVal(params, "setting_name");
			var changingSettingName = g_ucAdmin.getVal(params, "changing_setting_name");
			var addonData = g_ucAdmin.getVal(params, "addon_data");
			var parentData = getActivePaneData();
			var parentObjectID = g_ucAdmin.getVal(parentData, "objectID");
			var addonName = g_ucAdmin.getVal(addonData, "name");
			
			if(!panelData)
				var panelData = {};
			
			panelData["is_child_pane"] = true;
			panelData["parent_pane"] = t.getActivePaneName();
			panelData["parent_setting_name"] = settingName;
			panelData["parent_changing_setting_name"] = changingSettingName;
			panelData["addon_data"] = addonData;
			
			var childObjectID = null;
			if(parentObjectID && addonName)
				childObjectID = parentObjectID + "_" + addonName;
			
			t.open(paneName, sendData, childObjectID, title, panelData);
			
		});
		
	}
	
	
	/**
	 * run on after open settings, check command
	 */
	function onAfterLoadSettings(event, objPane){
				
		if(!g_temp.command_after_load)
			return(true);
		
		objPane = jQuery(objPane);
		var objSettings = getPaneSettingsObject(objPane);
		
		objSettings.runCommand(g_temp.command_after_load);
		
		g_temp.command_after_load = null;
	}
		
	/**
	 * run action
	 */
	function runAction(action){
		
		switch(action){
			case "copy":
				copySettings();
			break;
			case "paste":
				pasteSettings();
			break;
			default:
				trace("action not found: "+action);
			break;
		}
		
	}
	
	
	
	/**
	 * on run some action from button
	 */
	function checkInternalButtonAction(action, params){
		
		switch(action){
			case "back_to_parent":
				backToParentPane();
			break;
			default:
				return(false);
			break;
		}
		
		return(true);
	}
	
	
	/**
	 * on action icon click
	 */
	function onIconActionClick(){
		var objIcon = jQuery(this);
		
		var action = objIcon.data("action");
		runAction(action);
	}
	
	
	/**
	 * init events
	 */
	function initEvents(){
		
		jQuery( window ).resize(setPanelSizes);
		
		
		//html / show buttons
		if(g_objButtonHide)
			g_objButtonHide.on("click",hidePanel);
		
		if(g_objButtonShow)
			g_objButtonShow.on("click",showPanel);
		
		if(g_objButtonClose)
			g_objButtonClose.on("click",hidePanel);
		
		//set resizable
		var optionsResizable = {
				minWidth: g_temp.minWidth,
				resize: onResizableResize
		};
		
		//mouse events
		g_objHead.on("mouseenter",function(){
			triggerEvent(t.events.HEAD_MOUSEOVER);
		});
		
		g_objHead.on("mouseleave",function(){
			triggerEvent(t.events.HEAD_MOUSEOUT);
		});
		
		g_objPanel.on("mouseenter", function(){
			triggerEvent(t.events.PANEL_MOUSEOVER);
		});
		
		g_objPanel.on("mouseleave", function(){
			triggerEvent(t.events.PANEL_MOUSEOUT);
		});
		
		
		if(g_options.allow_undock == false)
			optionsResizable["handles"] = "w,e";
		
		g_objPanel.resizable(optionsResizable);
		
		t.onEvent(t.events.AFTER_OPEN_SETTINGS, onAfterLoadSettings);
		
		//copy paste icons
		g_objPanel.on("click", ".uc-panel-icon-action", onIconActionClick);
		
		//action button click
		g_objPanel.on("click", ".uc-panel-action-button", onActionButtonClick);
				
	}
	
	
	/**
	 * trigger event
	 */
	function triggerEvent(eventName, params){
		
		if(!params)
			var params = getActivePaneData();
		
		g_objPanel.trigger(eventName, params);
	}
	
	
	/**
	 * on event name
	 */
	this.onEvent = function(eventName, func){
		
		validateInited();		
		
		g_objPanel.on(eventName,func);
	};
	
	
	
	function _____INIT_____(){}
	
	
	/**
	 * destroy pane
	 */
	function destroyPane(objPane){
		
		var name = objPane.data("name");
		
		//destroy settings
		var existingSettings = g_ucAdmin.getVal(g_objSettings, name);
		if(existingSettings){
			existingSettings.destroy();
			g_objSettings[name] = null;
		}
		
		//clear content
		var objContent = objPane.children(".uc-grid-panel-pane-content");
		objContent.html("");
		
	}
	
	
	/**
	 * init all objects
	 */
	function initObjects(objPanel){
		
		g_objPanel = objPanel;
		g_ucAdmin.validateDomElement(g_objPanel, "panel object");
		
		g_objBody = g_objPanel.children(".uc-grid-panel-body");
		g_ucAdmin.validateDomElement(g_objBody, "panel body");
		
		g_objHead = g_objPanel.children(".uc-grid-panel-head");
		g_ucAdmin.validateDomElement(g_objHead, "panel head");
		
		g_objGridBuilderIframeWrapper = g_objPanel.siblings(".uc-iframe-wrapper");
		g_ucAdmin.validateDomElement(g_objGridBuilderIframeWrapper, "grid builder outer");
		
		g_objButtonClose = g_objPanel.find(".uc-grid-panel-head-close");
		g_ucAdmin.validateDomElement(g_objButtonClose, "grid builder close");
			
		g_objHeaderLink = g_objHead.find(".uc-grid-panel-head-edit");
		g_ucAdmin.validateDomElement(g_objHeaderLink, "header edit link");
		
		g_objButtonHide = g_objPanel.find(".uc-panel-button-hide");
		if(g_objButtonHide.length)
			g_objButtonHide = null;
		
		g_objButtonShow = g_objPanel.siblings(".uc-grid-panel-show-handle");
		
		if(g_objButtonShow.length)
			g_objButtonShow = null;
	}
	
	
	/**
	 * init some options
	 */
	function initOptions(){
		
		g_temp.isHidden = !g_objPanel.is(":visible");
		
		//init options from data
		
		var objOptions = g_objPanel.data("options");
		g_ucAdmin.validateIsObject(objOptions, "objOptions");
		
		jQuery.extend(g_options, objOptions);
		
	}
	
	
	/**
	 * init pane settings object, after ajax load
	 */
	function initPaneSettingsObject(objPane){
		
		var name = objPane.data("name");
		
		//if existing exists, destroy settings object
		var existingSettings = g_ucAdmin.getVal(g_objSettings, name);
		if(existingSettings){
			trace(existingSettings);
			throw new Error("No settings shoud be before init!");
		}
		
		var objPaneContent = objPane.children(".uc-grid-panel-pane-content");
		var objSettingsWrapper = objPaneContent.children(".unite_settings_wrapper");
		
		if(objSettingsWrapper.length > 1){
			trace(objSettingsWrapper);
			throw new Error("Could be only 1 settings object in pane");
		}
		
		if(objSettingsWrapper.length == 0){
			g_objSettings[name] = null;
			return(false);
		}
		
		var objSettings = new UniteSettingsUC();
		objSettings.init(objSettingsWrapper);
		
		//init events
		initEvents_settings(objSettings, name);
		
		//set size
		if(g_temp.isInited == true)
			setSettingsSize(objSettings);
			
		
		g_objSettings[name] = objSettings;
		
	}
	
	
	
	/**
	 * init all panes
	 */
	function initPanes(){
		
		var objPanes = g_objPanel.find(".uc-grid-panel-pane");
		
		jQuery.each(objPanes, function(index, pane){			
			var objPane = jQuery(pane);
			
			//add in simple object
			var paneName = objPane.data("name");
			var paneTitle = objPane.data("title");
			g_paneNames[paneName] = paneTitle;
			
			initPaneSettingsObject(objPane);
		});
		
	}
	
	/**
	 * update placeholders
	 */
	this.updatePlaceholders = function(paneName, objPlaceholders){
		
		validatePane(paneName);
		
		var objSettings = g_objSettings[paneName];
		
		objSettings.updatePlaceholders(objPlaceholders);
	};
	
	/**
	 * returrn if the pane exists by name
	 */
	this.isPaneExists = function(paneName){
		
		var paneTitle = g_ucAdmin.getVal(g_paneNames, paneName);
		
		if(!paneTitle)
			return(false);
		else
			return(true);
				
	};
	
	/**
	 * init buffer, insert all panes types
	 */
	function initBuffer(){
		
		for(var name in g_paneNames){
			var title = g_paneNames[name];
			
			g_objBuffer.addType(name, title);
		}
		
		
	}
	
	
	/**
	 * init panel
	 */
	this.init = function(objPanel, objBuffer){
		
		g_objBuffer = objBuffer;
				
		initObjects(objPanel);
		
		initOptions();
		
		initPanes();
		
		initBuffer();
		
		setPanelSizes();
				
		initEvents();
		
		//run all js procedures related to current pane
		switchPane();
		
		g_temp.isInited = true;
	};
	
}

//------------------ copy / paste object ------------------

