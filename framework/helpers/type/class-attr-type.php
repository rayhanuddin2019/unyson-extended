<?php if ( ! defined( 'ATTR' ) ) die( 'Forbidden' );

/**
 * @since 2.4.10
 */
abstract class ATTR_Type {
	/**
	 * @return string Unique type
	 */
	abstract public function get_type();
}
