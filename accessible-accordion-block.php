<?php
/**
 * Plugin Name:       Accessible Accordion Block
 * Description:       An ACF block that builds accordions from native <details> and <summary> elements. No JavaScript required.
 * Version:           0.1.0
 * Requires at least: 6.3
 * Requires PHP:      7.4
 * Author:            Kris Owens
 * Author URI:        https://krisowensdev.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       accessible-accordion-block
 *
 * @package Accessible_Accordion_Block
 */

// Stop if this file is loaded directly instead of through WordPress.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the block from its block.json file.
 *
 * ACF Pro reads the "acf" key in block.json and takes over rendering,
 * so this bails out with an admin notice if ACF Pro isn't active.
 */
function aab_register_blocks() {
	if ( ! function_exists( 'acf_register_block_type' ) ) {
		add_action( 'admin_notices', 'aab_missing_acf_notice' );
		return;
	}

	register_block_type( __DIR__ . '/blocks/accordion' );
}
add_action( 'init', 'aab_register_blocks' );

/**
 * Tells admins why the block isn't available.
 */
function aab_missing_acf_notice() {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	printf(
		'<div class="notice notice-warning"><p>%s</p></div>',
		esc_html__( 'Accessible Accordion Block needs Advanced Custom Fields Pro to be active.', 'accessible-accordion-block' )
	);
}

/**
 * Checks whether a field group is assigned to this block.
 *
 * Looks through the group's location rules for "Block is equal to
 * Accessible Accordion", so no field group key is hardcoded.
 *
 * @param array $post The ACF field group array.
 * @return bool
 */
function aab_is_block_field_group( $post ) {
	if ( empty( $post['location'] ) || ! is_array( $post['location'] ) ) {
		return false;
	}

	foreach ( $post['location'] as $rule_group ) {
		foreach ( (array) $rule_group as $rule ) {
			if (
				isset( $rule['param'], $rule['value'] )
				&& 'block' === $rule['param']
				&& 'accessible-accordion/accordion' === $rule['value']
			) {
				return true;
			}
		}
	}

	return false;
}

/**
 * Saves this block's field group to the plugin's acf-json folder.
 *
 * Every other field group keeps saving wherever it did before.
 *
 * @param array $paths The folders ACF will try to save to.
 * @param array $post  The field group being saved.
 * @return array
 */
function aab_json_save_paths( $paths, $post ) {
	if ( aab_is_block_field_group( $post ) ) {
		return array( __DIR__ . '/acf-json' );
	}

	return $paths;
}
add_filter( 'acf/json/save_paths', 'aab_json_save_paths', 10, 2 );

/**
 * Tells ACF to also look in the plugin's acf-json folder for field groups.
 *
 * @param array $paths The folders ACF loads JSON from.
 * @return array
 */
function aab_json_load_paths( $paths ) {
	$paths[] = __DIR__ . '/acf-json';

	return $paths;
}
add_filter( 'acf/json/load_paths', 'aab_json_load_paths' );

/**
 * Gets the active theme's presets as slug => name.
 *
 * Works for classic themes and theme.json themes alike. Theme presets
 * come first, then user-added ones, then WordPress's defaults.
 *
 * @param string $type Either 'color' or 'font-size'.
 * @return array
 */
function aab_get_presets( $type ) {
	$path    = 'color' === $type ? array( 'color', 'palette' ) : array( 'typography', 'fontSizes' );
	$origins = wp_get_global_settings( $path );
	$presets = array();

	foreach ( array( 'theme', 'custom', 'default' ) as $origin ) {
		if ( empty( $origins[ $origin ] ) || ! is_array( $origins[ $origin ] ) ) {
			continue;
		}

		foreach ( $origins[ $origin ] as $preset ) {
			if ( isset( $preset['slug'], $preset['name'] ) && ! isset( $presets[ $preset['slug'] ] ) ) {
				$presets[ $preset['slug'] ] = $preset['name'];
			}
		}
	}

	return $presets;
}

/**
 * Turns a saved preset slug into the theme's CSS variable.
 *
 * Returns an empty string unless the slug is one of the theme's presets,
 * so only known values ever reach the page.
 *
 * @param string $type Either 'color' or 'font-size'.
 * @param mixed  $slug The value saved in the field.
 * @return string
 */
function aab_preset_var( $type, $slug ) {
	if ( ! is_string( $slug ) || ! array_key_exists( $slug, aab_get_presets( $type ) ) ) {
		return '';
	}

	return 'var(--wp--preset--' . $type . '--' . $slug . ')';
}

/**
 * Fills a Select field with the theme's colors, plus "Default".
 *
 * @param array $field The ACF field.
 * @return array
 */
function aab_prepare_color_field( $field ) {
	$field['choices'] = array( '' => __( 'Default', 'accessible-accordion-block' ) ) + aab_get_presets( 'color' );

	return $field;
}

/**
 * Fills a Select field with the theme's font sizes, plus "Default".
 *
 * @param array $field The ACF field.
 * @return array
 */
function aab_prepare_size_field( $field ) {
	$field['choices'] = array( '' => __( 'Default', 'accessible-accordion-block' ) ) + aab_get_presets( 'font-size' );

	return $field;
}

/**
 * Adds "Default" to the top of the Label weight choices.
 *
 * @param array $field The ACF field.
 * @return array
 */
function aab_prepare_weight_field( $field ) {
	$field['choices'] = array( '' => __( 'Default', 'accessible-accordion-block' ) ) + $field['choices'];

	return $field;
}

// prepare_field runs only when a field is drawn for editing a block,
// never on the field group screen, so the lists are never saved to JSON.
// These keys ship in acf-json, so they're the same on every site.
add_filter( 'acf/prepare_field/key=field_6ab892a613511', 'aab_prepare_color_field' );  // Label color.
add_filter( 'acf/prepare_field/key=field_6ab892c313512', 'aab_prepare_color_field' );  // Body color.
add_filter( 'acf/prepare_field/key=field_6ab892cf13513', 'aab_prepare_color_field' );  // Item background.
add_filter( 'acf/prepare_field/key=field_6ab8931e13514', 'aab_prepare_size_field' );   // Label size.
add_filter( 'acf/prepare_field/key=field_6ab8932c13515', 'aab_prepare_size_field' );   // Body size.
add_filter( 'acf/prepare_field/key=field_6ab8933a13516', 'aab_prepare_weight_field' ); // Label weight.
