<?php if (!defined('ATTR')) die('Forbidden');

class ATTR_Container_Type_Group extends ATTR_Container_Type {
	public function get_type() {
		return 'group';
	}

	protected function _get_defaults() {
		return array();
	}

	protected function _enqueue_static($id, $option, $values, $data) {
		//
	}

	protected function _render($containers, $values, $data) {
		$html = '';

		foreach ( $containers as $id => &$group ) {
			// prepare attributes
			{
				$attr = isset( $group['attr'] ) ? $group['attr'] : array();

				$attr['id'] = 'attr-backend-options-group-' . $id;

				if ( ! isset( $attr['class'] ) ) {
					$attr['class'] = 'attr-backend-options-group';
				} else {
					$attr['class'] = 'attr-backend-options-group ' . $attr['class'];
				}
			}

			$html .= '<div ' . attr_attr_to_html( $attr ) . '>';
			$html .= attr()->backend->render_options( $group['options'], $values, $data );
			$html .= '</div>';
		}

		return $html;
	}
}
