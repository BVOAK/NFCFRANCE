<?php
namespace ElementPack\Modules\PortfolioGallery\Skins;

use Elementor\Skin_Base as Elementor_Skin_Base;
use Elementor\Utils;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Skin_Trosia extends Elementor_Skin_Base {
	public function get_id() {
		return 'bdt-trosia';
	}

	public function get_title() {
		return __( 'Trosia', 'bdthemes-element-pack' );
	}

	public function render_post() {
		$settings = $this->parent->get_settings_for_display();
		global $post;

		$element_key = 'portfolio-item-' . $post->ID;

		if ($settings['tilt_show']) {
			$this->parent->add_render_attribute('portfolio-item-inner', 'data-tilt', '', true);
			if ($settings['tilt_scale']) {
				$this->parent->add_render_attribute('portfolio-item-inner', 'data-tilt-scale', '1.2', true);
			}
		}

		$this->parent->add_render_attribute('portfolio-item-inner', 'class', 'bdt-portfolio-inner', true);

		$this->parent->add_render_attribute('portfolio-item', 'class', 'bdt-gallery-item bdt-transition-toggle', true);

		if ( 'yes' === $settings['show_filter_bar'] ) {
			$this->parent->add_render_attribute( $element_key, 'data-filter', $this->parent->filter_menu_terms(), true );
		}

		?>
		<div <?php $this->parent->print_render_attribute_string( $element_key ); ?>>
			<div <?php $this->parent->print_render_attribute_string( 'portfolio-item' ); ?>>
				<div <?php $this->parent->print_render_attribute_string( 'portfolio-item-inner' ); ?>>
					<?php
						$this->parent->render_thumbnail();
						$this->parent->render_overlay();
					?>
					<div class="bdt-portfolio-desc bdt-position-z-index bdt-position-bottom">
						<?php
						$this->parent->render_title(); 
						$this->parent->render_excerpt();
						?>
					</div>
					<div>
						<?php $this->parent->render_categories_names(); ?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	public function render() {
		$settings = $this->parent->get_settings_for_display();
		
		$this->parent->query_posts( $settings['posts_per_page'] );

		$wp_query = $this->parent->get_query();

		if ( ! $wp_query->found_posts ) {
			return;
		}

		$this->parent->render_header('trosia');

		while ( $wp_query->have_posts() ) {
			$wp_query->the_post();

			$this->render_post();
		}

		$this->parent->render_footer();

		if ($settings['show_pagination']) {
			element_pack_post_pagination($wp_query);
		}
		
		wp_reset_postdata();

	}
}

