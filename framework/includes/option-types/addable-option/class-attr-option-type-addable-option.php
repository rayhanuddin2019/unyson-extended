<?php if (!defined('ATTR')) die('Forbidden');

class ATTR_Option_Type_Addable_Option extends ATTR_Option_Type
{
	public function get_type()
	{
		return 'addable-option';
	}

	/**
	 * @internal
	 */
	protected function _get_defaults()
	{
		return array(
			'value'  => array(),
			'option' => array(
				'type' => 'text',
			),
			'add-button-text' => __('Add', 'attr'),
			/**
			 * Makes the options sortable
			 *
			 * You can disable this in case the options order doesn't matter,
			 * to not confuse the user that if changing the order will affect something.
			 */
			'sortable' => true,
		);
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
		static $enqueue = true;

		if ($enqueue) {
			wp_enqueue_style(
				'attr-option-'. $this->get_type(),
				attr_get_framework_directory_uri('/includes/option-types/'. $this->get_type() .'/static/css/styles.css'),
				array(),
				attr()->manifest->get_version()
			);

			wp_enqueue_script(
				'attr-option-'. $this->get_type(),
				attr_get_framework_directory_uri('/includes/option-types/'. $this->get_type() .'/static/js/scripts.js'),
				array('attr-events', 'jquery-ui-sortable'),
				attr()->manifest->get_version(),
				true
			);

			$enqueue = false;
		}

		attr()->backend->option_type($option['option']['type'])->enqueue_static();

		return true;
	}

	/**
	 * @internal
	 */
	protected function _render($id, $option, $data)
	{
		return attr_render_view(attr_get_framework_directory('/includes/option-types/'. $this->get_type() .'/view.php'), array(
			'id'     => $id,
			'option' => $option,
			'data'   => $data,
			'move_img_src' => attr_get_framework_directory_uri('/static/img/sort-vertically.png'),
		));
	}

	/**
	 * @internal
	 */
	protected function _get_value_from_input($option, $input_value)
	{
		if (!is_array($input_value)) {
			return $option['value'];
		}

		$option_type = attr()->backend->option_type($option['option']['type']);

		$value = array();

		foreach ($input_value as $option_input_value) {
			$value[] = $option_type->get_value_from_input(
				$option['option'],
				$option_input_value
			);
		}

		return $value;
	}
}
