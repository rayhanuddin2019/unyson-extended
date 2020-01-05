<?php if (!defined('ATTR')) die('Forbidden');

/**
 * Rows with options
 */
class ATTR_Option_Type_Switch extends ATTR_Option_Type
{
	private static $color_regex = '/^#?([a-f]|[A-F]|[0-9]){3}(([a-f]|[A-F]|[0-9]){3})?$/';

	public function get_type()
	{
		return 'switch';
	}

	protected function _get_data_for_js($id, $option, $data = array()) {
		return false;
	}

	/**
	 * @internal
	 * {@inheritdoc}
	 */
	protected function _enqueue_static($id, $option, $data)
	{
		// adaptive switch
		{
			wp_enqueue_style(
				'attr-option-'. $this->get_type() .'-adaptive-switch',
				attr_get_framework_directory_uri('/includes/option-types/'. $this->get_type() .'/static/adaptive-switch/styles.css'),
				array(),
				attr()->manifest->get_version()
			);

			wp_enqueue_script(
				'attr-option-'. $this->get_type() .'-adaptive-switch',
				attr_get_framework_directory_uri('/includes/option-types/'. $this->get_type() .'/static/adaptive-switch/jquery.adaptive-switch.js'),
				array('jquery'),
				attr()->manifest->get_version(),
				true
			);
		}

		wp_enqueue_style(
			'attr-option-'. $this->get_type(),
			attr_get_framework_directory_uri('/includes/option-types/'. $this->get_type() .'/static/css/styles.css'),
			array('attr-option-'. $this->get_type() .'-adaptive-switch'),
			attr()->manifest->get_version()
		);

		wp_enqueue_script(
			'attr-option-'. $this->get_type(),
			attr_get_framework_directory_uri('/includes/option-types/'. $this->get_type() .'/static/js/scripts.js'),
			array('attr-events', 'attr-option-'. $this->get_type() .'-adaptive-switch'),
			attr()->manifest->get_version(),
			true
		);
	}

	/**
	 * @internal
	 */
	protected function _render($id, $option, $data)
	{
		if (is_null($data['value'])) {
			$data['value'] = $this->get_value_from_input($option, null);
		}

		{
			$input_attr = array(
				'name' => $option['attr']['name'],
				'id'   => $option['attr']['id'] .'--checkbox',
				'data-switch-left'  => $option['left-choice']['label'],
				'data-switch-right' => $option['right-choice']['label'],
			);

			foreach (array('left', 'right') as $value_type) {
				$input_attr['data-switch-'. $value_type .'-value-json'] = json_encode($option[$value_type .'-choice']['value']);
			}

			if ($checked = ($data['value'] === $option['right-choice']['value'])) {
				$input_attr['checked'] = 'checked'; // right choice means checked
			}

			$input_attr['value'] = json_encode($option[ ($checked ? 'right' : 'left') .'-choice' ]['value']);
		}

		{
			unset(
				$option['attr']['name'],
				$option['attr']['value'],
				$option['attr']['checked'],
				$option['attr']['type']
			);

			foreach (array('left', 'right') as $value_type) {
				if (
					isset($option[$value_type .'-choice']['color'])
					&&
					preg_match(self::$color_regex, $option[$value_type .'-choice']['color'])
				) {
					$option['attr']['data-'. $value_type .'-color'] = $option[$value_type .'-choice']['color'];
				}
			}
		}

		return '<div '. attr_attr_to_html($option['attr']) .'>'.
			'<!-- note: value is json encoded, if want to use it in js, do: var val = JSON.parse($input.val()); -->'.
			'<!-- deprecated: use reactive options for extracting the current value of the switch -->'.
			($checked ? '' : attr_html_tag('input', array(
				'type' => 'hidden',
				'name' => $input_attr['name'],
				'value' => $input_attr['data-switch-left-value-json'],
			))).
			'<input type="checkbox" '. attr_attr_to_html($input_attr) .' />'.
		'</div>';
	}

	/**
	 * @internal
	 */
	protected function _get_value_from_input($option, $input_value)
	{
		if (is_null($input_value)) {
			if (in_array($option['value'], array($option['left-choice']['value'], $option['right-choice']['value']), true)) {
				return $option['value'];
			} else {
				return $option['left-choice']['value'];
			}
		} else {
			$tmp_json = json_decode($input_value);

			/**
			 * Check if parsing is successful.
			 * If it's not - leave $input_value as it is.
			 */
			if (!is_null($tmp_json)) {
				$input_value = $tmp_json;
			}

			if (in_array($input_value, array($option['left-choice']['value'], $option['right-choice']['value']), true)) {
				return $input_value;
			} else {
				return $option['value'];
			}
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
			'value' => null,
			'left-choice' => array(
				'value' => false,
				'label' => __('No', 'attr'),
				'color' => '', // #HEX
			),
			'right-choice' => array(
				'value' => true,
				'label' => __('Yes', 'attr'),
				'color' => '', // #HEX
			),
		);
	}
}
