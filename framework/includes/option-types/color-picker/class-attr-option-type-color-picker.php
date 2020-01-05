<?php if (!defined('ATTR')) die('Forbidden');

/**
 * Color Picker
 */
class ATTR_Option_Type_Color_Picker extends ATTR_Option_Type
{
	public function get_type()
	{
		return 'color-picker';
	}

	/**
	 * @internal
	 * {@inheritdoc}
	 */
	protected function _enqueue_static($id, $option, $data)
	{
		wp_enqueue_style(
			'attr-option-'. $this->get_type(),
			attr_get_framework_directory_uri('/includes/option-types/'. $this->get_type() .'/static/css/styles.css'),
			array(),
			attr()->manifest->get_version()
		);

		wp_enqueue_script(
			'attr-option-'. $this->get_type(),
			attr_get_framework_directory_uri('/includes/option-types/'. $this->get_type() .'/static/js/scripts.js'),
			array('jquery', 'attr-events', 'wp-color-picker'),
			attr()->manifest->get_version(),
			true
		);

		wp_localize_script(
			'attr-option-'. $this->get_type(),
			'_attr_option_type_'. str_replace('-', '_', $this->get_type()) .'_localized',
			array(
				'l10n' => array(
					'reset_to_default' => __('Reset', 'attr'),
					'reset_to_initial' => __('Reset', 'attr'),
				),
			)
		);
	}

	/**
	 * @internal
	 */
	protected function _render($id, $option, $data)
	{
		$option['attr']['value']  = strtolower($data['value']);
		$option['attr']['class'] .= ' code';
		$option['attr']['size']   = '7';
		$option['attr']['maxlength'] = '7';
		$option['attr']['onclick'] = 'this.select()';
		$option['attr']['data-default'] = $option['value'];


		$palettes = (bool) $option['palettes'];
		if ( ! empty( $option['palettes'] ) && is_array( $option['palettes'] ) ) {
			$palettes = $option['palettes'];
		}

		$option['attr']['data-palettes'] = json_encode( $palettes );

		return '<input type="text" '. attr_attr_to_html($option['attr']) .'>';
	}

	/**
	 * @internal
	 */
	protected function _get_value_from_input($option, $input_value)
	{
		if (
			is_null($input_value)
			||
			(
				
				!empty($input_value)
				&&
				!preg_match('/^#([a-f0-9]{3}){1,2}$/i', $input_value)
			)
		) {
			return (string)$option['value'];
		} else {
			return (string)$input_value;
		}
	}

	/**
	 * @internal
	 */
	public function _get_backend_width_type()
	{
		return 'auto';
	}

	/**
	 * @internal
	 */
	protected function _get_defaults()
	{
		return array(
			'value' => '',
			'palettes'=> true,
		);
	}
}
