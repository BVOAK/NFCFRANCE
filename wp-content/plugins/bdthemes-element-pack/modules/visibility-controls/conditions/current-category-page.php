<?php
	
	namespace ElementPack\Modules\VisibilityControls\Conditions;
	
	use ElementPack\Base\Condition;
	use Elementor\Controls_Manager;
	use ElementPack\Includes\Controls\SelectInput\Dynamic_Select;
	
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	
	class Current_Category_Page extends Condition {
		
		/**
		 * Get the name of condition
		 * @return string as per our condition control name
		 */
		public function get_name() {
			return 'current_category_page';
		}
		
		/**
		 * Get the title of condition
		 * @return string as per condition control title
		 */
		public function get_title() {
			return esc_html__( 'Current Category Page', 'bdthemes-element-pack' );
		}

		/**
		 * Get the group of condition
		 * @return string as per our condition control name
		 */
		public function get_group() {
			return 'woocommerce';
		}
		
		/* *
		 * Get the control value
		 * @return array as per condition control value
		 */
		public function get_control_value() {
			return [
				'label'       => esc_html__( 'Select', 'bdthemes-element-pack' ),
				'type'        => Controls_Manager::SELECT2,
				'default'     => '',
				'options' => element_pack_get_terms('product_cat'),
				'label_block' => true,
				'multiple'    => true,
			];
		}

		
		/**
		 * Check the condition
		 * @param string $relation Comparison operator for compare function
		 * @param mixed $val will check the control value as per condition needs
		 * @since 6.7.1
		 */
		public function check( $relation, $val ) {
			if ( ! $val ) {
				return;
			}

			$category = get_queried_object();

			$taxonomy = ! isset( $category->taxonomy ) || ! isset( $category->term_id ) || ( 'product_cat' !== $category->taxonomy && 'yith_product_brand' !== $category->taxonomy );

			if ( $taxonomy ) {
				return;
			}

			$category_id = $category->term_id;

			$show = false !== array_search( $category_id, $val ) ? true : false;
			
			return $this->compare( $show, true, $relation );
		}
	}
