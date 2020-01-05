<?php if (!defined('ATTR')) die('Forbidden');

/**
 * @internal
 */
class _ATTR_Option_Storage_Type_Register extends ATTR_Type_Register {
	protected function validate_type(ATTR_Type $type) {
		return $type instanceof ATTR_Option_Storage_Type;
	}
}
