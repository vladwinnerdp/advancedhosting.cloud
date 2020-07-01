"use strict";

 /*
 * browser object
 */
function UniteCreatorGridActionsPanel(){
	
	var g_objPanel;
	var t = this;
	
	this.events = {
			BUTTON_CLICK: "button_click"
	};
	
	var g_options = {
			//allow_undock: false
	};
	
	var g_temp = {
			isInited:false
	};
	
	if(!g_ucAdmin)
		var g_ucAdmin = new UniteAdminUC();
	
	/**
	 * validate inited
	 */
	function validateInited(){
		
		g_ucAdmin.validateDomElement(g_objPanel, "actions panel");
	}
	
	
	/**
	 * on action button click
	 */
	function onActionButtonClick(){
		
		var objButton = jQuery(this);
		var action = objButton.data("action");
		
		if(!action)
			return(true);
		
		triggerEvent(t.events.BUTTON_CLICK, action);
		
	}
	
	function ____________EVENTS______________(){}
	
	
	/**
	 * grigger event
	 */
	function triggerEvent(eventName, options){
		
		g_objPanel.trigger(eventName, options);
	
	}
	
	
	/**
	 * on some event
	 */
	this.onEvent = function(eventName, func){
		
		g_objPanel.on(eventName, func);
		
	};
	
	
	/**
	 * init events
	 */
	function initEvents(){
		
		var objButtons = g_objPanel.find(".uc-toppanel-button");
				
		g_objPanel.find(".uc-toppanel-button").on("click",onActionButtonClick);
		
	}
	
	
	/**
	 * init panel
	 */
	this.init = function(objPanel){
		
		g_objPanel = objPanel;
		g_ucAdmin.validateDomElement(g_objPanel, "actions panel wrapper");
						
		initEvents();
		
		g_temp.isInited = true;
	};
	
}

//------------------ copy / paste object ------------------

