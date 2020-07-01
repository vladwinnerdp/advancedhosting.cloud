"use strict";

/**
 * container class for contain and restore information
 */
function UniteCreatorBuffer(){
	
	var g_data = null, g_localBufferID = null, g_objTypes = {};
	var t = this, g_objIndicator,g_objIndicatorContent;
	
	
	this.events = {
			STORE: "buffer_store",
			CLEAR: "buffer_cleared",
			UPDATED: "buffer_updated" 
	};
	
	/*
	var g_options = {
	};
	*/
	
	var g_temp = {
			isinited:false,
			isStorage:false,
			localStorageKey:"uc_buffer_data",
			localStorageKeyID:"uc_buffer_data_bufferid",
			scanTimeout:500
	};
	
	
	if(!g_ucAdmin)
		var g_ucAdmin = new UniteAdminUC();
	
	
	/**
	 * validate inited
	 */
	function validateInited(){
		if(g_temp.isinited == false)
			throw new Error("The memory container object is not inited");
	}
	
	
	/**
	 * don't allow double types
	 */
	function validateTypeNotExists(type){
		if(g_objTypes.hasOwnProperty(name) == true)
			throw new Error("Type: "+type +" already exists");
	}
	
	/**
	 * validate that some type exists
	 */
	function validateTypeExists(type){
		
		if(g_objTypes.hasOwnProperty(name) == false)
			throw new Error("Type: "+type +" not exists");
		
	}
	
	/**
	 * trigger event
	 */
	function triggerEvent(eventName, params){
		
		g_objIndicator.trigger(eventName, params);
	}
	
	
	/**
	 * on event name
	 */
	this.onEvent = function(eventName, func){
		
		validateInited();		
		
		g_objIndicator.on(eventName,func);
	};
	
	
	/**
	 * add some type
	 */
	this.addType = function(name, title){
		
		g_objTypes[name] = title;
	};
	
	
	/**
	 * det indicator html from data
	 */
	function setIndicatorHtml(objData){
		
		if(!objData){
			g_objIndicatorContent.html("");
			g_objIndicator.hide();
			return(false);
		}
		
		var type = objData["type"];
		var typeText = g_objTypes[type];
		var htmlIndicator = typeText+" "+g_uctext["in_buffer"];
		
		g_objIndicator.show();
		g_objIndicatorContent.html(htmlIndicator);
	}
	
	
	/**
	 * run after set data
	 */
	function afterSetData(objData){
		
		var type = objData["type"];
		
		setIndicatorHtml(objData);
		
		triggerEvent(t.events.STORE, type);
		triggerEvent(t.events.UPDATED);
	}
	
	
	/**
	 * store some content
	 * desc - description about the data stored
	 */
	this.store = function(type, id, htmlContent, objContent, params){
		
		validateInited();
		
		var generatedID = "ucbuffer_" + g_ucAdmin.getRandomString();
		g_localBufferID = generatedID;
		
		var objData = {};
		objData["type"] = type;
		objData["id"] = id;
		objData["content_html"] = htmlContent;
		objData["content_object"] = objContent;
		
		//store local html data (can't store in local)
		
		g_data = objData;
		
		if(g_temp.isStorage)
			storeDataInLocalStorage(objData, generatedID);
		
		afterSetData(objData);
	};
	
	
	/**
	 * clear buffer
	 */
	this.clear = function(){
		
		g_data = null;
		g_localBufferID = null;
		
		clearLocalStorage();
		setIndicatorHtml();
		
		triggerEvent(t.events.CLEAR);
		triggerEvent(t.events.UPDATED);
	};
	
	
	
	/**
	 * get type stored
	 */
	this.getStoredType = function(){
		
		if(!g_data)
			return(null);
			
		var type = g_ucAdmin.getVal(g_data, "type");
		
		return(type);
	};

		
	
	/**
	 * get stored content
	 */
	this.getStoredContent = function(){
		
		if(!g_data)
			return(null);
		
		return(g_data);
	};
	
	
	function ___________LOCAL_STORAGE___________(){}
	
	
	/**
	 * clear local storage
	 */
	function clearLocalStorage(){
		if(g_temp.isStorage == false)
			return(false);
		
		window.localStorage.removeItem(g_temp.localStorageKeyID);
		window.localStorage.removeItem(g_temp.localStorageKey);
	}
	
	
	/**
	 * get data from local storage
	 * if empty return false
	 */
	function getDataFromLocalStorage(){
		
		if(g_temp.isStorage == false)
			return(false);
				
		var strData = window.localStorage.getItem(g_temp.localStorageKey);
		
		if(!strData)
			return(false);
		
		var objData = jQuery.parseJSON(strData);
		if(!objData)
			return(false);
		
		if(g_data && g_data.hasOwnProperty("content_html"))
			objData["content_html"] = g_data["content_html"];
		
		return(objData);
	}
	
	/**
	 * get data ID from local storage
	 */
	function getBufferIDFromLocalStorage(){
		
		if(g_temp.isStorage == false)
			return(false);
		
		var bufferID = window.localStorage.getItem(g_temp.localStorageKeyID);
		
		if(!bufferID)
			return(false);
		
		return(bufferID);
	}
	
	
	/**
	 * check if is some data in local storage
	 */
	function isDataInLocalStorage(){
		var bufferID = getBufferIDFromLocalStorage();
		
		if(bufferID)
			return(true);
		else
			return(false);
	}
	
	
	/**
	 * restore data from local storage if available
	 */
	function restoreFromLocalStorage(){
		
		var storageBufferID = getBufferIDFromLocalStorage();
		if(!storageBufferID)
			return(false);
		
		if(storageBufferID == g_localBufferID)
			return(false);
		
		g_localBufferID = storageBufferID;
		
		var objData = getDataFromLocalStorage();
		g_data = objData;
		
		afterSetData(objData);
	}
	
	
	/**
	 * store data in local storage
	 */
	function storeDataInLocalStorage(objData, bufferID){
		
		if(g_temp.isStorage == false)
			return(false);
		
		if(!g_data){
			clearLocalStorage();
			return(false);
		}
				
		var dataStore = jQuery.extend({}, objData);
		delete dataStore["content_html"];
		
		var strData = JSON.stringify(dataStore);
				
		window.localStorage.setItem(g_temp.localStorageKeyID, bufferID);
		window.localStorage.setItem(g_temp.localStorageKey, strData);
		
	}
	
	
	/**
	 * scan for some data to appear
	 */
	function runCronSync(){
		
		var storageBufferID = getBufferIDFromLocalStorage();
		
		if(storageBufferID == g_localBufferID)
			return(false);
		
		if(!storageBufferID && !g_localBufferID)
			return(false);
				
		if(!storageBufferID)
			t.clear();
		else
			restoreFromLocalStorage();
		
	}
	
	
	
	/**
	 * init the object
	 */
	this.init = function(){
		
		g_objIndicator = jQuery("#uc_buffer_indicator");
		
		//create invisible indicator
		if(g_objIndicator.length == 0){
			jQuery("body").append("<div id='uc_buffer_indicator' style='display:none'><div class='uc-buffer-container-content'></div></div>");
			g_objIndicator = jQuery("#uc_buffer_indicator");
		}
		
		g_ucAdmin.validateDomElement(g_objIndicator, "Buffer Indicator");
		
		g_objIndicatorContent = g_objIndicator.find(".uc-buffer-container-content");
		g_ucAdmin.validateDomElement(g_objIndicatorContent, "Buffer Indicator Content");
		
		
		g_temp.isinited = true;
		g_temp.isStorage = (typeof(Storage) !== "undefined");
				
	};
	
	
	/**
	 * run the sync
	 */
	this.run = function(){
		
		//clearLocalStorage();	//for clear at start
		
		restoreFromLocalStorage();
		
		if(g_temp.isStorage == true)
			setInterval(runCronSync, g_temp.scanTimeout);
		
	};
	
	
}

