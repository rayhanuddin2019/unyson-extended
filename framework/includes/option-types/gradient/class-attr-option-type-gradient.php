<?php if (!defined('ATTR')) {
	die('Forbidden');
}

/**
 * Background Color
 */
class ATTR_Option_Type_Gradient extends ATTR_Option_Type
{
	/**
	 * @internal
	 */
	public function _get_backend_width_type()
	{
		return 'auto';
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
			array(),
			attr()->manifest->get_version()
		);

		attr()->backend->option_type('color-picker')->enqueue_static();

		wp_enqueue_script(
			'attr-option-' . $this->get_type(),
			attr_get_framework_directory_uri('/includes/option-types/' . $this->get_type() . '/static/js/scripts.js'),
			array('jquery', 'attr-events'),
			attr()->manifest->get_version()
		);
	}

	/**
	 * @internal
	 */
	protected function _render($id, $option, $data)
	{
		return attr_render_view(
			attr_get_framework_directory('/includes/option-types/' . $this->get_type() . '/view.php'),
			array(
				'id' => $id,
				'option' => $option,
				'data' => $data
			)
		);
	}

	public function get_type()
	{
		return 'gradient';
	}

	/**
	 * @internal
	 */
	protected function _get_value_from_input($option, $input_value)
	{
		if (!is_array($input_value)) {
			return $option['value'];
		}

		if (
			isset($input_value['primary'])   && $input_value['primary'] === ''
			&&
			isset($input_value['secondary']) && $input_value['secondary'] === ''
		) {
			return array(
				'primary' => '',
				'secondary' => '',
			);
		} else {
			$color_regex = '/^#([a-f0-9]{3}){1,2}$/i';

			if (
				!isset($input_value['primary']) || !preg_match($color_regex, $input_value['primary'])
			) {
				$input_value['primary'] = $option['value']['primary'];
			}

			if (
				!isset($input_value['secondary']) || !preg_match($color_regex, $input_value['secondary'])
			) {
				$input_value['secondary'] = (isset($option['value']['secondary'])) ? $input_value['primary'] : false;
			}
		}

		return $input_value;
	}

	/**
	 * @internal
	 */
	protected function _get_defaults()
	{
		return array(
			'value' => array(
				'primary'   => '',
				'secondary' => '',
			)
		);
	}
}