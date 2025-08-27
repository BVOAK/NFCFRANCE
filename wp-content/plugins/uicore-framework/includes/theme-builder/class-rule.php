<?php
namespace UiCore\ThemeBuilder;
defined('ABSPATH') || exit();

/**
 * Theme Builder generic functions
 *
 * @author Andrei Voica <andrei@uicore.co
 * @since 2.0.0
 */
class Rule {



	private static $instance;

	private static $meta_option;

	private static $current_page_type = null;

	private static $current_page_data = array();

	private static $user_selection;

	private static $location_selection;

	/**
	 * Initiator
	 *
	 * @since  1.0.0
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'admin_action_edit', array( $this, 'initialize_options' ) );
	}

	/**
	 * Initialize member variables.
	 *
	 * @return void
	 */
	public function initialize_options() {
		self::$location_selection = Common::get_location_selections();
	}

	/**
	 * Checks for the display condition for the current page/
	 *
	 * @param  int   $post_id Current post ID.
	 * @param  array $rules   Array of rules Display on | Exclude on.
	 *
	 * @return boolean      Returns true or false depending on if the $rules match for the current page and the layout is to be displayed.
	 */
	public function parse_layout_display_condition( $post_id, $rules ) {
		$display           = false;
		$current_post_type = get_post_type( $post_id );


		if (is_array( $rules) && ! empty( $rules) ) {
			foreach ( $rules as $key => $rule ) {

				// If the rule is empty, skip it.
				if( !isset($rule['rule']['value']) ){
					continue;
				}


				if ( strrpos( $rule['rule']['value'], 'all' ) !== false ) {
					$rule_case = 'all';
				} else {
					$rule_case =  $rule['rule']['value'];
				}

				switch ( $rule_case ) {
					case 'basic-global':
						$display = true;
						break;

					case 'basic-page':
						if ( is_page() ) {
							$display = true;
						}
						break;

					case 'basic-single':
						if ( is_single() ) {
							$display = true;
						}
						break;

					case 'basic-archives':
						if ( is_archive() ) {
							$display = true;
						}
						break;

					case 'special-404':
						if ( is_404() ) {
							$display = true;
						}
						break;

					case 'special-search':
						if ( is_search() ) {
							$display = true;
						}
						break;

					case 'special-blog':
						if ( is_home() ) {
							$display = true;
						}
						break;

					case 'special-front':
						if ( is_front_page() ) {
							$display = true;
						}
						break;

					case 'special-date':
						if ( is_date() ) {
							$display = true;
						}
						break;

					case 'special-author':
						if ( is_author() ) {
							$display = true;
						}
						break;

					case 'special-woo-shop':
						if ( function_exists( 'is_shop' ) && is_shop() ) {
							$display = true;
						}
						break;

					case 'all':
						$rule_data = explode( '|', $rule );

						$post_type     = isset( $rule_data[0] ) ? $rule_data[0] : false;
						$archieve_type = isset( $rule_data[2] ) ? $rule_data[2] : false;
						$taxonomy      = isset( $rule_data[3] ) ? $rule_data[3] : false;
						if ( false === $archieve_type ) {
							$current_post_type = get_post_type( $post_id );

							if ( false !== $post_id && $current_post_type == $post_type ) {
								$display = true;
							}
						} else {
							if ( is_archive() ) {
								$current_post_type = get_post_type();
								if ( $current_post_type == $post_type ) {
									if ( 'archive' == $archieve_type ) {
										$display = true;
									} elseif ( 'taxarchive' == $archieve_type ) {
										$obj              = get_queried_object();
										$current_taxonomy = '';
										if ( '' !== $obj && null !== $obj ) {
											$current_taxonomy = $obj->taxonomy;
										}

										if ( $current_taxonomy == $taxonomy ) {
											$display = true;
										}
									}
								}
							}
						}
						break;

					case 'specifics':
						if ( isset( $rule['specific'] ) && is_array( $rule['specific'] ) ) {
							foreach ( $rule['specific'] as $specific_page ) {
								$specific_page = isset($specific_page['id']) ? $specific_page['id'] : $specific_page;
								$specific_data = explode( '-', $specific_page );

								$specific_post_type = isset( $specific_data[0] ) ? $specific_data[0] : false;
								$specific_post_id   = isset( $specific_data[1] ) ? $specific_data[1] : false;
								if ( 'post' == $specific_post_type ) {
									if ( $specific_post_id == $post_id ) {
										$display = true;
									}
								} elseif ( isset( $specific_data[2] ) && ( 'single' == $specific_data[2] ) && 'tax' == $specific_post_type ) {
									if ( is_singular() ) {
										$term_details = get_term( $specific_post_id );

										if ( isset( $term_details->taxonomy ) ) {
											$has_term = has_term( (int) $specific_post_id, $term_details->taxonomy, $post_id );

											if ( $has_term ) {
												$display = true;
											}
										}
									}
								} elseif ( 'tax' == $specific_post_type ) {
									$tax_id = get_queried_object_id();
									if ( $specific_post_id == $tax_id ) {
										$display = true;
									}
								}
							}
						}
						break;

					default:
						
						if( ($type = substr( $rule_case, 0, 11 )) === "cp-archive-" ){
							if ( is_post_type_archive(str_replace($type,'',$rule_case)) ) {
								$display = true;
							}
							break;
						}elseif( ($type = substr( $rule_case, 0,3 )) === "cp-" ){
							if ( is_singular(str_replace($type,'',$rule_case)) ) {
								$display = true;
							}
						}
						break;
				}

				if ( $display ) {
					break;
				}
			}
		}

		return $display;
	}

