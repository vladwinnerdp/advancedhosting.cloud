<?php

/**
 * @package Unlimited Elements
 * @author UniteCMS http://unitecms.net
 * @copyright Copyright (c) 2016 UniteCMS
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

//no direct accees
defined ('UNLIMITED_ELEMENTS_INC') or die ('restricted aceess');

class UniteCreatorElementorPagination{
	
	
	/**
	 * add content controls
	 */
	private function addElementorControls_content($widget){
    	
		$widget->start_controls_section(
                'section_pagination', array(
                'label' => esc_html__("Posts Pagination", "unlimited_elements"),
                    )
         );

		$widget->add_control(
			'pagination_heading',
			[
				'label' => __( 'When turned on, the pagination will appear in archive pages only', "unlimited_elements" ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'default' => ''
			]
		);
         
         
		$widget->add_control(
			'pagination_type',
			[
				'label' => __( 'Pagination', "unlimited_elements" ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					'' => __( 'None', "unlimited_elements" ),
					'numbers' => __( 'Numbers', "unlimited_elements" ),
					//'prev_next' => __( 'Previous/Next', "unlimited_elements" ),
					//'numbers_and_prev_next' => __( 'Numbers', "unlimited_elements" ) . ' + ' . __( 'Previous/Next', "unlimited_elements" ),
				],
			]
		);

		/*
		$widget->add_control(
			'pagination_page_limit',
			[
				'label' => __( 'Page Limit', "unlimited_elements" ),
				'default' => '5',
				'condition' => [
					'pagination_type!' => '',
				],
			]
		);

		$widget->add_control(
			'pagination_numbers_shorten',
			[
				'label' => __( 'Shorten', "unlimited_elements" ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'condition' => [
					'pagination_type' => [
						'numbers',
						'numbers_and_prev_next',
					],
				],
			]
		);

		$widget->add_control(
			'pagination_prev_label',
			[
				'label' => __( 'Previous Label', "unlimited_elements" ),
				'default' => __( '&laquo; Previous', "unlimited_elements" ),
				'condition' => [
					'pagination_type' => [
						'prev_next',
						'numbers_and_prev_next',
					],
				],
			]
		);

		$widget->add_control(
			'pagination_next_label',
			[
				'label' => __( 'Next Label', "unlimited_elements" ),
				'default' => __( 'Next &raquo;', "unlimited_elements" ),
				'condition' => [
					'pagination_type' => [
						'prev_next',
						'numbers_and_prev_next',
					],
				],
			]
		);
		

		$widget->add_control(
			'pagination_align',
			[
				'label' => __( 'Alignment', "unlimited_elements" ),
				'type' => \Elementor\Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => __( 'Left', "unlimited_elements" ),
						'icon' => 'fa fa-align-left',
					],
					'center' => [
						'title' => __( 'Center', "unlimited_elements" ),
						'icon' => 'fa fa-align-center',
					],
					'right' => [
						'title' => __( 'Right', "unlimited_elements" ),
						'icon' => 'fa fa-align-right',
					],
				],
				'default' => 'center',
				'selectors' => [
					'{{WRAPPER}} .uc-posts-pagination' => 'text-align: {{VALUE}};',
				],
				'condition' => [
					'pagination_type!' => '',
				],
			]
		);
		*/

                  
        $widget->end_controls_section();
	}
	
	
	/**
	 * add styles controls
	 */
	private function addElementorControls_styles($widget){
		
		$widget->start_controls_section(
			'section_pagination_style',
			[
				'label' => __( 'Pagination', "unlimited_elements" ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					'pagination_type!' => '',
				],
			]
		);

		$widget->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'pagination_typography',
				'selector' => '{{WRAPPER}} .uc-posts-pagination',
				'scheme' => \Elementor\Scheme_Typography::TYPOGRAPHY_2,
			]
		);

		$widget->add_control(
			'pagination_color_heading',
			[
				'label' => __( 'Colors', "unlimited_elements" ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$widget->start_controls_tabs( 'pagination_colors' );

		$widget->start_controls_tab(
			'pagination_color_normal',
			[
				'label' => __( 'Normal', "unlimited_elements" ),
			]
		);

		$widget->add_control(
			'pagination_color',
			[
				'label' => __( 'Color', "unlimited_elements" ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .uc-posts-pagination .page-numbers:not(.dots)' => 'color: {{VALUE}};',
				],
			]
		);

		$widget->end_controls_tab();

		$widget->start_controls_tab(
			'pagination_color_hover',
			[
				'label' => __( 'Hover', "unlimited_elements" ),
			]
		);

		$widget->add_control(
			'pagination_hover_color',
			[
				'label' => __( 'Color', "unlimited_elements" ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .uc-posts-pagination a.page-numbers:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$widget->end_controls_tab();

		$widget->start_controls_tab(
			'pagination_color_active',
			[
				'label' => __( 'Active', "unlimited_elements" ),
			]
		);

		$widget->add_control(
			'pagination_active_color',
			[
				'label' => __( 'Color', "unlimited_elements" ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .uc-posts-pagination .page-numbers.current' => 'color: {{VALUE}};',
				],
			]
		);

		$widget->end_controls_tab();

		$widget->end_controls_tabs();

		$widget->add_responsive_control(
			'pagination_spacing',
			[
				'label' => __( 'Space Between', "unlimited_elements" ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'separator' => 'before',
				'default' => [
					'size' => 10,
				],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
					'body:not(.rtl) {{WRAPPER}} .uc-posts-pagination .page-numbers:not(:first-child)' => 'margin-left: calc( {{SIZE}}{{UNIT}}/2 );',
					'body:not(.rtl) {{WRAPPER}} .uc-posts-pagination .page-numbers:not(:last-child)' => 'margin-right: calc( {{SIZE}}{{UNIT}}/2 );',
					'body.rtl {{WRAPPER}} .uc-posts-pagination .page-numbers:not(:first-child)' => 'margin-right: calc( {{SIZE}}{{UNIT}}/2 );',
					'body.rtl {{WRAPPER}} .uc-posts-pagination .page-numbers:not(:last-child)' => 'margin-left: calc( {{SIZE}}{{UNIT}}/2 );',
				],
			]
		);

		$widget->end_controls_section();
		
	}
	
	
	/**
	 * add elementor controls
	 */
	public function addElementorSectionControls($widget){
		
		$this->addElementorControls_content($widget);
		
		//$this->addElementorControls_styles($widget);
		
	}
	
	
	/**
	 * put pagination
	 */
	public function getHTMLPaginationByElementor($arrValues){
		
		$paginationType = UniteFunctionsUC::getVal($arrValues, "pagination_type");
		
		if(empty($paginationType))
			return(false);
		
		$options = array();
		$options["prev_next"] = false;
		 
		//$options["mid_size"] = 2;
		//$options["prev_text"] = __( 'Newer', "unlimited_elements");
		//$options["next_text"] = __( 'Older', "unlimited_elements");
		//$options["total"] = 10;
		//$options["current"] = 3;
		
		$pagination = get_the_posts_pagination($options);
				
		$html = "<div class='uc-posts-pagination'>$pagination</div>";
		
		return($html);
	}
	
}