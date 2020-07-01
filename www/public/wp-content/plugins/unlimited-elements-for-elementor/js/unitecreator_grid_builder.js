"use strict";

function UniteCreatorGridBuilder(){

	var g_objGrid, g_objStyle, g_options, g_objWrapper;
	var g_pageBuilder = null, g_objBrowser, g_objBrowserSections;	//addons browser
	var g_optionsCustom = {}, g_objRowStyleContainer, g_objColStyleContainer;
	var g_gridID, g_objSettingsGrid = new UniteSettingsUC();
	var g_objAddonConfig = new UniteCreatorAddonConfig();
	var g_objSettingsRow = new UniteSettingsUC(), g_panel = null;
	var g_objDialogRowSettings, g_objHistory = new UniteCreatorHistory();
	var g_objBuffer = null, g_objScroll, g_objBodyParent;
	
	var t = this;
	
	var g_vars = {
			class_col: "uc-grid-col",
			class_first_col: "uc-col-first",
			class_last_col: "uc-col-last",
			class_empty: "uc-col-empty",
			class_first_row: "uc-row-first",
			class_last_row: "uc-row-last",
			class_size_prefix:"uc-colsize-",
			id_prefix: "uc_addon_",
			addon_conetentid_prefix: "uc_addon_contentid",
			max_cols: 6,
			serial: 0,		//give serial numbers to addons
			shape_deviders:null
	};
	
	var g_temp = {
		init_counter: 0,
		is_live_view: false,
		indev: false,
		is_data_inited: false,
		page_params:{},		
		google_fonts:{},	
		icons:{},			//settings that hold delay before change
		settings_delay_row:["row_container_width","col_gutter","row_padding_top",
		                    "row_padding_bottom","space_between_addons",
		                    "padding_top_mobile","padding_bottom_mobile","row_class",
		                    "row_css","row_container_css","container_css"],
		settings_placeholders_grid:["row_padding_top","row_padding_bottom","col_gutter","space_between_addons"],
		settings_placeholders:["padding_top","padding_bottom","padding_left","padding_right","margin_top","margin_bottom","margin_left","margin_right",
		                       "shape_devider_top_height","shape_devider_bottom_height"],
		sizes: ["tablet","mobile"],
		addontype_bgaddon:"bg_addon",
		value_empty_array:"[[uc_empty_array]]"
	};
	
	this.events = {
			ROW_COLUMNS_UPDATED: "ROW_COLUMNS_UPDATED",
			ROWS_UPDATED: "ROWS_UPDATED",			//add, remove, reorder row
			ROW_ADDED: "ROW_ADDED",
			COL_ADDED: "COL_ADDED",
			COL_ADDONS_UPDATED: "ADDONS_UPDATED",	//updated col addons
			
			ROW_CONTAINER_DUPLICATED: "CONTAINER_DUPLICATED",
			ROW_CONTAINER_ADDED: "CONTAINER_ADDED",	//updated containers (sort,delete,remove,add)
			ROW_CONTAINERS_UPDATED: "CONTAINERS_UPDATES",	//updated containers (sort,delete,remove,add)
			ROW_CONTAINER_SETTINGS_UPDATED: "CONTAINER_SETTINGS_UPDATED",	//updated container settings
			
			BEFORE_REMOVE_ADDON: "BEFORE_REMOVE_ADDON",
			ELEMENT_REMOVED: "ELEMENT_REMOVED",		//on some addons removed
			ADDON_ADDED: "ADDON_ADDED",	//before duplicated addon data inserted
			ADDON_DUPLICATED: "ADDON_DUPLICATED",	//before duplicated addon data inserted
			COL_DUPLICATED: "COL_DUPLICATED",		//before duplicated column data added
			ROW_DUPLICATED: "ROW_DUPLICATED",		//before duplicated row data added
			GRID_SETTINGS_UPDATED: "GRID_SETTINGS_UPDATED",	//on row settings updated
			PAGE_PARAMS_UPDATED:"PAGE_PARAMS_UPDATED",
			ROW_SETTINGS_UPDATED: "ROW_SETTINGS_UPDATED",	//on row settings updated
			COL_SETTINGS_UPDATED: "COL_SETTINGS_UPDATED",	//on col settings updated
			ADDON_CONTAINER_SETTINGS_UPDATED: "ADDON_CONTAINER_SETTINGS_UPDATED",	//on addon container updated
			ADDON_SETTINGS_UPDATED: "ADDON_SETTINGS_UPDATED",	//on addon settings updated
			CHANGE_TAKEN: "CHANGE_TAKEN",			//on some sort change taken (enable save)
			DATA_INITED: "DATA_INITED",				//when the grid data inited
			BODY_CLICK: "BODY_CLICK"
	};
	
	
	if(!g_ucAdmin)
		var g_ucAdmin = new UniteAdminUC();
	
	
	function ____________GENERAL______________(){}
	
	/**
	 * get icon, check that it's exists
	 */
	function getIcon(name){
		
		var icon = g_ucAdmin.getVal(g_temp.icons, name);
		
		if(!icon){
			trace("those are available icons: ");
			trace(g_temp.icons);
			throw new Error("icon + "+name+" not found");
		}
		
		return(icon);
	}
	
	
	/**
	 * get element type - column, addon, row, undefined
	 */
	function getElementType(element){
		
		if(!element)
			return("undefined");
		
		if(element.hasClass("uc-grid-col"))
			return("column");
		
		if(element.hasClass("uc-grid-col-addon"))
			return("addon");

		if(element.hasClass("uc-grid-row"))
			return("row");
		
		if(element.hasClass("uc-grid-row-container"))
			return("container");
		
		if(element.hasClass("uc-grid-builder"))
			return("grid");
		
		if(element.hasClass("uc-grid-bg-addon"))
			return("bgaddon");
		
		
		return("undefined");
	}
	
	
	/**
	 * get element list that the given element
	 */
	function getBrothersElements(objElementInTheList, type){
		
		if(!(type))
			var type = getElementType(objElement);
		
		if(type == "undefined")
			throw new Error("getBrothersElements: the type can't be undefined");
		
		switch(type){
			case "row":
				var objRows = getRows();
				return(objRows);
			break;
			case "container":
				var objParentRow = getParentRow(objElement);
				var objContainers = getRowContainersAll(objParentRow);
				return(objContainers);
			break;
			case "column":
				var objParentContainer = getParentRowContainer(objElement);
				var objColumns = getCols(objParentContainer);
				return(objColumns);
			break;
			case "addon":
				var objParentColumn = getParentCol(objElement);
				var objAddons = getColAddons(objParentColumn);
				return(objAddons);
			break;
			default:
				throw new Error("wrong object type: "+type);
			break;
		}
		
	}
	
	/**
	 * get previous element
	 */
	function getPrevElement(objElement, type){
		
		if(!(type))
			var type = getElementType(objElement);
		
		var objElements = getBrothersElements(objElement, type);
		var index = objElement.index();
		if(index == 0)
			return(null);
		
		var objElementBefore = jQuery(objElements[index-1]);
		
		return(objElementBefore);
	}
	
	/**
	 * get previous element
	 */
	function getNextElement(objElement, type){
		
		if(!(type))
			var type = getElementType(objElement);
		
		var objElements = getBrothersElements(objElement, type);
		var index = objElement.index();
		var numElements = objElements.length;
		
		if(index >= (numElements-1) )
			return(null);
		
		var objElementAfter = jQuery(objElements[index+1]);
		
		return(objElementAfter);
	}
	
	/**
	 * get the parent of the element
	 */
	function getElementParent(objElement, type){
		
		if(!(type))
			var type = getElementType(objElement);
				
		switch(type){
			case "grid":
				return(null);
			break;
			case "row":
				
				return(g_objGrid);
			break;
			case "container":
				var objRow = getParentRow(objElement);
				return(objRow);
			break;
			case "column":
				var objContainer = getParentRowContainer(objElement);
				return(objContainer);
			break;
			case "addon":
				var objCol = getParentCol(objElement);
				return(objCol);
			break;
			default:
				throw new Error("Wrong element type: "+type);
			break;
		}
		
	}
	
	
	/**
	 * do grid action
	 */
	function doGridAction(action, parentRow, params){
		
		switch(action){
			case "open_grid_settings":
				openGridSettingsPanel();
			break;
			case "open_page_params_panel":
				openPageParamsPanel();
			break;
			case "add_row":
				addRow(parentRow);
			break;
			case "to_view_mode":		//no extras mode
				g_objGrid.addClass("uc-view-mode");
			break;
			case "to_regular_mode":		//no extras mode
				g_objGrid.removeClass("uc-view-mode");
			break;
			case "play_panelobject_animation":
				playPanelObjectAnimation();
			break;
			case "play_panelobject_section_animation":
				playPanelObjectSectionAnimation();
			break;
			
			case "undo":
				undo();
			break;
			default:
				throw new Error("Wrong grid action: "+action);
			break;
		}
		
	}
	
	
	/**
	 * get element addons (grid, row, col)
	 */
	function getElementAddons(objElement){
		
		var objAddons = objElement.find(".uc-grid-col-addon").not(".uc-grid-overlay-empty");
		
		return(objAddons);
	}
	
	/**
	 * show error message from the parent object
	 */
	function showErrorMessage(message){
					
		if(g_pageBuilder)
			g_pageBuilder.showErrorMessage(message);
		else{
			
			if(g_objWrapper && g_objWrapper.length){
				g_objWrapper.html("<div class='unite_error_message'>"+message+"</div>");
			}else
				alert(message);
		}
		
	}
	
	
	/**
	 * add mobile placeholder for some object (row,column,addon)
	 */
	function addMobilePlaceholder(objPlaceholders, mobileName, parentName, objParentSettings, objTopParentSettings, defaultValue){
		
		var parentSettingValue = g_ucAdmin.getVal(objParentSettings, parentName);
		if(parentSettingValue !== ""){
			objPlaceholders[mobileName] = parentSettingValue;
			return(objPlaceholders);
		}
		
		var parentTopSettingValue = g_ucAdmin.getVal(objTopParentSettings, parentName);
		if(parentTopSettingValue !== ""){
			
			objPlaceholders[mobileName] = parentTopSettingValue;
			return(objPlaceholders);
		}
		
		if(typeof defaultValue !== "undefined")
			objPlaceholders[mobileName] = defaultValue;
		
		return(objPlaceholders);
	}
	
	
	/**
	 * get placeholders object for all the sizes
	 */
	function getObjSizePlaceholders(arrSettings, objPlaceholders, objOptions, objParentOptions, prefix, defaultValue){
		
		var arrSizes = g_temp.sizes;
		
		var arrParentSizeValues = {};
		
		for(var indexSize in arrSizes){
			
			var size = arrSizes[indexSize];
			
			for(var indexSetting in arrSettings){
				var settingName = arrSettings[indexSetting];
				
				var isPrefix = false;
				if(prefix)
					isPrefix = true;
								
				if(settingName.indexOf("noprefix_") === 0){
					settingName = settingName.replace("noprefix_","");
					isPrefix = false;
				}
				
				var settingNameSize = settingName + "_"+size;
				
				if(isPrefix == true)
					settingName = prefix+"_"+settingName;
				
									
				objPlaceholders = addMobilePlaceholder(objPlaceholders, settingNameSize, settingName, objOptions, objParentOptions, defaultValue);
				
				//take placeholder from parent size if available (mobile from tablet)
				var parentValue = g_ucAdmin.getVal(arrParentSizeValues, settingName);
				if(parentValue !== "")
					objPlaceholders[settingNameSize] = parentValue;
				
				//save parent setting size for child size values placeholders
				var sizeValue = g_ucAdmin.getVal(objOptions, settingNameSize);
				if(sizeValue !== "")
					arrParentSizeValues[settingName] = sizeValue;
				
			}
			
		}
		
		return(objPlaceholders);
	}
	
	
	/**
	 * get new element ID
	 */
	function getNewElementID(type){
		
		g_vars.serial++;
		var elementID = g_gridID+"_"+type+"_"+g_vars.serial;
		elementID = elementID.replace("#", "");
		
		return(elementID);
	}
	
	
	/**
	 * get element data settings key
	 */
	function getElementDataSettingsKey(objElement){
		
		var type = getElementType(objElement);
		
		switch(type){
			case "addon":
				return("addon_settings");
			break;
			case "grid":
				return("options");
			break;
			default:
				return("settings");
			break;
		}
		
	}
	
	/**
	 * get element settings
	 */
	function getElementSettings(objElement){
		
		var dataKey = getElementDataSettingsKey(objElement);
		
		var objSettings = objElement.data(dataKey);
		if(!objSettings)
			objSettings = {};
		
		return(objSettings);
	}
	
	
	/**
	 * update element setting (not addon data)
	 */
	function updateElementSetting(objElement, settingName, settingValue){
		
		var dataKey = getElementDataSettingsKey(objElement);
				
		var objSettings = objElement.data(dataKey);
		if(!objSettings)
			objSettings = {};
		
		objSettings[settingName] = settingValue;
		
		objElement.data(dataKey, objSettings);
		
		updatePanelSettingIfActive(objElement, settingName, settingValue);
	}
	
	
	/**
	 * update panel settings if active
	 */
	function updatePanelSettingIfActive(objElement, settingName, settingValue, isAddonSettings){
		
		var elementType = getElementType(objElement);
		if(elementType == "column")
			elementType = "col";
		
		if(elementType == "addon" && isAddonSettings !== true)
			elementType = "addon-container";
		
		var paneName = elementType+"-settings";
		var elementID = objElement.prop("id");
		
		
		g_panel.setSettingValueIfActive(paneName, elementID, settingName, settingValue);
		
	}
		
	/**
	 * map section children, run some func on them.
	 */
	function mapSectionChildren(objRow, func){
		
		var objContainers = getRowContainersAll(objRow);
		var objColumns = getAllRowCols(objRow);
		var objAddons = getElementAddons(objRow);
		
		objContainers.each(function(index, container){
			var objContainer = jQuery(container);
			func(objContainer, "container");
		});
		
		objColumns.each(function(index, column){
			var objCol = jQuery(column);
			func(objCol, "column");
		});
		
		objAddons.each(function(index, addon){
			var objAddon = jQuery(addon);
			func(objAddon, "addon");
		});
		
		
	}
	
	
	/**
	 * play element animation
	 */
	function playElementAnimation(objElement, data, includeDelay){
		
		if(objElement.length == 0)
			return(true);
				
		if(data)
			var settings = data.settings;
		else
			var settings = getElementSettings(objElement);
		
		
		var animationType = g_ucAdmin.getVal(settings, "animation_type");
		if(!animationType)
			return(true);
		
		var animationDelay = g_ucAdmin.getVal(settings, "animation_delay");
		var animationDuration = g_ucAdmin.getVal(settings, "animation_duration");
		animationDuration = parseInt(animationDuration);
				
		if(!animationDuration)
			animationDuration = null;
		
		var delay = 0;
		if(includeDelay === true && animationDelay && jQuery.isNumeric(animationDelay))
			delay = parseFloat(animationDelay) * 1000;
		
		setTimeout(function(){
			var classAnimated = "animated";
			
			var className = animationType + ' ' + classAnimated;
			
			objElement.removeClass(classAnimated).addClass(className);
			if(animationDuration)
				objElement.css("animation-duration", animationDuration+"s");
			else
				objElement.css("animation-duration", "");
				
			objElement.one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function(){
			      jQuery(this).removeClass(classAnimated).removeClass(animationType);
				  objElement.css("animation-duration", "");
			});
			
		}, delay);
		
	}

	
	
	/**
	 * play section animation
	 */
	function playPanelObjectSectionAnimation(){
		
		var data = g_panel.getActivePaneData();
		var objectID = data.objectID;
		
		var objElement = jQuery("#"+objectID);
		var type = getElementType(objElement);
		
		if(type != "row")		
			objElement = getParentRow(objElement);
		
		if(objElement.length == 0)
			return(true);
		
		mapSectionChildren(objElement, function(objElement, type){
						
			playElementAnimation(objElement, null, true);
			
		});
				
	}
	
	/**
	 * play panel object animation
	 */
	function playPanelObjectAnimation(){
		
		var data = g_panel.getActivePaneData();
		var objectID = data.objectID;
			
		var objElement = jQuery("#"+objectID);
		
		if(objElement.length == 0)
			return(true);
		
		playElementAnimation(objElement, data);
		
	}
	
	
	/**
	 * check if bg color changed of element
	 */
	function isElementBGColorChange(oldSettings, newSettings){
		
		var bgColorOld = getElementBGColor(null, oldSettings);
		var bgColorNew = getElementBGColor(null, newSettings);
				
		if(bgColorOld !== bgColorNew)
			return(true);
		else
			return(false);
	}
	
	
	/**
	 * open some element settings panel
	 */
	function openElementSettingsPanel(objElement){
		
		var type = getElementType(objElement);
		
		switch(type){
			case "row":
				openRowSettingsPanel(objElement);
			break;
			case "container":
				openContainerSettingsPanel(objElement);
			break;
			case "addon":
				openAddonSettingsPanel(objElement);
			break;
			case "column":
				openColSettingsPanel(objElement);
			break;
		}
		
	}
	
	/**
	 * undo action
	 */
	function undo(){
		
		if(g_temp.indev == false)
			return(false);
		
		//var operation = g_objHistory.getNextOperation();
	}
	
	function ____________ROW______________(){}
	
	
	/**
	 * validate row
	 */
	function validateRow(objRow){
		
		if(!objRow)
			throw new Error("Empty Row Found: "+objRow);
		
		if(objRow.hasClass("uc-grid-row") == false && objRow.hasClass("uc-grid-row-container") == false)
			throw new Error("Wrong Row or container: "+objRow);
		
	}
	
	
	/**
	 * get row html
	 */
	function getHtmlRow(noContainer){
				
		var rowID = getNewElementID("row");
		var iconSettings = getIcon("settings");
		var iconDuplicate = getIcon("duplicate");
		var iconMenu = getIcon("menu_closed");
		
		
		var html = "";
		html += "<div id='"+rowID+"' class='uc-grid-row'>";
		
		//add shape deviders
		
		html += "<div class='uc-grid-shape-devider uc-devider-top'></div>";
		html += "<div class='uc-grid-shape-devider uc-devider-bottom'></div>";
		
		html += "<div class='uc-grid-row-anchor'></div>";
		html += "<div class='uc-grid-row-anchor-indicator uc-grid-extras' title='"+g_uctext.section_anchor+"'></div>";
		
		html += "<div class='uc-grid-row-hover uc-hover-top uc-grid-extras'></div>";
		html += "<div class='uc-grid-row-hover uc-hover-bottom uc-grid-extras'></div>";
		html += "<div class='uc-grid-row-hover uc-hover-left uc-grid-extras'></div>";
		html += "<div class='uc-grid-row-hover uc-hover-right uc-grid-extras'></div>";
		
		html += "<div class='uc-grid-element-hidden-overlay'>"+g_uctext.hidden_row+"</div>";
		html += "<div class='uc-grid-element-background-overlay'></div>";
		
		//add row panel
		html += "	<div class='uc-grid-object-panel uc-grid-row-panel uc-grid-extras'>";
		html += "		<a href='javascript:void(0)' data-action='row_settings' data-actiontype='row' title='"+g_uctext.settings+"' class=\"uc-row-icon uc-grid-action-icon uc-tip\" ><i class=\""+iconSettings+"\" aria-hidden=\"true\"></i></a> ";
		html += "		<a href='javascript:void(0)' data-action='duplicate_row' data-actiontype='row' title='"+g_uctext.duplicate_section+"' class=\"uc-row-icon uc-grid-action-icon uc-tip\" ><i class=\""+iconDuplicate+"\" aria-hidden=\"true\"></i></a> ";
		html += "		<a href='javascript:void(0)' data-action='delete_row' data-actiontype='row' title='"+g_uctext.delete_section+"' class=\"uc-row-icon uc-grid-action-icon uc-tip\" ><i class=\""+getIcon("delete")+"\" aria-hidden=\"true\"></i></a> ";
		html += "		<a href='javascript:void(0)' title='"+g_uctext.move_section+"' class=\"uc-row-icon uc-grid-icon-move uc-row-icon-move uc-tip\" ><i class=\""+getIcon("move")+"\" aria-hidden=\"true\"></i></a> ";
		html += "		<a href='javascript:void(0)' class=\"uc-row-icon uc-row-icon-menu\" ><i class=\""+iconMenu+"\" aria-hidden=\"true\"></i>";
		html += "			<span data-action='copy_row' data-actiontype='row' class=\"uc-grid-action-icon\" >"+g_uctext.copy_section+"</span>";
		html += "			<span data-action='paste_row' data-actiontype='row' class=\"uc-grid-action-icon uc-icon-paste-row\" >"+g_uctext.paste_section+"</span>";
		html += "			<span data-action='save_row_tolibrary' data-actiontype='row' class=\"uc-grid-action-icon\" >"+g_uctext.save_to_library+"</span>";
		html += "			<span data-action='import_row_fromlibrary' data-actiontype='row' class=\"uc-grid-action-icon\" >Import From Library</span>";
		html += "</a>";
		html += "	</div>";
		
		//add buttons panel
		html += "	<div class='uc-grid-object-panel uc-row-addbuttons-panel uc-grid-extras'>";
		html += "		<a href='javascript:void(0)' data-action='add_row' data-actiontype='grid' title='Add Section' class=\"uc-row-icon uc-grid-action-icon uc-iconcolor-row uc-tip\" ><i class=\"fa fa-plus\" aria-hidden=\"true\"></i></a> ";
		html += "		<a href='javascript:void(0)' data-action='add_row_container' data-actiontype='row' title='Add Row' class=\"uc-row-icon uc-grid-action-icon uc-iconcolor-container uc-tip\" ><i class=\"fa fa-plus\" aria-hidden=\"true\"></i></a> ";
		html += "	</div>";
        
		
		//add container
		if(noContainer !== true){
			
			html += getHtmlRowContainer();
		}
						
		html += "</div>";
		
		return(html);
	}
	
	/**
	 * get row title by index or options
	 */
	function getRowTitle(objRow){
		
		var rowIndex = objRow.index() + 1;
		var title = g_uctext["section"] + " " + rowIndex;
		
		return(title);
	}
	
	
	/**
	 * add empty row
	 * insertTo - before / after
	 */
	function addEmptyRow(parentRow, insertTo, noContainer){
		
		if(!insertTo)
			insertTo = "after";
		
		var html = getHtmlRow(noContainer);
					
		var objRow = jQuery(html);
		
        if(!parentRow) {
        	g_objGrid.append(objRow);
        } else {
        	if(insertTo == "after")
        		objRow.insertAfter(parentRow);
        	else
        		objRow.insertBefore(parentRow);
        }
		
		triggerEvent(t.events.ROW_ADDED, objRow);
		triggerEvent(t.events.ROWS_UPDATED);
				
		//if added container trigger event
		if(noContainer !== true){
						
			var objContainer = getRowContainer(objRow);
			
			triggerEvent(t.events.ROW_CONTAINER_ADDED, objContainer);
			triggerEvent(t.events.ROW_CONTAINERS_UPDATED, objRow);
		}
		
		return(objRow);
	}
	
	
	/**
	 * add row to the end
	 */
	function addRow(parentRow){
		
        var objRow = addEmptyRow(parentRow);
		
		addColumn(objRow);
		
	}
	
	
	/**
	 * get all rows
	 */
	function getRows(){
		
		var objRows = g_objGrid.children(".uc-grid-row");
		
		return(objRows);
	}
	
	
	/**
	 * get number of rows
	 */
	function getNumRows(){
		
		var objRows = getRows();
		
		var numRows = objRows.length;
		
		return(numRows);
	}
	
	
	
	/**
	 * get row bu number
	 */
	function getRow(num){
		
		if(!num)
			var num = 0;
		
		var objRows = getRows();
		
		if(num >= objRows.length)
			throw new Error("getRow error: Row "+num+" don't exists");
		
		var objRow = jQuery(objRows[num]);
		
		return(objRow);
	}
	
	
	/**
	 * get parent row
	 */
	function getParentRow(objChild){
		
		if(objChild.hasClass("uc-grid-row"))
			return(objChild);
		
		var objRow = objChild.parents(".uc-grid-row");
		
		return(objRow);
	}
	
	
	/**
	 * get row addons
	 */
	function getRowAddons(objRow){
		validateRow(objRow);
		
		var objAddons = getElementAddons(objRow);
		
		return(objAddons);
	}
	
	
	/**
	 * get number of row addons
	 */
	function getNumRowAddons(objRow){
		
		var objAddons = getRowAddons(objRow);
		return(objAddons.length);
	}
	
	
	/**
	 * delete row
	 */
	function deleteRow(objRow){
		
		validateRow(objRow);
		
		var rowID = objRow.prop("id");
		
		objRow.remove();
		
		var numRows = getNumRows();
		if(numRows == 0)
			addRow();	//triggers the updated event
		else
			triggerEvent(t.events.ROWS_UPDATED);
		
		triggerEvent(t.events.ELEMENT_REMOVED, rowID);
	}
		
	
	/**
	 * duplicate row
	 */
	function duplicateRow(objRow){
		
		var rowID = getNewElementID("row");
		
		//clear containers and columns sortable
		objRow.sortable("destroy");
		
		var objContainers = getRowContainersAll(objRow);
		jQuery.each(objContainers, function(index, container){
			var objContainer = jQuery(container);
						
			objContainer.sortable("destroy");
		});
		
		
		var objRowCopy = objRow.clone(true, true);
		
		objRowCopy.attr("id", rowID);
				
		objRowCopy.insertAfter(objRow);
		
		triggerEvent(t.events.ROW_DUPLICATED, objRowCopy);
		
		triggerEvent(t.events.ROWS_UPDATED);
		
		//init original row sortable
		initSortableContainers(objRow);
		
		jQuery.each(objContainers, function(index, container){
			var objContainer = jQuery(container);
			initSortableColumns(objContainer);
		});
		
		
	}
	
	
	/**
	 * copy row - put into container
	 */
	function copyRow(objRow){
				
		//get data
		var objRowCopy = objRow.clone(true, true);
		var rowID = objRow.prop("id");
		
		var dataRow = getGridData_row(objRow, true);
		
		//store row
		if(g_objBuffer)
			g_objBuffer.store("row", rowID, objRowCopy, dataRow);
	}
	
	
	/**
	 * paste row
	 */
	function pasteRow(objRow){
		
		var errorMessage = "Stored row not found in the buffer";
		
		var type = g_objBuffer.getStoredType();
		if(type != "row"){
			trace(errorMessage);
			return(false);
		}
		
		var storedData = g_objBuffer.getStoredContent();
			
		if(!storedData){
			trace(errorMessage);
			return(false);
		}
		
		var storedRowHtml = g_ucAdmin.getVal(storedData,"content_html");
		var storedRowObject = g_ucAdmin.getVal(storedData,"content_object");
		
		var rowID = getNewElementID("row");
		
		if(storedRowHtml){
			storedRowHtml.attr("id", rowID);
			storedRowHtml.insertBefore(objRow);
			triggerEvent(t.events.ROW_DUPLICATED, storedRowHtml);
		}else{
			
			initByData_row(storedRowObject, objRow, "before");
		}
		
		g_objBuffer.clear();
		triggerEvent(t.events.ROWS_UPDATED);
		
	}
	
	
	/**
	 * do action
	 */
	function doRowAction(action, objRow){
		
		switch(action){
			case "add_row":
				addRow(objRow);
			break;
			case "delete_row":
				deleteRow(objRow);
			break;
			case "duplicate_row":
				duplicateRow(objRow);
			break;
			case "copy_row":
				copyRow(objRow);
			break;
			case "paste_row":
				pasteRow(objRow);
			break;
			case "save_row_tolibrary":
				openSaveSectionSettingsPanel(objRow);
			break;
			case "import_row_fromlibrary":
				importSectionFromLibrary(objRow);
			break;
			case "row_settings":				
				openRowSettingsPanel(objRow);
			break;
            case "add_row_container":
            	addRowContainer(objRow, true);
            break;
            default:
				trace("wrong row action: " + action);
			break;
		}
		
	}
	
	function ____________UPDATE_VISUAL_GENERAL______________(){}
	
	/**
	 * get shape devider bg color
	 */
	function getDividerBGColor(objElement, objSettings, elementType, position, checkParentParam){
		
		var checkParent = true;
		if(elementType == "row" && !checkParentParam)
			checkParent = false;
				
		var defaultBG = "#ffffff";
		
		if(checkParent == false){		//for rows - check brothers
			
			if(position == "top")
				var objSourceElement = getPrevElement(objElement);
			else
				var objSourceElement = getNextElement(objElement);
			
			if(!objSourceElement)
				return(null);
			
			var bgColor = getElementBGColor(objSourceElement);
			if(!bgColor)
				bgColor = defaultBG;
			
			return(bgColor);
			
			
		}else{		//check parent
			
			var objParentElement = getElementParent(objElement);
			
			if(!objParentElement)
				return(null);
			
			var bgColor = getElementBGColor(objParentElement);
			if(bgColor)
				return(bgColor);
			
			//if not found use recursion
			bgColor = getDividerBGColor(objParentElement, objSettings, elementType, position, true);
			return(bgColor);
		}
		
		
	}
	
	
	/**
	 * get current element bg color,
	 * settings - optional
	 */
	function getElementBGColor(objElement, objSettings){
		
		if(!objSettings){
			
			if(!objElement)
				return(null);
			
			if(objElement.length == 0)
				return(null);
			
			var objSettings = getElementSettings(objElement);
		}
		
		var defaultBG = null;
		
		var enableBG = g_ucAdmin.getVal(objSettings, "bg_enable",true, g_ucAdmin.getvalopt.FORCE_BOOLEAN);
		
		if(enableBG == false)
			return(defaultBG);
		
		var bgColor = g_ucAdmin.getVal(objSettings, "bg_color", defaultBG);
		bgColor = jQuery.trim(bgColor);
		if(!bgColor)
			return(defaultBG);
		
		return(bgColor);
	}
	
	
	/**
	 * get element shape devider
	 */
	function getElementShapeDivider(objSettings, position){
		
		if(!objSettings)
			return(null);
		
		//keep old format if exists
		var enableShapeDivider = true;
		if(objSettings.hasOwnProperty("enable_shape_devider_"+position))
			var enableShapeDivider =  g_ucAdmin.getVal(objSettings, "enable_shape_devider_"+position, false, g_ucAdmin.getvalopt.FORCE_BOOLEAN);
		
		if(enableShapeDivider == false){
			return(null);
		}
		
		//new way - decide by shape
		var shape = g_ucAdmin.getVal(objSettings, "shape_devider_"+position+"_type");
		
		return(shape);
	}

	
	/**
	 * on parent element color change, check if it could affect the shape
	 * if there is shape in this element, and autodetect is ok, then redraw shape
	 */
	function onShapeDetectedColorParentChange(objElement, position){
		
		var type = getElementType(objElement);
		
		var objSettings = objElement.data("settings");
		if(!objSettings)
			objSettings = {};
		
		var shape = getElementShapeDivider(objSettings, position);
		
		if(!shape)
			return(false);
		
		var isAutodetectColor = g_ucAdmin.getVal(objSettings, "shape_devider_"+position+"_autodetect_color");
		isAutodetectColor = g_ucAdmin.strToBool(isAutodetectColor);
		
		if(isAutodetectColor == false)
			return(false);
				
		updateElementVisual_shapeDivider(objElement, objSettings, type, position);
		
	}
	
	
	/**
	 * update shape devider
	 */
	function updateElementVisual_shapeDivider(objElement, objSettings, elementType, position){
		
		var elementID = objElement.prop("id");
				
		var classDividerEnable = "uc-has-devider-"+position;
		
		var shape = getElementShapeDivider(objSettings, position);
		
		if(!shape){
			objElement.removeClass(classDividerEnable);
			return(false);
		}
		
		
		var selectorDivider = ".uc-grid-shape-devider.uc-devider-"+position;
		var objDivider = objElement.children(selectorDivider);
		
		var settingNameColor = "shape_devider_"+position+"_color";
			
		var height = g_ucAdmin.getVal(objSettings, "shape_devider_"+position+"_height");
		var shapeColor = g_ucAdmin.getVal(objSettings, settingNameColor, "#ffffff");
		var isflip = g_ucAdmin.getVal(objSettings, "shape_devider_"+position+"_flip");
		var isAutodetectColor = g_ucAdmin.getVal(objSettings, "shape_devider_"+position+"_autodetect_color");
		var placement = g_ucAdmin.getVal(objSettings, "shape_devider_"+position+"_placement");
		var repeat = g_ucAdmin.getVal(objSettings, "shape_devider_"+position+"_repeat",1,g_ucAdmin.getvalopt.FORCE_NUMERIC);
		
		isAutodetectColor = g_ucAdmin.strToBool(isAutodetectColor);
		isflip = g_ucAdmin.strToBool(isflip);
		if(!repeat || repeat <= 0)
			repeat = 1;
		
		//autodetect color, and update it in settings as well
		if(isAutodetectColor == true){
			
			var detectedColor = getDividerBGColor(objElement, objSettings, elementType, position);
			
			
			if(detectedColor && detectedColor != shapeColor){
				shapeColor = detectedColor;
				
				if(g_temp.is_data_inited == true)
					updateElementSetting(objElement, settingNameColor, detectedColor);
			}
				
		}
		
				
		//if just installed - get content
		if(g_vars.shape_deviders.hasOwnProperty(shape) == false && g_temp.is_data_inited == true){
			
			var objBrowserShapes = g_pageBuilder.getObjBrowser("shape_deviders");
			var shapeData = objBrowserShapes.getAddonData(shape);
			var bgImage = g_ucAdmin.getVal(shapeData, "bgimage");

			if(bgImage){
				
				bgImage = bgImage.replace("url(\"", "");
				bgImage = bgImage.replace("\")", "");				
				g_vars.shape_deviders[shape] = bgImage;
			}
			
		}
		
		var shapeContent = g_ucAdmin.getVal(g_vars.shape_deviders, shape);
		
		if(!shapeContent){
			objElement.removeClass(classDividerEnable);
			return(false);
		}
		
		objElement.addClass(classDividerEnable);
		
		var objDividerCss = {};
		
		//modify shape color
		if(shapeColor){
			var svg = g_ucAdmin.base64_decode(shapeContent);
			svg = svg.replace('g fill="#ffffff"','g fill="'+shapeColor+'"');
			shapeContent = g_ucAdmin.base64_encode(svg);
		}
		
		var svgPrefix = "data:image/svg+xml;base64,";
		
		//change color in the content
		objDividerCss["background-image"] = "url('"+svgPrefix+shapeContent+"')";
		
		//get repear percent
		var percentRepeat = 100;
		
		if(jQuery.isNumeric(repeat) && repeat > 0){
			percentRepeat = 100 / repeat;
			
			var decimal = percentRepeat % 1;
						
			if(decimal > 0)
				percentRepeat = percentRepeat.toFixed(2);
		}
		
		if(height !== ""){
			
			height = g_ucAdmin.normalizeSizeValue(height);
						
			objDividerCss["background-size"] = percentRepeat+"% "+height;
			objDividerCss["height"] = height;
		}
		
		
		//flip the shape
		if(isflip == true){
			if(position == "top")
				objDividerCss["transform"] = "rotateY(180deg)";
			else
				objDividerCss["transform"] = "rotateX(180deg) rotateY(180deg)";
		}
		
		if(placement == "beneath")
			objDividerCss["z-index"] = "0";
		
		//put mobile css
		var arrSettingsSizeRelated = {
			"height":"shape_devider_"+position+"_height"
		};
		
		objDivider.removeAttr("style");
		objDivider.css(objDividerCss);
		
		//print mobile size
		var mobileSelector = "#"+elementID+" > "+selectorDivider;
		
		var cssSizeRelated = getSizeRelatedCss(arrSettingsSizeRelated, objSettings, mobileSelector,"!important",function(objSizeCss, cssAttribute, settingName, value, size){
			
			if(value === "")
				return(objSizeCss);
			
			if(cssAttribute == "height"){
				objSizeCss["background-size"] = percentRepeat + "% " + value+"px !important";
			}
			
			return(objSizeCss);
		});
		
		
		if(cssSizeRelated){
			var styleContainerID = elementType+"_shape_devider_"+elementID+"_sizes";
			
			g_ucAdmin.printCssStyle(cssSizeRelated, styleContainerID, g_objColStyleContainer);
		}
		
		
	}
	
	/**
	 * update object visual
	 */
	function updateElementVisual(objElement, objCss, objSettings, prefix){
		
		var elementID = objElement.prop("id");
		
		var settingsPrefix = "";
		if(prefix == "row")
			settingsPrefix = "row_";
		
		objCss = g_ucAdmin.addCssSetting(objSettings, objCss, settingsPrefix+"padding_top", "padding-top","px");
		objCss = g_ucAdmin.addCssSetting(objSettings, objCss, settingsPrefix+"padding_bottom", "padding-bottom","px");
		objCss = g_ucAdmin.addCssSetting(objSettings, objCss, settingsPrefix+"padding_left", "padding-left","px");
		objCss = g_ucAdmin.addCssSetting(objSettings, objCss, settingsPrefix+"padding_right", "padding-right","px");
		objCss = g_ucAdmin.addCssSetting(objSettings, objCss, settingsPrefix+"margin_top", "margin-top","px");
		objCss = g_ucAdmin.addCssSetting(objSettings, objCss, settingsPrefix+"margin_bottom", "margin-bottom","px");
		objCss = g_ucAdmin.addCssSetting(objSettings, objCss, settingsPrefix+"margin_left", "margin-left","px");
		objCss = g_ucAdmin.addCssSetting(objSettings, objCss, settingsPrefix+"margin_right", "margin-right","px");
		objCss = g_ucAdmin.addCssSetting(objSettings, objCss, settingsPrefix+"text_align", "text-align");
		
		var cssBG = getBackgroundCss(objSettings);
		if(cssBG)
			jQuery.extend(objCss, cssBG);
				
		//remove style
		objElement.removeAttr("style");
		
		//add css
		var strAddCss = g_ucAdmin.getVal(objSettings, prefix+"_css", null);
		
		var addClass = g_ucAdmin.getVal(objSettings, prefix+"_class", null);
		
		//remove previous class
		var oldClass = objElement.data("addclass");
		if(oldClass && oldClass != addClass){
			objElement.removeClass(oldClass);
			objElement.data("addclass", null);
		}
		
		//add the additional class
		if(addClass && addClass != oldClass){
			objElement.addClass(addClass);
			objElement.data("addclass", addClass);
		}
		
		objElement.css(objCss);
		
		//update mobile
		var arrSettings = {
				"padding-top":"padding_top",
				"padding-bottom":"padding_bottom",
				"padding-left":"padding_left",
				"padding-right":"padding_right",
				"margin-top":"margin_top",
				"margin-bottom":"margin_bottom",
				"margin-left":"margin_left",
				"margin-right":"margin_right",
				"inline-css":prefix+"_css"
		};
		
		//hide in devices	
		
		var hideInMobile = g_ucAdmin.getVal(objSettings, "hide_in_mobile");
		hideInMobile = g_ucAdmin.strToBool(hideInMobile);
		if(hideInMobile == true)
			objElement.addClass("uc-hide-mobile");
		else
			objElement.removeClass("uc-hide-mobile");
		
		
		var hideInTablet = g_ucAdmin.getVal(objSettings, "hide_in_tablet");
		hideInTablet = g_ucAdmin.strToBool(hideInTablet);
		if(hideInTablet == true)
			objElement.addClass("uc-hide-tablet");
		else
			objElement.removeClass("uc-hide-tablet");
		
		var hideInDesktop = g_ucAdmin.getVal(objSettings, "hide_in_desktop");
		hideInDesktop = g_ucAdmin.strToBool(hideInDesktop);
		if(hideInDesktop == true)
			objElement.addClass("uc-element-hidden-desktop");
		else
			objElement.removeClass("uc-element-hidden-desktop");
		
		//put add css
		
		var container = g_objColStyleContainer;
		if(prefix == "row")
			container = g_objRowStyleContainer;
		
		if(strAddCss){
			var styleContainerID = prefix+"_common_"+elementID+"_addcss";
			
			strAddCss = g_ucAdmin.removeLineBreaks(strAddCss);
			
			var cssAdditional = "#"+elementID+"{"+strAddCss+"}";
			
			g_ucAdmin.printCssStyle(cssAdditional, styleContainerID, container);
		}
		
		//put add css size related
		var cssSizeRelated = getSizeRelatedCss(arrSettings, objSettings, "#"+elementID,"!important");
		
		if(cssSizeRelated){
			
			var styleContainerID = prefix+"_common_"+elementID+"_sizes";
			
			g_ucAdmin.printCssStyle(cssSizeRelated, styleContainerID, container);
		}
		
		//shape deviders
		
		updateElementVisual_shapeDivider(objElement, objSettings, prefix, "top");
		updateElementVisual_shapeDivider(objElement, objSettings, prefix, "bottom");
		
		//background addons
		
		updateElementVisual_bgAddons(objElement, objSettings, prefix);
	}
	
	function ____________BACKGROUND_ADDONS______________(){}
	
	/**
	 * update background addons
	 */
	function updateElementVisual_bgAddons(objElement, options, prefix){
		
		var classBGExists = "uc-has-bg-overlay";
		var enableBG = g_ucAdmin.getVal(options, "bg_enable",false, g_ucAdmin.getvalopt.FORCE_BOOLEAN);
		var objBGOverlay = objElement.children(".uc-grid-element-background-overlay");
		
		if(objBGOverlay.length == 0){
			objElement.removeClass(classBGExists);
			return(false);
		}
		
		//get bg addon
		var existingAddonData = null;
		var objBGAddon = objBGOverlay.children(".uc-grid-bg-addon");
		if(objBGAddon.length == 0)
			objBGAddon = null;
		else{
			
			existingAddonData = objBGAddon.data("addon_data");
			var existingAddonName = existingAddonData.name;
		}
		
		var enableBGAddon = g_ucAdmin.getVal(options, "bg_addon_single_enable", false, g_ucAdmin.getvalopt.FORCE_BOOLEAN);
		
		if(enableBG == false || enableBGAddon == false){
			if(objBGAddon)
				deleteBGAddon(objBGAddon);
			
			objElement.removeClass(classBGExists);
			return(false);
		}
		
		
		var settingsKey = "bg_addon_single";
		
		//get addon name
		var bgAddonName = g_ucAdmin.getVal(options, settingsKey);
		
		if(!bgAddonName){
			if(objBGAddon)
				deleteBGAddon(objBGAddon);
			
			objElement.removeClass(classBGExists);
			return(false);
		}
		
		//get addon data, or create
		var addonData = g_ucAdmin.getVal(options, settingsKey+"_data");
		
		if(!addonData){
			
			var extra = {};
			extra["title"] = bgAddonName;
			extra["url_icon"] = "";
			
			var addonData = {};
			addonData["name"] = bgAddonName;
			addonData["addontype"] = g_temp.addontype_bgaddon;
			addonData["extra"] = extra;
		}
		
		//compare addons data - existing and new
		if(existingAddonData){
			
			var addonDataForCompare = modifyAddonDataBeforeSave(addonData);
			var isEqual = g_objAddonConfig.isAddonsDataEqual(addonDataForCompare, existingAddonData);
			if(isEqual == true)
				return(false);
		}
		
		//if available existing bg addon - remove it
		
		if(objBGAddon)
			deleteBGAddon(objBGAddon);
		
		addonData = modifyAddonDataBeforeAdd(addonData);
		
		generateAddonHtml(addonData, function(htmlAddon){
			
			objElement.addClass(classBGExists);
			objBGOverlay.html(htmlAddon);
			
			//store bg addon data
			var objBGAddon = objBGOverlay.children(".uc-grid-bg-addon");
			var addonDataSave = modifyAddonDataBeforeSave(addonData);
			objBGAddon.data("addon_data", addonDataSave);
			
		});
		
		
	}
	
	/**
	 * delete background addon
	 */
	function deleteBGAddon(objBGAddon){
		
		if(!objBGAddon)
			return(false);
		
		validateColAddonElement(objBGAddon);
		
		var addonID = objBGAddon.prop("id");
		
		objBGAddon.remove();
		
		triggerEvent(t.events.ELEMENT_REMOVED, addonID);
	}
	
	
	function ____________ROW_UPDATE_VISUAL______________(){}
	
	
	/**
	 * get background css
	 */
	function getBackgroundCss(options){
		
		var css = {};
		
		var oldColor = g_ucAdmin.getVal(options, "row_background_color");
		if(oldColor == true){
			var enableBG = true;
		}else
			var enableBG = g_ucAdmin.getVal(options, "bg_enable",true, g_ucAdmin.getvalopt.FORCE_BOOLEAN);
		
		if(enableBG == false)
			return(css);
		
		//set color
		var color = g_ucAdmin.getVal(options, "bg_color");
		if(!color)
			color = oldColor;
		
		if(color)
			css["background-color"] = color;
				
		//set image
		var urlImage = g_ucAdmin.getVal(options, "bg_image_url");
		if(urlImage){
			
			if(jQuery.isNumeric(urlImage))
				urlImage = g_ucAdmin.getVal(options, "bg_image_url_url");
			
			urlImage = g_ucAdmin.urlToFull(urlImage);
			
			var imageSize = g_ucAdmin.getVal(options, "bg_image_size");
			var imagePosition = g_ucAdmin.getVal(options, "bg_image_position");
			var imageRepeat = g_ucAdmin.getVal(options, "bg_image_repeat");
			var imageBlend = g_ucAdmin.getVal(options, "bg_image_blend");
			var imageParallax = g_ucAdmin.getVal(options, "bg_image_parallax", false, g_ucAdmin.getvalopt.FORCE_BOOLEAN);
			
			css["background-image"] = "url('"+urlImage+"')";
			
			if(imageSize)
				css["background-size"] = imageSize;
			
			if(imagePosition)
				css["background-position"] = imagePosition;
			
			if(imageRepeat)
				css["background-repeat"] = imageRepeat;
			
			if(imageBlend && imageBlend != "normal")
				css["background-blend-mode"] = imageBlend;
			
			if(imageParallax === true)
				css["background-attachment"] = "fixed";
			
		}
		
		//set gradient
		var enableGradient = g_ucAdmin.getVal(options, "bg_gradient_enable",false, g_ucAdmin.getvalopt.FORCE_BOOLEAN);
		if(enableGradient == true){
			
			var gradientReverse = g_ucAdmin.getVal(options, "bg_gradient_reverse", false, g_ucAdmin.getvalopt.FORCE_BOOLEAN); 
			
			if(gradientReverse == true){
				var gradientColor2 = g_ucAdmin.getVal(options, "bg_gradient_color1"); 
				var gradientColor1 = g_ucAdmin.getVal(options, "bg_gradient_color2"); 
			}else{
				var gradientColor1 = g_ucAdmin.getVal(options, "bg_gradient_color1"); 
				var gradientColor2 = g_ucAdmin.getVal(options, "bg_gradient_color2"); 
			}
			
			var gradientStartPos = g_ucAdmin.getVal(options, "bg_gradient_start_pos"); 
			var gradientEndPos = g_ucAdmin.getVal(options, "bg_gradient_end_pos"); 
			var gradientLinearDir = g_ucAdmin.getVal(options, "bg_gradient_linear_direction"); 
			var gradientRadialDir = g_ucAdmin.getVal(options, "bg_gradient_radial_direction"); 
			
			var gradientType = g_ucAdmin.getVal(options, "bg_gradient_type"); 
			
			var strGradient = "";
			strGradient += gradientType+"-gradient(";
			if(gradientType == "linear"){
				strGradient += gradientLinearDir+"deg, ";
			}else
				strGradient += "circle at "+gradientRadialDir+", ";
			
			strGradient += gradientColor1+" "+gradientStartPos+"%, ";
			strGradient += gradientColor2+" "+gradientEndPos+"%";
			
			strGradient += ")";
			
			var bgImageContent = g_ucAdmin.getVal(css, "background-image");
			if(bgImageContent)
				bgImageContent += ", ";
			
			bgImageContent += strGradient;
			
			css["background-image"] = bgImageContent;
			
		}
		
		
		return(css);
	}
	
	/**
	 * update cols and addons css of the row or container
	 */
	function updateVisualCss_updateColsAndAddons(objSettings, parentID, selectorParent, objStyleContainer){
		
		//------ child columns ---- 
		
		var cssCol = {};
		cssCol = g_ucAdmin.addCssSetting(objSettings, cssCol, "col_gutter", "padding-left","px");
		cssCol = g_ucAdmin.addCssSetting(objSettings, cssCol, "col_gutter", "padding-right","px");
		
		var colSelector = selectorParent+" .uc-grid-col";
		var strCss = g_ucAdmin.arrCssToStrCss(cssCol, colSelector);
		
		//------ child addons ---- 
		
		var addonBoxStyle = "";
		var spaceBetweenAddons = g_ucAdmin.getVal(objSettings, "space_between_addons", null);
		
		if(spaceBetweenAddons){
			spaceBetweenAddons = g_ucAdmin.normalizeSizeValue(spaceBetweenAddons);
			addonBoxStyle += "margin-top:" + spaceBetweenAddons+";";
		}
		
		var addonsSelector = selectorParent+" .uc-grid-col .uc-grid-col-addon + .uc-grid-col-addon";
		var selectorFirstAddon = selectorParent+" .uc-grid-col .uc-grid-overlay-empty + .uc-grid-col-addon";
		
		if(addonBoxStyle){
			if(strCss)
				strCss += "\n";
			
			strCss += addonsSelector+"{"+addonBoxStyle+"}";
			strCss += selectorFirstAddon+"{margin-top:0px;}";
		}
		
		g_ucAdmin.printCssStyle(strCss, parentID+"_children", objStyleContainer);
				
		//mobile columns
		var arrSettings = {
				"padding-left":"col_gutter",
				"padding-right":"col_gutter"
		};
		
		var sizeRelatedCss = "";
		
		sizeRelatedCss += getSizeRelatedCss(arrSettings, objSettings, colSelector);
		
		//mobile addons
		var arrSettings = {
				"margin-top":"space_between_addons"
		};
		
		sizeRelatedCss += getSizeRelatedCss(arrSettings, objSettings, addonsSelector);
		
		
		g_ucAdmin.printCssStyle(sizeRelatedCss, parentID+"_size", g_objRowStyleContainer);
		
	}
	
	
	/**
	 * update row visual css
	 */
	function updateRowVisual_css(objRow, objSettings){
		
		var rowID = objRow.prop("id");
		var selectorRow = g_gridID+" .uc-grid-row#"+rowID;
		var selectorContainer = selectorRow+" .uc-grid-row-container";
		
		
		//back color
		var cssRow = {};
		var cssRowMobile = {};
		var cssRowTablet = {};
		
		//output common objects
		var cssRow = {};
		updateElementVisual(objRow, cssRow, objSettings, "row");
		
		
		//------ row anchor ---- 
		var strAnchorID = g_ucAdmin.getVal(objSettings, "row_id");
		var objAnchor = objRow.children(".uc-grid-row-anchor");
		var objAnchorIndicator = objRow.children(".uc-grid-row-anchor-indicator");
		
		if(strAnchorID){
			objAnchor.prop("id", strAnchorID);
			objRow.addClass("uc-has-anchor");
			objAnchorIndicator.text("#"+strAnchorID);
		}else{
			objAnchor.prop("id","");			
			objRow.removeClass("uc-has-anchor");
			objAnchorIndicator.find("span").text("");
		}
		
		
		updateVisualCss_updateColsAndAddons(objSettings, rowID, selectorRow, g_objRowStyleContainer);
		
		var strCss = "";
		
		
		//----------- Container
		
		var strStyleContainer = "";
		var containerWidth = g_ucAdmin.getVal(objSettings, "row_container_width", null);
		if(containerWidth){
			containerWidth = g_ucAdmin.normalizeSizeValue(containerWidth);
			strStyleContainer += "max-width:" + containerWidth+";";
		}
		
		//add container css
		var containerAddCss = g_ucAdmin.getVal(objSettings, "row_container_css", null);
		if(containerAddCss){
			containerAddCss = g_ucAdmin.removeLineBreaks(containerAddCss);
			strStyleContainer += containerAddCss;
		}
		
		
		if(strStyleContainer){
			strCss += "\n"+selectorContainer+"{"+strStyleContainer+"}";
		}
		
		//------ print inner objects css ---- 
		
		g_ucAdmin.printCssStyle(strCss, rowID, g_objRowStyleContainer);
		
	}
	
	/**
	 * check if section intersect with row or addon
	 */
	function updateRowVisual_buttons_getIntersect(objRow){
		
		//var objContainers = getRowContainersAll(objRow);
		//var objContainer = objContainers.last();
		var intersectType = null;
		
		var rowPaddingBottom = getRowSetting(objRow, "row_padding_bottom", true);
		if(rowPaddingBottom >= 30)
			return(null);
		
		intersectType = "container";
		
		return(intersectType);
	}
	
	
	
	/**
	 * check container panel if intersects with row panel
	 */
	function updateRowVisual_buttons_firstContainerPanel(objRow){
		
		if(objRow.length == 0)
			return(false);
		
		var objContainer = getRowContainer(objRow);
		
		var offsetRow = objRow.offset();
		var offsetContainer = objContainer.offset();
		
		var diffTop = offsetContainer.top - offsetRow.top;
		if(diffTop > 30){
			objRow.removeClass("uc-container-panel-intersect");
			return(false);
		}
		
		var diffLeft = offsetContainer.left - offsetRow.left;
		if(diffLeft > 30){
			objRow.removeClass("uc-container-panel-intersect");
			return(false);
		}
		
		//if there is intersection add class
		objRow.addClass("uc-container-panel-intersect");
		
	}
	
	/**
	 * check buttons intersection
	 */
	function updateRowVisual_buttons(objRow){
				
		validateRow(objRow);
		
		//updateRowVisual_buttons_bottomButton(objRow);
		
		//try to update visual twice, for speed first time and more reliability second time
		setTimeout(function(){
			updateRowVisual_buttons_firstContainerPanel(objRow);
		},500);
		
		setTimeout(function(){
			updateRowVisual_buttons_firstContainerPanel(objRow);
		},2000);
		
	}
	
	
	/**
	 * update row css
	 */
	function updateRowVisual(objRow, settings){
		
		var objSettings = settings || objRow.data("settings");
		
		updateRowVisual_css(objRow, objSettings);
		updateRowVisual_buttons(objRow, objSettings);
		
	}
	
	
	
	function ____________ROW_SETTINGS______________(){}
	
	/**
	 * update row settings placeholders
	 */
	function updateRowSettingsPlaceholders(objRowSettings, objOptions){
		
		if(!objOptions)
			var objOptions = getCombinedOptions();
				
		var objRowPlaceholders = jQuery.extend({}, objOptions);
		
		var arrSettingNames = jQuery.extend([], g_temp.settings_placeholders);
		arrSettingNames.push("noprefix_col_gutter");
		arrSettingNames.push("noprefix_space_between_addons");
		arrSettingNames.push("noprefix_shape_devider_top_height");
		
		objRowPlaceholders = getObjSizePlaceholders(arrSettingNames, objRowPlaceholders, objRowSettings, objOptions,"row","0");
				
		if(g_panel)
			g_panel.updatePlaceholders("row-settings", objRowPlaceholders);
	}
	
	
	/**
	 * check shape deviders if color changed
	 */
	function checkRowUpdateRelatedElements(objRow, oldSettings, newSettings){
				
		if(g_temp.is_data_inited == false)
			return(false);
				
		var isBGChanged = isElementBGColorChange(oldSettings, newSettings);
				
		if(isBGChanged == false)
			return(false);
		
		//--- check rows
		
		var prevRow = getPrevElement(objRow, "row");
		if(prevRow)
			onShapeDetectedColorParentChange(prevRow, "bottom");
		
		var nextRow = getNextElement(objRow, "row");
		if(nextRow)
			onShapeDetectedColorParentChange(nextRow, "top");
		
		
		//--- check columns
		var objCols = getCols(objRow, true);
		if(objCols.length == 0)
			return(false);
		
		var objCols = getAllRowCols(objRow);
		
		objCols.each(function(index, col){
			
			var objCol = jQuery(col);
			
			onShapeDetectedColorParentChange(objCol, "top");
			onShapeDetectedColorParentChange(objCol, "bottom");
		});
		
	}
	
	
	/**
	 * set row settings, update css
	 */
	function updateRowSettings(objRow, objSettings){
		
		//check bg color change
		var oldSettings = objRow.data("settings");
		
		objRow.data("settings", objSettings);
		
		updateRowVisual(objRow, objSettings);
		
		if(g_panel && g_panel.isVisible())
			updateRowSettingsPlaceholders(objSettings);
		
		triggerEvent(t.events.ROW_SETTINGS_UPDATED, objRow);
		
		checkRowUpdateRelatedElements(objRow, oldSettings, objSettings);
		
	}
	
	
	/**
	 * get row settings, combined with grid settings
	 */
	function getRowSettings(objRow, addGridOptions, arrFilter){
		
		var objRowSettings = objRow.data("settings");
		
		var objSettings = {};
		
		if(objRowSettings)
			jQuery.extend(objSettings, objRowSettings);
		
		//old Color fix
		var oldColor = g_ucAdmin.getVal(objSettings, "row_background_color");
		if(oldColor)
			objSettings["bg_color"] = oldColor;
		
		
		//add grid options
		if(addGridOptions === true){
			var options = getCombinedOptions();
			jQuery.each(options, function(key, value){
				var rowOption = g_ucAdmin.getVal(objSettings, key);
				if(rowOption === "")
					objSettings[key] = value;
			});
		}
		
		if(arrFilter)
			objSettings = g_ucAdmin.filterObjectByKeys(objSettings, arrFilter);
		
		return(objSettings);
	}
	
	
	
	/**
	 * get row setting
	 */
	function getRowSetting(objRow, settingName, isNumeric){
		
		var objSettings = getRowSettings(objRow, true);
		
		var getValOpt = null;
		if(isNumeric === true)
			getValOpt = g_ucAdmin.getvalopt.FORCE_NUMERIC;
		
		var value = g_ucAdmin.getVal(objSettings, settingName, 0, getValOpt);
		
		return(value);
	}
	
	
	/**
	 * open row settings dialog
	 */
	function openRowSettingsPanel(objRow){
		
		var objSettingsData = getRowSettings(objRow);
		var rowID = objRow.prop("id");
		
		var numRowAddons = getNumRowAddons(objRow);
		var isEmptyRow = (numRowAddons == 0);
		
		var params = {};
		if(isEmptyRow)
			params["add_title_prefix"] = g_uctext["empty"];
		else{
			var rowTitle = getRowTitle(objRow);
			
			var rowIndex = objRow.index() + 1;
			params["replace_title"] = rowTitle + " " + g_uctext["settings"];
		}
		
		if(g_panel)
			g_panel.open("row-settings", objSettingsData, rowID, null, params);
		
		updateRowSettingsPlaceholders(objSettingsData);
	}
	
	
	/**
	 * get column settings object
	 */
	function getColSettings(objCol){
		validateCol(objCol);
		
		var objSettings = objCol.data("settings");
		
		if(!objSettings)
			objSettings = {};
		
		return(objSettings);
	}
	
	
	/**
	 * apply row settings from panel
	 */
	function applyRowSettings(params){
		
		var isInstant = g_ucAdmin.getVal(params, "is_instant");
		var paramName = g_ucAdmin.getVal(params, "name");
		
		if(isInstant == true){		//check delay oriented params
			
			var isSettingDelayed = (jQuery.inArray(paramName, g_temp.settings_delay_row) !== -1);
			
			if(isSettingDelayed == true)
				return(false);
		}
		
		
		var data = g_panel.getPaneData("row-settings");
		var rowID = data.objectID;
		var objRow = jQuery("#"+rowID);
		
		if(objRow.length == 0)
			return(false);
		
		
		updateRowSettings(objRow, data.settings);
	}
	
	
	/**
	 * import from library
	 */
	function importSectionFromLibrary(objRow){
		
		if(!g_objBrowserSections)
			throw new Error("Sections browser not inited");
		
		g_objBrowserSections.openAddonsBrowser(null, function(data){
			
			var layoutID = g_ucAdmin.getVal(data, "id");
			if(!layoutID)
				throw new Error("empty layout id for import");
			
			var dataSend = {};
			dataSend["layoutid"] = layoutID;
			
			g_panel.close();
			
			g_ucAdmin.ajaxRequest("get_grid_import_layout_data", dataSend, function(response){
				
				var gridData = response["grid_data"];
				var objData = jQuery.parseJSON(gridData);
				
				var rows = g_ucAdmin.getVal(objData, "rows");
				
				jQuery.each(rows, function(index, row){
					
					initByData_row(row, objRow, "before");
										
				});
				
				
			});
			
		});
		
	}
	
	/**
	 * open save section settings panel
	 */
	function openSaveSectionSettingsPanel(objRow){
		
		var rowID = objRow.prop("id");
		
		if(!g_panel)
			return(false);
		
		var title = getRowTitle(objRow);
		var objSectionData = getGridData_row(objRow, false, true);
				
		var strData = g_ucAdmin.encodeObjectForSave(objSectionData);
		
		var objSettings = {};
		objSettings["section_title"] = title;
		objSettings["section_data"] = strData;
				
		var params = {
			"disable_trigger_change":true
		};
		
		g_panel.open("save-section", objSettings , rowID, null, params);
	}
	
	
	function ____________MULTIPLE_ROWS______________(){}
	
	
	/**
	 * update all rows visual
	 */
	function updateAllRowsVisual(){
		var rows = getRows();
				
		jQuery.each(rows,function(index, row){
			var objRow = jQuery(row);
			updateRowVisual(objRow);
		});
	}
	
	
	/**
	 * update rows icons, enable / disable relevant icons
	 */
	function updateRowsIcons(){
		
		//disable paste icons
		if(!g_objBuffer)
			return(false);
		
		var storedBufferType = g_objBuffer.getStoredType();
		
		if(storedBufferType == "row"){
			g_objGrid.addClass("uc-inbuffer-row");
		}
		else{
			g_objGrid.removeClass("uc-inbuffer-row");
		}
		
	}

	function ____________ROW_CONTAINER______________(){}
		
	
	
	/**
	 * get row container
	 * position - can be first - default or "last"
	 * @param objRow
	 */
	function getRowContainer(objRow, position){
		
		if(objRow.hasClass("uc-grid-row-container"))
			return(objRow);
		
		var objContainer = objRow.find(".uc-grid-row-container");
		
		if(objContainer.length > 1){
			if(position == "last")
				objContainer = objContainer.last();
			else
				objContainer = jQuery(objContainer[0]);
		}
		
		g_ucAdmin.validateDomElement(objContainer, "Row Container");
		return(objContainer);
	}
	
	/**
	 * validate if the object is row container
	 */
	function validateRowContainer(objContainer){
		
		if(!objContainer || objContainer.length == 0){
			console.trace();
			throw new Error("row container not found");
		}
		
		if(typeof objContainer.hasClass != "function"){
			console.trace();
			trace(objContainer);
			throw new Error("row container is not jquery object");
		}
		
		if(objContainer.hasClass("uc-grid-row-container") == false){
			trace(objContainer);
			throw new Error("Wrong row container object");
		}
	}
	
	
	/**
	 * get parent row
	 */
	function getParentRowContainer(objChild){
		
		var objContainer = objChild.parents(".uc-grid-row-container");
		
		return(objContainer);
	}
	
	
	/**
	 * get row container html
	 */
	function getHtmlRowContainer(){
		
		var containerID = getNewElementID("container");
		
		html = "";
		html += "	<div id='"+containerID+"' class='uc-grid-row-container unite-clearfix'>";
		
		//add hover lines
		
		html += "	<div class='uc-grid-container-hover uc-hover-top uc-grid-extras'></div>";
		html += "	<div class='uc-grid-container-hover uc-hover-bottom uc-grid-extras'></div>";
		html += "	<div class='uc-grid-container-hover uc-hover-left uc-grid-extras'></div>";
		html += "	<div class='uc-grid-container-hover uc-hover-right uc-grid-extras'></div>";
		
		//add container panel
		html += "	<div class='uc-grid-object-panel uc-grid-container-panel uc-grid-extras'>";
		html += "			<a href='javascript:void(0)' data-action='container_settings' data-actiontype='container' title='Row Settings' class=\"uc-container-icon uc-grid-action-icon uc-tip\" ><i class=\""+getIcon("settings")+"\" aria-hidden=\"true\"></i></a> ";
		html += "			<a href='javascript:void(0)' data-action='duplicate_container' data-actiontype='container' title='Duplicate Row' class=\"uc-container-icon uc-grid-action-icon uc-tip\" ><i class=\""+getIcon("duplicate")+"\" aria-hidden=\"true\"></i></a> ";
		html += "			<a href='javascript:void(0)' data-action='delete_container' data-actiontype='container' title='Delete Row' class=\"uc-container-icon uc-grid-action-icon uc-tip\" ><i class=\""+getIcon("delete")+"\" aria-hidden=\"true\"></i></a> ";
		html += "			<a href='javascript:void(0)' title='Move Row' class=\"uc-container-icon uc-grid-icon-move uc-container-icon-move uc-tip\" ><i class=\""+getIcon("move")+"\" aria-hidden=\"true\"></i></a> ";
		html += "			<a href='javascript:void(0)' data-action='add_row_container' data-actiontype='container' title='Add Row' class=\"uc-container-icon uc-grid-action-icon uc-tip\" ><i class=\"fa fa-plus\" aria-hidden=\"true\"></i></a> ";
        
		/*
		html += "			<a href='javascript:void(0)' data-action='container_columns_layout' data-actiontype='container' title='Row Columns Layout' class=\"uc-container-icon uc-grid-action-icon uc-tip\" ><i class=\"fal fa-th-list\" aria-hidden=\"true\"></i></a> ";
        */
		
		html += "	</div>";	//container panel end
		
		//add another container
		/*
		html += "	<a href='javascript:void(0)' data-action='add_row_container' data-actiontype='container' title='Add Row' class='uc-btn-wrap uc-grid-action-icon uc-button-addcontainer-wrapper uc-grid-extras'><span class='uc-btn uc-btn-square'><i class='fa fa-plus' aria-hidden='true'></i></span></a>";
		*/
		
		html += "</div>";	//row container end

		return(html);
	}
	
	
	/**
	 * add row container
	 */
	function addEmptyRowContainer(objElement){
		
		var html = getHtmlRowContainer();
		var objContainer = jQuery(html);
		
		var type = getElementType(objElement);
		
		
		switch(type){
			case "row":
				objElement.append(objContainer);
			break;
			case "container":
				objContainer.insertAfter(objElement);
			break;
		}
		
		var objRow = getParentRow(objElement);
		
		triggerEvent(t.events.ROW_CONTAINER_ADDED, objContainer);
		triggerEvent(t.events.ROW_CONTAINERS_UPDATED, objRow);
		
		return(objContainer);
	}
	
	
	/**
	 * add row container
	 */
	function addRowContainer(objContainer, isFromRow){
        
		if(isFromRow === true)	//if the element is row passed
			objContainer = getRowContainer(objContainer, "last");
		
		
		var objContainer = addEmptyRowContainer(objContainer);
		addColumn(objContainer);
	}
	
	
	/**
	 * get last row container
	 */
	function getRowContainersAll(objRow){
		
		var objContainers = objRow.find(".uc-grid-row-container");
		
		return(objContainers);
	}
	
	
	/**
	 * get number of row containers
	 */
	function getNumContainers(objRow){
		
		var objContainers = getRowContainersAll(objRow);
		
		return(objContainers.length);
	}
	
	
	/**
	 * delete row container
	 */
	function deleteRowContainer(objContainer){
		
		validateRow(objContainer);
		
		var objRow = getParentRow(objContainer);
		
		var numContainers = getNumContainers(objRow);
		if(numContainers <= 1){
			deleteRow(objRow);
			return(false);
		}
		
		var containerID = objContainer.prop("id");
		
		objContainer.remove();
		
		triggerEvent(t.events.ROW_CONTAINERS_UPDATED, objRow);
		triggerEvent(t.events.ELEMENT_REMOVED, containerID);
		
		
	}
	
	
	/**
	 * duplicate container
	 */
	function duplicateRowContainer(objContainer){
		
		//get container in case that row passed
		objContainer = getRowContainer(objContainer);
		
		objContainer.sortable("destroy");
		
		var containerID = getNewElementID("container");
		
		var objContainerCopy = objContainer.clone(true, true);
		
		objContainerCopy.attr("id", containerID);
				
		objContainerCopy.insertAfter(objContainer);
		
		var objRow = getParentRow(objContainer);
		
		triggerEvent(t.events.ROW_CONTAINER_DUPLICATED, objContainerCopy);
		triggerEvent(t.events.ROW_CONTAINERS_UPDATED, objRow);

		//make sortable the initial container again
		
		initSortableColumns(objContainer);		
	}
	
	
    /**
	 * row layout chosen
     */
    function changeColumnsLayout(objContainer, layoutType) {
    	
        var objLayout = jQuery(this);
        
        var columnsSize = layoutType.split('-');
        var numCols = getNumCols(objContainer);
        var newNumCols = columnsSize.length;
        
        //even number, just resize
        if(numCols == newNumCols){
        	
            columnsSize.forEach(function (size, index) {
                var objCol = getCol(objContainer, index);
                setColSize(objCol, size);
            });
            
            return(false);
		}
        
        //add more columns, add new
        if(newNumCols > numCols) {
        	
            columnsSize.forEach(function (size, index) {
            	
            	if(index < numCols){
                	var objCol = getCol(objContainer, index);
                    setColSize(objCol, size);
            	}else
                    addLayoutColumn(objContainer, size);
            });
            
            return(false);
        }
        
        
        //reduce number of columns - put the rest of addons to the last column
        
    	var isOK = true;
    	var numAddons = getNumRowAddons(objContainer);
    	if(numAddons > 1)
    		isOK = confirm("Replace Addons?");
    	 
    	if(isOK == false)
    		return(false);
    	
        for (var i = numCols - 1; i >= 0; i--) {
            var objCol = getCol(objContainer, i);
            if (i < columnsSize.length)
                setColSize(objCol, columnsSize[i]);
            else {
                var objAddons = getColAddonsData(objCol);
                var lastObjCol = getCol(objContainer, columnsSize.length - 1);
                if (objAddons.length)
                    objAddons.forEach(function (item, index) {
                        addColAddon(lastObjCol, item, true);
                    });
                deleteCol(objCol);
            }
        }
        
    }
	
	

	/**
	 * do row container related action
	 */
	function doContainerAction(action, objContainer){
		
		validateRowContainer(objContainer);
		
		switch(action){
			case "delete_container":
				deleteRowContainer(objContainer);
			break;
			case "duplicate_container":
				duplicateRowContainer(objContainer);
			break;
			case "container_settings":
				openContainerSettingsPanel(objContainer);
			break;
            case "add_row_container":
            	addRowContainer(objContainer);
            break;
			default:
				throw new Error("Wrong container action: "+action);
			break;
		}
		
	}
	
	function ____________CONTAINER_SETTINGS______________(){}
	
	
	/**
	 * update placeholders
	 */
	function updateRowContainerSettingsPlaceholders(objContainer, objContainerSettings){
		
		var objRow = getParentRow(objContainer);
		var objRowSettings = getRowSettings(objRow, true);
		
		var arrSettingNames = jQuery.extend([], g_temp.settings_placeholders);
		arrSettingNames.push("noprefix_col_gutter");
		arrSettingNames.push("noprefix_space_between_addons");
		
		arrSettingNames.push("row_container_width");
		arrSettingNames.push("col_gutter");
		arrSettingNames.push("space_between_addons");
		
		var rowSettingsFiltered = g_ucAdmin.filterObjectByKeys(objRowSettings, arrSettingNames);
		var objContainerPlaceholders = jQuery.extend({}, rowSettingsFiltered);
		
		objContainerPlaceholders = getObjSizePlaceholders(arrSettingNames, objContainerPlaceholders, objContainerSettings, objRowSettings, null, "0");
						
		g_panel.updatePlaceholders("container-settings", objContainerPlaceholders);
	}

	
	/**
	 * open row settings dialog
	 */
	function openContainerSettingsPanel(objContainer){
		
		//in case of row pass
		objContainer = getRowContainer(objContainer);
		
		var objSettingsData = getRowContainerSettings(objContainer);
		
		var containerID = objContainer.prop("id");
		
		g_panel.open("container-settings", objSettingsData, containerID);

		updateRowContainerSettingsPlaceholders(objContainer, objSettingsData);
	}
	
	/**
	 * get container settings
	 */
	function getRowContainerSettings(objContainer){
		
		var objSettings = objContainer.data("settings");
		if(!objSettings)
			var objSettings = {};
		
		//add columns layout
		objSettings["col_layout"] = getColSizesString(objContainer);
				
		return(objSettings);
	}
	
	
	/**
	 * update container visual settings
	 */
	function updateRowContainerVisual(objContainer){
		
		var containerID = objContainer.prop("id");
		var selectorContainer = g_gridID+" .uc-grid-row-container#"+containerID;
		
		var objSettings = getRowContainerSettings(objContainer);
		var objCss = {};
		
		//update defaults
		var cssContainer = {};
		
		//get css object
		var containerWidth = g_ucAdmin.getVal(objSettings, "row_container_width");
		
		if(containerWidth !== "")
			cssContainer["max-width"] = g_ucAdmin.normalizeSizeValue(containerWidth);
		
		updateElementVisual(objContainer, cssContainer, objSettings, "container");
		
		//update columns and addons
		updateVisualCss_updateColsAndAddons(objSettings, containerID, selectorContainer, g_objColStyleContainer);
		
	}
	
	
	/**
	 * update container settings
	 */
	function updateContainerSettings(objContainer, objSettings){
		
		objContainer.data("settings", objSettings);
		
		updateRowContainerVisual(objContainer, objSettings);
		
		if(g_panel && g_panel.isVisible())
			updateRowContainerSettingsPlaceholders(objContainer, objSettings);
		
		triggerEvent(t.events.ROW_CONTAINER_SETTINGS_UPDATED, objContainer);
	}
	
	
	/**
	 * apply row container settings
	 */
	function applyRowContainerSettings(params){
		
		var isInstant = g_ucAdmin.getVal(params, "is_instant");
		var paramName = g_ucAdmin.getVal(params, "name");
		var paramValue = g_ucAdmin.getVal(params, "value");
		
		if(isInstant == true){		//check delay oriented params			
			var isSettingDelayed = (jQuery.inArray(paramName, g_temp.settings_delay_row) !== -1);
			
			if(isSettingDelayed == true)
				return(false);
		}
				
		var data = g_panel.getPaneData("container-settings");
		var containerID = data.objectID;
		var objSettings = g_ucAdmin.getVal(data, "settings");
		
		var objContainer = jQuery("#"+containerID);
		if(objContainer.length == 0)
			return(false);
		
		//update col layout, if was a click on layout row
		if(paramName == "col_layout"){
			changeColumnsLayout(objContainer, paramValue);
			return(false);
		}
		
		updateContainerSettings(objContainer, objSettings);
	}
	
	
	function ____________COLUMN______________(){}
	
	
	
	/**
	 * validate that the object is column
	 */
	function validateCol(objCol){
		
		g_ucAdmin.validateDomElement(objCol, "column");
		
		if(objCol.hasClass("uc-grid-col") == false)
			throw new Error("The object is not column type");
	}
	
	
	/**
	 * get html columns
	 */
	function getHtmlColumn(){
		
		var colID = getNewElementID("col");
		
		var html = "";
		html += "<div id='"+colID+"' class=\"uc-grid-col\">";
		
		//add shape deviders
		
		html += "<div class='uc-grid-shape-devider uc-devider-top'></div>";
		html += "<div class='uc-grid-shape-devider uc-devider-bottom'></div>";
		
		html += "<div class=\"uc-grid-box-wrapper\">";
		
		html += "		<div class=\"uc-grid-col-addons\">";
				
		//set addon
		html += "			<div class=\"uc-grid-col-addon uc-grid-addon-sortable uc-grid-overlay-empty uc-grid-action-icon\" data-actiontype='col' data-action='add_col_addon' >";
		html += "				<div class=\"uc-grid-col-addon-html\">";
		html +=	"					<div class=\"uc-overlay-empty-content\" title='Select Addon'>";
		html += "						<span class='uc-overlay-empty-button uc-btn uc-btn-square uc-btn-muted'>";
		html += "							<span class='fa fa-plus'></span>";
		html += "						</span>";
		html += "						<i class='uc-overlay-empty-loader "+getIcon("spinner")+"'></i>";
		html +=	"         </div>";
		html +=	"				</div>";
		html +=	"				<div class=\"uc-grid-overlay-edit\" style=\"display: none;\"></div>"; 
		html += "			</div>";
		
		html += "		</div>";	//col addons
				
		
		html += "	</div>";	//box wrapper end
		
		//hidden overlay
		html += "<div class='uc-grid-element-hidden-overlay'>"+g_uctext.hidden_column+"</div>";
		
		//top panel
		html += "		<div class = \"uc-grid-object-panel uc-grid-col-panel uc-grid-extras\">";
		html += "			<a href='javascript:void(0)' title='"+g_uctext.move_column+"' class=\"uc-col-icon uc-grid-icon-move uc-col-icon-move uc-tip\"><i class=\""+getIcon("move")+"\" aria-hidden=\"true\"></i></a> ";
		html += "			<a href='javascript:void(0)' data-action='duplicate' data-actiontype='col' title='"+g_uctext.duplicate_column+"' class=\"uc-col-icon uc-grid-action-icon uc-tip\"><i class=\""+getIcon("duplicate")+"\" aria-hidden=\"true\"></i></a> ";
		html += "			<a href='javascript:void(0)' data-action='delete' data-actiontype='col' title='"+g_uctext.delete_column+"' class=\"uc-col-icon uc-grid-action-icon uc-tip\"><i class=\""+getIcon("delete")+"\" aria-hidden=\"true\"></i></a> ";
		html += "			<a href='javascript:void(0)' data-action='settings' data-actiontype='col' title='"+g_uctext.column_settings+"' class=\"uc-col-icon uc-grid-action-icon uc-tip\"><i class=\""+getIcon("settings")+"\" aria-hidden=\"true\"></i></a> ";
		html += "			<a href='javascript:void(0)' data-action='addcol_after' data-actiontype='col' title='"+g_uctext.add_column+"' class=\"uc-col-icon uc-grid-action-icon uc-icon-addcol uc-tip\"><i class=\"fa fa-plus\" aria-hidden=\"true\"></i></a> ";
		html += "		</div>";
		
		html += "<div class='uc-grid-col-hover uc-hover-left uc-grid-extras'></div>";
		html += "<div class='uc-grid-col-hover uc-hover-top uc-grid-extras'></div>";
		html += "<div class='uc-grid-col-hover uc-hover-right uc-grid-extras'></div>";
		html += "<div class='uc-grid-col-hover uc-hover-bottom uc-grid-extras'></div>";
        
		/*
		html += "			<a href='javascript:void(0)' data-action='addcol_before' data-actiontype='col' title='"+g_uctext.add_column+"' class=\"uc-icon-addcol uc-addcol-before uc-grid-action-icon\" ><span class='uc-btn uc-btn-square uc-btn-column uc-grid-extras'><i class=\"fa fa-plus\" aria-hidden=\"true\"></i></span></a> ";
		html += "			<a href='javascript:void(0)' data-action='addcol_after' data-actiontype='col' title='"+g_uctext.add_column+"' class=\"uc-icon-addcol uc-addcol-after uc-grid-action-icon\" ><span class='uc-btn uc-btn-square uc-btn-column uc-grid-extras'><i class=\"fa fa-plus\" aria-hidden=\"true\"></i></span></a>";
		*/
		
		html += "</div>";	//col;
		
		return(html);
	}
	
	/**
	 * get all row cols from all the containers
	 */
	function getAllRowCols(objRow){
		
		var objCols = objRow.find(".uc-grid-col");
		
		return(objCols);
	}
	
	
	/**
	 * get columns in row
	 */
	function getCols(objRow, novalidate){
		
		var objContainer = getRowContainer(objRow);
		
		var objCols = objContainer.children(".uc-grid-col");
		
		if(objCols.length == 0 && !novalidate)
			throw new Error("getCols error - row should have at least 1 column");
		
		return(objCols);
	}
	
	
	/**
	 * get column by number
	 */
	function getCol(objRow, numCol){
		
		var objCols = getCols(objRow);
		if(numCol >= objCols.length)
			throw new Error("There is no col number: "+numCol+" in the row");
		
		var objCol = jQuery(objCols[numCol]);
		
		return(objCol);
	}
	
	
	
	/**
	 * get parent column
	 */
	function getParentCol(objChild){
		
		var objCol = objChild.parents(".uc-grid-col");
		
		return(objCol);
	}
	
	
	/**
	 * get number of columns in row or container
	 */
	function getNumCols(objRow){
		
		validateRow(objRow);
		
		var objCols = getCols(objRow, true);
		
		var numCols = objCols.length;
		
		return(numCols);
	}
	
	/**
	 * get addons wrapper
	 */
	function getColAddonsWrapper(objCol){
		
		var objAddonsWrapper = objCol.find(".uc-grid-col-addons");
		
		g_ucAdmin.validateDomElement(objAddonsWrapper, "col addons wrapper");
		
		return(objAddonsWrapper);
	}
	
		
	
	/**
	 * check if it's first column
	 */
	function isFirstCol(objCol){
		var isFirst = objCol.hasClass("uc-col-first");
		
		return isFirst;
	}
	
	
	/**
	 * check if it's last column
	 */
	function isLastCol(objCol){
		var isLast = objCol.hasClass("uc-col-last");
		
		return isLast;
	}
	
	
	/**
	 * get column size from the column
	 */
	function getColSize(objCol){
		
		validateCol(objCol);
		
		var arrClasses = objCol[0].className.match(/\buc-colsize\S+/ig);
		
		if(arrClasses.length == 0)
			return(null);
		
		var className = arrClasses[0];
		var size = className.replace("uc-colsize-", "");
		
		return(size);
	}

	
	/**
	 * get row container row sizes string
	 */
	function getColSizesString(objContainer){
		
		var objColumns = getCols(objContainer, true);
		if(objColumns.length == 0)
			return("");
		
		var strSizes = "";
		jQuery.each(objColumns, function(index, col){
			
			var objCol = jQuery(col);
			var size = getColSize(objCol);
			
			if(strSizes.length)
				strSizes += "-";
			
			strSizes += size;
		});
		
		
		return(strSizes);
	}
	
	
	function ____________COLUMN_ACTIONS______________(){}
	
	
	/**
	 * add empty column.
	 * row can be row container as well
	 * the mode can be: empty, before, after
	 */
	function addColumn(objRow, objCol, mode){
		
		if(!objRow){
			
			if(objCol)
				var objContainer = getParentRowContainer(objCol);
			else{
				var objRow = getRow();
				var objContainer = getRowContainer(objRow);
			}
		}else{
			var objContainer = getRowContainer(objRow);
		}
		
		validateRowContainer(objContainer);
		
		//check the limits
		var numCols = getNumCols(objContainer);
				
		if(numCols == g_vars.max_cols)
			return(false);
				
		//add the column
		var htmlCol = getHtmlColumn();
		
		var objNewCol = jQuery(htmlCol);
		
		//insert before or after column
		objNewCol.hide();
		
		if(objCol){
			
			switch(mode){
				case "before":
					objNewCol.insertBefore(objCol);
				break;
				case "after":
					objNewCol.insertAfter(objCol);
				break;
				default:
				break;
			}
			
			
		}else{	//insert last column
						
			objContainer.append(objNewCol);
		}
		
		triggerEvent(t.events.ROW_COLUMNS_UPDATED, objContainer);
		triggerEvent(t.events.COL_ADDED, objNewCol);
		
		//show the column after transition
		setTimeout(function(){
			objNewCol.show();
		},550);
		
		return(objNewCol);
	}
	
    /**
	 * add columns for layout
     */
    function addLayoutColumn(objRow, colClass){
    	
    	if(!objRow)
    		return(false);
    	
    	var objContainer = getRowContainer(objRow);
    	
        var numCols = getNumCols(objContainer);
        var htmlCol = getHtmlColumn();
        var objNewCol = jQuery(htmlCol).addClass("uc-col-last uc-colsize-"+colClass+" uc-col-trans");
        
        objNewCol.hide();
        
        var objCol = getCol(objContainer, numCols - 1);
        jQuery(objCol).removeClass("uc-col-last");
        
        objNewCol.insertAfter(objCol);
        updateColOperationButtons(objContainer);
        
        setTimeout(function(){
            objNewCol.show();
        },350);
            
	}
	
	
	/**
	 * duplicate column
	 */
	function duplicateCol(objCol){
		
		var objContainer = getParentRowContainer(objCol);
		
        //check the limits
		var numCols = getNumCols(objContainer);
		
		if(numCols == g_vars.max_cols)
			return(false);
		
		var objColCopy = objCol.clone(true, true);
        objColCopy.hide();
		
		var colID = getNewElementID("col");
		objColCopy.attr("id", colID);
		
		objColCopy.insertAfter(objCol);
		
		triggerEvent(t.events.ROW_COLUMNS_UPDATED, objContainer);
		
		//show the column after transition
		
		setTimeout(function(){
			objColCopy.show();
			
			triggerEvent(t.events.COL_ADDED, objColCopy);
			triggerEvent(t.events.COL_DUPLICATED, objColCopy);
		},500);    
		
		
	}
	
	
	/**
	 * 
	 * @param objCol
	 */
	function deleteCol(objCol){
		
		var objContainer = getParentRowContainer(objCol);
		var numCols = getNumCols(objContainer);
		var colID = objCol.prop("id");
		
		if(numCols <= 1){
			deleteRowContainer(objContainer);
			return(false);
		}
		
		objCol.remove();
		
		triggerEvent(t.events.ROW_COLUMNS_UPDATED, objContainer);
		triggerEvent(t.events.ELEMENT_REMOVED, colID);
	}
	
	
	
	/**
	 * update row columns classes
	 */
	function updateColsClasses(objContainer){
				
		var objCols = getCols(objContainer);
		
		var numCols = objCols.length;
		
		var colWidth = 1;	//temp value, num cells that it take
		
		var classColSize = g_vars.class_size_prefix + colWidth+ "_" + numCols;
		
		objCols.each(function(num, col){
			
			var isFirst = (num == 0);
			var isLast = (num == numCols-1);
			
			var objCol = jQuery(col);
			var isEmpty = objCol.hasClass(g_vars.class_empty);
			
			//set class
			var classCol = g_vars.class_col;

			if(isFirst)
				classCol += " "+g_vars.class_first_col;
			
			if(isLast)
				classCol += " "+g_vars.class_last_col;
			
			if(isEmpty)
				classCol += " "+g_vars.class_empty;
			
			classCol += " " + classColSize;
			
			classCol += " uc-col-trans";
			
			col.className = classCol;
			
		});
		
	}
	
	
	/**
	 * set the add col icon active / not active
	 */
	function activateAddColIcon(objIcon, isActivate){
		
		if(isActivate){
			objIcon.addClass("uc-icon-active");
		}
		else{
			objIcon.removeClass("uc-icon-active");
		}
		
	}
	
	
	/**
	 * check column operations buttons when 
	 */
	function updateColOperationButtons(objContainer){
		
		validateRowContainer(objContainer);
		
		var numCols = getNumCols(objContainer);
		
		if(numCols >= g_vars.max_cols){		//hide
			
			objContainer.addClass("uc-max-cols");
			
		}else{	//not hide
			objContainer.removeClass("uc-max-cols");
			
		}
		
	}
	
	
	/**
	 * set col empty state visual
	 */
	function setColEmptyStateVisual(objCol, isEmpty){
		
		var objOverlayEmpty = objCol.find(".uc-grid-overlay-empty");
		var objIconAddMore =  objCol.find(".uc-icon-add-more-addon");
		
		if(isEmpty == true){
			objOverlayEmpty.show();
			objIconAddMore.hide();			//empty column
		}else{
			objOverlayEmpty.hide();			//has addons
			objIconAddMore.show();
		}
		
	}
	
	
	/**
	 * set column empty state loading
	 */
	function setColEmptyStateLoading(objCol, isLoading){
				
		validateCol(objCol);
		
		var objOverlayEmptyContent = objCol.find(".uc-grid-overlay-empty .uc-overlay-empty-content");
		
		if(isLoading == true)
			objOverlayEmptyContent.addClass("uc-state-loading");
		else
			objOverlayEmptyContent.removeClass("uc-state-loading");
		
	}
	
	
	/**
	 * set row hover second mode
	 */
	function setColHoverSecondMode(event, objCol){
		
		if(!objCol)
			var objCol = jQuery(this);
		
		if(objCol.hasClass("uc-grid-col") == false)
			objCol = getParentCol(objCol);
		
		objCol.addClass("uc-over-mode2");
		
	}
    
	
	/**
	 * unset row hover second mode
	 */
	function unsetColHoverSecondMode(event, objCol){
		
		if(!objCol)
			var objCol = jQuery(this);
		
		if(objCol.hasClass("uc-grid-col") == false)
			objCol = getParentCol(objCol);
		
		objCol.removeClass("uc-over-mode2");
	}
	
	
	/**
	 * update row  layout columns classes
     */
	function setColSize(objCol, size){
		
		validateCol(objCol);
		
		var className = objCol[0].className;
		
        objCol[0].className = className.replace(/\buc-colsize\S+/ig,"uc-colsize-" + size);
        
	}
	
	/**
	 * do column action
	 */
	function doColAction(objCol, action){
		
		switch(action){
			case "delete":
				
				deleteCol(objCol);
				
			break;
			case "duplicate":
				
				duplicateCol(objCol);
			
			break;
			case "addcol_before":
				
				addColumn(null, objCol, "before");
			
			break;
			case "addcol_after":
			
				addColumn(null, objCol, "after");
				
			break;
			case "settings":
				openColSettingsPanel(objCol);				
			break;
			case "add_col_addon":
				openAddonsBrowser(objCol);				
			break;
			default:
				trace("wrong col action: "+action);
			break;
		}
		
	}
	
	
	function ______COLUMN_SETTINGS_______(){}
	
	/**
	 * set column settings placeholders
	 */
	function setColumnPlaceholders(objCol, colSettings){
		
		var objPlaceholders = {};
		
		var objRow = getParentRow(objCol);
		var filter = ["space_between_addons", "col_gutter"];
		var rowSettings = getRowSettings(objRow, true, filter);
		
		objPlaceholders["col_space_between_addons"] = g_ucAdmin.getVal(rowSettings, "space_between_addons");
		
		var colPaddingSides = g_ucAdmin.getVal(rowSettings, "col_gutter");
		objPlaceholders["padding_left"] = colPaddingSides;
		objPlaceholders["padding_right"] = colPaddingSides;
		
		//set mobile placeholders
		
		var arrSettingNames = jQuery.extend([], g_temp.settings_placeholders);
		arrSettingNames.push("col_space_between_addons");
		
		objPlaceholders = getObjSizePlaceholders(arrSettingNames, objPlaceholders, colSettings, objPlaceholders,null,"0");
		
		if(g_panel)
			g_panel.updatePlaceholders("col-settings", objPlaceholders);
	}
	
	
	/**
	 * open col settings panel
	 */
	function openColSettingsPanel(objCol){
		
		validateCol(objCol);
		var colID = objCol.prop("id");
		var colSettings = getColSettings(objCol);
		
		//set column placeholders
		setColumnPlaceholders(objCol, colSettings);
		
		g_panel.open("col-settings", colSettings, colID);
	}
	
	
	
	
	/**
	 * update column visual
	 */
	function updateColVisual(objCol, objSettings){
		
		if(!objSettings)
			var objSettings = objCol.data("settings");
		
		var cssCol = {};
		updateElementVisual(objCol, cssCol, objSettings, "col");
		
		var strCss = "";
		
		//--- column addons elements
		
		var colID = objCol.prop("id");
		var objRow = getParentRow(objCol);
		var rowID = objRow.prop("id");
		var selectorAddons = g_gridID+" .uc-grid-row#"+rowID+" .uc-grid-col#"+colID+" .uc-grid-col-addon + .uc-grid-col-addon";
		var selectorFirstAddon = g_gridID+" .uc-grid-row#"+rowID+" .uc-grid-col#"+colID+" .uc-grid-overlay-empty + .uc-grid-col-addon";
		
		var spaceBetweenAddons = g_ucAdmin.getVal(objSettings, "col_space_between_addons", null);
		if(spaceBetweenAddons){
			strCss += selectorAddons+"{";
			strCss += "margin-top:"+g_ucAdmin.normalizeSizeValue(spaceBetweenAddons);
			strCss += "}";
			
			strCss += selectorFirstAddon+"{";
			strCss += "margin-top:0px";
			strCss += "}";
			
		}
		
		g_ucAdmin.printCssStyle(strCss, colID, g_objColStyleContainer);
		
		//------- size related space between addons
		
		var arrSettings = {
				"margin-top":"col_space_between_addons"
		};
		
		var sizeRelatedCss = getSizeRelatedCss(arrSettings, objSettings, selectorAddons);
		
		g_ucAdmin.printCssStyle(sizeRelatedCss, colID+"_size", g_objColStyleContainer);
		
	}
	
	
	/**
	 * update column settings
	 */
	function updateColSettings(objCol, settings){
		
		validateCol(objCol);
		
		objCol.data("settings", settings);
		
		updateColVisual(objCol, settings);
		
		triggerEvent(t.events.COL_SETTINGS_UPDATED, objCol);
	}
	
	
	/**
	 * apply column settings
	 */
	function applyColSettings(){
		
		var data = g_panel.getPaneData("col-settings");
		var colID = data.objectID;
		var objCol = jQuery("#"+colID);
		
		if(objCol.length == 0)
			return(false);
		
		updateColSettings(objCol, data.settings);
		
		if(g_panel.isVisible())
			setColumnPlaceholders(objCol, data.settings);
		
	}
	
	
	function ____________ADDON_HTML______________(){}
	
	
	/**
	 * get the html
	 */
	function generateAddonHtml_wrapAddonHtml(addon_name, htmlAddon, addonData, onlyInner){
		
		if(!onlyInner)
			var onlyInner = false;
		
		var classAdd = "";
		
		var addonType = g_ucAdmin.getVal(addonData, "addontype");
		var isBGAddon = (addonType == g_temp.addontype_bgaddon);
		
		var serialID = addonData.extra.serial_id;
		var addonID = g_vars.id_prefix + serialID;
				
		var html = "";

		//get html of bg addon
		if(isBGAddon == true){
			
			if(onlyInner == false)
				html += "		<div id='"+addonID+"' class=\"uc-grid-bg-addon\" data-name='"+addon_name+"'>";
			
			html += htmlAddon;
			
			if(onlyInner == false)
				html += "		</div>";
			
			return(html);
		}
		
		// regular addon
		
		if(onlyInner == false)
			html += "		<div id='"+addonID+"' class=\"uc-grid-col-addon uc-grid-addon-sortable\" data-name='"+addon_name+"'>";
		
		html += "			<div class=\"uc-grid-col-addon-html unite-clearfix "+classAdd+"\" >";
		
		html += htmlAddon;
		
		html += "			</div>";
		
		html += "			<div class='uc-grid-element-hidden-overlay'>"+g_uctext.hidden_addon+"</div>";
		
		html += "			<div class=\"uc-grid-addon-hover uc-hover-left uc-grid-extras\" ></div>";
		html += "			<div class=\"uc-grid-addon-hover uc-hover-right uc-grid-extras\" ></div>";
		html += "			<div class=\"uc-grid-addon-hover uc-hover-top uc-grid-extras\" ></div>";
		html += "			<div class=\"uc-grid-addon-hover uc-hover-bottom uc-grid-extras\" ></div>";
		
		html += "			<div class='uc-grid-object-panel uc-grid-addon-panel uc-grid-extras'>";
		html += "					<a href=\"javascript:void(0)\" data-actiontype='addon' data-action='delete_addon' title=\""+g_uctext.delete_addon+"\" class=\"uc-grid-action-icon \"><i class=\""+getIcon("delete")+"\" aria-hidden=\"true\"></i></a>";
		html += "					<a href=\"javascript:void(0)\" data-actiontype='addon' data-action='addon_container_settings' title=\""+g_uctext.addon_container_settings+"\" class=\"uc-grid-action-icon \"><i class=\""+getIcon("settings")+"\" aria-hidden=\"true\"></i></a>";
		html += "					<a href=\"javascript:void(0)\" data-actiontype='addon' data-action='edit_addon' title=\""+g_uctext.edit_addon+"\" class=\"uc-grid-action-icon \"><i class=\""+getIcon("edit")+"\" aria-hidden=\"true\"></i></a>";
		html += "					<a href=\"javascript:void(0)\" data-actiontype='addon' data-action='duplicate_addon' title=\""+g_uctext.duplicate_addon+"\" class=\"uc-grid-action-icon \"><i class=\""+getIcon("duplicate")+"\" aria-hidden=\"true\"></i></a>";
		html += "					<a href='javascript:void(0)' title='"+g_uctext.move_addon+"' class=\"uc-addon-icon-move uc-grid-icon-move uc-tip\"><i class=\""+getIcon("move")+"\" aria-hidden=\"true\"></i></a> ";
		html += "					<a href=\"javascript:void(0)\" data-actiontype='addon' data-action='add_col_addon' title='"+g_uctext.add_addon_to_column+"' class=\"uc-grid-action-icon \"><i class=\"fa fa-plus\" aria-hidden=\"true\"></i></a>";
		
		html += "			</div>";
		
		//html += "		<a href='javascript:void(0)' onfocus='this.blur()' data-action='add_col_addon' data-actiontype='addon' title='"+g_uctext.add_addon_to_column+"' class=\"uc-col-icon uc-grid-action-icon uc-tip uc-icon-add-more-addon uc-grid-extras\" style='display:none'><span class='uc-btn uc-btn-square uc-btn-addon'><i class=\"fa fa-plus\" aria-hidden=\"true\"></i></span></a> ";
		
		if(onlyInner == false)
			html += "		</div>";
		
		return(html);
	}
	
	
	/**
	 * get box html desciprion, by values and admin labels
	 */
	function getBoxHtmlDescription(addonData){
		
		var extra = g_ucAdmin.getVal(addonData, "extra");
		
		var adminLabels = g_ucAdmin.getVal(extra, "admin_labels");
		var config = g_ucAdmin.getVal(addonData, "config");
		var items = g_ucAdmin.getVal(addonData, "items");
		
		if(!adminLabels || adminLabels.length == 0)
			return("");
		
		//combine description
		
		var desc = "";
				
		jQuery.each(adminLabels, function(index, arrLabel){
			
			var value = "";
			
			var key = arrLabel[0];
			var title = arrLabel[1];
			
			//take number of items
			if(key == "uc_num_items"){
				var numItems = 0;
				if(typeof items == "object")
					numItems = items.length;
				
				desc = numItems+ " "+title;
				return(true);
			}
			
			//get from config
			value = g_ucAdmin.getVal(config, key);
			
			//check for value2 (select field alternative)
			var value2 = g_ucAdmin.getVal(config, key+"_unite_selected_text");
			if(value2)
				value = value2;
			
			else{	//check for post field
				
				var value2 = g_ucAdmin.getVal(config, key+"_post_title");
				if(value2)
					value = value2;
			}
			
			
			value = jQuery.trim(value);
			value = g_ucAdmin.stripTags(value, "b,strong,i");
			
			if(!value || value == "")
				return(true);
			
			if(desc)
				desc += " | ";
			
			desc += title + ": " + value;
			
		});
				
		return(desc);
	}
	
	
	/**
	 * get box view html
	 */
	function generateAddonHtml_getBoxHtml(url_icon, title, addonData){
		
		url_icon = 	g_ucAdmin.escapeDoubleQuote(url_icon);
		title = g_ucAdmin.htmlspecialchars(title);
		
		var description = getBoxHtmlDescription(addonData);
		
		//title = addonData.extra.serial_id;	//remove me
		
		html = "";
		html += "				<img src=\""+url_icon+"\">";
		html += "				<span class='uc-grid-addon-title'>"+title+"</span>";
		
		if(description)
			html += "				<span class='uc-grid-addon-description'>"+description+"</span>";
		
		return(html);
	}
	
		
	/**
	 * put addon includes
	 */
	function putAddonLiveModeIncludes(addonData, objIncludes, funcOnLoaded){
		
		var isLoadOneByOne = true;
		
		var serialID = addonData.extra.serial_id;
		
		var handlePrefix = "uc_include_";
		
		g_ucAdmin.validateNotEmpty(serialID, "serial ID");
		
		//make a list of js handles
		var arrHandles = {};
		jQuery.each(objIncludes, function(event, objInclude){
			
			var handle = handlePrefix + objInclude.type + "_" + objInclude.handle;
			
			if( !(objInclude.type == "js" && objInclude.handle == "jquery") )
				arrHandles[handle] = objInclude;
		});
				
		var isAllFilesLoaded = false;
		
		//inner function that check that all files loaded by handle
		function checkAllFilesLoaded(){
			
			if(isAllFilesLoaded == true)
				return(false);
			
			if(!jQuery.isEmptyObject(arrHandles))
				return(false);
			
			isAllFilesLoaded = true;
			
			if(!funcOnLoaded)
				return(false);
			
			funcOnLoaded();
			
		}
		
		
		/**
		 * on js file loaded - load first js file, from available handles
		 * in case that loading one by one
		 */
		function onJsFileLoaded(){
			
			for(var index in arrHandles){
				var objInclude = arrHandles[index];
				
				if(objInclude.type == "js"){
					loadIncludeFile(objInclude);
					return(false);
				}
				
			}
			
		}
		
		
		/**
		 * load include file
		 */
		function loadIncludeFile(objInclude){
			
			var url = objInclude.url;
			var handle = handlePrefix + objInclude.type + "_" + objInclude.handle;
			var type = objInclude.type;
			
			//skip jquery for now
			if(objInclude.handle == "jquery"){
				
				checkAllFilesLoaded();
				
				if(isLoadOneByOne)
					onJsFileLoaded();
				
				return(true);
			}
			
			var data = {
					replaceID:handle,
					name: "uc_include_file"
			};
			
			//onload throw event when all scripts loaded
			data.onload = function(obj, handle){
								
				var objDomInclude = jQuery(obj);
						
				objDomInclude.data("isloaded", true);
								
				//delete the handle from the list, and check for all files loaded
				if(arrHandles.hasOwnProperty(handle) == true){
										
					delete arrHandles[handle];
					
					checkAllFilesLoaded();
					
				}//end checking
				
				if(isLoadOneByOne){
					var tagName = objDomInclude.prop("tagName").toLowerCase();
					if(tagName == "script")
						onJsFileLoaded();
				}
				
			};
			
			
			//if file not included - include it
			var objDomInclude = jQuery("#"+handle);
			
			if(objDomInclude.length == 0){
				
				objDomInclude = g_ucAdmin.loadIncludeFile(type, url, data);
			}
			else{
				
				//if the files is in the loading list but still not loaded, 
				//wait until they will be loaded and then check for firing the finish event (addons with same files)
				
				//check if the file is loaded
				var isLoaded = objDomInclude.data("isloaded");
				if(isLoaded == true){
					
					//if it's already included - remove from handle
					if(arrHandles.hasOwnProperty(handle) == true)
						delete arrHandles[handle];
					
					if(isLoadOneByOne){
						var tagName = objDomInclude.prop("tagName").toLowerCase();
						if(tagName == "script")
							onJsFileLoaded();
					}
					
					
				}else{
					
					var timeoutHandle = setInterval(function(){
						var isLoaded = objDomInclude.data("isloaded");
						
						if(isLoaded == true){
							clearInterval(timeoutHandle);
							
							if(arrHandles.hasOwnProperty(handle) == true)
								delete arrHandles[handle];
							
							checkAllFilesLoaded();
							
							if(isLoadOneByOne){
								var tagName = objDomInclude.prop("tagName").toLowerCase();
								if(tagName == "script")
									onJsFileLoaded();
							}
							
						}
						
					},100);
										
				}
								
			}			
			
			
			//add addon serialID to the include
			var arrSerials = objDomInclude.data("serials");
			
			if(!arrSerials)
				arrSerials = {};
			
			if(arrSerials.hasOwnProperty(serialID) == false)
				arrSerials[serialID] = true;
			
			
			objDomInclude.data("serials", arrSerials);
			
		}
		
		if(isLoadOneByOne == false){
			
			jQuery.each(objIncludes, function(event, objInclude){
				loadIncludeFile(objInclude);
			});
			
		}else{
			
			//load css files and first js files
			var isFirstJS = true;
			
			jQuery.each(objIncludes, function(event, objInclude){
				if(objInclude.type == "css")
					loadIncludeFile(objInclude);
				else{		//js file, load first only
					
					if(isFirstJS == true){
						loadIncludeFile(objInclude);
						isFirstJS = false;
					}
					
				}
			});
			
			
		}
		
		
		//check if all files loaded
		checkAllFilesLoaded();
		
	}
	
	
	/**
	 * check and remove addon includes if needed
	 * this function called on addon delete
	 */
	function checkRemoveLiveModeIncludes(){
		
		var objIncludes = jQuery("script[name='uc_include_file']").add("link[name='uc_include_file']");
			
		//check if there is addon serial in include.
		//if do - remove it, if no more serials - delete the include
		
		jQuery.each(objIncludes, function(event, objInclude){
			
			objInclude = jQuery(objInclude);
			var arrSerials = objInclude.data("serials");
			
			jQuery.each(arrSerials, function(serial, value){
				
				//search addon by id
				var addonID = g_vars.id_prefix + serial;
				var objAddon = jQuery("#"+addonID);
				if(objAddon.length == 0)
					delete arrSerials[serial];					
			});
			
			if(jQuery.isEmptyObject(arrSerials)){
				
				objInclude.remove();
				
			}else{	//insert serials data bacl
				objInclude.data("serials", arrSerials);
			}
			
		});
		
	}
	
	
	/**
	 * modify addon html, replace the id's to unique
	 * the html is get from server
	 */
	function modifyAddonLiveHtml(html, addonData, outputData){
		
		var resultID = addonData.extra.serial_contentid;
		
		var constants = g_ucAdmin.getVal(outputData,"constants");
		var sourceID = g_ucAdmin.getVal(constants,"uc_id");
		
		if(!sourceID || !resultID)
			return(html);
		
		//modify only if there is addon like this already
		var existingElement = jQuery("#"+sourceID);
		if(existingElement.length == 0)
			return(html);
		
		html = g_ucAdmin.replaceAll(html, sourceID, resultID);
		
		return(html);
	}
	
	
	/**
	 * handle output data when generate html
	 */
	function generateAddonHtml_handleAddonOutputData(outputData, addonData, onFinish, onlyInner){
		
		var addon_name = addonData.name;
		
		var htmlLive = g_ucAdmin.getVal(outputData, "html");
		
		htmlLive = modifyAddonLiveHtml(htmlLive, addonData, outputData);
		
		var objIncludes = g_ucAdmin.getVal(outputData, "includes");
		var objConstants = g_ucAdmin.getVal(outputData, "constants");
		
		//replace constants in html		
		var htmlColAddon = generateAddonHtml_wrapAddonHtml(addon_name, htmlLive, addonData, onlyInner);
		
		//put includes, on all scripts loaded put html
		putAddonLiveModeIncludes(addonData, objIncludes, function(){
			
			//put html			
			onFinish(htmlColAddon);
		});
		
	}
	
	
	/**
	 * generate addon html
	 * after finish, call a function
	 */
	function generateAddonHtml(addonData, onFinish, onlyInner){
		
		var arrExtra = g_ucAdmin.getVal(addonData, "extra");
		
		var title = g_ucAdmin.getVal(arrExtra, "title");
		var url_icon = g_ucAdmin.getVal(arrExtra, "url_icon");
		var addon_name = addonData.name;
		
		if(!title)
			title = addon_name;
		
		if(onFinish && typeof onFinish != "function")
			throw new Error("The second param should be a function");
		
		var addontype = g_ucAdmin.getVal(addonData, "addontype");
		var isBGAddon = false;
		if(addontype == g_temp.addontype_bgaddon)
			isBGAddon = true;
		
		//box view
		if(g_temp.is_live_view == false){
			
			if(isBGAddon == true){
				htmlColAddon = title;
			}else{
				var htmlBox = generateAddonHtml_getBoxHtml(url_icon, title, addonData);
				var htmlColAddon = generateAddonHtml_wrapAddonHtml(addon_name, htmlBox, addonData, onlyInner);
			}
			
			if(onFinish)
				onFinish(htmlColAddon);
			else
				return(htmlColAddon);
			
		}else{	//live view
			
			if(typeof onFinish != "function")
				throw new Error("The second param should be a function");
			
			//do without ajax
			var output = g_ucAdmin.getVal(addonData, "output");
			
			if(output){	
				generateAddonHtml_handleAddonOutputData(output, addonData, onFinish, onlyInner);
			}
			else{	//call ajax
					
				if(jQuery.isArray(addonData.items) == true && addonData.items.length == 0)
				     addonData.items = g_temp.value_empty_array;
				
				g_ucAdmin.ajaxRequest("get_addon_output_data", addonData, function(response){
					
					//save output data
					addonData["output"] = response;
					generateAddonHtml_handleAddonOutputData(response, addonData, onFinish, onlyInner);
					
				});
			}
			
		}
		
	}
	
	
	/**
	 * set duplicated addon html, before insert it into dome
	 */
	function setDuplicatedAddonHtml(objAddon, addonData){
		
		delete addonData["output"];
		
		var addonID = g_vars.id_prefix + addonData.extra.serial_id;
		objAddon.attr("id", addonID);
		
		if(g_temp.is_live_view == false){		//box view
			
			var newHTML = generateAddonHtml(addonData, null, true);
			objAddon.html(newHTML);
			
		}else{
			
			generateAddonHtml(addonData, function(newHTML){
				objAddon.html(newHTML);
			}, true);
			
		}
		
		
	}
	
	
	function ____________COL_ADDON_ELEMENT______________(){}
	
	/**
	 * validate that the column element is col addon type
	 */
	function validateColAddonElement(objElement){
		
		//validate type
		var type = getElementType(objElement);
		if(type != "addon" && type != "bgaddon")
			throw new Error("The element must be addons type");
		
		//validate single
		if(objElement.length > 1){
			trace(objElement);
			throw new Error("The addon element should be sinlge");
		}
		
	}
	
	
	
	/**
	 * get col addons data
	 */
	function getColAddons(objCol){
		
		validateCol(objCol);
		
		var objAddonsWrapper = getColAddonsWrapper(objCol);
				
		var objAddons = getElementAddons(objAddonsWrapper);
		
		return(objAddons);
	}
	
		
	
	/**
	 * get number of col addons
	 */
	function getNumColAddons(objCol){
		
		validateCol(objCol);
		
		var objAddons = getColAddons(objCol);
		var numAddons = objAddons.length;
		
		return(numAddons);
	}
	
	
	
	
	/**
	 * get parent row
	 */
	function getParentAddonElement(objChild){
		
		var objAddon = objChild.parents(".uc-grid-col-addon");
		
		g_ucAdmin.validateDomElement(objAddon, "addon holder");
		
		return(objAddon);
	}
	
	
	/**
	 * show/hide move icon when number addons = 1,columns = 1,rows = 1
	 */
	function isSingleAddonInGrid(objAddon){
	        
			var objCol = getParentCol(objAddon);
			
			var numRows = getNumRows();
			
			if(numRows>1)
				return(false);
			
			var objRow = getParentRow(objCol);
			
			var numColums = getNumCols(objRow);
			
			if(numColums >1)
				return(false);
			
			var numAddons = getNumColAddons(objCol);
			
			if(numAddons > 1)
				return(false);
			
			return(true);
	}
	
	
	/**
	 * delete column addon
	 */
	function deleteColAddon(objAddon){
		
		validateColAddonElement(objAddon);
		
		var addonID = objAddon.prop("id");
		
		var objCol = getParentCol(objAddon);
		
		objAddon.remove();
				
		triggerEvent(t.events.COL_ADDONS_UPDATED, objCol);
		triggerEvent(t.events.ELEMENT_REMOVED, addonID);
	}
	
	
	/**
	 * duplicate col addon
	 */
	function duplicateColAddon(objAddon){
		
		validateColAddonElement(objAddon);
		
		var objAddonCopy = objAddon.clone(true, true);
				
		//hide overlay
		//showAddonOverlay(objAddonCopy, false);
		
		//insert new addon
		objAddonCopy.insertAfter(objAddon);
		triggerEvent(t.events.ADDON_DUPLICATED, objAddonCopy);
		
		var objCol = getParentCol(objAddon);
		triggerEvent(t.events.COL_ADDONS_UPDATED, objCol);
	}
	
		
	
	/**
	 * do addon element related action
	 */
	function doAddonAction(objAddon, action){
		
		validateColAddonElement(objAddon);
		
		switch(action){
			case "edit_addon":
				openAddonsBrowser(objAddon);
			break;
			case "addon_container_settings":
				openAddonContainerSettingsPanel(objAddon);
			break;
			case "delete_addon":
				deleteColAddon(objAddon);
			break;
			case "duplicate_addon":
				duplicateColAddon(objAddon);
			break;
			case "add_col_addon":
				var parentCol = getParentCol(objAddon);
				openAddonsBrowser(parentCol, objAddon);				
			break;
			default:
				throw new Error("Wrong addon action: "+action);
			break;
		}
		
	}

	function ____________ADDONS_SETTINGS______________(){}
		
	
	/**
	 * update addon container visual
	 */
	function updateAddonContainerVisual(objAddon, objSettings){
		
		if(!objSettings)
			var objSettings = objAddon.data("addon_settings");
		
		var cssAddon = {};
		updateElementVisual(objAddon, cssAddon, objSettings, "addon");
	}
	
	
	/**
	 * update addon container settings
	 */
	function updateAddonContainerSettings(objAddon, settings, isGridInit){
				
		validateColAddonElement(objAddon);
		
		objAddon.data("addon_settings", settings);
		
		updateAddonContainerVisual(objAddon, settings);
		
		triggerEvent(t.events.ADDON_CONTAINER_SETTINGS_UPDATED, [objAddon, {"is_grid_init":isGridInit}]);
		
	}
	
	
	/**
	 * get space between addons of the column
	 */
	function getColSpaceBetweenAddons(objColSettings, objAddon){
		
		var index = objAddon.index();
		if(index == 1)
			return(0);
		
		if(!objColSettings)
			objColSettings = {};
		
		if(objColSettings.hasOwnProperty("col_space_between_addons"))
			return(objColSettings["col_space_between_addons"]);
		
		var objContainer = getParentRowContainer(objAddon);
			
		var settingsContainer = getRowContainerSettings(objContainer);
		if(settingsContainer.hasOwnProperty("col_space_between_addons"))
			return(settingsContainer["col_space_between_addons"]);
		
		var objRow = getParentRow(objContainer);
		var spaceBetweenAddons = getRowSetting(objRow, "space_between_addons", true);
		
		return(spaceBetweenAddons);
	}
	
	
	/**
	 * update placeholders
	 */
	function updateAddonContainerSettingsPlaceholders(objColSettings, objAddon){
		
		var arrSettingNames = jQuery.extend([], g_temp.settings_placeholders);
		
		//get space between addons
		var spaceBetweenAddons = getColSpaceBetweenAddons(objColSettings, objAddon);
		
		var objPlaceholders = {};
		objPlaceholders["margin_top"] = spaceBetweenAddons;
		
		objPlaceholders = getObjSizePlaceholders(arrSettingNames, objPlaceholders, objColSettings,null, null, "0");
		
		if(g_panel)
			g_panel.updatePlaceholders("addon-container-settings", objPlaceholders);
	}
	
	
	/**
	 * apply column settings
	 */
	function applyAddonContainerSettings(){
		
		var data = g_panel.getPaneData("addon-container-settings");
		
		var addonID = data.objectID;
		var objAddon = jQuery("#"+addonID);
		
		if(objAddon.length == 0)
			return(false);
		
		updateAddonContainerSettings(objAddon, data.settings);
		
		if(g_panel.isVisible() == true)
			updateAddonContainerSettingsPlaceholders(data.settings, objAddon);
	}
	
	
	/**
	 * open addon container settings
	 */
	function openAddonContainerSettingsPanel(objAddon){
		
		var addonID = objAddon.prop("id");
		
		var addonContainerSettings = objAddon.data("addon_settings");
		
		g_panel.open("addon-container-settings", addonContainerSettings, addonID);
		
		updateAddonContainerSettingsPlaceholders(addonContainerSettings, objAddon);
	}
	
	
	/**
	 * apply addon settings
	 */
	function applyAddonSettings(params){
		
		var isInstant = params["is_instant"];
		var addonID = params["object_id"];
		var objAddon = jQuery("#"+addonID);
		if(objAddon.length == 0)
			return(false);
		
		var fieldName = g_ucAdmin.getVal(params, "name");
		
		//update instant field if available
		
		//try instant update
		if(isInstant == true){
			
			var objField = objAddon.find("span[data-uc_font_field="+fieldName+"]");
			if(objField.length){
				var value = g_ucAdmin.getVal(params, "value");
				objField.html(value);
			}
			
			return(false);
		}
		
		var data = g_panel.getPaneData("addon-settings");
		
		var settings = data.settings;
		
		var addonDataNew = g_objAddonConfig.getAddonDataFromSettingsValues(settings);
		
		var addonData = getColAddonData(objAddon);
		
		addonData = g_objAddonConfig.setNewAddonData(addonData, addonDataNew);
		
		saveAddonElementData(objAddon, addonData);
		
		redrawAddon(objAddon);
	}
	
	
	/**
	 * open addon settings panel from addon object
	 * the command will send command to later
	 */
	function openAddonSettingsPanel(objAddon, command){
		
		validateColAddonElement(objAddon);
		
		var addonData = getColAddonData(objAddon);
				
		var title = g_objAddonConfig.getAddonTitle(addonData);
		//title = g_uctext["edit_addon"] + ": "+title;
		
		var sendData = g_objAddonConfig.getSendDataFromAddonData(addonData);
		
		var addonID = objAddon.prop("id");
		
		var data = {};
		if(command)
			data.command = command;
		
		var panelData = g_objAddonConfig.getPanelData(addonData);
		if(panelData)
			jQuery.extend(data, panelData);
		
		
		g_panel.open("addon-settings", sendData, addonID, title, data);
	}
	
	
	function ____________ADDONS______________(){}
	
	
	/**
	 * get col addon data
	 */
	function getColAddonData(objAddon, funcModify){
		
		validateColAddonElement(objAddon);
		
		var objData = objAddon.data("addon_data");
				
		var options = objAddon.data("addon_settings");
		if(options){
			if(!objData)
				objData = {};
			
			objData.options = getGridData_modifySettings(options, null, funcModify);
		}
		
		//set options
		if(!objData)
			objData = null;
		
		//make it not a link
		var objDataOutput = jQuery.extend({}, objData);
		
		if(funcModify && typeof funcModify == "function"){
			var returnData = funcModify(objDataOutput);
			return(returnData);
		}
		
		
		return(objDataOutput);
	}
	
	
	/**
	 * get col addons data
	 */
	function getColAddonsData(objCol, funcModify){
		
		var objAddons = getColAddons(objCol);
		
		var arrData = [];
		
		jQuery.each(objAddons, function(index, addon){
			var objAddon = jQuery(addon);
			var objData = getColAddonData(objAddon, funcModify);
			
			arrData.push(objData);
		});
		
		return(arrData);
	}
	
	
	/**
	 * generate serial ID from data and serial
	 */
	function generateAddonSerialID(addonData){
		
		var name = addonData.name;
		g_vars.serial++;
		
		var serialID = name+"_"+g_vars.serial;
		
		return(serialID);
	}

	
	/**
	 * modify addon data before adding addon
	 */
	function modifyAddonDataBeforeAdd(addonData){
		
		var extra = g_ucAdmin.getVal(addonData,"extra",{});
		if(!extra)
			extra = {};
		
		extra["serial_id"] = generateAddonSerialID(addonData);
		extra["serial_contentid"] = g_vars.addon_conetentid_prefix + "_"+addonData.name + "_" + g_vars.serial;
		
		addonData.extra = extra;
		
		return(addonData);
	}
	
	
	/**
	 * modify addon data before save
	 * remove everything except the real data
	 */
	function modifyAddonDataBeforeSave(addonData, removeOutputOnly){
		
		if(!addonData)
			return(null);
		
		var arrDeleteFields = ["output","extra"];
		if(removeOutputOnly === true)
			arrDeleteFields = ["output"];
		
		var addonDataOutput = {};
		
		jQuery.each(addonData, function(key,value){
			
			if(arrDeleteFields.indexOf(key) == -1)
				addonDataOutput[key] = addonData[key];
		});
		
		return(addonDataOutput);
	}	
	
	
	/**
	 * modify addon data before copy
	 */
	function modifyAddonDataBeforeCopy(addonData){
		
		return modifyAddonDataBeforeSave(addonData, true);
	}
	
	
	/**
	 * save addon element data
	 */
	function saveAddonElementData(objAddon, addonData, isGridInit){
		
		validateColAddonElement(objAddon);
		
		var newAddonData = jQuery.extend({}, addonData);
		
		var addonName = newAddonData.name;
		
		objAddon.data("addon_name", addonName);
		objAddon.data("addon_data", newAddonData);
		
		var options = g_ucAdmin.getVal(addonData, "options");
		if(options)
			objAddon.data("addon_settings", options);
		
		triggerEvent(t.events.ADDON_SETTINGS_UPDATED, [objAddon, {"is_grid_init": isGridInit}]);
		
	}
	
	
	/**
	 * get addon error message html
	 */
	function getHtmlAddonError(message, addonData){
		
		var title = addonData.name;
		var extra = g_ucAdmin.getVal(addonData, "extra");
		var extraTitle = g_ucAdmin.getVal(extra, "title");
		if(extraTitle)
			title = extraTitle;
		
		
		var html = "<div class='uc-grid-addon-error'>";
		html += "Error in "+title+" addon: <br>";
		html += message;
		html += "</div>";
		
		return(html);
	}
	
	
	/**
	 * update column with addon data
	 */
	function addColAddon(objCol, addonData, isGridInit, placeholderID, objAddonBefore){
		
		if(jQuery.isArray(addonData))
			return(false);
		
		addonData = modifyAddonDataBeforeAdd(addonData);
		
		var objAddonsWrapper = getColAddonsWrapper(objCol);
		
		//save data
		generateAddonHtml(addonData, function(htmlAddon){
			
			var objHtml = jQuery(htmlAddon);
			
			saveAddonElementData(objHtml, addonData, isGridInit);
			
			var errorReturned = null;
			
			try{
				
				var objAddon = jQuery(objHtml);
				
				//update container settings
				var addonOptions = g_ucAdmin.getVal(addonData, "options");
				if(addonOptions)
					updateAddonContainerSettings(objAddon, addonOptions, isGridInit);
				
				//put addon in it's place
				if(placeholderID){
					
					var objPlaceholder = jQuery("#"+placeholderID);
					if(objPlaceholder.length == 0)
						throw new Error("addColAddon: Placeholder not found: "+placeholderID);
					
					objPlaceholder.replaceWith(objHtml);
					
				}else{
					if(objAddonBefore && objAddonBefore.length){
						objAddon.insertAfter(objAddonBefore);
					}
					else
						objAddonsWrapper.append(objHtml);
				}
				
				
				triggerEvent(t.events.ADDON_ADDED, {"addon":objAddon,"is_grid_init":isGridInit});
				
			}catch(error){
				
				var htmlErrorMessage = getHtmlAddonError(error, addonData);
				objHtml.append(htmlErrorMessage);
				
				trace("js error in "+addonData.name+" addon: "+error);
				
				errorReturned = "Javascript Error Occured: "+error;
			}
			
			triggerEvent(t.events.COL_ADDONS_UPDATED, [objCol, {"is_grid_init":isGridInit}]);
			
			if(errorReturned)
				throw errorReturned;
			
		});
		
	}
	
	
	/**
	 * redraw addon
	 */
	function redrawAddon(objAddon){
		
		validateColAddonElement(objAddon);
		
		//remove the output
		var addonData = getColAddonData(objAddon);
		addonData["output"] = null;
		
		//no modify serial id before redraw
		//addonData = modifyAddonDataBeforeAdd(addonData);
		
		saveAddonElementData(objAddon, addonData);
		
		//generate html
		generateAddonHtml(addonData, function(htmlAddon){
			
			objAddon.html(htmlAddon);
			
		}, true);
		
	}
	
	
	/**
	 * update obj addon with new one
	 */
	function updateColAddon(objAddon, addonData){
		
		g_ucAdmin.validateNotEmpty(addonData, "addon data");
		var addonID = objAddon.prop("id");
		
		validateColAddonElement(objAddon);
		
		addonData = modifyAddonDataBeforeAdd(addonData);
		
		//generate html
		generateAddonHtml(addonData, function(htmlAddon){
			
			var objAddonNew = jQuery(htmlAddon);
			
			objAddon.replaceWith(objAddonNew);
			
			saveAddonElementData(objAddonNew, addonData);
			
			//previous addon element removed
			triggerEvent(t.events.ELEMENT_REMOVED, addonID);
			
		});
		
	}
	
	
	/**
	 * update addon data without redraw the actual addon
	 * run after content editing
	 */
	function updateAddonDataSettingNoRedraw(objAddon, settingName, settingValue){
		
		validateColAddonElement(objAddon);
		var addonData = getColAddonData(objAddon);
		
		addonData = g_objAddonConfig.updateAddonDataSetting(addonData, settingName, settingValue);
				
		saveAddonElementData(objAddon, addonData);
		
		var isItems = false;
		if(settingName.indexOf("uc_items_attribute") === 0)
			isItems = true;
		
		if(isItems == true){			
			updatePanelSettingIfActive(objAddon, "uc_items_editor", addonData.items, true);
			
		}else{
			updatePanelSettingIfActive(objAddon, settingName, settingValue, true);
		}
					

	}
	
	
	/**
	 * open addon browser
	 */
	function openAddonsBrowser(objElement, objAddonBefore){
				
		if(!g_objBrowser){
			trace("browser not available");
			return(false);
		}
		
		var isNew = true;
		var addonData = null;
		
		var type = getElementType(objElement);
		
		if(type == "addon"){	//edit addon
			
			openAddonSettingsPanel(objElement);
			return(false);
		}
		
		g_objBrowser.openAddonsBrowser(addonData, function(newAddonData){
			
			setColEmptyStateLoading(objElement, true);
			
			newAddonData.return_output = g_temp.is_live_view;
			
			g_objAddonConfig.loadNewAddonData(newAddonData, function(addonData){
				
				setColEmptyStateLoading(objElement, false);
				
				addColAddon(objElement, addonData, false, null, objAddonBefore);
				
			});
			
		});
		
	}
	
	
	function ____________________GET_DATA________________(){}
	
	/**
	 * modify element settings before get
	 */
	function getGridData_modifySettings(settings, forCopy, modifyFunc){
		
		if(!settings)
			return(settings);
		
		var addonData = g_ucAdmin.getVal(settings, "bg_addon_single_data");
		if(!addonData)
			return(settings);
		
			
		//set modify func
		if(!modifyFunc){
			if(forCopy === true)
				var modifyFunc = modifyAddonDataBeforeCopy;
			else
				var modifyFunc = modifyAddonDataBeforeSave;
		}
		
		addonData = modifyFunc(addonData);
		settings["bg_addon_single_data"] = addonData;
		
		return(settings);
	}
	
	
	/**
	 * get columns data
	 */
	function getGridData_cols(objRow, forCopy){
		
		var objContainer = getRowContainer(objRow);
		
		var objCols = getCols(objContainer);
		var dataCols = [];
		
		if(forCopy === true)
			var modifyFunc = modifyAddonDataBeforeCopy;
		else
			var modifyFunc = modifyAddonDataBeforeSave;
		
		
		//create col data
		jQuery.each(objCols,function(colIndex, col){
			var objCol = jQuery(col);
			
			var dataCol = {};
			dataCol.addon_data = getColAddonsData(objCol, modifyFunc);
			
			var colSettings = objCol.data("settings");
			if(colSettings)
				dataCol.settings = getGridData_modifySettings(colSettings, forCopy);
			
			var size = getColSize(objCol);
			if(size)
				dataCol.size = size;
			
			dataCols.push(dataCol);
		});
		
		return(dataCols);
	}
	
	/**
	 * get containers columns data
	 */
	function getGridData_containers(objRow, forCopy){
		
		var dataContainers = [];
		var objContainers = getRowContainersAll(objRow);
		
		
		//create col data
		jQuery.each(objContainers, function(containerIndex, container){
			
			var objContainer = jQuery(container);
			
			var dataContainer = {};
			dataContainer.cols = getGridData_cols(objContainer, forCopy);
			
			var containerSettings = objContainer.data("settings");
			if(containerSettings)
				dataContainer.settings = getGridData_modifySettings(containerSettings, forCopy);
						
			dataContainers.push(dataContainer);
		});
		
		return(dataContainers);
	}
	
		
	
	/**
	 * get row data
	 * forCopy - yes/no
	 */
	function getGridData_row(objRow, forCopy, forSingleSave){
		
		var dataRow = {};
		
		var objContainers = getRowContainersAll(objRow);
		
		dataRow.containers = getGridData_containers(objRow, forCopy);
		
		var rowSettings = objRow.data("settings");
		
		if(rowSettings)
			dataRow.settings = getGridData_modifySettings(rowSettings, forCopy);
		
		if(forSingleSave === true){
			var dataGrid = {};
			dataGrid.rows = [dataRow];
			return(dataGrid);
		}
		
		return(dataRow);
	}
		
	
	/**
	 * get grid rows
	 */
	function getGridData_rows(){
		
		var dataRows = [];
		var objRows = getRows();
		
		jQuery.each(objRows, function(index, row){
			var objRow = jQuery(row);
			var dataRow = getGridData_row(objRow);
			
			dataRows.push(dataRow);
			
		});
		
		return(dataRows);
	}
	
	
	/**
	 * get grid data
	 */
	function getGridData(){
		
		var data = {};
		data.rows = getGridData_rows();
		
		if(g_optionsCustom)
			data.options = g_optionsCustom;
		
		
		return(data);
	}
	
	
	/**
	 * get grid data
	 */
	this.getGridData = function(){
		
		var objData = getGridData();
				
		return(objData);
	};
	
	/**
	 * get page params
	 */
	this.getPageParams = function(){
		
		if(!g_temp.page_params)
			return(null);
		
		return(g_temp.page_params);
	};
	
	
	function ____________GRID_SETTINGS______________(){}
	
	
	/**
	 * update grid settings placeholders
	 */
	function updateGridSettingsPlaceholders(){
		
		var objOptions = getCombinedOptions();
		
		var arrSettings = jQuery.extend([], g_temp.settings_placeholders_grid);
		
		var objPlaceholders = {};
		objPlaceholders = getObjSizePlaceholders(arrSettings, objPlaceholders, arrSettings, objOptions, null, "0");
		
		if(g_panel)
			g_panel.updatePlaceholders("grid-settings", objPlaceholders);
	}
	
	
	/**
	 * open grid settings panel
	 */
	function openGridSettingsPanel(){
		
		g_panel.open("grid-settings", null);
		
		updateGridSettingsPlaceholders();
	}
	
	/**
	 * open page params panel
	 */
	function openPageParamsPanel(){
		
		//update settings
		g_panel.open("page-params", g_temp.page_params);
	}
	
	/**
	 * get grid option
	 */
	function getGridOption(name){
		
		var gridOptions = getCombinedOptions();
		
		var value = g_ucAdmin.getVal(gridOptions, name);
		
		return(value);
	}
	
	
	/**
	 * get combined options
	 */
	function getCombinedOptions(){
		
		if(!g_optionsCustom)
			g_optionsCustom = {};
				
		var objOptions = {};
		jQuery.extend(objOptions, g_options, g_optionsCustom);
		
		return(objOptions);
	}
	
	
	/**
	 * get font css from object
	 */
	function getFontCss(objFont, selector){
				
		var arrStyle = {};
		var cssMobileSize;
		var customStyles = "";
		
		for(var styleName in objFont){
			var styleValue = objFont[styleName];
			
			if(!styleValue)
				continue;
			
			if(styleValue == "not_chosen")
				continue;
						
			switch(styleName){
				case "font-family":
					if(styleValue.indexOf(" ") != -1 && styleValue.indexOf(",") == -1)
						arrStyle[styleName] = "'"+styleValue+"'";
					else
						arrStyle[styleName] = styleValue;
										
					//add google font					
					if(g_temp.google_fonts.hasOwnProperty(styleValue)){
						var urlGoogleFont = "https://fonts.googleapis.com/css?family="+styleValue;
						g_ucAdmin.loadIncludeFile("css", urlGoogleFont,{"norand":true});
					}
					
				break;
				case "font-weight":
				case "line-height":
				case "text-decoration":
				case "color":
				case "font-style":
					arrStyle[styleName] = styleValue;
				break;
				case "font-size":
					styleValue = g_ucAdmin.normalizeSizeValue(styleValue);
					arrStyle[styleName] = styleValue;
				break;
				case "mobile-size":
					styleValue = g_ucAdmin.normalizeSizeValue(styleValue);
					cssMobileSize = selector+"{font-size:"+styleValue+"}";
					cssMobileSize = g_ucAdmin.wrapCssInMobile(cssMobileSize);
				break;
				case "custom":
					customStyles = styleValue;
				break;
			}
		}
		
		//make this function
		var css = g_ucAdmin.arrCssToStrCss(arrStyle);
		
		if(customStyles)
			css += customStyles;
		
		css = selector+"{"+css+"}\n";
		
		if(cssMobileSize)
			css += cssMobileSize+"\n";
		
		return(css);
	}
	
	/**
	 * get size related css
	 */
	function getSizeRelatedCss(arrSettingsNames, objOptions, selector, suffix, funcModify){
		
		var br = "\n";
		var tab = "	    ";
		
		var css = "";
		var arrSizes = g_temp.sizes;
		for(var indexSize in arrSizes){
			var size = arrSizes[indexSize];
			
			var objSizeCss = {};
			
			for(var cssAttribute in arrSettingsNames){
					
				var settingName = arrSettingsNames[cssAttribute];
				
				var settingNameSize = settingName+"_"+size;
				
				var value = g_ucAdmin.getVal(objOptions, settingNameSize);
				
				if(value !== ""){
					objSizeCss[cssAttribute] = g_ucAdmin.normalizeSizeValue(value);
					
					if(suffix && cssAttribute != "inline-css")
						objSizeCss[cssAttribute] += " "+suffix;
					
				}
				
				if(funcModify && typeof funcModify == "function")
					objSizeCss = funcModify(objSizeCss, cssAttribute, settingName, value, size);
			}
			
			if(jQuery.isEmptyObject(objSizeCss) == true)
				continue;
			
			var cssSize = g_ucAdmin.arrCssToStrCss(objSizeCss, selector, true);
			
			cssSize = g_ucAdmin.wrapCssInMobile(br+cssSize, size);
			
			css += br + cssSize;
		}
		
		
		return(css);
	}
	
	
	/**
	 * put css based on the options
	 */
	function putGeneratedCss(){
		var br = "\n";
		var tab = "	    ";
		var objOptions = getCombinedOptions();
				
		var css = "";
		
		g_ucAdmin.validateObjProperty(objOptions, ["col_gutter",
		                                           "page_css",
		                                           "row_padding_top",
		                                           "row_padding_bottom",
		                                           "row_padding_top_mobile",
		                                           "row_padding_bottom_mobile",
		                                           "row_container_width"
		              ],"grid options");
		
		//row css
		var selectorRow = g_gridID+" .uc-grid-row";
		
		css += selectorRow+"{"+br;
			css += tab+"padding-top:"+objOptions.row_padding_top+"px;"+br;
			css += tab+"padding-bottom:"+objOptions.row_padding_bottom+"px;"+br;
		css += "}"+br+br;
		
		
		//row container css
		css += g_gridID+" .uc-grid-row .uc-grid-row-container{"+br;
		
		css += tab+"max-width:"+g_ucAdmin.normalizeSizeValue(objOptions.row_container_width)+";"+br;
		
		css += "}"+br+br;
		
		//column css
		var selectorCol = g_gridID+" .uc-grid-row .uc-grid-col";
		
		css += selectorCol+"{"+br;
		
		//add gutter
		css += tab+"padding-left:"+objOptions.col_gutter+"px;"+br;
		css += tab+"padding-right:"+objOptions.col_gutter+"px;"+br;
		css += "}"+br;
				
		//column addons
		var selectorAddons = g_gridID+" .uc-grid-row .uc-grid-col .uc-grid-col-addon + .uc-grid-col-addon";
		var selectorFirstAddons = g_gridID+" .uc-grid-row .uc-grid-col .uc-grid-overlay-empty + .uc-grid-col-addon";
		
		var spaceBetweenAddons = g_ucAdmin.getVal(objOptions, "space_between_addons", null);
		if(spaceBetweenAddons){
			
			spaceBetweenAddons = g_ucAdmin.normalizeSizeValue(spaceBetweenAddons);
			var addonsStyle = "margin-top:" + spaceBetweenAddons+";";
			css += selectorAddons+"{"+addonsStyle+"}"+br;
			
			css += selectorFirstAddons+"{margin-top:0px}"+br;
		}
		
		
		//put font styles
		var objPageFonts = g_ucAdmin.getVal(objOptions, "page_fonts", null);
		if(objPageFonts){
			for(var fontName in objPageFonts){
				var objFontData = objPageFonts[fontName];
				var fontSelector = g_gridID+" .uc-page-font-"+fontName;
				if(fontName == "page")
					fontSelector = g_gridID+" .uc-grid-col-addon-html";
				
				var cssFont = getFontCss(objFontData, fontSelector);
				
				css += "\n"+cssFont;
			}
			
		}
		
		g_objGrid.removeAttr("style");
		
		//body background
		var cssBG = getBackgroundCss(objOptions);
		if(cssBG)
			g_objGrid.css(cssBG);
		
		//add page css
		var pageCss = objOptions.page_css;
		pageCss = jQuery.trim(pageCss);
		if(pageCss)
			css += pageCss;
		
		
		var sizeRelatedCss = "";
				
		//row mobile
		var arrSettings = {
				"padding-top":"row_padding_top",
				"padding-bottom":"row_padding_bottom"
		};
		
		
		sizeRelatedCss += getSizeRelatedCss(arrSettings, objOptions, selectorRow);
		
		//columns mobile
		var arrSettings = {
				"padding-left":"col_gutter",
				"padding-right":"col_gutter"
		};
		
		sizeRelatedCss += getSizeRelatedCss(arrSettings, objOptions, selectorCol);
		
		//addons mobile
		var arrSettings = {
				"margin-top":"space_between_addons"
		};
		sizeRelatedCss += getSizeRelatedCss(arrSettings, objOptions, selectorAddons);
		
		//size related inline css
		var arrSettings = {
				"inline-css":"page_css"
		};
		
		sizeRelatedCss += getSizeRelatedCss(arrSettings, objOptions, "");
		
		css += sizeRelatedCss;
		
		g_objStyle.html(css);
		
		//apply the scroll
		var isScroll = g_ucAdmin.getVal(objOptions, "enable_smooth_scroll");
		isScroll = g_ucAdmin.strToBool(isScroll);
				
		if(isScroll == true)
			g_objGrid.addClass("uc-smooth-scroll");
		else 
			g_objGrid.removeClass("uc-smooth-scroll");
			
	}
	
	
	/**
	 * update options from settings dialog
	 */
	function updateOptionsFromGridSettings(){
		
		if(!g_panel)
			return(false);
		
		var objValues = g_panel.getSettingsValues("grid-settings");
		
		//update custom options, skip empty values
		g_optionsCustom = {};
		
		jQuery.each(objValues, function(option, val){
			if(!val || jQuery.trim(val) == "")
				return(true);
			
			//convert to int
			if(typeof val == "string" && jQuery.isNumeric(val))
				val = parseInt(val);
			
			g_optionsCustom[option] = val;
		});
		
	}
	
	
	/**
	 * apply grid options
	 */
	function applyGridSettings(){
		
		updateOptionsFromGridSettings();
		putGeneratedCss();
		updateAllRowsVisual();
		updateGridSettingsPlaceholders();
		
		triggerEvent(t.events.GRID_SETTINGS_UPDATED);
	}
	
	/**
	 * save page params to object
	 */
	function applyPageParams(){
		
		var objParams = g_panel.getSettingsValues("page-params");
		g_temp.page_params = objParams;
		
		triggerEvent(t.events.PAGE_PARAMS_UPDATED);
	}
	
	
	/**
	 * init grid settings related, style, options, dialogs
	 */
	function initGridSettings(){
		
		//init style object
		g_objStyle = g_objWrapper.children("style");
		g_ucAdmin.validateDomElement(g_objStyle, "style tag");
		
		//init options
		g_options = g_objGrid.data("options");
		if(!g_options)
			throw new Error("Should be passed some options!");
		
		
		g_objGrid.removeAttr("data-options");	//remove attribute for not interfere
	}
	
	
	function ____________EVENTS______________(){}
	
	/**
	 * trigger event that some grid change happened, based on main event
	 */
	function checkTriggerChangeEvent(eventName, options){
		
		//skip if grid not inited
		if(g_temp.is_data_inited == false)
			return(false);
		
		//skip grid init related events
		if(options){
			if(jQuery.isArray(options))
				var objEventParams = options[1];
			else
				var objEventParams = options;
						
			var isGridInit = g_ucAdmin.getVal(objEventParams,"is_grid_init");
			if(isGridInit === true)
				return(false);
			
		}
		
		//trigger change event
		g_objGrid.trigger(t.events.CHANGE_TAKEN,[eventName, options]);
		
	}
	
	
	/**
	 * grigger event
	 */
	function triggerEvent(eventName, options){
		
		g_objGrid.trigger(eventName, options);
		
		checkTriggerChangeEvent(eventName, options);
		
	}
	
	
	/**
	 * on some event
	 */
	function onEvent(eventName, func){
		
		g_objGrid.on(eventName, func);
		
	}
	
	
	/**
	 * on rows updated
	 * happends on add / update / delete / reorder row
	 */
	function onRowsUpdated(event){
						
		//update row classes
		var numRows = getNumRows();
		if(numRows <= 1)
			g_objGrid.addClass("uc-single-row");
		else
			g_objGrid.removeClass("uc-single-row");
		
		updateRowsIcons();
	}
	
	
	/**
	 * on add column
	 */
	function onRowColumnsUpdated(event, container){
		
		var objContainer = jQuery(container);
		validateRowContainer(objContainer);
		
		updateColsClasses(objContainer);
		
		updateColOperationButtons(objContainer);
		
		var objRow = getParentRow(objContainer);
		updateRowVisual_buttons(objRow);
	}
	
	
	/**
	 * on col addons updated function
	 * show / hide empty visual if no addons
	 */
	function onColAddonsUpdated(event, objCol, params){
				
		objCol = jQuery(objCol);
		
		var origEventType = g_ucAdmin.getVal(params, "orig_event_type");
		
		var numAddons = getNumColAddons(objCol);
		
		if(origEventType == "sortchange"){
			numAddons--;
		}
		
		if(numAddons <= 0)
			setColEmptyStateVisual(objCol, true);
		else
			setColEmptyStateVisual(objCol, false);
		
	}
		
	
	/**
	 * on col or row action icon click
	 */
	function onActionIconClick(){
		
		var objIcon = jQuery(this);
		
		if(g_ucAdmin.isButtonEnabled(objIcon) == false)
			return(false);
		
		var action = objIcon.data("action");
		var actionType = objIcon.data("actiontype");
		
		if(!action || action == "")
			throw new Error("wrong icon action");
		
		switch(actionType){
			case "grid":
                var objRow = getParentRow(objIcon);
                doGridAction(action, objRow);
			break;
			case "col":
				var objAddon = getParentCol(objIcon);
				doColAction(objAddon, action);
			break;
			case "row":
				var objRow = getParentRow(objIcon);
				doRowAction(action, objRow);
			break;
			case "addon":
				var objAddon = getParentAddonElement(objIcon);
				
				doAddonAction(objAddon, action);
			break;
			case "container":
				var objContainer = getParentRowContainer(objIcon);
				doContainerAction(action, objContainer);
			break;
			default:
				throw new Error("Wrong action type: " + actionType);
			break;
		}
		
	}
	
	
	/**
	 * init new row events
	 */
	function onNewRowAdded(event, row){
		
		var objRow = jQuery(row);
		
		initSortableContainers(objRow);
				
		//show settings if the panel is visible
		if(g_temp.is_data_inited == true && g_panel && g_panel.isVisible() == true)
			openRowSettingsPanel(objRow);
		
	}
	
	
	
	/**
	 * on new container added, init new container events
	 */
	function onNewContainerAdded(event, container){
		
		var objContainer = jQuery(container);
				
		initSortableColumns(objContainer);
	}
	
	
	/**
	 * on sortable addons change
	 */
	function onSortableAddonsChanged(event, ui){
		
		var objAddon = ui.item;
		var objCol = getParentCol(objAddon);
		
		triggerEvent(t.events.COL_ADDONS_UPDATED, [objCol, {"orig_event_type":event.type}]);
	}
	
	
	/**
	 * on row duplicated - update all columns and row visual
	 */
	function onRowDuplicated(event, row){
		
		var objRow = jQuery(row);
		
		initSortableContainers(objRow);		
		
		updateRowVisual(objRow);
		
		//send event to all containers
		
		var objContainers = getRowContainersAll(objRow);
		
		objContainers.each(function(index, container){
			var objContainer = jQuery(container);
			
			//update IDs
			var containerID = getNewElementID("container");
			objContainer.attr("id", containerID);
			
			triggerEvent(t.events.ROW_CONTAINER_DUPLICATED, objContainer);
		});
		
	}
	
	
	/**
	 * on container duplicated, send event to all cols
	 */
	function onRowContainerDuplicated(event, container){
		
		var objContainer = jQuery(container);
		
		initSortableColumns(objContainer);
	
		updateRowContainerVisual(objContainer);
		
		var objCols = getCols(objContainer);
		
		jQuery(objCols).each(function(index, col){
			var objCol = jQuery(col);
			
			//update IDs
			var colID = getNewElementID("col");
			objCol.attr("id", colID);
			
			triggerEvent(t.events.COL_DUPLICATED, objCol);
		});
		
		
	}
	
	
	/**
	 * on column duplicated, update all addons and col visual
	 */
	function onColDuplicated(event, col){
		
		var objCol = jQuery(col);
		
		updateColVisual(objCol);
		
		var objAddons = getElementAddons(objCol);
				
		jQuery(objAddons).each(function(index, addon){
			var objAddon = jQuery(addon);
			triggerEvent(t.events.ADDON_DUPLICATED, objAddon);
		});
		
	}
	
	
	/**
	 * on panel settings change
	 */
	function onPanelSettingsChange(event, params){
		
		var settingsType = params["object_name"];
				
		switch(settingsType){
			case "grid-settings":
				applyGridSettings(params);
			break;
			case "page-params":
				applyPageParams(params);
			break;
			case "row-settings":
				applyRowSettings(params);
			break;
			case "col-settings":
				applyColSettings(params);
			break;
			case "addon-container-settings":
				applyAddonContainerSettings(params);
			break;
			case "addon-settings":
				applyAddonSettings(params);
			break;
			case "container-settings":
				applyRowContainerSettings(params);
			break;
			default:
				//console.trace();
				throw new Error("onPanelSettingsChange: Wrong settings type: " + settingsType);
			break;
		}
		
		
	}
	
	
	/**
	 * on panel html settings loaded from ajax
	 * save settings in addon data
	 */
	function onPanelHtmlSettingsLoaded(event, data){
		
		var addonID = data["object_id"];
		
		var objAddon = jQuery("#"+addonID);
		if(objAddon.length == 0)
			return(true);
		
		var htmlSettings = data["html_settings"];
		if(!htmlSettings)
			return(true);
		
		var addonData = getColAddonData(objAddon);
		
		addonData = g_objAddonConfig.setHtmlSettingsInAddonData(addonData, htmlSettings);
		
		saveAddonElementData(objAddon, addonData);
	}
	
	
	/**
	 * calling after delete or update addon
	 */
	function onElementRemoved(event, elementID){
		
		if(g_temp.is_live_view == true)
			checkRemoveLiveModeIncludes();
		
		if(elementID && g_panel)
			g_panel.hideIfActive(elementID);
		
	}
	
	
	/**
	 * on addon duplicated, modify content
	 */
	function onAddonDuplicated(event, addonCopy){
		
		var objAddonCopy = jQuery(addonCopy);
		
		//modify data
		var addonData = getColAddonData(objAddonCopy);
		
		addonData = modifyAddonDataBeforeAdd(addonData);
		
		addonData = g_objAddonConfig.clearDuplicatedAddonData(addonData);
		
		saveAddonElementData(objAddonCopy, addonData);
		
		setDuplicatedAddonHtml(objAddonCopy, addonData);
	}
	
	
	/**
	 * trigger after new addon added
	 */
	function onAddonAdded(event, data){
		
		var objAddon = data["addon"];
		var isGridInit = data["is_grid_init"];
		if(isGridInit === true)
			return(true);
		
		var addonData = getColAddonData(objAddon);
		var command = g_objAddonConfig.getPanelCommand("add_addon", addonData);
		
		//if(!command)
			//return(true);
		
		openAddonSettingsPanel(objAddon, command);
	}
	
	
	/**
	 * on containers updated, set row and containre classes related to number of container
	 */
	function onContainersUpdated(event, row){
		
		var objRow = jQuery(row);
		
		validateRow(objRow);
		
		var objContainers = getRowContainersAll(objRow);
		var numContainers = objContainers.length;
				
		if(numContainers == 0)
			return(false);
		
		var firstContainer = objContainers.first();
		var lastContainer = objContainers.last();
		
		//set first container class
		if(numContainers > 1){
			objContainers.not(firstContainer).removeClass("uc-first-container");
			objContainers.not(lastContainer).removeClass("uc-last-container");
		}
		
		firstContainer.addClass("uc-first-container");
		lastContainer.addClass("uc-last-container");
		
	}
	
	
	/**
	 * on install addon
	 * if svg - add to svg content
	 */
	function onInstallAddon(event, data){
		
		var addonType = g_ucAdmin.getVal(data, "addontype");
		if(addonType != "shape_devider")
			return(true);
		
		var shapeContent = g_ucAdmin.getVal(data, "shape_content");
		if(!shapeContent)
			throw new Error("Wrong shape content");
		
		g_vars.shape_deviders[data.name] = shapeContent;
		
		
	}
	
	
	/**
	 * on contenteditable field change event,
	 * update parent addon and the panel field if available
	 */
	function onEditableFieldChange(event){
		
		var objStyle = jQuery(this);
		
		var settingName = objStyle.data("uc_font_field");
		var objAddon = objStyle.parents(".uc-grid-col-addon");
		var value = objStyle.html();
		
		//clear content if pasted
		var isJustPasted = objStyle.data("just_pasted");
		
		//on paste - clear html tags
		if(isJustPasted === true){
			
			var valueBeforePaste = objStyle.data("value_before_paste");
									
			var diffText = g_ucAdmin.getTextDiff(valueBeforePaste, value);
			
			var newText = g_ucAdmin.stripTagsKeepFormatting(diffText);
			
			value = value.replace(diffText, newText);
			
			objStyle.html(value);
			
			var posStart = value.indexOf(newText);
			var posEnd = posStart+newText.length;
			
			g_ucAdmin.setCursorPosition(objStyle, posEnd);
			
			
			objStyle.data("just_pasted", null);
			objStyle.data("value_before_paste", null);
		}
		
		
		updateAddonDataSettingNoRedraw(objAddon, settingName, value);
	}
	
	
	/**
	 * on paste, clear the html data
	 */
	function onEditableFieldPaste(event){
		
		var objStyle = jQuery(this);
		
		var value = objStyle.html();
		
		objStyle.data("just_pasted",true);
		objStyle.data("value_before_paste",value);
		
	}
	
	
	
	function ____________HOVER_EVENTS______________(){}

	
	/**
	 * on panel head mouseover
	 * show borders of relative element
	 */
	function onPanelHeadMouseOver(event, data){
		
		var objectID = data.objectID;
		
		setElementAlwaysHoverMode(objectID, true);
	}
	
	
	/**
	 * on panel head mouseover
	 * hide borders of relative element
	 */
	function onPanelHeadMouseOut(event, data){
		
		var objectID = data.objectID;
		
		setElementAlwaysHoverMode(objectID, false);
	}
	
	/**
	 * on panel mouse over
	 * blink with active element
	 */
	function onPanelMouseOver(event, data){
		
		//var objectID = data.objectID;
		//setElementAlwaysHoverMode(objectID, true, true);
	}
	
	
	/**
	 * set hover mode to the element
	 */
	function setElementAlwaysHoverMode(elementID, set, isTimeout){
		
		if(typeof elementID == "string"){
			
			var objElement = jQuery("#"+elementID);
			if(objElement.length == 0)
				return(false);
			
		}else
			var objElement = elementID;
		
		
		var type = getElementType(objElement);
		
		switch(type){
			case "row":
			case "column":
			case "addon":
			case "container":
			break;
			default:
				return(false);
			break;
		}
		
		
		if(set == true){
			objElement.addClass("uc-grid-always-hover");
			if(isTimeout == true){
				setTimeout(function(){
					objElement.removeClass("uc-grid-always-hover");
				}, 800);
			}
		}
		else
			objElement.removeClass("uc-grid-always-hover");
		
	}
	
	/**
	 * remove all element hover modes
	 */
	function removeAllElementHoverMode(){
		
		g_objGrid.find("uc-grid-always-hover").removeClass("uc-grid-always-hover");
		
	}
	
	
	
	/**
	 * check some element hover mode, set mode2 if there is some other hover elements children
	 */
	function checkElementHoverMode(objElement){
		
		var hoverChildren = objElement.find(".uc-element-hover");
		if(hoverChildren.length == 0)
			objElement.removeClass("uc-over-mode2");
		else
			objElement.addClass("uc-over-mode2");
		
	}
	
	
	/**
	 * check parent hover mode
	 */
	function checkParentsHoverMode(objElement){
		
		//check column
		var objCol = getParentCol(objElement);
		if(objCol.length)
			checkElementHoverMode(objCol);
		
		//check container
		var objContainer = getParentRowContainer(objElement);
		if(objContainer.length)
			checkElementHoverMode(objContainer);
			
		//check row
		var objRow = getParentRow(objElement);
		if(objRow.length)
			checkElementHoverMode(objRow);
		
	}
	
	
	/**
	 * on element mouse over
	 */
	function onElementMouseOver(){
		
		var objElement = jQuery(this);
		
		objElement.addClass("uc-element-hover");
		
		checkParentsHoverMode(objElement);
	}
	
	
	/**
	 * on element mouse out
	 */
	function onElementMouseOut(){
		
		var objElement = jQuery(this);
		
		objElement.removeClass("uc-element-hover");
		
		checkParentsHoverMode(objElement);
	}
	
	
	/**
	 * on element double click
	 */
	function onElementDoubleClick(event){
		
		event.stopPropagation();
		event.stopImmediatePropagation();
		
		var objElement = jQuery(this);
		
		
		openElementSettingsPanel(objElement);
		
	}
	
	
	/**
	 * on sort addons start
	 */
	function onSortElementStart(){
		
		g_objGrid.addClass("uc-drag-mode");
		
	}
	
	/**
	 * on sort stop
	 */
	function onSortElementStop(){
		
		g_objGrid.removeClass("uc-drag-mode");
		
	}
		
	
	/**
	 * init the events
	 */
	function initEvents(){
		
		onEvent(t.events.ROW_COLUMNS_UPDATED, onRowColumnsUpdated);
		onEvent(t.events.ROWS_UPDATED, onRowsUpdated);
		onEvent(t.events.ROW_ADDED, onNewRowAdded);
		onEvent(t.events.ROW_CONTAINER_ADDED, onNewContainerAdded);
		onEvent(t.events.COL_ADDONS_UPDATED, onColAddonsUpdated);
		onEvent(t.events.ELEMENT_REMOVED, onElementRemoved);
		onEvent(t.events.ADDON_ADDED, onAddonAdded);
		onEvent(t.events.ROW_DUPLICATED, onRowDuplicated);
		onEvent(t.events.ROW_CONTAINER_DUPLICATED, onRowContainerDuplicated);
		onEvent(t.events.COL_DUPLICATED, onColDuplicated);
		onEvent(t.events.ADDON_DUPLICATED,onAddonDuplicated);
		onEvent(t.events.ROW_CONTAINERS_UPDATED, onContainersUpdated);
		
		g_objGrid.on("click", ".uc-grid-action-icon", onActionIconClick);
		
		//init sortable rows
		g_objGrid.sortable({
			handle: ".uc-row-icon-move",
			axis: "y",
			update: function(){
				triggerEvent(t.events.ROWS_UPDATED);
			},
			start:  onSortElementStart,
			stop:  onSortElementStop			
		});	
		
		//init sortable addons
		var objGridOuter = g_objGrid.parents(".uc-grid-builder-outer");
				
		objGridOuter.sortable({
			items: ".uc-grid-addon-sortable",
			//items: ".uc-grid-col-addon",
			handle: ".uc-addon-icon-move",
			cursor: "move",
			axis: "y,x",
			//placeholder: ".uc-grid-addon-sort-placeholder",
			//forcePlaceholderSize: true,
			//helper: "clone",
			start:  onSortElementStart,
			stop:  onSortElementStop,
	        change: onSortableAddonsChanged,
			update: onSortableAddonsChanged 
		});
	
		
		//hover events
		var hoverSelectors = ".uc-grid-row, .uc-grid-row-container, .uc-grid-col, .uc-grid-col-addon";
		
		g_objGrid.on("mouseenter", hoverSelectors, onElementMouseOver);
		g_objGrid.on("mouseleave", hoverSelectors, onElementMouseOut);
		
		g_objGrid.on("dblclick", hoverSelectors, onElementDoubleClick);
		
		//init buffer events
		if(g_objBuffer)
			g_objBuffer.onEvent(g_objBuffer.events.UPDATED, updateRowsIcons);
		
		jQuery(window).on("beforeunload", function(){
			
			if(g_pageBuilder){
				var showMessage = g_pageBuilder.onBeforeUnload();
				if(showMessage == true)
					return("Are you sure?");
			}
			
		});		
		
		
		//send body click event to top window
		jQuery("body").on("click",function(){
			triggerEvent(t.events.BODY_CLICK);
		});
		
		//on contenteditable field change
		g_objGrid.on("input",".uc-font-editable-field", onEditableFieldChange);
		g_objGrid.on("paste",".uc-font-editable-field", onEditableFieldPaste);
		
		
	}
	
	
	/**
	 * init tipsy
	 */
	function initTipsy(){
		
		if(typeof jQuery("body").tipsy != "function")
			return(false);
		
		var tipsyOptions = {
				html:true,
				gravity:"s",
		        delayIn: 1000,
		        selector: ".uc-tip"
		};
		
		g_objGrid.tipsy(tipsyOptions);
		
	}
	
	
	/**
	 * init side panel
	 */
	function initPanel(){
		
		if(!g_pageBuilder){
			
			g_panel = null;
			return(false);
		}
		
		g_panel = new UniteCreatorGridPanel();		
		g_panel = g_pageBuilder.getSidePanel();
		
		//init panel events
		
		g_panel.onEvent(g_panel.events.SETTINGS_CHANGE, onPanelSettingsChange);
		g_panel.onEvent(g_panel.events.SETTINGS_HTML_LOADED, onPanelHtmlSettingsLoaded);
		g_panel.onEvent(g_panel.events.HEAD_MOUSEOVER, onPanelHeadMouseOver);
		g_panel.onEvent(g_panel.events.HEAD_MOUSEOUT, onPanelHeadMouseOut);
		g_panel.onEvent(g_panel.events.PANEL_MOUSEOVER, onPanelMouseOver);
		g_panel.onEvent(g_panel.events.PANEL_MOUSEOUT, removeAllElementHoverMode);
		
	}
	
	
	
	function ____________INIT______________(){}
	
	/**
	 * init sortable columns
	 */
	function initSortableColumns(objContainer){
				
		//init columns sortable
		objContainer.sortable({
			
			items: ".uc-grid-col",
			handle: ".uc-col-icon-move",
			cursor: "move",
			axis: "x",
			update: function(event, ui){
				var objCol = ui.item;
				var objContainer = getParentRowContainer(objCol);
				
				triggerEvent(t.events.ROW_COLUMNS_UPDATED, objContainer);
			},
			start:  onSortElementStart,
			stop:  onSortElementStop			
		});
		
	}
	
	/**
	 * init row sortable
	 */
	function initSortableContainers(objRow){
				
		//init container sortable
		objRow.sortable({
			items: ".uc-grid-row-container",
			handle: ".uc-container-icon-move",
			cursor: "move",
			axis: "y",
			update: function(event, ui){
				var objContainer = ui.item;
				var objRow = getParentRow(objContainer);
				
				triggerEvent(t.events.ROW_CONTAINERS_UPDATED, objRow);
			},
			start:  onSortElementStart,
			stop:  onSortElementStop
		});
		
	}
	
	
	//init columns from data inside container
	function initByData_cols(cols, objContainer){
		
		jQuery.each(cols, function(colIndex, col){
			
			var objCol = addColumn(objContainer);
			var addonsData = col.addon_data;
			
			if(jQuery.isArray(addonsData)){
				
				//add addons placeholders
				var arrPlaceholders = null;
				if(addonsData.length > 1){
					arrPlaceholders = [];
					var objAddonsWrapper = getColAddonsWrapper(objCol);
					
					jQuery.each(addonsData, function(addonIndex, addonData){
						var placeholderID = "uc_placeholder_"+g_ucAdmin.getRandomString();
						var htmlPlaceholder = "<span id='"+placeholderID+"'></span>";
						arrPlaceholders.push(placeholderID);
						objAddonsWrapper.append(htmlPlaceholder);
					});
				}
				
				//add addons
				jQuery.each(addonsData, function(addonIndex, addonData){
					try{				
						var placeholderID = null;
						if(arrPlaceholders)
							placeholderID = arrPlaceholders[addonIndex];
						
						addColAddon(objCol, addonData, true, placeholderID);
																		
					}catch(Error){
						//skip error
						trace(Error);						
					}
					
				});
				
			}else{
						//single - old way
				try{
				if(addonsData)
					addColAddon(objCol, addonsData, true);
											
				}catch(Error){}
				
			}
			
		});	//end add columns
		
		//set custom sizes, update visual
		
		var objCols = getCols(objContainer);
		
		jQuery.each(objCols, function(index, col){
						
			var objCol = jQuery(col);
			var colData = cols[index];
			var size = g_ucAdmin.getVal(colData, "size");
			if(size)
				setColSize(objCol, size);
			
			var settings = g_ucAdmin.getVal(colData, "settings");
			if(settings)
				updateColSettings(objCol, settings);
			
		});
		
	}
	
	/**
	 * init row by data
	 */
	function initByData_row(row, objParentRow, insertTo){
		
		var containers = g_ucAdmin.getVal(row, "containers");
				
		var numContainers = containers.length;
		if(numContainers == 0)
			return(false);
		
		var objRow = addEmptyRow(objParentRow, insertTo, true);
		
		jQuery.each(containers, function(indexContainer, container){
			
			var objContainer = addEmptyRowContainer(objRow);
			
			g_ucAdmin.validateObjProperty(container, "cols");
			
			var cols = container.cols;
			initByData_cols(cols, objContainer);
			
		});
			
		
		//update row visual
		if(row.hasOwnProperty("settings") && typeof row.settings == "object"){
			updateRowSettings(objRow, row.settings);
		}
		
		//update containers visual
		var objContainers = getRowContainersAll(objRow);
		jQuery.each(containers, function(index, container){
			
			//update row visual
			if(container.hasOwnProperty("settings") && typeof container.settings == "object"){
				var objContainer = jQuery(objContainers[index]);
				
				updateContainerSettings(objContainer, container.settings);
			}
			
		});
		
	}
	
	
	/**
	 * init rows
	 */
	function initByData_rows(rows){
		
		jQuery.map(rows, function(row, rowIndex){
			
			initByData_row(row);
		});
		
	}
	
	
	/**
	 * init options by data
	 */
	function initByData_options(options){
		
		g_optionsCustom = options;
		
		if(!g_panel)
			return(false);
		
		g_panel.setSettingsValues("grid-settings", g_optionsCustom);
		
		if(g_panel.isVisible())
			updateGridSettingsPlaceholders();
	}
	
	
	/**
	 * init the builder by data
	 */
	function initByData(initData){
		
	
		try{
						
			g_ucAdmin.validateObjProperty(initData, "rows");
						
			//init options
			if(initData.hasOwnProperty("options"))
				initByData_options(initData.options);
			
			//init rows
			if(initData.hasOwnProperty("rows"))
				initByData_rows(initData.rows);
			
		}catch(error){
			var errorText = "Error in grid init: "+error+" <br><br>Save disabled, Please move to box view";
			showErrorMessage(errorText);
			
			if(g_pageBuilder)
				g_pageBuilder.hideSaveButton();
			
			throw(error);
			
		}
		
	}
	
	/**
	 * do grid action
	 */
	this.doAction = function(action, params){
		doGridAction(action, null, params);
	};
	
	/**
	 * on event run function
	 */
	this.onEvent = function(eventName, func){
		onEvent(eventName, func);
	};
	
	/**
	 * init builder options
	 */
	function initBuilderOptions(){
		
		var builderOptions = g_objGrid.data("builder-options");
		g_temp.indev = builderOptions["indev"];
		g_temp.google_fonts = builderOptions["google_fonts"];
		g_temp.icons = builderOptions["icons"];
		g_temp.page_params = builderOptions["params"];
		
		g_objGrid.removeAttr("data-builder-options");	//remove attribute for not interfere
	}
		
	
	/**
	 * init page builder - top window class
	 */
	function initPageBuilder(){
		
		if(window.top == window){
			trace("the grid is not inside iframe");
			return(false);
		}
		
		if(typeof window.top.g_objPageBuilder == "undefined"){
			
			g_temp.init_counter++;
			if(g_temp.init_counter < 60)		//30 seconds
				setTimeout(initPageBuilder, 500);
			else
				alert("The page builder is not inited, please write support ticket, we'll help you with this issue");
			
			return(false);
		}
		
		if(!window.top.g_objPageBuilder){
			
			g_temp.init_counter++;
			if(g_temp.init_counter < 60)		//30 seconds
				setTimeout(initPageBuilder, 500);
			else
				alert("page builder in parent frame not inited. please write support ticket, we'll help you with this issue");
			
			return(false);
		}
		
		//after page builder inited
		g_pageBuilder = window.top.g_objPageBuilder;
		window.top.g_objPageBuilder.initGridBuilder(t);
		
		g_objBuffer = g_pageBuilder.getObjBuffer();
		
		//init browser
		g_objBrowser = g_pageBuilder.getObjBrowser();
		g_objBrowserSections = g_pageBuilder.getObjBrowser("sections");
		
		g_objBodyParent = g_pageBuilder.getDomBody();
				
		initPanel();
		
		//init events
		g_ucAdmin.onEvent("install_addon", onInstallAddon, g_objBodyParent);
		
	}
	
	
	/**
	 * check duplicated IDs and trace if available
	 */
	function checkDuplicateIDs(){
		
		var arrIDs = {};
		var allElements = g_objWrapper.find("div,span");
		var numIDs = 0;
		
		jQuery.each(allElements, function(index, element){
			var objElement = jQuery(element);
			var id = objElement.prop("id");
			if(!id)
				return(true);
			
			if(arrIDs.hasOwnProperty(id)){
				trace("duplicate ID Found: "+id);
				numIDs++;
			}
			
			arrIDs[id] = true;
		});
		
		if(numIDs == 0)
			trace("duplicate ID not found");
		else
			trace("duplicate ID found");
			
		
		setTimeout(function(){
			alert("duplicate ID check finished, please remove it: "+numIDs);
		},2000);
		
	}
	
	
	/**
	 * init shape deviders
	 */
	function initShapeDividers(){
		
		if(typeof g_ucShapeDividersJson == "undefined"){
			
			g_vars.shape_deviders = [];
			
			//window.g_ucShapeDividersJson = [];
			//throw new Error("shape deviders object not found");
			//return(false);
		}
		else
			g_vars.shape_deviders = JSON.parse(g_ucShapeDividersJson);
		
	}
	
	/**
	 * get temp vars
	 */
	this.getTempVars = function(){
		
		return g_temp;
	};
	
	/**
	 * init grid
	 */
	this.init = function(gridID){
		
		try{
		
			g_objGrid = jQuery(gridID);
			if(g_objGrid.length == 0)
				throw new Error("grid object: " + gridID + " not found");
			
			g_gridID = gridID;
					
			//init parent frame
			initPageBuilder();
			
			g_objWrapper = g_objGrid.parents(".uc-grid-builder-wrapper");
						
			initBuilderOptions();
					
			//init live view
			var isLiveView = g_objGrid.data("liveview");
			g_temp.is_live_view = g_ucAdmin.strToBool(isLiveView);
			
			initShapeDividers();
			
			//init panel
			initEvents();
						
			initGridSettings();
			
			if(g_objBuffer)
				g_objBuffer.run();
			
			g_objRowStyleContainer = g_objWrapper.children(".uc-grid-row-styles");
			g_objColStyleContainer = g_objWrapper.children(".uc-grid-col-styles");
			
			//add the data
			var initData = g_objGrid.data("init");
			if(initData){
				initByData(initData);
				g_objGrid.removeAttr("data-init");   //remove attribute for not interfere
			}
			else{
				addRow();
			}
			
			triggerEvent(t.events.DATA_INITED);
			g_temp.is_data_inited = true;
			
			//put the css by the options
			putGeneratedCss();
			
			//init smooth scroll
			g_objScroll = new SmoothScroll('.uc-smooth-scroll a[href*="#"]');
			
			g_objHistory.initGrid(this);
			
			//initTipsy();
			
		}catch(error){
			
			showErrorMessage(error);
					
		}
	};
	
	
}