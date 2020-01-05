<?php if (!defined('ATTR')) die('Forbidden');

// Process the `attr-storage` option parameter

/**
 * @param string $id
 * @param array $option
 * @param mixed $value
 * @param array $params
 *
 * @return mixed
 *
 * @since 2.5.0
 */
function attr_db_option_storage_save($id, array $option, $value, array $params = array()) {
	if (
		!empty($option['attr-storage'])
		&&
		($storage = is_array($option['attr-storage'])
			? $option['attr-storage']
			: array('type' => $option['attr-storage'])
		)
		&&
		!empty($storage['type'])
		&&
		($storage_type = attr_db_option_storage_type($storage['type']))
	) {
		$option['attr-storage'] = $storage;
	} else {
		return $value;
	}

	/** @var ATTR_Option_Storage_Type $storage_type */

	return $storage_type->save($id, $option, $value, $params);
}

/**
 * @param string $id
 * @param array $option
 * @param mixed $value
 * @param array $params
 *
 * @return mixed
 *
 * @since 2.5.0
 */
function attr_db_option_storage_load($id, array $option, $value, array $params = array()) {
	if (
		!empty($option['attr-storage'])
		&&
		($storage = is_array($option['attr-storage'])
			? $option['attr-storage']
			: array('type' => $option['attr-storage'])
		)
		&&
		!empty($storage['type'])
		&&
		($storage_type = attr_db_option_storage_type($storage['type']))
	) {
	
		if (isset($params['customizer']) && is_customize_preview()) {
			/** @var WP_Customize_Manager $wp_customize */
			global $wp_customize;

			if (
				($setting = $wp_customize->get_setting($setting_id = 'attr_options[' . $id . ']'))
				&&
				!is_null($wp_customize->post_value($setting))
			) {
				// Use POST preview value
				return $value;
			}
		}

		$option['attr-storage'] = $storage;
	} else {
		return $value;
	}

	/** @var ATTR_Option_Storage_Type $storage_type */

	return $storage_type->load($id, $option, $value, $params);
}

/**
 * @param null|string $type
 * @return ATTR_Option_Storage_Type|ATTR_Option_Storage_Type[]|null
 * @since 2.5.0
 */
function attr_db_option_storage_type($type = null) {
	static $types = null;

	if (is_null($types)) {
		$access_key = new ATTR_Access_Key('attr:option-storage-register');
		$register = new _ATTR_Option_Storage_Type_Register($access_key->get_key());

		{
			$register->register(new ATTR_Option_Storage_Type_WP_Option());
			$register->register(new ATTR_Option_Storage_Type_Post_Meta());
			$register->register(new ATTR_Option_Storage_Type_Term_Meta());
		}

		do_action('attr:option-storage-types:register', $register);

		$types = $register->_get_types($access_key);
	}

	if (empty($type)) {
		return $types;
	} elseif (isset($types[$type])) {
		return $types[$type];
	} else {
		return null;
	}
}
