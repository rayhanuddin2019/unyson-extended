<?php if (!defined('ATTR')) {
	die('Forbidden');
}

/**
 * Typography
 */
class ATTR_Option_Type_Typography extends ATTR_Option_Type
{
	/*
	 * Allowed fonts
	 */
	private $fonts;

	/**
	 * Returns fonts
	 * @return array
	 */
	public function get_fonts()
	{
		if($this->fonts === null) {
			$this->fonts = array(
				'standard' => apply_filters('attr_option_type_typography_standard_fonts', array(
					"Arial",
					"Verdana",
					"Trebuchet",
					"Georgia",
					"Times New Roman",
					"Tahoma",
					"Palatino",
					"Helvetica",
					"Calibri",
					"Myriad Pro",
					"Lucida",
					"Arial Black",
					"Gill Sans",
					"Geneva",
					"Impact",
					"Serif"
				)),
				'google' => attr_get_google_fonts()
			);
		}

		return $this->fonts;
	}

	/**
	 * @internal
	 * {@inheritdoc}
	 */
	protected function _enqueue_static($id, $option, $data)
	{
		wp_enqueue_style(
			'attr-option-' . $this->get_type(),
			attr_get_framework_directory_uri('/includes/option-types/' . $this->get_type() . '/static/css/styles.css'),
			array('attr-selectize'),
			attr()->manifest->get_version()
		);

		attr()->backend->option_type('color-picker')->enqueue_static();

		wp_enqueue_script(
			'attr-option-' . $this->get_type(),
			attr_get_framework_directory_uri('/includes/option-types/' . $this->get_type() . '/static/js/scripts.js'),
			array('jquery', 'underscore', 'attr', 'attr-selectize'),
			attr()->manifest->get_version()
		);

		wp_localize_script(
			'attr-option-' . $this->get_type(),
			'attr_typography_fonts',
			$this->get_fonts()
		);
	}

	/**
	 * @internal
	 */
	protected function _render($id, $option, $data)
	{
		return attr_render_view(attr_get_framework_directory('/includes/option-types/' . $this->get_type() . '/view.php'), array(
			'id' => $id,
			'option' => $option,
			'data' => $data,
			'fonts' => $this->get_fonts()
		));
	}

	public function get_type()
	{
		return 'typography';
	}

	/**
	 * @internal
	 */
	protected function _get_value_from_input($option, $input_value)
	{
		if (!is_array($input_value)) {
			return $option['value'];
		}

		$components = (isset($option['components']) && is_array($option['components'])) ? $option['components'] : array();
		$components = array_merge(array(
			'size' => true,
			'family' => true,
			'color' => true,
		), $components);

		$values = array(
			'size'   => ( ! empty( $components['size'] ) ) ? ( isset( $input_value['size'] ) ) ? intval( $input_value['size'] ) : intval( $option['value']['size'] ) : false,
			'family' => ( ! empty( $components['family'] ) ) ? ( isset( $input_value['family'] ) ) ? $input_value['family'] : $option['value']['family'] : false,
			'style'  => ( ! empty( $components['family'] ) ) ? ( isset( $input_value['style'] ) ) ? $input_value['style'] : $option['value']['style'] : false,
			'color'  => ( ! empty( $components['color'] ) ) ? ( isset( $input_value['color'] ) && preg_match( '/^#([a-f0-9]{3}){1,2}$/i', $input_value['color'] ) ) ? $input_value['color'] : $option['value']['color'] : false,
		);

		return $values;
	}

	/**
	 * @internal
	 */
	protected function _get_defaults()
	{
		return array(
			'value' => array(
				'size'   => 12,
				'family' => 'Arial',
				'style'  => '400',
				'color'  => '#000000'
			),
			'components' => array(
				'size'   => true,
				'family' => true,
				'color'  => true
			)
		);
	}

	public function _get_backend_width_type()
	{
		return 'fixed';
	}
}