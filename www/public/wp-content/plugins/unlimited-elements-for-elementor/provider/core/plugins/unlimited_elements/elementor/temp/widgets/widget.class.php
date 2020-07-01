<?php

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

class WidgetBaseAL extends Widget_Base {

    public $widgetname = "";
    public $widgetitle = "";
    
	
    public function get_name() {
        return $this->widgetname;
    }
    
    public function get_title() {
        return $this->widgetitle;
    }

}
