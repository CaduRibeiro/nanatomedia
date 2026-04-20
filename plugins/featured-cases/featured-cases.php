<?php

/**
 * Plugin Name: Featured Cases
 * Description: Registers the Featured Case custom post type and related meta fields.
 * Version: 1.0.0
 * Author: Carlos Ribeiro
 * Text Domain: fc-featured-cases
 */

if (! defined('ABSPATH')) {
	exit;
}

define('FC_FEATURED_CASE_POST_TYPE', 'featured_case');
define('FC_CASE_TYPE_TAXONOMY', 'fc_case_type');
define('FC_META_CASE_TYPE', '_fc_case_type');
define('FC_META_SETTLEMENT_AMOUNT', '_fc_settlement_amount');
define('FC_META_BOX_NONCE_ACTION', 'fc_featured_case_meta_box_action');
define('FC_META_BOX_NONCE_NAME', 'fc_featured_case_meta_box_nonce');

/**
 * Determines whether the current Featured Case request is missing required meta fields.
 *
 * @return bool
 */
function fc_featured_case_has_missing_required_fields()
{
	$case_type = isset($_POST['fc_case_type']) ? absint(wp_unslash($_POST['fc_case_type'])) : 0;
	$amount    = isset($_POST['fc_settlement_amount']) ? fc_sanitize_settlement_amount(wp_unslash($_POST['fc_settlement_amount'])) : '';

	return 0 === $case_type || '' === $amount;
}

/**
 * Registers the Featured Case custom post type.
 *
 * @return void
 */
function fc_register_featured_case_post_type()
{
	$labels = array(
		'name'                  => __('Featured Cases', 'fc-featured-cases'),
		'singular_name'         => __('Featured Case', 'fc-featured-cases'),
		'menu_name'             => __('Featured Cases', 'fc-featured-cases'),
		'name_admin_bar'        => __('Featured Case', 'fc-featured-cases'),
		'add_new'               => __('Add New', 'fc-featured-cases'),
		'add_new_item'          => __('Add New Featured Case', 'fc-featured-cases'),
		'new_item'              => __('New Featured Case', 'fc-featured-cases'),
		'edit_item'             => __('Edit Featured Case', 'fc-featured-cases'),
		'view_item'             => __('View Featured Case', 'fc-featured-cases'),
		'all_items'             => __('All Featured Cases', 'fc-featured-cases'),
		'search_items'          => __('Search Featured Cases', 'fc-featured-cases'),
		'not_found'             => __('No featured cases found.', 'fc-featured-cases'),
		'not_found_in_trash'    => __('No featured cases found in Trash.', 'fc-featured-cases'),
		'featured_image'        => __('Featured Case Image', 'fc-featured-cases'),
		'set_featured_image'    => __('Set featured case image', 'fc-featured-cases'),
		'remove_featured_image' => __('Remove featured case image', 'fc-featured-cases'),
		'use_featured_image'    => __('Use as featured case image', 'fc-featured-cases'),
	);

	$args = array(
		'labels'             => $labels,
		'public'             => true,
		'show_in_rest'       => true,
		'has_archive'        => true,
		'menu_icon'          => 'dashicons-portfolio',
		'supports'           => array('title', 'editor'),
		'rewrite'            => array('slug' => 'featured-cases'),
		'publicly_queryable' => true,
		'show_in_menu'       => true,
	);

	register_post_type(FC_FEATURED_CASE_POST_TYPE, $args);
}
add_action('init', 'fc_register_featured_case_post_type');

/**
 * Registers the Case Type taxonomy so admins can manage options in WordPress.
 *
 * @return void
 */
function fc_register_case_type_taxonomy()
{
	$labels = array(
		'name'          => __('Case Types', 'fc-featured-cases'),
		'singular_name' => __('Case Type', 'fc-featured-cases'),
		'menu_name'     => __('Case Types', 'fc-featured-cases'),
		'all_items'     => __('All Case Types', 'fc-featured-cases'),
		'edit_item'     => __('Edit Case Type', 'fc-featured-cases'),
		'view_item'     => __('View Case Type', 'fc-featured-cases'),
		'update_item'   => __('Update Case Type', 'fc-featured-cases'),
		'add_new_item'  => __('Add New Case Type', 'fc-featured-cases'),
		'new_item_name' => __('New Case Type Name', 'fc-featured-cases'),
		'search_items'  => __('Search Case Types', 'fc-featured-cases'),
	);

	register_taxonomy(
		FC_CASE_TYPE_TAXONOMY,
		FC_FEATURED_CASE_POST_TYPE,
		array(
			'labels'            => $labels,
			'public'            => false,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_rest'      => false,
			'hierarchical'      => false,
			'show_tagcloud'     => false,
			'meta_box_cb'       => false,
			'rewrite'           => false,
		)
	);
}
add_action('init', 'fc_register_case_type_taxonomy');

/**
 * Uses the classic editor for Featured Cases so native meta boxes submit reliably.
 *
 * @param bool   $use_block_editor Whether the block editor should be used.
 * @param string $post_type        Current post type.
 * @return bool
 */
