<?php

namespace ElementPack\Modules\GiveReceipt\Widgets;

use ElementPack\Base\Module_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;


if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Give_Receipt extends Module_Base {

	public function get_name() {
		return 'bdt-give-receipt';
	}

	public function get_title() {
		return BDTEP . esc_html__('Give Receipt', 'bdthemes-element-pack');
	}

	public function get_icon() {
		return 'bdt-wi-give-receipt';
	}

	public function get_categories() {
		return ['element-pack'];
	}

	public function get_keywords() {
		return ['give', 'charity', 'donation', 'donor', 'history', 'wall', 'form', 'goal', 'receipt'];
	}

	public function get_style_depends() {
		if ($this->ep_is_edit_mode()) {
			return ['ep-styles'];
		} else {
			return ['ep-give-receipt'];
		}
	}

	public function get_custom_help_url() {
		return 'https://youtu.be/2xoXNi_Hx3k';
	}

	public function has_widget_inner_wrapper(): bool {
        return ! \Elementor\Plugin::$instance->experiments->is_feature_active( 'e_optimized_markup' );
    }
	protected function is_dynamic_content(): bool {
		return true;
	}
	
	protected function register_controls() {

		$this->start_controls_section(
			'content_section',
			[
				'label' => esc_html__('Donation Receipt', 'bdthemes-element-pack'),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'error',
			[
				'label' => esc_html__('Error Message', 'bdthemes-element-pack'),
				'type' => Controls_Manager::TEXTAREA,
				'default' => esc_html__('You are missing the donation id to view this donation receipt.', 'bdthemes-element-pack'),
				'placeholder' => esc_html__('You are missing the donation id to view this donation receipt.', 'bdthemes-element-pack'),
			]
		);

		$this->add_control(
			'success',
			[
				'label' => esc_html__('Success Message', 'bdthemes-element-pack'),
				'type' => Controls_Manager::TEXTAREA,
				'default' => esc_html__('Thank you for your donation.', 'bdthemes-element-pack'),
				'placeholder' => esc_html__('Thank you for your donation.', 'bdthemes-element-pack'),
			]
		);

		$this->add_control(
			'price',
			[
				'label' => esc_html__('Donation Total', 'bdthemes-element-pack'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes'
			]
		);

		$this->add_control(
			'donor',
			[
				'label' => esc_html__('Donor', 'bdthemes-element-pack'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes'
			]
		);

		$this->add_control(
			'date',
			[
				'label' => esc_html__('Date', 'bdthemes-element-pack'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes'
			]
		);

		$this->add_control(
			'method',
			[
				'label' => esc_html__('Payment Method', 'bdthemes-element-pack'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes'
			]
		);

		$this->add_control(
			'payment_id',
			[
				'label' => esc_html__('Payment ID', 'bdthemes-element-pack'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes'
			]
		);

		$this->add_control(
			'status',
			[
				'label' => esc_html__('Status', 'bdthemes-element-pack'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes'
			]
		);

		$this->add_control(
			'company',
			[
				'label' => esc_html__('Company Name', 'bdthemes-element-pack'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes'
			]
		);

		$this->add_control(
			'status_notice',
			[
				'label' => esc_html__('Payment Status Notice', 'bdthemes-element-pack'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes'
			]
		);

		$this->end_controls_section();

		//Style
		//Style
		$this->start_controls_section(
			'section_style_header',
			[
				'label' => esc_html__('Header', 'bdthemes-element-pack'),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'header_background',
			[
				'label'     => esc_html__('Background', 'bdthemes-element-pack'),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#e7ebef',
				'selectors' => [
					'{{WRAPPER}} .bdt-give-receipt .give-table th' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'header_color',
			[
				'label'     => esc_html__('Text Color', 'bdthemes-element-pack'),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#333',
				'selectors' => [
					'{{WRAPPER}} .bdt-give-receipt .give-table th' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'header_border_style',
			[
				'label'     => esc_html__('Border Style', 'bdthemes-element-pack'),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'solid',
				'options'   => [
					'none'   => esc_html__('None', 'bdthemes-element-pack'),
					'solid'  => esc_html__('Solid', 'bdthemes-element-pack'),
					'double' => esc_html__('Double', 'bdthemes-element-pack'),
					'dotted' => esc_html__('Dotted', 'bdthemes-element-pack'),
					'dashed' => esc_html__('Dashed', 'bdthemes-element-pack'),
					'groove' => esc_html__('Groove', 'bdthemes-element-pack'),
				],
				'selectors' => [
					'{{WRAPPER}} .bdt-give-receipt .give-table th' => 'border-style: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'header_border_width',
			[
				'label'     => esc_html__('Border Width', 'bdthemes-element-pack'),
				'type'      => Controls_Manager::SLIDER,
				'default'   => [
					'size' => 1,
				],
				'range'     => [
					'px' => [
						'min' => 0,
						'max' => 20,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .bdt-give-receipt .give-table th' => 'border-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'header_border_color',
			[
				'label'     => esc_html__('Border Color', 'bdthemes-element-pack'),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ccc',
				'selectors' => [
					'{{WRAPPER}} .bdt-give-receipt .give-table th' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'header_padding',
			[
				'label'      => esc_html__('Padding', 'bdthemes-element-pack'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'em', '%'],
				'default'    => [
					'top'    => 1,
					'bottom' => 1,
					'left'   => 1,
					'right'  => 2,
					'unit'   => 'em'
				],
				'selectors'  => [
					'{{WRAPPER}} .bdt-give-receipt .give-table th' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'header_text_typography',
				'selector' => '{{WRAPPER}} .bdt-give-receipt .give-table th',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_body',
			[
				'label' => esc_html__('Body', 'bdthemes-element-pack'),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'cell_border_style',
			[
				'label'     => esc_html__('Border Style', 'bdthemes-element-pack'),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'solid',
				'options'   => [
					'none'   => esc_html__('None', 'bdthemes-element-pack'),
					'solid'  => esc_html__('Solid', 'bdthemes-element-pack'),
					'double' => esc_html__('Double', 'bdthemes-element-pack'),
					'dotted' => esc_html__('Dotted', 'bdthemes-element-pack'),
					'dashed' => esc_html__('Dashed', 'bdthemes-element-pack'),
					'groove' => esc_html__('Groove', 'bdthemes-element-pack'),
				],
				'selectors' => [
					'{{WRAPPER}} .bdt-give-receipt .give-table td' => 'border-style: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'cell_border_width',
			[
				'label'     => esc_html__('Border Width', 'bdthemes-element-pack'),
				'type'      => Controls_Manager::SLIDER,
				'default'   => [
					'size' => 1,
				],
				'range'     => [
					'px' => [
						'min' => 0,
						'max' => 20,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .bdt-give-receipt .give-table td' => 'border-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'cell_padding',
			[
				'label'      => esc_html__('Cell Padding', 'bdthemes-element-pack'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'em', '%'],
				'default'    => [
					'top'    => 1,
					'bottom' => 1,
					'left'   => 2,
					'right'  => 2,
					'unit'   => 'em'
				],
				'selectors'  => [
					'{{WRAPPER}} .bdt-give-receipt .give-table td' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'body_text_typography',
				'selector' => '{{WRAPPER}} .bdt-give-receipt .give-table td',
			]
		);

		$this->start_controls_tabs('tabs_body_style');

		$this->start_controls_tab(
			'tab_normal',
			[
				'label' => esc_html__('Normal', 'bdthemes-element-pack'),
			]
		);

		$this->add_control(
			'normal_background',
			[
				'label'     => esc_html__('Background', 'bdthemes-element-pack'),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#fff',
				'selectors' => [
					'{{WRAPPER}} .bdt-give-receipt .give-table td' => 'background-color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'normal_color',
			[
				'label'     => esc_html__('Text Color', 'bdthemes-element-pack'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-give-receipt .give-table td' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'normal_border_color',
			[
				'label'     => esc_html__('Border Color', 'bdthemes-element-pack'),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ccc',
				'selectors' => [
					'{{WRAPPER}} .bdt-give-receipt .give-table td' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_hover',
			[
				'label' => esc_html__('Hover', 'bdthemes-element-pack'),
			]
		);

		$this->add_control(
			'row_hover_background',
			[
				'label'     => esc_html__('Background', 'bdthemes-element-pack'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-give-receipt .give-table tr:hover td' => 'background-color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'row_hover_text_color',
			[
				'label'     => esc_html__('Text Color', 'bdthemes-element-pack'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-give-receipt .give-table tr:hover td' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_stripe',
			[
				'label'     => esc_html__('Stripe', 'bdthemes-element-pack'),
			]
		);

		$this->add_control(
			'stripe_background',
			[
				'label'     => esc_html__('Background', 'bdthemes-element-pack'),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#f5f5f5',
				'selectors' => [
					'{{WRAPPER}} .bdt-give-receipt .give-table tr:nth-child(even) td' => 'background-color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'stripe_color',
			[
				'label'     => esc_html__('Text Color', 'bdthemes-element-pack'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-give-receipt .give-table tr:nth-child(even) td' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}


	private function render_editor_content() {
		$settings = $this->get_settings_for_display();

		$success = esc_html($settings['success']);

		// Adding PDF Receipt row, and Subscription table
		$pdfreceipts = (class_exists('Give_PDF_Receipts')) ? "true" : "false";
		$recurring = (class_exists('Give_Recurring')) ? "true" : "false";

?>
		<div id="give-receipt">
			<div class="give_notices give_errors" id="give_error_fail">
				<p class="give_notice give_error">
					<?php echo (!empty($error) ? wp_kses_post($error ): esc_html__('You are missing the donation ID to view this donation receipt.', 'bdthemes-element-pack')); ?>
				</p>
			</div>
			<?php if ('yes' == $settings['status_notice']) : ?>
				<div class="give_notices give_errors" id="give_error_success">
					<p class="give_notice give_success">
						<?php echo (!empty($success) ? wp_kses_post($success) : esc_html__('Thank you for your donation.', 'bdthemes-element-pack')); ?>
					</p>
				</div>
			<?php endif; ?>
			<table id="give_donation_receipt" class="give-table">
				<thead>
					<tr>
						<th scope="colgroup" colspan="2">
							<span class="give-receipt-thead-text"><?php esc_html_e('Donation Receipt', 'bdthemes-element-pack'); ?></span>
						</th>
					</tr>
				</thead>

				<tbody>
					<?php
					if ('yes' == $settings['donor']) : ?>
						<tr>
							<td scope="row"><strong><?php esc_html_e('Donor', 'bdthemes-element-pack'); ?></strong></td>
							<td><?php esc_html_e('Test Donor', 'bdthemes-element-pack'); ?></td>
						</tr>
					<?php endif;
					if ('yes' == $settings['company']) : ?>
						<tr>
							<td scope="row"><strong><?php esc_html_e('Company Name', 'bdthemes-element-pack'); ?></strong></td>
							<td>Impress.org</td>
						</tr>
					<?php endif;
					if ('yes' == $settings['date']) : ?>
						<tr>
							<td scope="row"><strong>Date</strong></td>
							<td>April 18, 2020</td>
						</tr>
					<?php endif;
					if ('yes' == $settings['price']) : ?>
						<tr>
							<td scope="row"><strong><?php esc_html_e('Total Donation', 'bdthemes-element-pack'); ?></strong></td>
							<td>$25.00</td>
						</tr>
					<?php endif; ?>
					<tr>
						<td scope="row"><strong><?php esc_html_e('Donation', 'bdthemes-element-pack'); ?></strong></td>
						<td><?php esc_html_e('First Form', 'bdthemes-element-pack'); ?><span class="donation-level-text-wrap"></span></td>
					</tr>
					<?php
					if ('yes' == $settings['status']) : ?>
						<tr>
							<td scope="row"><strong><?php esc_html_e('Donation Status', 'bdthemes-element-pack'); ?></strong></td>
							<td><?php esc_html_e('Complete', 'bdthemes-element-pack'); ?></td>
						</tr>
					<?php endif;
					if ('yes' == $settings['payment_id']) : ?>
						<tr>
							<td scope="row"><strong><?php esc_html_e('Donation ID', 'bdthemes-element-pack'); ?></strong></td>
							<td>3</td>
						</tr>
					<?php endif;
					if ('yes' == $settings['method']) : ?>
						<tr>
							<td scope="row"><strong><?php esc_html_e('Payment Method', 'bdthemes-element-pack'); ?></strong></td>
							<td><?php esc_html_e('Test Donation', 'bdthemes-element-pack'); ?></td>
						</tr>
					<?php endif;
					if ('true' == $pdfreceipts) : ?>
						<tr>
							<td><strong><?php esc_html_e('Receipt', 'bdthemes-element-pack'); ?>:</strong></td>
							<td><a class="give_receipt_link" title="Download Receipt" href="#"><?php esc_html_e('Download Receipt', 'bdthemes-element-pack'); ?> »</a></td>
						</tr>
					<?php endif; ?>
				</tbody>
			</table>

			<?php if ('true' == $recurring) : ?>
				<table id="give-subscription-receipt" class="give-table">

					<thead>
						<tr>
							<th scope="colgroup" colspan="2">
								<span class="give-receipt-thead-text"><?php esc_html_e('Subscription Details', 'bdthemes-element-pack'); ?></span>
							</th>
						</tr>
					</thead>

					<tbody>

						<tr>
							<td scope="row"><strong><?php esc_html_e('Subscription:', 'bdthemes-element-pack'); ?></strong></td>
							<td>
								<span class="give-subscription-billing-cycle">$25.00 / <?php esc_html_e('Monthly', 'bdthemes-element-pack'); ?></span>
							</td>
						</tr>
						<tr>
							<td scope="row"><strong><?php esc_html_e('Status:', 'bdthemes-element-pack'); ?></strong></td>
							<td>
								<span class="give-subscription-status"><span class="give-donation-status status-active"><span class="give-donation-status-icon"></span> <?php esc_html_e('Active', 'bdthemes-element-pack'); ?></span></span>
							</td>
						</tr>
						<tr>
							<td scope="row"><strong><?php esc_html_e('Renewal Date:', 'bdthemes-element-pack'); ?></strong></td>
							<td><span class="give-subscription-renewal-date"><?php esc_html_e('June 4, 2020', 'bdthemes-element-pack'); ?></span></td>
						</tr>
						<tr>
							<td scope="row"><strong><?php esc_html_e('Progress:', 'bdthemes-element-pack'); ?></strong></td>
							<td><span class="give-subscription-times-billed">1 / <?php esc_html_e('Ongoing', 'bdthemes-element-pack'); ?></span>
							</td>
						</tr>

					</tbody>
				</table>
				<a href="#" class="give-recurring-manage-subscriptions-receipt-link"><?php esc_html_e('Manage Subscriptions', 'bdthemes-element-pack'); ?> »</a>
			<?php endif; ?>
		</div>
	<?php
	}

	private function get_shortcode() {
		$settings = $this->get_settings_for_display();

		$attributes = [
			'error'          => esc_html($settings['error']),
			'price'          => $settings['price'],
			'donor'          => $settings['donor'],
			'date'           => $settings['date'],
			'payment_method' => $settings['method'],
			'payment_id'     => $settings['payment_id'],
			'payment_status' => $settings['status'],
			'company_name'   => $settings['company'],
			'status_notice'  => $settings['status_notice'],
		];

		$this->add_render_attribute('shortcode', $attributes);

		$shortcode   = [];
		$shortcode[] = sprintf('[give_receipt %s]', $this->get_render_attribute_string('shortcode'));

		return implode("", $shortcode);
	}

	public function render() {

		$this->add_render_attribute('give_wrapper', 'class', 'bdt-give-receipt give_receipt');

	?>

		<div <?php $this->print_render_attribute_string('give_wrapper'); ?>>

			<?php if (!\Elementor\Plugin::$instance->editor->is_edit_mode()) { ?>
				<?php echo do_shortcode($this->get_shortcode()); ?>
			<?php } else { ?>
				<?php $this->render_editor_content(); ?>
			<?php } ?>

		</div>

<?php
	}

	public function render_plain_content() {
		echo wp_kses_post($this->get_shortcode());
	}
}
