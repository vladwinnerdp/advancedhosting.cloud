<?php

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Scheme_Color;

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
/*class First extends Widget_Base {
        public function get_name() {
            return "hello-world-test-{$i}";
        }
        
        public function get_title() {
            return __("Hello World1111", 'hello-world');
        }
    }*/

//class First extends Widget_Base { public function get_name() { return "hello-world-test-aaaa"; } }

for($i=0; $i<=0; $i++) {
    $ElementorClass = "First";
    $code = 'class '.$ElementorClass.' extends Widget_Base {
        public function get_name() {
            return "hello-world-test-aaaa";
        }
    }';
    eval($code);
    echo UniteProviderFunctionsUC::escCombinedHtml($code); //die();
    
}