function fc_disable_block_editor_for_featured_cases($use_block_editor, $post_type)
{
	if (FC_FEATURED_CASE_POST_TYPE === $post_type) {
		return false;
	}

	return $use_block_editor;
}
add_filter('use_block_editor_for_post_type', 'fc_disable_block_editor_for_featured_cases', 10, 2);

/**
 * Registers the custom meta fields for Featured Cases.
 *
 * @return void
 */
function fc_register_featured_case_meta()
{
	register_post_meta(
		FC_FEATURED_CASE_POST_TYPE,
		FC_META_CASE_TYPE,
		array(
			'show_in_rest'      => true,
			'single'            => true,
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'auth_callback'     => function () {
				return current_user_can('edit_posts');
			},
		)
	);

	register_post_meta(
		FC_FEATURED_CASE_POST_TYPE,
		FC_META_SETTLEMENT_AMOUNT,
		array(
			'show_in_rest'      => true,
			'single'            => true,
			'type'              => 'string',
			'sanitize_callback' => 'fc_sanitize_settlement_amount',
			'auth_callback'     => function () {
				return current_user_can('edit_posts');
			},
		)
	);
}
add_action('init', 'fc_register_featured_case_meta');

/**
 * Adds the Featured Case details meta box.
 *
 * @return void
 */
function fc_add_featured_case_meta_box()
{
	add_meta_box(
		'fc-featured-case-details',
		__('Featured Case Details', 'fc-featured-cases'),
		'fc_render_featured_case_meta_box',
		FC_FEATURED_CASE_POST_TYPE,
		'normal',
		'default'
	);
}
add_action('add_meta_boxes', 'fc_add_featured_case_meta_box');

/**
 * Renders the Featured Case details meta box.
 *
 * @param WP_Post $post Current post object.
 * @return void
 */
function fc_render_featured_case_meta_box($post)
{
	$selected_case_type = 0;
	$assigned_terms     = wp_get_post_terms($post->ID, FC_CASE_TYPE_TAXONOMY, array('fields' => 'ids'));
	$settlement_amount  = get_post_meta($post->ID, FC_META_SETTLEMENT_AMOUNT, true);
	$case_type_terms    = get_terms(
		array(
			'taxonomy'   => FC_CASE_TYPE_TAXONOMY,
			'hide_empty' => false,
		)
	);

	if (! is_wp_error($assigned_terms) && ! empty($assigned_terms)) {
		$selected_case_type = (int) $assigned_terms[0];
	}

	wp_nonce_field(FC_META_BOX_NONCE_ACTION, FC_META_BOX_NONCE_NAME);
?>
<div class="fc-featured-case-fields">
    <p class="fc-featured-case-field">
        <label for="fc-case-type"><strong><?php esc_html_e('Case Type', 'fc-featured-cases'); ?></strong></label>
        <br />
        <select id="fc-case-type" name="fc_case_type" class="widefat" required>
            <option value=""><?php esc_html_e('Select a case type', 'fc-featured-cases'); ?></option>
			<?php if (! is_wp_error($case_type_terms)) : ?>
				<?php foreach ($case_type_terms as $case_type_term) : ?>
            <option value="<?php echo esc_attr($case_type_term->term_id); ?>" <?php selected($selected_case_type, (int) $case_type_term->term_id); ?>>
				<?php echo esc_html($case_type_term->name); ?>
            </option>
				<?php endforeach; ?>
			<?php endif; ?>
        </select>
        <small>
			<?php
			printf(
				'%s <a href="%s">%s</a>',
				esc_html__('Need another option?', 'fc-featured-cases'),
				esc_url(admin_url('edit-tags.php?taxonomy=' . FC_CASE_TYPE_TAXONOMY . '&post_type=' . FC_FEATURED_CASE_POST_TYPE)),
				esc_html__('Manage case types.', 'fc-featured-cases')
			);
			?>
        </small>
    </p>
    <p class="fc-featured-case-field">
        <label
            for="fc-settlement-amount"><strong><?php esc_html_e('Settlement Amount', 'fc-featured-cases'); ?></strong></label>
        <br />
        <input type="text" id="fc-settlement-amount" name="fc_settlement_amount"
            value="<?php echo esc_attr($settlement_amount); ?>" class="widefat" placeholder="$1,000,000" required />
    </p>
</div>
<p><em><?php esc_html_e('Both fields are required.', 'fc-featured-cases'); ?></em></p>
<?php
}

/**
 * Saves Featured Case meta box fields.
 *
 * @param int $post_id Post ID.
 * @return void
 */