	/**
	 * Get current page type
	 *
	 * @since  1.0.0
	 *
	 * @return string Page Type.
	 */
	public function get_current_page_type() {
		if ( null === self::$current_page_type ) {
			$page_type  = '';
			$current_id = false;

			if ( is_404() ) {
				$page_type = 'is_404';
			} elseif ( is_search() ) {
				$page_type = 'is_search';
			} elseif ( is_archive() ) {
				$page_type = 'is_archive';

				if(is_tax('portfolio_category') ) {
					$page_type = 'is_portfolio_category';
				} elseif ( is_category() || is_tag() || is_tax() ) {
					$page_type = 'is_tax';
				} elseif ( is_date() ) {
					$page_type = 'is_date';
				} elseif ( is_author() ) {
					$page_type = 'is_author';
				} elseif ( function_exists( 'is_shop' ) && is_shop() ) {
					$page_type = 'is_woo_shop_page';
				} 
			} elseif ( is_home() ) {
				$page_type = 'is_home';
			} elseif ( is_front_page() ) {
				$page_type  = 'is_front_page';
				$current_id = get_the_id();
			} elseif ( is_page() ) {
				$page_type  = 'is_page';
				$current_id = get_the_id();
			}elseif ( is_singular('post') ) {
				$page_type  = 'is_single';
				$current_id = get_the_id();
			}elseif ( is_singular() ) {
				$page_type  = 'is_singular';
				$current_id = get_the_id();
			} else {
				$current_id = get_the_id();
			}

			self::$current_page_data['ID'] = $current_id;
			self::$current_page_type       = $page_type;
		}
		// print_r(self::$current_page_type);
		return self::$current_page_type;
	}

