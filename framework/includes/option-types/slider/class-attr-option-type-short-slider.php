<?php if ( ! defined( 'ATTR' ) ) {
	die( 'Forbidden' );
}

class ATTR_Option_Type_Slider_Short extends ATTR_Option_Type_Slider {
	public function get_type() {
		return 'short-slider';
	}

	protected function _render( $id, $option, $data ) {
		$option['attr']['class'] .= ' short-slider attr-option-type-slider';

		return parent::_render( $id, $option, $data );
	}
}