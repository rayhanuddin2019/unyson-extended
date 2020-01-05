<?php if (!defined('ATTR')) die('Forbidden');

/**
 * This will be returned when tried to get a not existing option type
 * to prevent fatal errors for cases when just one option type was typed wrong
 * or any other minor bug that has no sense to crash the whole site
 */
final class ATTR_Option_Type_Undefined extends ATTR_Option_Type {
	public function get_type() {
		return 'attr-undefined';
	}

	/**
	 * @internal
	 * {@inheritdoc}
	 */
	protected function _enqueue_static( $id, $option, $data ) {
	}

	/**
	 * @internal
	 * {@inheritdoc}
	 */
	protected function _render( $id, $name, $data ) {
		return '/* ' . __( 'UNDEFINED OPTION TYPE', 'attr' ) . ' */';
	}

	/**
	 * @internal
	 * {@inheritdoc}
	 */
	protected function _get_value_from_input( $option, $input_value ) {
		return $option['value'];
	}

	/**
	 * @internal
	 */
	protected function _get_defaults() {
		return array(
			'value' => array()
		);
	}
}
