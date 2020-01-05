<?php if (!defined('ATTR')) die('Forbidden');

/**
 * array(
 *  'term-id' => 3 // optional // hardcoded term id
 *  'term-meta' => 'hello_world' // optional (default: 'attr:opt:{option_id}')
 *  'key' => 'option_id/sub_key' // optional
 * )
 */
class ATTR_Option_Storage_Type_Term_Meta extends ATTR_Option_Storage_Type {
	public function get_type() {
		return 'term-meta';
	}

	/**
	 * {@inheritdoc}
	 */
	protected function _save( $id, array $option, $value, array $params ) {
		if ($term_id = $this->get_term_id($option, $params)) {
			$meta_id = $this->get_meta_id($id, $option, $params);

			if (isset($option['attr-storage']['key'])) {
				$meta_value = get_term_meta($term_id, $meta_id, true);

				attr_aks($option['attr-storage']['key'], $value, $meta_value);

				update_term_meta($term_id, $meta_id, $meta_value);

				unset($meta_value);
			} else {
				update_term_meta($term_id, $meta_id, $value);
			}

			return attr()->backend->option_type($option['type'])->get_value_from_input(
				array('type' => $option['type']), null
			);
		} else {
			return $value;
		}
	}

	/**
	 * {@inheritdoc}
	 */
	protected function _load( $id, array $option, $value, array $params ) {
		if ($term_id = $this->get_term_id($option, $params)) {
			$meta_id = $this->get_meta_id($id, $option, $params);
			$meta_value = get_term_meta($term_id, $meta_id, true);

			if ($meta_value === '' && is_array($value)) {
				return $value;
			}

			if (isset($option['attr-storage']['key'])) {
				return attr_akg($option['attr-storage']['key'], $meta_value, $value);
			} else {
				return $meta_value;
			}
		} else {
			return $value;
		}
	}

	private function get_term_id($option, $params) {
		$term_id = null;

		if (!empty($option['attr-storage']['term-id'])) {
			$term_id = $option['attr-storage']['term-id'];
		} elseif (!empty($params['term-id'])) {
			$term_id = $params['term-id'];
		}

		$term_id = intval($term_id);

		if ($term_id > 0) {
			return $term_id;
		} else {
			return false;
		}
	}

	private function get_meta_id($id, $option, $params) {
		return empty($option['attr-storage']['term-meta'])
			? 'attr:opt:'. $id
			: $option['attr-storage']['term-meta'];
	}
}
