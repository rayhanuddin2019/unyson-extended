<?php if (!defined('ATTR')) die('Forbidden');

class ATTR_Container_Type_Popup extends ATTR_Container_Type {
	public function get_type() {
		return 'popup';
	}

	protected function _get_defaults() {
		return array(
			'modal-size' => 'small', // small, medium, large
			'desc' => '',
		);
	}

	protected function _enqueue_static($id, $option, $values, $data) {
		$uri = attr_get_framework_directory_uri('/includes/container-types/popup');

		wp_enqueue_script(
			'attr-container-type-'. $this->get_type(),
			$uri .'/scripts.js',
			array('jquery', 'attr-events', 'attr'),
			attr()->manifest->get_version()
		);

		wp_enqueue_style('attr');

		wp_enqueue_style(
			'attr-container-type-'. $this->get_type(),
			$uri .'/styles.css',
			array(),
			attr()->manifest->get_version()
		);
	}

	protected function _render($containers, $values, $data) {
		$html = '';

		$defaults = $this->get_defaults();

		foreach ($containers as $id => &$option) {
			{
				$attr = $option['attr'];

				$attr['data-modal-title'] = $option['title'];

				if (in_array($option['modal-size'], array('small', 'medium', 'large'))) {
					$attr['data-modal-size'] = $option['modal-size'];
				} else {
					$attr['data-modal-size'] = $defaults['modal-size'];
				}

				$attr['id'] = $data['id_prefix'] . $id;
			}

			$html .=
				'<div '. attr_attr_to_html($attr) .'>'
				. '<p class="popup-button-wrapper">'
				. attr_html_tag(
					'button',
					array(
						'type' => 'button',
						'class' => 'button button-secondary popup-button',
					),
					$option['title']
				)
				. '</p>'
				. (empty($option['desc']) ? '' : ('<div class="popup-desc">'. $option['desc'] .'</div>'))
				. '<div class="popup-options attr-hidden">'
				. attr()->backend->render_options($option['options'], $values, $data)
				. '</div>'
				. '</div>';
		}

		return $html;
	}
}