/**
 * history object
 */
function UniteCreatorHistory(){
	
	var t = this;
	var g_arrData = [], g_objGridBuilder;
	var g_temp = {
			max_items:30,
			handle:null,
			keyupTrashold:500,
			hashPrefix: "uc_obj_hash_",
			indev:false
	};
	
	
	/**
	 * run function with trashold
	 */
	function runWithTrashold(func){
		
		if(g_temp.handle)
			clearTimeout(g_temp.handle);
		
		g_temp.handle = setTimeout(function(){
			func();
		}
		, g_temp.keyupTrashold);
		
	};
	
	
	/**
	 * add grid data
	 */
	function addGridData(){
		
		var objData = g_objGridBuilder.getGridData();
				
		g_arrData.push(objData);
		
		if(g_arrData.length > g_temp.max_items)
			g_arrData.shift();
		
	}
	
	
	/**
	 * on change taken
	 */
	function onChangeTaken(){
		
		runWithTrashold(addGridData);
		
	}
	
	/**
	 * add hashes
	 */
	function addHashesToObjData(obj, isArray){
		
		if(typeof obj != "object")
			return(obj);
				
		jQuery.each(obj, function(key, value){
						
			if(typeof value != "object")
				return(true);
			
			var isChildArray = jQuery.isArray(value);
			
			//if it's array - go dipper in recursion
			if(isChildArray || (isArray === true && typeof value == "object")){
				
				//trace("go into:" + key);
				
				value = addHashesToObjData(value, isChildArray);
			}
			
			var hash = g_ucAdmin.getHash(value);
			
			if(isArray === true)
				obj[key][g_temp.hashPrefix + "_array_item_"+key] = hash;
			else
				obj[g_temp.hashPrefix + key] = hash;
			
				//value = addHashesToObjData(value);
			
		});
		
		
		return(obj);
	}
	
	
	/**
	 * test
	 */
	function test(){
		
		trace("test history");
		
		//g_ucAdmin.startTimer();
		
		var objData = g_objGridBuilder.getGridData();
		var objData = addHashesToObjData(objData);
		
		//g_ucAdmin.printTimer();
		
		trace(objData);
	}
	
	/**
	 * get next operation
	 */
	this.getNextOperation = function(){
		
	};
	
	/**
	 * init grid builder
	 */
	this.initGrid = function(gridBuilder){
		
		g_objGridBuilder = gridBuilder;
		
		var tempGrid = g_objGridBuilder.getTempVars();
		g_temp.indev = tempGrid.indev;
		
		//trace(g_temp.indev);
		
		g_objGridBuilder.onEvent(g_objGridBuilder.events.CHANGE_TAKEN, onChangeTaken);
		
		//test();
	};
	
	
}