function fc_save_featured_case_meta($post_id)
{
	if (! isset($_POST[FC_META_BOX_NONCE_NAME])) {
		return;
	}

	if (! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[FC_META_BOX_NONCE_NAME])), FC_META_BOX_NONCE_ACTION)) {
		return;
	}

	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}

	if (wp_is_post_revision($post_id)) {
		return;
	}

	if (! current_user_can('edit_post', $post_id)) {
		return;
	}

	if (! isset($_POST['fc_case_type']) && ! isset($_POST['fc_settlement_amount'])) {
		return;
	}

	$case_type = isset($_POST['fc_case_type']) ? absint(wp_unslash($_POST['fc_case_type'])) : 0;
	$amount    = isset($_POST['fc_settlement_amount']) ? fc_sanitize_settlement_amount(wp_unslash($_POST['fc_settlement_amount'])) : '';

	if (fc_featured_case_has_missing_required_fields()) {
		return;
	}

	$case_type_term = get_term($case_type, FC_CASE_TYPE_TAXONOMY);

	if ($case_type_term instanceof WP_Term) {
		wp_set_object_terms($post_id, array($case_type_term->term_id), FC_CASE_TYPE_TAXONOMY, false);
		update_post_meta($post_id, FC_META_CASE_TYPE, $case_type_term->name);
	}

	if ('' !== $amount) {
		update_post_meta($post_id, FC_META_SETTLEMENT_AMOUNT, $amount);
	} else {
		delete_post_meta($post_id, FC_META_SETTLEMENT_AMOUNT);
	}
}
add_action('save_post_' . FC_FEATURED_CASE_POST_TYPE, 'fc_save_featured_case_meta');

/**
 * Outputs simple responsive admin styles for the Featured Case meta box.
 *
 * @return void
 */
function fc_featured_case_meta_box_styles()
{
	$screen = get_current_screen();

	if (! $screen || FC_FEATURED_CASE_POST_TYPE !== $screen->post_type) {
		return;
	}
	?>
<style>
    .fc-featured-case-fields {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }

    .fc-featured-case-field {
        margin: 0;
    }

    .fc-featured-case-field small {
        display: inline-block;
        margin-top: 8px;
    }

    @media (max-width: 767px) {
        .fc-featured-case-fields {
            grid-template-columns: 1fr;
        }
    }
</style>
<?php
}
add_action('admin_head', 'fc_featured_case_meta_box_styles');

/**
 * Prevents Featured Cases from being published when required fields are empty.
 *
 * @param array $data    Sanitized post data.
 * @param array $postarr Raw submitted post data.
 * @return array
 */
function fc_prevent_publishing_incomplete_featured_case($data, $postarr)
{
	if (FC_FEATURED_CASE_POST_TYPE !== $data['post_type']) {
		return $data;
	}

	if (empty($_POST[FC_META_BOX_NONCE_NAME])) {
		return $data;
	}

	if (! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[FC_META_BOX_NONCE_NAME])), FC_META_BOX_NONCE_ACTION)) {
		return $data;
	}

	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return $data;
	}

	if (! current_user_can('edit_posts')) {
		return $data;
	}

	if (! in_array($data['post_status'], array('publish', 'future'), true)) {
		return $data;
	}

	if (! fc_featured_case_has_missing_required_fields()) {
		return $data;
	}

	add_filter('redirect_post_location', 'fc_add_required_fields_error_query_arg', 99);
	$data['post_status'] = 'draft';

	return $data;
}
add_filter('wp_insert_post_data', 'fc_prevent_publishing_incomplete_featured_case', 10, 2);

/**
 * Adds a query arg to the redirect URL after validation fails.
 *
 * @param string $location Redirect URL.
 * @return string
 */
function fc_add_required_fields_error_query_arg($location)
{
	remove_filter('redirect_post_location', 'fc_add_required_fields_error_query_arg', 99);

	return add_query_arg('fc_required_fields', '1', $location);
}

/**
 * Displays an admin notice when required fields are missing.
 *
 * @return void
 */
function fc_featured_case_required_fields_notice()
{
	if (! is_admin()) {
		return;
	}

	$screen = get_current_screen();

	if (! $screen || FC_FEATURED_CASE_POST_TYPE !== $screen->post_type) {
		return;
	}

	if (empty($_GET['fc_required_fields'])) {
		return;
	}
	?>
<div class="notice notice-error is-dismissible">
    <p><?php esc_html_e('Case Type and Settlement Amount are required. The post has been saved as a draft until both fields are completed.', 'fc-featured-cases'); ?>
    </p>
</div>
<?php
}
add_action('admin_notices', 'fc_featured_case_required_fields_notice');

/**
 * Sanitizes the settlement amount while preserving common currency formatting.
 *
 * @param string $value Raw input value.
 * @return string
 */
function fc_sanitize_settlement_amount($value)
{
	$value = sanitize_text_field($value);

	return preg_replace('/[^0-9\.,\$\s]/', '', $value);
}

/**
 * Flushes rewrite rules on activation.
 *
 * @return void
 */
function fc_activate_featured_cases_plugin()
{
	fc_register_featured_case_post_type();
	flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'fc_activate_featured_cases_plugin');

/**
 * Flushes rewrite rules on deactivation.
 *
 * @return void
 */
function fc_deactivate_featured_cases_plugin()
{
	flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'fc_deactivate_featured_cases_plugin');
