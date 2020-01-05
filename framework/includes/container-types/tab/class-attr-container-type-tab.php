<?php if (!defined('ATTR')) die('Forbidden');

class ATTR_Container_Type_Tab extends ATTR_Container_Type {
	public function get_type() {
		return 'tab';
	}

	protected function _get_defaults() {
		return array(
			'title' => '',
		);
	}

	protected function _enqueue_static($id, $option, $values, $data) {
		//
	}

	protected function _render($containers, $values, $data) {
		return attr_render_view(
			dirname(__FILE__) .'/view.php',
			array(
				'tabs'         => &$containers,
				'values'       => &$values,
				'options_data' => &$data,
			)
		);
	}
}
