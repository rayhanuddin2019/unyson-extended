<?php if ( ! defined( 'ATTR' ) ) {
	die( 'Forbidden' );
}

/**
 * Slider
 * -----*--
 */
class ATTR_Option_Type_Slider extends ATTR_Option_Type {

	/**
	 * This class is extended by 'short-slider' option type
	 * but the type here should be this
	 * @return string
	 */
	private function _get_type() {
		return 'slider';
	}

	protected function _get_data_for_js($id, $option, $data = array()) {
		return false;
	}

	/**
	 * @internal
	 * {@inheritdoc}
	 */
	protected function _enqueue_static( $id, $option, $data ) {
		{
			wp_enqueue_style(
				'attr-option-' . $this->_get_type() . 'ion-range-slider',
				attr_get_framework_directory_uri( '/includes/option-types/' . $this->_get_type() . '/static/libs/ion-range-slider/ion.rangeSlider.css' ),
				attr()->manifest->get_version()
			);

			wp_enqueue_script(
				'attr-option-' . $this->_get_type() . 'ion-range-slider',
				attr_get_framework_directory_uri( '/includes/option-types/' . $this->_get_type() . '/static/libs/ion-range-slider/ion.rangeSlider.min.js' ),
				array( 'jquery', 'attr-moment' ),
				attr()->manifest->get_version()
			);
		}

		wp_enqueue_style(
			'attr-option-' . $this->_get_type(),
			attr_get_framework_directory_uri( '/includes/option-types/' . $this->_get_type() . '/static/css/styles.css' ),
			attr()->manifest->get_version()
		);

		wp_enqueue_script(
			'attr-option-' . $this->_get_type(),
			attr_get_framework_directory_uri( '/includes/option-types/' . $this->_get_type() . '/static/js/scripts.js' ),
			array( 'jquery',  'attr-events', 'underscore', 'attr-option-' . $this->_get_type() . 'ion-range-slider' ),
			attr()->manifest->get_version()
		);
	}

	public function get_type() {
		return $this->_get_type();
	}

	/**
	 * @internal
	 */
	protected function _render( $id, $option, $data ) {
		$option['properties']['type'] = 'single';
		$option['properties']['from'] = isset( $data['value'] ) ? $data['value'] : $option['value'];

		if(isset($option['properties']['values']) && is_array($option['properties']['values'])){
			$option['properties']['from'] = array_search($option['properties']['from'], $option['properties']['values']);
		}

		$option['attr']['data-attr-irs-options'] = json_encode(
			$this->default_properties($option['properties'])
		);

		return attr_render_view( attr_get_framework_directory( '/includes/option-types/' . $this->_get_type() . '/view.php' ), array(
			'id'     => $id,
			'option' => $option,
			'data'   => $data,
			'value'  => $data['value']
		) );
	}

	private function default_properties($properties = array()) {
		return array_merge(array(
			'min' => 0,
			'max' => 100,
			'step' => 1,
			/**
			 * For large ranges, this will create https://static.md/6340ebf52a36255649f10b3d0dff3b1c.png
			 */
			'grid_snap' => false,
		), $properties);
	}

	/**
	 * @internal
	 */
	protected function _get_defaults() {
		return array(
			'value'      => 0,
			'properties' => $this->default_properties(), // https://github.com/IonDen/ion.rangeSlider#settings
		);
	}

	/**
	 * @internal
	 */
	protected function _get_value_from_input( $option, $input_value ) {
		if (is_null($input_value)) {
			return $option['value'];
		} else {
			return floatval($input_value);
		}
	}

}
