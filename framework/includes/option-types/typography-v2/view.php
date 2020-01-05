<?php if ( ! defined( 'ATTR' ) ) {
	die( 'Forbidden' );
}
/**
 * @var  ATTR_Option_Type_Typography_v2 $typography_v2
 * @var  string $id
 * @var  array $option
 * @var  array $data
 * @var array $defaults
 */

{
	$wrapper_attr = $option['attr'];

	unset(
		$wrapper_attr['value'],
		$wrapper_attr['name']
	);
}

{
	$option['value'] = array_merge( $defaults['value'], (array) $option['value'] );
	$data['value']   = array_merge( $option['value'], is_array($data['value']) ? $data['value'] : array() );
	$google_font     = $typography_v2->get_google_font( $data['value']['family'] );

}

$components = (isset($option['components']) && is_array($option['components']))
	? array_merge($defaults['components'], $option['components'])
	: $defaults['components'];
?>
<div <?php echo attr_attr_to_html( $wrapper_attr ) ?>>
	<?php if ( $components['family'] ) : ?>
		<div class="attr-option-typography-v2-option attr-option-typography-v2-option-family attr-border-box-sizing attr-col-sm-5">
			<select data-type="family" data-value="<?php echo esc_attr($data['value']['family']); ?>"
			        name="<?php echo esc_attr( $option['attr']['name'] ) ?>[family]"
			        class="attr-option-typography-v2-option-family-input">
			</select>

			<div class="attr-inner"><?php _e('Font face', 'attr'); ?></div>
		</div>

		<?php if ( $components['style'] ) : ?>
		<div class="attr-option-typography-v2-option attr-option-typography-v2-option-style attr-border-box-sizing attr-col-sm-3"
		     style="display: <?php echo ( $google_font ) ? 'none' : 'inline-block'; ?>;">
			<select data-type="style" name="<?php echo esc_attr( $option['attr']['name'] ) ?>[style]"
			        class="attr-option-typography-v2-option-style-input">
				<?php foreach (
					array(
						'normal'  => __('Normal', 'attr'),
						'italic'  => __('Italic', 'attr'),
						'oblique' => __('Oblique', 'attr')
					)
					as $key => $style
				): ?>
					<option value="<?php echo esc_attr( $key ) ?>"
					        <?php if ($data['value']['style'] === $key): ?>selected="selected"<?php endif; ?>><?php echo attr_htmlspecialchars( $style ) ?></option>
				<?php endforeach; ?>
			</select>

			<div class="attr-inner"><?php _e( 'Style', 'attr' ); ?></div>
		</div>
		<?php endif; ?>

		<?php if ( $components['weight'] ) : ?>
		<div class="attr-option-typography-v2-option attr-option-typography-v2-option-weight attr-border-box-sizing attr-col-sm-3"
		     style="display: <?php echo ( $google_font ) ? 'none' : 'inline-block'; ?>;">
			<select data-type="weight" name="<?php echo esc_attr( $option['attr']['name'] ) ?>[weight]"
			        class="attr-option-typography-v2-option-weight-input">
				<?php foreach (
					array(
						100 => 100,
						200 => 200,
						300 => 300,
						400 => 400,
						500 => 500,
						600 => 600,
						700 => 700,
						800 => 800,
						900 => 900
					)
					as $key => $style
				): ?>
					<option value="<?php echo esc_attr( $key ) ?>"
					        <?php if ($data['value']['weight'] == $key): ?>selected="selected"<?php endif; ?>><?php echo attr_htmlspecialchars( $style ) ?></option>
				<?php endforeach; ?>
			</select>

			<div class="attr-inner"><?php _e( 'Weight', 'attr' ); ?></div>
		</div>
		<?php endif; ?>

		<div class="attr-option-typography-v2-option attr-option-typography-v2-option-subset attr-border-box-sizing attr-col-sm-2"
		     style="display: <?php echo ( $google_font ) ? 'inline-block' : 'none'; ?>;">
			<select data-type="subset" name="<?php echo esc_attr( $option['attr']['name'] ) ?>[subset]"
			        class="attr-option-typography-v2-option-subset">
				<?php if ( $google_font ) {
					foreach ( $google_font['subsets'] as $subset ) { ?>
						<option value="<?php echo esc_attr( $subset ) ?>"
						        <?php if ($data['value']['subset'] === $subset): ?>selected="selected"<?php endif; ?>><?php echo attr_htmlspecialchars( $subset ); ?></option>
					<?php }
				}
				?>
			</select>

			<div class="attr-inner"><?php _e( 'Script', 'attr' ); ?></div>
		</div>


		<?php if ( $components['variation'] ) : ?>
		<div
			class="attr-option-typography-v2-option attr-option-typography-v2-option-variation attr-border-box-sizing attr-col-sm-2"
			style="display: <?php echo ( $google_font ) ? 'inline-block' : 'none'; ?>;">
			<select data-type="variation" name="<?php echo esc_attr( $option['attr']['name'] ) ?>[variation]"
			        class="attr-option-typography-v2-option-variation">
				<?php if ( $google_font ) {
					foreach ( $google_font['variants'] as $variant ) { ?>
						<option value="<?php echo esc_attr( $variant ) ?>"
						        <?php if ($data['value']['variation'] == $variant): ?>selected="selected"<?php endif; ?>><?php echo attr_htmlspecialchars( $variant ); ?></option>
					<?php }
				}
				?>
			</select>

			<div class="attr-inner"><?php esc_html_e( 'Style', 'attr' ); ?></div>
		</div>
		<?php endif; ?>
	<?php endif; ?>

	<?php if ( $components['size'] ) : ?>
		<div class="attr-option-typography-v2-option attr-option-typography-v2-option-size attr-border-box-sizing attr-col-sm-2">
			<input data-type="size" name="<?php echo esc_attr( $option['attr']['name'] ) ?>[size]"
			       class="attr-option-typography-v2-option-size-input" type="text"
			       value="<?php echo esc_attr($data['value']['size']); ?>">

			<div class="attr-inner"><?php esc_html_e( 'Size', 'attr' ); ?></div>
		</div>
	<?php endif; ?>

	<?php if ( $components['line-height'] ) : ?>
		<div
			class="attr-option-typography-v2-option attr-option-typography-v2-option-line-height attr-border-box-sizing attr-col-sm-2">
			<input data-type="line-height" name="<?php echo esc_attr( $option['attr']['name'] ) ?>[line-height]"
			       value="<?php echo esc_attr($data['value']['line-height']); ?>"
			       class="attr-option-typography-v2-option-line-height-input" type="text">

			<div class="attr-inner"><?php esc_html_e( 'Line height', 'attr' ); ?></div>
		</div>
	<?php endif; ?>

	<?php if ( $components['letter-spacing'] ) : ?>
		<div
			class="attr-option-typography-v2-option attr-option-typography-v2-option-letter-spacing attr-border-box-sizing attr-col-sm-2">
			<input data-type="letter-spacing" name="<?php echo esc_attr( $option['attr']['name'] ) ?>[letter-spacing]"
			       value="<?php echo esc_attr($data['value']['letter-spacing']); ?>"
			       class="attr-option-typography-v2-option-letter-spacing-input" type="text">

			<div class="attr-inner"><?php esc_html_e( 'Spacing', 'attr' ); ?></div>
		</div>
	<?php endif; ?>

	<?php if ( $components['color'] ) : ?>
		<div class="attr-option-typography-v2-option attr-option-typography-v2-option-color attr-border-box-sizing attr-col-sm-2"
		     data-type="color">
			<?php
			echo attr()->backend->option_type( 'color-picker' )->render(
				'color',
				array(
					'label' => false,
					'desc'  => false,
					'type'  => 'color-picker',
					'value' => $option['value']['color']
				),
				array(
					'value'       => $data['value']['color'],
					'id_prefix'   => 'attr-option-' . $id . '-typography-v2-option-',
					'name_prefix' => $data['name_prefix'] . '[' . $id . ']',
				)
			)
			?>
			<div class="attr-inner"><?php esc_html_e( 'Color', 'attr' ); ?></div>
		</div>
	<?php endif; ?>

</div>
