<?php
/**
 * Render template for the Accessible Accordion block.
 *
 * ACF provides these variables:
 *
 * @var array $block      The block's settings and attributes.
 * @var bool  $is_preview True in the editor, false on the front end.
 *
 * @package Accessible_Accordion_Block
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$items         = get_field( 'items' );
$heading_level = get_field( 'heading_level' );
$exclusive     = get_field( 'exclusive' );

// Only real heading tags get through. Anything else means no heading.
$allowed_headings = array( 'h2', 'h3', 'h4', 'h5', 'h6' );
$heading_tag      = in_array( $heading_level, $allowed_headings, true ) ? $heading_level : '';

// Exclusive mode: every <details> in this accordion shares one name.
// ACF's block ID is a fingerprint of the content, so two identical
// accordions would match. wp_unique_id() adds a per-page counter.
$name_attr = '';
if ( $exclusive ) {
	$group_name = 'aab-' . $block['id'] . '-' . wp_unique_id();
	$name_attr  = ' name="' . esc_attr( $group_name ) . '"';
}

// No items yet: show a hint in the editor, nothing on the front end.
if ( empty( $items ) ) {
	if ( $is_preview ) {
		printf(
			'<div %s><p>%s</p></div>',
			get_block_wrapper_attributes(),
			esc_html__( 'Add items to this accordion.', 'accessible-accordion-block' )
		);
	}
	return;
}

// Style fields become CSS custom properties on the wrapper.
// Each value is empty unless it's a real theme preset or allowed weight.
$label_weight = (string) get_field( 'label_weight' );

$tokens = array(
	'--aab-label-color'  => aab_preset_var( 'color', get_field( 'label_color' ) ),
	'--aab-label-size'   => aab_preset_var( 'font-size', get_field( 'label_size' ) ),
	'--aab-label-weight' => in_array( $label_weight, array( '400', '600', '700' ), true ) ? $label_weight : '',
	'--aab-body-color'   => aab_preset_var( 'color', get_field( 'body_color' ) ),
	'--aab-body-size'    => aab_preset_var( 'font-size', get_field( 'body_size' ) ),
	'--aab-item-bg'      => aab_preset_var( 'color', get_field( 'item_background' ) ),
);

$style = '';
foreach ( $tokens as $property => $value ) {
	if ( ! empty( $value ) ) {
		$style .= sprintf( '%s:%s;', $property, $value );
	}
}
?>
<div <?php echo get_block_wrapper_attributes( array( 'style' => $style ) ); ?>>
	<?php foreach ( $items as $item ) : ?>
		<details class="aab-item"<?php echo $name_attr; ?><?php echo ! empty( $item['open'] ) ? ' open' : ''; ?>>
			<summary class="aab-summary">
				<?php
				$summary = esc_html( $item['summary'] );
				if ( $heading_tag ) {
					printf(
						'<%1$s class="aab-label">%2$s</%1$s>',
						esc_html( $heading_tag ),
						$summary
					);
				} else {
					printf(
						'<span class="aab-label">%s</span>',
						$summary
					);
				}
				?>
			</summary>
			<div class="aab-body">
				<?php echo wp_kses_post( $item['body'] ); ?>
			</div>
		</details>
	<?php endforeach; ?>
</div>
