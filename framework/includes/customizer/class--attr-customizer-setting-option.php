<?php if (!defined('ATTR')) die('Forbidden');

class _ATTR_Customizer_Setting_Option extends WP_Customize_Setting {
	/**
	 * @var array
	 * This is sent in args and set in parent construct
	 */
	protected $attr_option = array();

	/**
	 * @var string
	 * This is sent in args and set in parent construct
	 */
	protected $attr_option_id;

	public function get_attr_option() {
		
		return $this->attr_option;
	}

	public function sanitize($value) {
		if ( is_array( $value ) ) {
			return null;
		}
		   
		$value = json_decode($value, true);

		if (is_null($value) || !is_array($value)) {
			return null;
		}

		$POST = array();

		foreach ($value as $var) {
			attr_aks(
				attr_html_attr_name_to_array_multi_key($var['name'], true),
				$var['value'],
				$POST
			);
		}

		$value = attr()->backend->option_type($this->attr_option['type'])->get_value_from_input(
			$this->attr_option,
			attr_akg(attr_html_attr_name_to_array_multi_key($this->id), $POST)
		);

		return $value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function value() {
		return attr_db_option_storage_load(
			$this->attr_option_id,
			$this->attr_option,
			parent::value(),
			array('customizer' => true)
		);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function update( $value ) {
		return parent::update(
			attr_db_option_storage_save(
				$this->attr_option_id,
				$this->attr_option,
				$value,
				array('customizer' => true)
			)
		);
	}
}