	/**
	 * Get posts by conditions
	 *
	 * @since  1.0.0
	 * @param  string $post_type Post Type.
	 * @param  array  $option meta option name.
	 *
	 * @return object  Posts.
	 */
	public function get_posts_by_conditions() {
		global $wpdb;
		global $post;

		$option = [
            'include'=>'tb_rule_include',
            'exclude' =>'tb_rule_exclude',
        ];

		$post_type = 'uicore-tb';

		if ( is_array( self::$current_page_data ) && isset( self::$current_page_data[ $post_type ] ) ) {
			return self::$current_page_data[ $post_type ];
		}

		$current_page_type = $this->get_current_page_type();
		self::$current_page_data[ $post_type ] = array();

		$option['current_post_id'] = self::$current_page_data['ID'];
		$current_post_type = esc_sql( get_post_type() );
		$current_post_id   = false;
		$q_obj             = get_queried_object();

		$include = isset( $option['include'] ) ? esc_sql( $option['include'] ) : '';

		$query = "SELECT p.ID, pm.meta_value FROM {$wpdb->postmeta} as pm
					INNER JOIN {$wpdb->posts} as p ON pm.post_id = p.ID
					WHERE pm.meta_key = '{$include}'
					AND p.post_type = '{$post_type}'
					AND p.post_status = 'publish'";

		$orderby = ' ORDER BY p.post_date DESC';

		
		/* Entire Website */
		$meta_args = "pm.meta_value LIKE '%\"basic-global\"%'";

		switch ( $current_page_type ) {
			case 'is_404':
				$meta_args .= " OR pm.meta_value LIKE '%\"special-404\"%'";
				break;
			case 'is_search':
				$meta_args .= " OR pm.meta_value LIKE '%\"special-search\"%'";
				break;
			case 'is_portfolio_category':
				$meta_args .= " OR pm.meta_value LIKE '%\"cp-archive-portfolio_category\"%'";
				$meta_args .= " OR pm.meta_value LIKE '%\"basic-portfolio\"%'";
				$meta_args .= " OR pm.meta_value LIKE '%\"cp-archive-portfolio\"%'";
				if ( is_object( $q_obj ) ) {
					$meta_args .= " OR pm.meta_value LIKE '%\"cp-archive-{$current_post_type}|all|taxarchive|{$q_obj->taxonomy}\"%'";
					$meta_args .= " OR pm.meta_value LIKE '%\"tax-{$q_obj->term_id}\"%'";
				}
				break;
			case 'is_archive':
			case 'is_tax':
			case 'is_date':
			case 'is_author':
				$meta_args .= " OR pm.meta_value LIKE '%\"basic-archives\"%'";
				$meta_args .= " OR pm.meta_value LIKE '%\"{$current_post_type}|all|archive\"%'";
				$meta_args .= " OR pm.meta_value LIKE '%\"cp-archive-{$current_post_type}\"%'";

				if ( 'is_tax' == $current_page_type && ( is_category() || is_tag() || is_tax() ) ) {
					if ( is_object( $q_obj ) ) {
						$meta_args .= " OR pm.meta_value LIKE '%\"cp-archive-{$current_post_type}|all|taxarchive|{$q_obj->taxonomy}\"%'";
						$meta_args .= " OR pm.meta_value LIKE '%\"tax-{$q_obj->term_id}\"%'";
					}
				} elseif ( 'is_date' == $current_page_type ) {
					$meta_args .= " OR pm.meta_value LIKE '%\"special-date\"%'";
				} elseif ( 'is_author' == $current_page_type ) {
					$meta_args .= " OR pm.meta_value LIKE '%\"special-author\"%'";
				}
				break;
			case 'is_home':
				$meta_args .= " OR pm.meta_value LIKE '%\"special-blog\"%'";
				break;
			case 'is_front_page':
				$current_id      = esc_sql( get_the_id() );
				$current_post_id = $current_id;
				$meta_args      .= " OR pm.meta_value LIKE '%\"special-front\"%'";
				$meta_args      .= " OR pm.meta_value LIKE '%\"{$current_post_type}|all\"%'";
				$meta_args      .= " OR pm.meta_value LIKE '%\"post-{$current_id}\"%'";
				break;
			case 'is_page':
				$current_id      = esc_sql( get_the_id() );
				$current_post_id = $current_id;
				$meta_args      .= " OR pm.meta_value LIKE '%\"basic-page\"%'";
				$meta_args      .= " OR pm.meta_value LIKE '%\"{$current_post_type}|all\"%'";
				$meta_args      .= " OR pm.meta_value LIKE '%\"post-{$current_id}\"%'";
				break;
			case 'is_single':
				$current_id      = esc_sql( get_the_id() );
				$current_post_id = $current_id;
				$meta_args      .= " OR pm.meta_value LIKE '%\"basic-single\"%'";
				$meta_args      .= " OR pm.meta_value LIKE '%\"cp-{$current_post_type}|all\"%'";
				$meta_args      .= " OR pm.meta_value LIKE '%\"post-{$current_id}\"%'";

				$taxonomies = get_object_taxonomies( $q_obj->post_type );
				$terms      = wp_get_post_terms( $q_obj->ID, $taxonomies );

				foreach ( $terms as $key => $term ) {
					$meta_args .= " OR pm.meta_value LIKE '%\"tax-{$term->term_id}-single-{$term->taxonomy}\"%'";
				}
				

				break;
			case 'is_singular':
				$current_id      = esc_sql( get_the_id() );
				$current_post_id = $current_id;
				$meta_args      .= " OR pm.meta_value LIKE '%\"all\"%'";
				$meta_args      .= " OR pm.meta_value LIKE '%\"cp-{$current_post_type}\"%'";
				$meta_args      .= " OR pm.meta_value LIKE '%\"post-{$current_id}\"%'";


				$taxonomies = get_object_taxonomies( $q_obj->post_type );
				//terms and terms parents (tested with 2 levels on products)
				$terms      = wp_get_post_terms( $q_obj->ID, $taxonomies );
				$terms_parents = array();
				foreach ( $terms as $key => $term ) {
					if($term->parent != 0){
						$terms_parents[] = $term->parent;
					}
					$meta_args .= " OR pm.meta_value LIKE '%\"tax-{$term->term_id}-single-{$term->taxonomy}\"%'";
				}
				$terms_parents_2 = array();
				foreach ( $terms_parents as $key => $term_parent ) {
					$term = get_term($term_parent);
					if($term->parent != 0){
						$terms_parents_2[] = $term->parent;
					}
					$meta_args .= " OR pm.meta_value LIKE '%\"tax-{$term_parent}-single-{$term->taxonomy}\"%'";
				}
				foreach($terms_parents_2 as $key => $term_parent_2){
					$meta_args .= " OR pm.meta_value LIKE '%\"tax-{$term_parent_2}-single-{$term->taxonomy}\"%'";
				}

				break;
			case 'is_portfolio':
				$current_id      = esc_sql( get_the_id() );
				$current_post_id = $current_id;
				$meta_args      .= " OR pm.meta_value LIKE '%\"basic-portfolio\"%'";
				$meta_args      .= " OR pm.meta_value LIKE '%\"{$current_post_type}|all\"%'";
				$meta_args      .= " OR pm.meta_value LIKE '%\"post-{$current_id}\"%'";

				$taxonomies = get_object_taxonomies( $q_obj->post_type );
				$terms      = wp_get_post_terms( $q_obj->ID, $taxonomies );

				foreach ( $terms as $key => $term ) {
					$meta_args .= " OR pm.meta_value LIKE '%\"tax-{$term->term_id}\"%'";
				}
				if ( 'is_tax' == $current_page_type && ( is_category() || is_tag() || is_tax() ) ) {
					if ( is_object( $q_obj ) ) {
						$meta_args .= " OR pm.meta_value LIKE '%\"cp-archive-{$current_post_type}|all|taxarchive|{$q_obj->taxonomy}\"%'";
						$meta_args .= " OR pm.meta_value LIKE '%\"tax-{$q_obj->term_id}\"%'";
					}
				} elseif ( 'is_date' == $current_page_type ) {
					$meta_args .= " OR pm.meta_value LIKE '%\"special-date\"%'";
				} elseif ( 'is_author' == $current_page_type ) {
					$meta_args .= " OR pm.meta_value LIKE '%\"special-author\"%'";
				}

				break;
			case 'is_woo_shop_page':
				$meta_args .= " OR pm.meta_value LIKE '%\"special-woo-shop\"%'";
				break;
			case '':
				$current_post_id = get_the_id();
				break;
		}
		// error_log($current_page_type);
		// error_log($meta_args);
		// Ignore the PHPCS warning about constant declaration.
		// @codingStandardsIgnoreStart
		$posts  = $wpdb->get_results( $query . ' AND (' . $meta_args . ') ' . $orderby );

		// @codingStandardsIgnoreEnd
		foreach ( $posts as $local_post ) {
			self::$current_page_data[ $post_type ][ Common::get_the_type($local_post->ID) ][] = $local_post->ID;
		}

		$option['current_post_id'] = $current_post_id;
		$this->remove_exclusion_rule_posts( $post_type, $option );

		return self::$current_page_data[ $post_type ];
	}

	/**
	 * Remove exclusion rule posts.
	 *
	 * @since  1.0.0
	 * @param  string $post_type Post Type.
	 * @param  array  $option meta option name.
	 */
	public function remove_exclusion_rule_posts( $post_type, $option ) {
		$exclusion       = isset( $option['exclude'] ) ? $option['exclude'] : '';
		$current_post_id = isset( $option['current_post_id'] ) ? $option['current_post_id'] : false;

		foreach ( self::$current_page_data[ $post_type ] as $c_type => $ids ) {

			foreach($ids as $index=>$id){
				$exclusion_rules = get_post_meta( $id, $exclusion, true );
				$is_exclude      = $this->parse_layout_display_condition( $current_post_id, $exclusion_rules );
	
				if ( $is_exclude ) {
					unset( self::$current_page_data[ $post_type ][ $c_type ][ $index ] );
				}
			}
			
		}
	}

}
Rule::get_instance();
//DO LIKE THIS FOR ALL!!!
