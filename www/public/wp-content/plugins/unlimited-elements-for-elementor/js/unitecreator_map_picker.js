"use strict";

function UniteCreatorMapPicker(){
	
	var g_objSettings = new UniteSettingsUC();
	var g_objMapWrapper;
	var g_objInputLocation, g_map, g_marker, g_autocomplete;
	var g_defaultLocation = {lat: -33.8688, lng: 151.2195};		//sydney
	var g_defaultZoom = 13;
	var g_initMapData = {};
	var g_initSettingsData = {};
	
	var t = this;
	
	if(!g_ucAdmin){		//for autocomplete
		var g_ucAdmin = new UniteAdminUC();
	};
	
	var g_vars = {
		link_static_template: "https://maps.googleapis.com/maps/api/staticmap?key=[api_key]",
		link_template: "https://maps.googleapis.com/maps/api/js?key=[api_key]&libraries=places",
		api_key: null,
		url_icons_base: "https://maps.google.com/mapfiles/kml/",
		last_marker_position:null
	};
	
	var g_arrIconsShapes = ["shapes/airports.png","shapes/arrow-reverse.png","shapes/arrow.png","shapes/arts.png","shapes/bars.png","shapes/broken_link.png","shapes/bus.png","shapes/cabs.png","shapes/camera.png","shapes/campfire.png","shapes/campground.png","shapes/capital_big.png","shapes/capital_big_highlight.png","shapes/capital_small.png","shapes/capital_small_highlight.png","shapes/caution.png","shapes/church.png","shapes/coffee.png","shapes/convenience.png","shapes/cross-hairs.png","shapes/cross-hairs_highlight.png","shapes/cycling.png","shapes/dining.png","shapes/dollar.png","shapes/donut.png","shapes/earthquake.png","shapes/electronics.png","shapes/euro.png","shapes/falling_rocks.png","shapes/ferry.png","shapes/firedept.png","shapes/fishing.png","shapes/flag.png","shapes/forbidden.png","shapes/gas_stations.png","shapes/golf.png","shapes/grocery.png","shapes/heliport.png","shapes/highway.png","shapes/hiker.png","shapes/homegardenbusiness.png","shapes/horsebackriding.png","shapes/hospitals.png","shapes/info-i.png","shapes/info.png","shapes/info_circle.png","shapes/lodging.png","shapes/man.png","shapes/marina.png","shapes/mechanic.png","shapes/motorcycling.png","shapes/mountains.png","shapes/movies.png","shapes/open-diamond.png","shapes/parking_lot.png","shapes/parks.png","shapes/pharmacy_rx.png","shapes/phone.png","shapes/picnic.png","shapes/placemark_circle.png","shapes/placemark_circle_highlight.png","shapes/placemark_square.png","shapes/placemark_square_highlight.png","shapes/play.png","shapes/poi.png","shapes/police.png","shapes/polygon.png","shapes/post_office.png","shapes/rail.png","shapes/ranger_station.png","shapes/realestate.png","shapes/road_shield1.png","shapes/road_shield2.png","shapes/road_shield3.png","shapes/ruler.png","shapes/sailing.png","shapes/salon.png","shapes/schools.png","shapes/shaded_dot.png","shapes/shopping.png","shapes/ski.png","shapes/snack_bar.png","shapes/square.png","shapes/star.png","shapes/subway.png","shapes/swimming.png","shapes/target.png","shapes/terrain.png","shapes/toilets.png","shapes/trail.png","shapes/tram.png","shapes/triangle.png","shapes/truck.png","shapes/volcano.png","shapes/water.png","shapes/webcam.png","shapes/wheel_chair_accessible.png","shapes/woman.png","shapes/yen.png","shapes/sunny.png","shapes/partly_cloudy.png","shapes/snowflake_simple.png","shapes/rainy.png","shapes/thunderstorm.png"];
	var g_arrStyles = {
			"silver":[{"elementType":"geometry","stylers":[{"color":"#f5f5f5"}]},{"elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"elementType":"labels.text.fill","stylers":[{"color":"#616161"}]},{"elementType":"labels.text.stroke","stylers":[{"color":"#f5f5f5"}]},{"featureType":"administrative.land_parcel","elementType":"labels.text.fill","stylers":[{"color":"#bdbdbd"}]},{"featureType":"poi","elementType":"geometry","stylers":[{"color":"#eeeeee"}]},{"featureType":"poi","elementType":"labels.text.fill","stylers":[{"color":"#757575"}]},{"featureType":"poi.park","elementType":"geometry","stylers":[{"color":"#e5e5e5"}]},{"featureType":"poi.park","elementType":"labels.text.fill","stylers":[{"color":"#9e9e9e"}]},{"featureType":"road","elementType":"geometry","stylers":[{"color":"#ffffff"}]},{"featureType":"road.arterial","elementType":"labels.text.fill","stylers":[{"color":"#757575"}]},{"featureType":"road.highway","elementType":"geometry","stylers":[{"color":"#dadada"}]},{"featureType":"road.highway","elementType":"labels.text.fill","stylers":[{"color":"#616161"}]},{"featureType":"road.local","elementType":"labels.text.fill","stylers":[{"color":"#9e9e9e"}]},{"featureType":"transit.line","elementType":"geometry","stylers":[{"color":"#e5e5e5"}]},{"featureType":"transit.station","elementType":"geometry","stylers":[{"color":"#eeeeee"}]},{"featureType":"water","elementType":"geometry","stylers":[{"color":"#c9c9c9"}]},{"featureType":"water","elementType":"labels.text.fill","stylers":[{"color":"#9e9e9e"}]}],
			"retro":[{"elementType":"geometry","stylers":[{"color":"#ebe3cd"}]},{"elementType":"labels.text.fill","stylers":[{"color":"#523735"}]},{"elementType":"labels.text.stroke","stylers":[{"color":"#f5f1e6"}]},{"featureType":"administrative","elementType":"geometry.stroke","stylers":[{"color":"#c9b2a6"}]},{"featureType":"administrative.land_parcel","elementType":"geometry.stroke","stylers":[{"color":"#dcd2be"}]},{"featureType":"administrative.land_parcel","elementType":"labels.text.fill","stylers":[{"color":"#ae9e90"}]},{"featureType":"landscape.natural","elementType":"geometry","stylers":[{"color":"#dfd2ae"}]},{"featureType":"poi","elementType":"geometry","stylers":[{"color":"#dfd2ae"}]},{"featureType":"poi","elementType":"labels.text.fill","stylers":[{"color":"#93817c"}]},{"featureType":"poi.park","elementType":"geometry.fill","stylers":[{"color":"#a5b076"}]},{"featureType":"poi.park","elementType":"labels.text.fill","stylers":[{"color":"#447530"}]},{"featureType":"road","elementType":"geometry","stylers":[{"color":"#f5f1e6"}]},{"featureType":"road.arterial","elementType":"geometry","stylers":[{"color":"#fdfcf8"}]},{"featureType":"road.highway","elementType":"geometry","stylers":[{"color":"#f8c967"}]},{"featureType":"road.highway","elementType":"geometry.stroke","stylers":[{"color":"#e9bc62"}]},{"featureType":"road.highway.controlled_access","elementType":"geometry","stylers":[{"color":"#e98d58"}]},{"featureType":"road.highway.controlled_access","elementType":"geometry.stroke","stylers":[{"color":"#db8555"}]},{"featureType":"road.local","elementType":"labels.text.fill","stylers":[{"color":"#806b63"}]},{"featureType":"transit.line","elementType":"geometry","stylers":[{"color":"#dfd2ae"}]},{"featureType":"transit.line","elementType":"labels.text.fill","stylers":[{"color":"#8f7d77"}]},{"featureType":"transit.line","elementType":"labels.text.stroke","stylers":[{"color":"#ebe3cd"}]},{"featureType":"transit.station","elementType":"geometry","stylers":[{"color":"#dfd2ae"}]},{"featureType":"water","elementType":"geometry.fill","stylers":[{"color":"#b9d3c2"}]},{"featureType":"water","elementType":"labels.text.fill","stylers":[{"color":"#92998d"}]}],
			"dark":[{"elementType":"geometry","stylers":[{"color":"#212121"}]},{"elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"elementType":"labels.text.fill","stylers":[{"color":"#757575"}]},{"elementType":"labels.text.stroke","stylers":[{"color":"#212121"}]},{"featureType":"administrative","elementType":"geometry","stylers":[{"color":"#757575"}]},{"featureType":"administrative.country","elementType":"labels.text.fill","stylers":[{"color":"#9e9e9e"}]},{"featureType":"administrative.land_parcel","stylers":[{"visibility":"off"}]},{"featureType":"administrative.locality","elementType":"labels.text.fill","stylers":[{"color":"#bdbdbd"}]},{"featureType":"poi","elementType":"labels.text.fill","stylers":[{"color":"#757575"}]},{"featureType":"poi.park","elementType":"geometry","stylers":[{"color":"#181818"}]},{"featureType":"poi.park","elementType":"labels.text.fill","stylers":[{"color":"#616161"}]},{"featureType":"poi.park","elementType":"labels.text.stroke","stylers":[{"color":"#1b1b1b"}]},{"featureType":"road","elementType":"geometry.fill","stylers":[{"color":"#2c2c2c"}]},{"featureType":"road","elementType":"labels.text.fill","stylers":[{"color":"#8a8a8a"}]},{"featureType":"road.arterial","elementType":"geometry","stylers":[{"color":"#373737"}]},{"featureType":"road.highway","elementType":"geometry","stylers":[{"color":"#3c3c3c"}]},{"featureType":"road.highway.controlled_access","elementType":"geometry","stylers":[{"color":"#4e4e4e"}]},{"featureType":"road.local","elementType":"labels.text.fill","stylers":[{"color":"#616161"}]},{"featureType":"transit","elementType":"labels.text.fill","stylers":[{"color":"#757575"}]},{"featureType":"water","elementType":"geometry","stylers":[{"color":"#000000"}]},{"featureType":"water","elementType":"labels.text.fill","stylers":[{"color":"#3d3d3d"}]}],
			"night":[{"elementType":"geometry","stylers":[{"color":"#242f3e"}]},{"elementType":"labels.text.fill","stylers":[{"color":"#746855"}]},{"elementType":"labels.text.stroke","stylers":[{"color":"#242f3e"}]},{"featureType":"administrative.locality","elementType":"labels.text.fill","stylers":[{"color":"#d59563"}]},{"featureType":"poi","elementType":"labels.text.fill","stylers":[{"color":"#d59563"}]},{"featureType":"poi.park","elementType":"geometry","stylers":[{"color":"#263c3f"}]},{"featureType":"poi.park","elementType":"labels.text.fill","stylers":[{"color":"#6b9a76"}]},{"featureType":"road","elementType":"geometry","stylers":[{"color":"#38414e"}]},{"featureType":"road","elementType":"geometry.stroke","stylers":[{"color":"#212a37"}]},{"featureType":"road","elementType":"labels.text.fill","stylers":[{"color":"#9ca5b3"}]},{"featureType":"road.highway","elementType":"geometry","stylers":[{"color":"#746855"}]},{"featureType":"road.highway","elementType":"geometry.stroke","stylers":[{"color":"#1f2835"}]},{"featureType":"road.highway","elementType":"labels.text.fill","stylers":[{"color":"#f3d19c"}]},{"featureType":"transit","elementType":"geometry","stylers":[{"color":"#2f3948"}]},{"featureType":"transit.station","elementType":"labels.text.fill","stylers":[{"color":"#d59563"}]},{"featureType":"water","elementType":"geometry","stylers":[{"color":"#17263c"}]},{"featureType":"water","elementType":"labels.text.fill","stylers":[{"color":"#515c6d"}]},{"featureType":"water","elementType":"labels.text.stroke","stylers":[{"color":"#17263c"}]}],
			"aubegine":[{"elementType":"geometry","stylers":[{"color":"#1d2c4d"}]},{"elementType":"labels.text.fill","stylers":[{"color":"#8ec3b9"}]},{"elementType":"labels.text.stroke","stylers":[{"color":"#1a3646"}]},{"featureType":"administrative.country","elementType":"geometry.stroke","stylers":[{"color":"#4b6878"}]},{"featureType":"administrative.land_parcel","elementType":"labels.text.fill","stylers":[{"color":"#64779e"}]},{"featureType":"administrative.province","elementType":"geometry.stroke","stylers":[{"color":"#4b6878"}]},{"featureType":"landscape.man_made","elementType":"geometry.stroke","stylers":[{"color":"#334e87"}]},{"featureType":"landscape.natural","elementType":"geometry","stylers":[{"color":"#023e58"}]},{"featureType":"poi","elementType":"geometry","stylers":[{"color":"#283d6a"}]},{"featureType":"poi","elementType":"labels.text.fill","stylers":[{"color":"#6f9ba5"}]},{"featureType":"poi","elementType":"labels.text.stroke","stylers":[{"color":"#1d2c4d"}]},{"featureType":"poi.park","elementType":"geometry.fill","stylers":[{"color":"#023e58"}]},{"featureType":"poi.park","elementType":"labels.text.fill","stylers":[{"color":"#3C7680"}]},{"featureType":"road","elementType":"geometry","stylers":[{"color":"#304a7d"}]},{"featureType":"road","elementType":"labels.text.fill","stylers":[{"color":"#98a5be"}]},{"featureType":"road","elementType":"labels.text.stroke","stylers":[{"color":"#1d2c4d"}]},{"featureType":"road.highway","elementType":"geometry","stylers":[{"color":"#2c6675"}]},{"featureType":"road.highway","elementType":"geometry.stroke","stylers":[{"color":"#255763"}]},{"featureType":"road.highway","elementType":"labels.text.fill","stylers":[{"color":"#b0d5ce"}]},{"featureType":"road.highway","elementType":"labels.text.stroke","stylers":[{"color":"#023e58"}]},{"featureType":"transit","elementType":"labels.text.fill","stylers":[{"color":"#98a5be"}]},{"featureType":"transit","elementType":"labels.text.stroke","stylers":[{"color":"#1d2c4d"}]},{"featureType":"transit.line","elementType":"geometry.fill","stylers":[{"color":"#283d6a"}]},{"featureType":"transit.station","elementType":"geometry","stylers":[{"color":"#3a4762"}]},{"featureType":"water","elementType":"geometry","stylers":[{"color":"#0e1626"}]},{"featureType":"water","elementType":"labels.text.fill","stylers":[{"color":"#4e6d70"}]}]
	};
	
	
	
	
	/**
	 * on place changed, update the map
	 */
	function onAutocompletePlaceChanged(){
		
    	g_marker.setVisible(false);
		    	
        var place = g_autocomplete.getPlace();
    	
        //trace(place);
    	
    	if(!place.geometry) {
            // User entered the name of a Place that was not suggested and
            // pressed the Enter key, or the Place Details request failed.
        	g_marker.setVisible(true);
            window.alert("No details available for input: '" + place.name + "'");
            return;
        }		
		
        // If the place has a geometry, then present it on a map.
        if (place.geometry.viewport) {
          g_map.fitBounds(place.geometry.viewport);
        } else {
          g_map.setCenter(place.geometry.location);
          g_map.setZoom(17);
        }
        
        g_marker.setPosition(place.geometry.location);
        g_marker.setVisible(true);
		
        g_objInputLocation.val("");
	}
	
	/**
	 * goto some location
	 */
	function gotoLocation(location, zoom){
    	
		if(!zoom)
			var zoom = 17;
		
		g_marker.setVisible(false);
        
    	g_map.setCenter(location);
        g_map.setZoom(zoom);
    	
        g_vars.last_marker_position = location;
        
        g_marker.setPosition(location);
        g_marker.setVisible(true);
	}
	
	/**
	 * get style json from setting values
	 */
	function getStyleEncoded(values){
		
		var style = g_ucAdmin.getVal(values, "style");
		if(style == "custom"){
			var styleJson = g_ucAdmin.getVal(values, "style_json");
		}else{
			var styleJson = g_arrStyles[style];
			styleJson = JSON.stringify(styleJson);
		}
		
		return(styleJson);
	}
	
	
	/**
	 * get controls from setting values
	 */
	function getArrControls(values){
		
	}
	
	
	/**
	 * get map location and zoom
	 */
	function getMapData(){
		
		var data = {};
		var center = g_map.getCenter();
		data.center = {
			lat: center.lat(),
			lng: center.lng()
		};
		
		data.zoom = g_map.getZoom();
		
		if(g_marker){
			
			data.marker = {};
			
			//--- marker
			var markerPosition = g_marker.getPosition();
			data.marker.lat = markerPosition.lat();
			data.marker.lng = markerPosition.lng();
			
			var markerIcon = g_marker.getIcon();
			if(!markerIcon)
				markerIcon = null;
			else
				markerIcon = g_ucAdmin.urlToRelative(markerIcon);
			
			data.marker.icon = markerIcon;
			data.marker.isvisible = g_marker.getVisible();
		}
		
		//styles
		var settingValues = g_objSettings.getSettingsValues();
		
		var styleJson = getStyleEncoded(settingValues);
		data.style = styleJson;
		
		//typeid
		data.maptypeid = g_map.getMapTypeId();
		
		//language
		data.lang = g_ucAdmin.getVal(settingValues, "language");
		
		//controls
		var arrControls = ["fullscreenControl","mapTypeControl","zoomControl","streetViewControl"];
		var controls = {};
		for(var index in arrControls){
			var control = arrControls[index];
			var value = g_ucAdmin.getVal(settingValues, control,true,g_ucAdmin.getvalopt.FORCE_BOOLEAN);
			
			controls[control] = value;
		}
		
		data.controls = controls;
		
		return(data);
	}
	
	
	function _______MARKER_____(){}
	
	/**
	 * parse some icons in js
	 */
	function parseIcons(){
		
		var objLinks = jQuery("#icons a");
		var base = "http://maps.google.com/mapfiles/kml/";
		
		var arrIcons = [];
			
		jQuery.each(objLinks, function(index, link){
			var objLink = jQuery(link);
			var url = objLink.attr("href");
			var icon = url.replace(base,"");
			arrIcons.push(icon);
		});
		
		var json = JSON.stringify(arrIcons);
		trace(json);
		
	}
	
	
	/**
	 * set marker icon
	 */
	function changeMarkerIcon(value){
		
		if(!value)
			return(false);
		
		var index = g_arrIconsShapes.indexOf(value);
		if(index == -1)
			return(false);
		
		var urlIcon = g_vars.url_icons_base + value;
		
		g_marker.setVisible(true);
				
		g_marker.setIcon(urlIcon);
		
		return(true);
	}
	
	/**
	 * set default marker icon
	 */
	function setDefaultMarker(){
		
		g_marker.setIcon("");
	}
	
	
	/**
	 * set marker by image
	 */
	function changeMarkerImage(image){
		
		image = jQuery.trim(image);
		
		if(!image)
			return(false);
		
		var fullImage = g_ucAdmin.urlToFull(image);
		g_marker.setIcon(fullImage);
		
		return(true);
	}
	
	
	/**
	 * set marker
	 */
	function setMarker(values){
		
		var type = g_ucAdmin.getVal(values, "marker_type");
		
		if(type == "no")
			g_marker.setVisible(false);
		else
			g_marker.setVisible(true);
		
		switch(type){
			case "default":
				setDefaultMarker();
			break;
			case "icon":
				var icon = g_ucAdmin.getVal(values, "icon");
				var changed = changeMarkerIcon(icon);
				if(changed == false)
					setDefaultMarker();
			break;
			case "image":
				var image = g_ucAdmin.getVal(values, "marker_image");
				var changed = changeMarkerImage(image);
				if(changed == false)
					setDefaultMarker();
			break;
		}
			
	}
	
	function _______STYLE_____(){}
	
	/**
	 * set custom map style
	 */
	function setCustomMapStyle(strStyle){
		
		var objError = jQuery("#uc-mappicker-style-error");
		objError.hide();
		
		strStyle = jQuery.trim(strStyle);
		
		if(!strStyle)
			return(false);
		
		try{
			
			var objStyle = jQuery.parseJSON(strStyle);
			
		}catch(error){
			objError.show().html("wrong map style given");
			return(false);
		}			
		
		g_map.setOptions({styles: objStyle});
		
	}
	
	
	/**
	 * change map style
	 */
	function changeMapStyle(style, strStyle){
		
		if(style == "custom"){
			setCustomMapStyle(strStyle);
			
			return(false);
		}
		
		if(!style){
			objStyle = {};
		}else{
			var objStyle = g_ucAdmin.getVal(g_arrStyles, style);
			if(!objStyle)
				throw new Error("Style not found: " + objStyle);
		}
		
		g_map.setOptions({styles: objStyle});
		
	}
	
	function _______LANGUAGE_AND_CONTROLS_____(){}
	
	
	/**
	 * set language
	 */
	function setLanguage(lang){
		
		var mapData = getMapData();
		
		g_objMapWrapper.html("");
		
		var url = g_vars.link_template+"&language="+lang;
		
		var data = {replaceID:"uc_mappicker_script"};
		data.onload = function(){
			
			g_objMapWrapper.html("<div id='uc_mappicker_map'></div>");
			
			initMapObjects(mapData);
		};
		
		g_ucAdmin.loadIncludeFile("js", url, data);
		
	}
	
	
	/**
	 * set control
	 */
	function setControl(settingName, settingValue){
		
		var value = g_ucAdmin.strToBool(settingValue);
		var option = {};
		option[settingName] = value;
					
		g_map.setOptions(option);
	}
	
	function _______EVENTS_____(){}
	
	/**
	 * run on setting change
	 */
	function onSettingChange(event, params){
		
		var settingName = g_ucAdmin.getVal(params, "name");
		var settingValue = g_ucAdmin.getVal(params, "value");
		
		switch(settingName){
			case "marker_type":
			case "marker_image":
			case "icon":
				var values = g_objSettings.getSettingsValues();
				setMarker(values);
			break;
			case "style_json":
			case "style":
				var values = g_objSettings.getSettingsValues();
				var style = g_ucAdmin.getVal(values, "style");
				var styleJson = g_ucAdmin.getVal(values, "style_json");
				
				changeMapStyle(style, styleJson);
			break;
			case "language":
				setLanguage(settingValue);
			break;
			case "map_type":
				g_map.setMapTypeId(settingValue);
			break;
			case "fullscreenControl":
			case "mapTypeControl":
			case "scaleControl":
			case "streetViewControl":
			case "rotateControl":
			case "fullscreenControl":
			case "zoomControl":
				setControl(settingName, settingValue);
			break;
		}
		
		
	}
	
	
	/**
	 * change map to my location
	 */
	function onMyLocationClick(){
		
		if(!navigator.geolocation){
			return(false);
		}
		
		var objLoader = jQuery("#uc_loader_mylocation");
		objLoader.show();
		
	    navigator.geolocation.getCurrentPosition(function(position) {
	    	
	    	objLoader.hide();
	    	
	    	var location = {
	          lat: position.coords.latitude,
	          lng: position.coords.longitude
	        };
	        
	    	gotoLocation(location, 15);
	    	
	      }, function() {
		    
	    	  objLoader.hide();
	        
	    	 alert("couldn't find the current location");
	    	  
	     });
		
	}
	
	/**
	 * on marker drag change
	 */
	function onMarkerDragChange(){
		
		var markerPosition = g_marker.getPosition();
		g_vars.last_marker_position = {
				lat: markerPosition.lat(),
				lng: markerPosition.lng()
		};
		
	}
	
	
	/**
	 * init events
	 */
	function initEvents(){
		
		g_autocomplete.addListener('place_changed', onAutocompletePlaceChanged);
		
		g_objSettings.setEventOnChange(onSettingChange);
		
		var objGetLocationButton = g_objSettings.getInputByName("button_my_location");
		objGetLocationButton.on("click",onMyLocationClick);
		
		//marker
		g_marker.addListener('dragend', onMarkerDragChange);
	}
		
	
	
	function _______INIT_____(){}

	/**
	 * init settings
	 */
	function initSettings(){
		
		var objSettingsWrapper = jQuery("#uc_settings_map");
		g_ucAdmin.validateDomElement(objSettingsWrapper, "settings wrapper");
		
		//add icons type
		var params = {};
		var template = "<img width='32' src='"+g_vars.url_icons_base+"[icon]'>";
		
		//get icon name
		params.getIconName = function(name){
			var iconName = name;
			var pos = name.indexOf("/");
			var prefix = (pos > 0)?name.substr(0, pos+1):null;
			if(prefix)
				iconName = iconName.replace(prefix, "");
			
			iconName = iconName.replace(".png","");
			
			return(iconName);
		};
		
		
		g_objSettings.iconPicker_addIconsType("map", g_arrIconsShapes, template, params);
		
		g_objSettings.init(objSettingsWrapper);
		
		//iinput objects
		g_objInputLocation = g_objSettings.getInputByName("location");
		
		//set values
		if(g_initSettingsData){
			g_objSettings.setValues(g_initSettingsData);
		}
		
	}
	
	
	/**
	 * set map data from frame parent
	 */
	function setMapDataFromFrameParent(){
		
		var objParent = window.parent;
		if(!objParent)
			return(false);
		
		var data = objParent.uc_mappicker_data;
		if(!data)
			return(false);
		
		g_initMapData = g_ucAdmin.getVal(data, "map");
		g_initSettingsData = g_ucAdmin.getVal(data, "settings");
		
	}
	
	
	/**
	 * init objects
	 */
	function initObjects(apiKey){
		
		g_objMapWrapper = jQuery("#uc_mappicker_mapwrapper");
		
		g_vars.api_key = apiKey;
		g_vars.link_template = g_vars.link_template.replace("[api_key]", apiKey);
		g_vars.link_static_template = g_vars.link_static_template.replace("[api_key]", apiKey);
		
		setMapDataFromFrameParent();
		
		//set map data
		/*
		var mapData = g_objMapWrapper.data("mapdata");
		if(mapData && typeof mapData == "object")			
			g_initMapData = g_objMapWrapper.data("mapdata");
		*/
	}
	
	
	/**
	 * init map objects
	 */
	function initMapObjects(mapData){
		if(!mapData)
			mapData = null;
		
		initMap_putMap(mapData);
		initMap_autocomplete();
	}
	
	/**
	 * put the map
	 */
	function initMap_putMap(mapData){
		
		var latLong = g_defaultLocation;
		var zoom = g_defaultZoom;
		
		if(mapData && mapData.center){
			latLong = mapData.center;
			zoom = mapData.zoom;
		}
		
		latLong.lat = Number(latLong.lat);
		latLong.lng = Number(latLong.lng);
		zoom = Number(zoom);
		
		var objMap = document.getElementById('uc_mappicker_map');
	    
		var mapOptions = {
		        center: latLong,
		        zoom: zoom
		 };
		
		//style
		if(mapData && mapData.style){
	    	var jsonStyles = JSON.parse(mapData.style);
	    	mapOptions.styles = jsonStyles;
	    }
		
		//typeid
		if(mapData && mapData.maptypeid){
			mapOptions.mapTypeId = mapData.maptypeid;
		}
		
		//controls
		if(mapData && mapData.controls){
			for(var control in mapData.controls){
				value = mapData.controls[control];
				if(value == false)
					mapOptions[control] = false;
			}
		}
		
		
		g_map = new google.maps.Map(objMap, mapOptions);
	    
	    //set marker
	    var markerPos = latLong;
	    var markerIcon = null;
	    var markerVisible = true;
	    
	    if(mapData && mapData.marker){
	    	
	    	markerPos = {
	    			lat: Number(mapData.marker.lat),
	    			lng: Number(mapData.marker.lng)
	    	};
	    	
	    	markerIcon = mapData.marker.icon;
	    	
	    	markerVisible = mapData.marker.isvisible;
	    }
	    
	    var markerOptions = {
				position: markerPos,
		        map: g_map,
		        draggable:true
		 };
	    
	    
	    if(markerIcon){
	    	markerIcon = g_ucAdmin.urlToFull(markerIcon);
	    	markerOptions.icon = markerIcon;
	    }
	    
	    if(markerVisible == false)
	    	markerOptions.visible = false;
	    
		g_marker = new google.maps.Marker(markerOptions);
		
		g_vars.last_marker_position = latLong;
		
	}
	
	
	/**
	 * init autocomplete
	 */
	function initMap_autocomplete(){
	    
		var input = g_objInputLocation[0];
	    
		g_autocomplete = new google.maps.places.Autocomplete(input);
		
	    //autocomplete.setTypes(["address","establishment"]);
		g_autocomplete.bindTo('bounds', g_map);
		
	}
	
	
	/**
	 * get static map url
	 */
	function getUrlStaticMap(mapData){
		
		var url = g_vars.link_static_template;
		
		var size = "220x100";
		url += "&size="+size;
		
		if(!mapData.center)
			return(null);
		
		url += "&center="+mapData.center.lat+","+mapData.center.lng;
		
		url += "&zoom="+mapData.zoom;
		
		return(url);
	}
	
	
	/**
	 * get data
	 */
	this.getData = function(){
		
		var data = {};
		data.map = getMapData();
		data.settings = g_objSettings.getSettingsValues();
		data.url_static_map = getUrlStaticMap(data.map);
		
		return(data);
	};
	
	
	/**
	 * init the map
	 */
	this.initMap = function(apiKey){
		
		initObjects(apiKey);
		
		initSettings();
		
		initMapObjects(g_initMapData);
		
		initEvents();
		
		//setTimeout(setLanguage, 3000);
	};
	
	
	
}