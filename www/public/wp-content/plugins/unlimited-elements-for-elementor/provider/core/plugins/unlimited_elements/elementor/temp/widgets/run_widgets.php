<?php

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Scheme_Color;

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly


$UniteCreatorAddons = new UniteCreatorAddons();
$arrAddons = $UniteCreatorAddons->getArrAddons();
foreach ($arrAddons as $addon) {
    $code = 'class ' . $addon->getName() . ' extends WidgetBaseAL{
            public $widgetname="' . $addon->getName() . '";
            public $widgetitle="' . $addon->getTitle() . '";
    }';
    eval($code);
    eval('$widget = new ' . $addon->getName() . '();');
}

