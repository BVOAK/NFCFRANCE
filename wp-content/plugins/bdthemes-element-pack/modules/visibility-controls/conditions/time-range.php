<?php
	
	namespace ElementPack\Modules\VisibilityControls\Conditions;
	
	use DateTime;
	use ElementPack\Base\Condition;
	use Elementor\Controls_Manager;
	
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	
	class Time_Range extends Condition {
		
		/**
		 * Get the name of condition
		 * @return string as per our condition control name
		 */
		public function get_name() {
			return 'time_range';
		}
		
		/**
		 * Get the title of condition
		 * @return string as per condition control title
		 */
		public function get_title() {
			return esc_html__( 'Time Range', 'bdthemes-element-pack' );
		}

		/**
		 * Get the group of condition
		 * @return string as per our condition control name
		 */
		public function get_group() {
			return 'date_time';
		}
		
		/**
		 * Get the control value
		 * @return array as per condition control value
		 */
		public function get_control_value() {			
			return [
				'label'          => esc_html__( 'Start Time', 'bdthemes-element-pack' ),
				'type'           => Controls_Manager::DATE_TIME,
				'dynamic'     => ['active' => true],
				'picker_options' => [
					'noCalendar'   => true,
					'dateFormat' => "H:i",
				],
				'label_block'    => true,
			];
		}
		
	/**
	 * Check the condition
	 * @param string $relation Comparison operator for compare function
	 * @param mixed $val will check the control value as per condition needs
	 */
	public function check( $relation, $val, $custom_page_id =false, $extra = false, $addition_operator = false, $end_time = false ) {

			$start_time 	= date( 'H:i', strtotime( preg_replace('/\s+/', '', $val ) ) );
			$end_time 	= date( 'H:i', strtotime( preg_replace('/\s+/', '', $end_time ) ) );
			$now 	= date( 'H:i', strtotime("now") + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) );

			if ( DateTime::createFromFormat( 'H:i', $start_time ) === false  && DateTime::createFromFormat( 'H:i', $end_time ) === false ) // Make sure it's a valid DateTime format
				return;

			$start_time = strtotime( $start_time );
			$end_time = strtotime( $end_time );
			$now = strtotime( $now );
			
			// Check that user date is between start & end
			$show = ( ( $now >= $start_time && $now <= $end_time ) );
			
			return $this->compare( $show, true, $relation );
		}
	}
