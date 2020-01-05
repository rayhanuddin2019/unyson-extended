<?php if ( ! defined( 'ATTR' ) ) {
	die( 'Forbidden' );
}
// Useful functions

/**
 * Convert to Unix style directory separators
 */
function attr_fix_path( $path ) {
	$windows_network_path = isset( $_SERVER['windir'] ) && in_array( substr( $path, 0, 2 ),
			array( '//', '\\\\' ),
			true );
	$fixed_path           = untrailingslashit( str_replace( array( '//', '\\' ), array( '/', '/' ), $path ) );

	if ( empty( $fixed_path ) && ! empty( $path ) ) {
		$fixed_path = '/';
	}

	if ( $windows_network_path ) {
		$fixed_path = '//' . ltrim( $fixed_path, '/' );
	}

	return $fixed_path;
}

/**
 * Relative path of the framework customizations directory
 *
 * @param string $append
 *
 * @return string
 */
function attr_get_framework_customizations_dir_rel_path( $append = '' ) {
	try {
		$dir = ATTR_Cache::get( $cache_key = 'attr_customizations_dir_rel_path' );
	} catch ( ATTR_Cache_Not_Found_Exception $e ) {
		ATTR_Cache::set(
			$cache_key,
			$dir = apply_filters( 'attr_framework_customizations_dir_rel_path', '/framework-customizations' )
		);
	}

	return $dir . $append;
}

/** Child theme related functions */
{
	/**
	 * Full path to the child-theme framework customizations directory
	 *
	 * @param string $rel_path
	 *
	 * @return null|string
	 */
	function attr_get_stylesheet_customizations_directory( $rel_path = '' ) {
		if ( is_child_theme() ) {
			return get_stylesheet_directory() . attr_get_framework_customizations_dir_rel_path( $rel_path );
		} else {
			// check is_child_theme() before using this function
			return null;
		}
	}

	/**
	 * URI to the child-theme framework customizations directory
	 *
	 * @param string $rel_path
	 *
	 * @return null|string
	 */
	function attr_get_stylesheet_customizations_directory_uri( $rel_path = '' ) {
		if ( is_child_theme() ) {
			return get_stylesheet_directory_uri() . attr_get_framework_customizations_dir_rel_path( $rel_path );
		} else {
			// check is_child_theme() before using this function
			return null;
		}
	}
}

/** Parent theme related functions */
{
	/**
	 * Full path to the parent-theme framework customizations directory
	 *
	 * @param string $rel_path
	 *
	 * @return string
	 */
	function attr_get_template_customizations_directory( $rel_path = '' ) {
		try {
			$dir = ATTR_Cache::get( $cache_key = 'attr_template_customizations_dir' );
		} catch ( ATTR_Cache_Not_Found_Exception $e ) {
			ATTR_Cache::set(
				$cache_key,
				$dir = get_template_directory() . attr_get_framework_customizations_dir_rel_path()
			);
		}

		return $dir . $rel_path;
	}

	/**
	 * URI to the parent-theme framework customizations directory
	 *
	 * @param string $rel_path
	 *
	 * @return string
	 */
	function attr_get_template_customizations_directory_uri( $rel_path = '' ) {
		try {
			$dir = ATTR_Cache::get( $cache_key = 'attr_template_customizations_dir_uri' );
		} catch ( ATTR_Cache_Not_Found_Exception $e ) {
			ATTR_Cache::set(
				$cache_key,
				$dir = get_template_directory_uri() . attr_get_framework_customizations_dir_rel_path()
			);
		}

		return $dir . $rel_path;
	}
}

/** Framework related functions */
{
	/**
	 * Full path to the parent-theme/framework directory
	 *
	 * @param string $rel_path
	 *
	 * @return string
	 */
	function attr_get_framework_directory( $rel_path = '' ) {
		try {
			$dir = ATTR_Cache::get( $cache_key = 'attr_framework_dir' );
		} catch ( ATTR_Cache_Not_Found_Exception $e ) {
			ATTR_Cache::set(
				$cache_key,
				$dir = apply_filters(
					'attr_framework_directory',
					attr_fix_path( dirname( dirname( __FILE__ ) ) ) // double dirname() to remove '/helpers', use parent dir
				)
			);
		}

		return $dir . $rel_path;
	}

	/**
	 * URI to the parent-theme/framework directory
	 *
	 * @param string $rel_path
	 *
	 * @return string
	 */
	function attr_get_framework_directory_uri( $rel_path = '' ) {
		try {
			$uri = ATTR_Cache::get( $cache_key = 'attr_framework_dir_uri' );
		} catch ( ATTR_Cache_Not_Found_Exception $e ) {
			ATTR_Cache::set(
				$cache_key,
				$uri = apply_filters(
					'attr_framework_directory_uri',
					( $uri = attr_get_path_url( attr_get_framework_directory() ) )
						? $uri
						: get_template_directory_uri() . '/framework'
				)
			);
		}

		return $uri . $rel_path;
	}
}

/**
 * Recursively find a key's value in array
 *
 * @param string $keys 'a/b/c'
 * @param array|object $array_or_object
 * @param null|mixed $default_value
 * @param string $keys_delimiter
 *
 * @return null|mixed
 */
function attr_akg( $keys, $array_or_object, $default_value = null, $keys_delimiter = '/' ) {
	if ( ! is_array( $keys ) ) {
		$keys = explode( $keys_delimiter, (string) $keys );
	}

	$array_or_object = attr_call( $array_or_object );

	$key_or_property = array_shift( $keys );
	if ( $key_or_property === null ) {
		return attr_call( $default_value );
	}

	$is_object = is_object( $array_or_object );

	if ( $is_object ) {
		if ( ! property_exists( $array_or_object, $key_or_property ) ) {
			return attr_call( $default_value );
		}
	} else {
		if ( ! is_array( $array_or_object ) || ! array_key_exists( $key_or_property, $array_or_object ) ) {
			return attr_call( $default_value );
		}
	}

	if ( isset( $keys[0] ) ) { // not used count() for performance reasons
		if ( $is_object ) {
			return attr_akg( $keys, $array_or_object->{$key_or_property}, $default_value );
		} else {
			return attr_akg( $keys, $array_or_object[ $key_or_property ], $default_value );
		}
	} else {
		if ( $is_object ) {
			return $array_or_object->{$key_or_property};
		} else {
			return $array_or_object[ $key_or_property ];
		}
	}
}

/**
 * Set (or create if not exists) value for specified key in some array level
 *
 * @param string $keys 'a/b/c', or 'a/b/c/' equivalent to: $arr['a']['b']['c'][] = $val;
 * @param mixed $value
 * @param array|object $array_or_object
 * @param string $keys_delimiter
 *
 * @return array|object
 */
function attr_aks( $keys, $value, &$array_or_object, $keys_delimiter = '/' ) {
	if ( ! is_array( $keys ) ) {
		$keys = explode( $keys_delimiter, (string) $keys );
	}

	$key_or_property = array_shift( $keys );
	if ( $key_or_property === null ) {
		return $array_or_object;
	}

	$is_object = is_object( $array_or_object );

	if ( $is_object ) {
		if ( ! property_exists( $array_or_object, $key_or_property )
		     || ! ( is_array( $array_or_object->{$key_or_property} ) || is_object( $array_or_object->{$key_or_property} ) )
		) {
			if ( $key_or_property === '' ) {
				// this happens when use 'empty keys' like: abc/d/e////i/j//foo/
				trigger_error( 'Cannot push value to object like in array ($arr[] = $val)', E_USER_WARNING );
			} else {
				$array_or_object->{$key_or_property} = array();
			}
		}
	} else {
		if ( ! is_array( $array_or_object ) ) {
			$array_or_object = array();
		}

		if ( ! array_key_exists( $key_or_property,
				$array_or_object ) || ! is_array( $array_or_object[ $key_or_property ] )
		) {
			if ( $key_or_property === '' ) {
				// this happens when use 'empty keys' like: abc.d.e....i.j..foo.
				$array_or_object[] = array();

				// get auto created key (last)
				end( $array_or_object );
				$key_or_property = key( $array_or_object );
			} else {
				$array_or_object[ $key_or_property ] = array();
			}
		}
	}

	if ( isset( $keys[0] ) ) { // not used count() for performance reasons
		if ( $is_object ) {
			attr_aks( $keys, $value, $array_or_object->{$key_or_property} );
		} else {
			attr_aks( $keys, $value, $array_or_object[ $key_or_property ] );
		}
	} else {
		if ( $is_object ) {
			$array_or_object->{$key_or_property} = $value;
		} else {
			$array_or_object[ $key_or_property ] = $value;
		}
	}

	return $array_or_object;
}

/**
 * Unset specified key in some array level
 *
 * @param string $keys 'a/b/c' -> unset($arr['a']['b']['c']);
 * @param array|object $array_or_object
 * @param string $keys_delimiter
 *
 * @return array|object
 */
function attr_aku( $keys, &$array_or_object, $keys_delimiter = '/' ) {
	if ( ! is_array( $keys ) ) {
		$keys = explode( $keys_delimiter, (string) $keys );
	}

	$key_or_property = array_shift( $keys );
	if ( $key_or_property === null || $key_or_property === '' ) {
		return $array_or_object;
	}

	$is_object = is_object( $array_or_object );

	if ( $is_object ) {
		if ( ! property_exists( $array_or_object, $key_or_property ) ) {
			return $array_or_object;
		}
	} else {
		if ( ! is_array( $array_or_object ) || ! array_key_exists( $key_or_property, $array_or_object ) ) {
			return $array_or_object;
		}
	}

	if ( isset( $keys[0] ) ) { // not used count() for performance reasons
		if ( $is_object ) {
			attr_aku( $keys, $array_or_object->{$key_or_property} );
		} else {
			attr_aku( $keys, $array_or_object[ $key_or_property ] );
		}
	} else {
		if ( $is_object ) {
			unset( $array_or_object->{$key_or_property} );
		} else {
			unset( $array_or_object[ $key_or_property ] );
		}
	}

	return $array_or_object;
}

/**
 * Generate random unique md5
 */
function attr_rand_md5() {
	return md5( time() . '-' . uniqid( rand(), true ) . '-' . mt_rand( 1, 1000 ) );
}

function attr_unique_increment() {
	static $i = 0;

	return ++ $i;
}

/**
 * print_r() alternative
 *
 * @param mixed $value Value to debug
 */
function attr_print( $value ) {
	static $first_time = true;

	if ( $first_time ) {
		ob_start();
		echo '<style type="text/css">
		div.attr_print_r {
			max-height: 500px;
			overflow-y: scroll;
			background: #23282d;
			margin: 10px 30px;
			padding: 0;
			border: 1px solid #F5F5F5;
			border-radius: 3px;
			position: relative;
			z-index: 11111;
		}

		div.attr_print_r pre {
			color: #78FF5B;
			background: #23282d;
			text-shadow: 1px 1px 0 #000;
			font-family: Consolas, monospace;
			font-size: 12px;
			margin: 0;
			padding: 5px;
			display: block;
			line-height: 16px;
			text-align: left;
		}

		div.attr_print_r_group {
			background: #f1f1f1;
			margin: 10px 30px;
			padding: 1px;
			border-radius: 5px;
			position: relative;
			z-index: 11110;
		}
		div.attr_print_r_group div.attr_print_r {
			margin: 9px;
			border-width: 0;
		}
		</style>';
		echo str_replace( array( '  ', "\n" ), '', ob_get_clean() );

		$first_time = false;
	}


}

/**
 * Alias for attr_print
 *
 * @see attr_print()
 */
if ( ! function_exists( 'debug' ) ) {
	function debug() {
		call_user_func_array( 'attr_print', func_get_args() );
	}
}

/**
 * Generate html tag
 *
 * @param string $tag Tag name
 * @param array $attr Tag attributes
 * @param bool|string $end Append closing tag. Also accepts body content
 *
 * @return string The tag's html
 */
function attr_html_tag( $tag, $attr = array(), $end = false ) {
	$html = '<' . $tag . ' ' . attr_attr_to_html( $attr );

	if ( $end === true ) {
		# <script></script>
		$html .= '></' . $tag . '>';
	} else if ( $end === false ) {
		# <br/>
		$html .= '/>';
	} else {
		# <div>content</div>
		$html .= '>' . $end . '</' . $tag . '>';
	}

	return $html;
}

/**
 * Generate attributes string for html tag
 *
 * @param array $attr_array array('href' => '/', 'title' => 'Test')
 *
 * @return string 'href="/" title="Test"'
 */
function attr_attr_to_html( array $attr_array ) {
	$html_attr = '';

	foreach ( $attr_array as $attr_name => $attr_val ) {
		if ( $attr_val === false ) {
			continue;
		}

		$html_attr .= $attr_name . '="' . attr_htmlspecialchars( $attr_val ) . '" ';
	}

	return $html_attr;
}

/**
 * Strip slashes from values, and from keys if magic_quotes_gpc = On
 */
function attr_stripslashes_deep_keys( $value ) {
	static $magic_quotes = null;
	if ( $magic_quotes === null ) {
		$magic_quotes = get_magic_quotes_gpc();
	}

	if ( is_array( $value ) ) {
		if ( $magic_quotes ) {
			$new_value = array();
			foreach ( $value as $key => $val ) {
				$new_value[ is_string( $key ) ? stripslashes( $key ) : $key ] = attr_stripslashes_deep_keys( $val );
			}
			$value = $new_value;
			unset( $new_value );
		} else {
			$value = array_map( 'attr_stripslashes_deep_keys', $value );
		}
	} elseif ( is_object( $value ) ) {
		$vars = get_object_vars( $value );
		foreach ( $vars as $key => $data ) {
			$value->{$key} = attr_stripslashes_deep_keys( $data );
		}
	} elseif ( is_string( $value ) ) {
		$value = stripslashes( $value );
	}

	return $value;
}

/**
 * Add slashes to values, and to keys if magic_quotes_gpc = On
 */
function attr_addslashes_deep_keys( $value ) {
	static $magic_quotes = null;
	if ( $magic_quotes === null ) {
		$magic_quotes = get_magic_quotes_gpc();
	}

	if ( is_array( $value ) ) {
		if ( $magic_quotes ) {
			$new_value = array();
			foreach ( $value as $key => $value ) {
				$new_value[ is_string( $key ) ? addslashes( $key ) : $key ] = attr_addslashes_deep_keys( $value );
			}
			$value = $new_value;
			unset( $new_value );
		} else {
			$value = array_map( 'attr_addslashes_deep_keys', $value );
		}
	} elseif ( is_object( $value ) ) {
		$vars = get_object_vars( $value );
		foreach ( $vars as $key => $data ) {
			$value->{$key} = attr_addslashes_deep_keys( $data );
		}
	} elseif ( is_string( $value ) ) {
		$value = addslashes( $value );
	}

	return $value;
}

/**
 * Check if current screen pass/match give rules
 *
 * @param array $rules Rules for current screen
 *
 * @return bool
 */
function attr_current_screen_match( array $rules ) {
	$available_options = array(
		'action'      => true,
		'base'        => true,
		'id'          => true,
		'is_network'  => true,
		'is_user'     => true,
		'parent_base' => true,
		'parent_file' => true,
		'post_type'   => true,
		'taxonomy'    => true,
	);

	if ( empty( $rules ) ) {
		return true;
	}

	$rules = array_merge(
		array(
			'exclude' => array(), // array of arrays or array with keys from $available_options
			'only'    => array(), // same as in 'exclude'
		),
		$rules
	);

	if ( empty( $rules['exclude'] ) && empty( $rules['only'] ) ) {
		return true;
	}

	global $current_screen;

	if ( gettype( $current_screen ) != 'object' ) {
		return false;
	}

	// check if current screen passes the "only" rules
	do {
		$only = $rules['only'];

		if ( empty( $only ) ) {
			break;
		}

		if ( ! isset( $only[0] ) ) { // if not array of arrays
			$only = array( $only );
		}

		$found_one = false;
		$counter   = 0;
		foreach ( $only as $rule ) {
			if ( ! count( $rule ) ) {
				continue;
			}

			$match = true;

			foreach ( $rule as $r_key => $r_val ) {
				if ( ! isset( $available_options[ $r_key ] ) ) {
					continue;
				}

				if ( gettype( $r_val ) != 'array' ) {
					$r_val = array( $r_val );
				}

				$counter ++;

				if ( ! in_array( $current_screen->{$r_key}, $r_val ) ) {
					$match = false;
					break;
				}
			}

			if ( $match ) {
				$found_one = true;
				break;
			}
		}

		if ( ! $found_one && $counter ) {
			return false;
		}
	} while ( false );

	// check if current screen passes the "exclude" rules
	do {
		$exclude = $rules['exclude'];

		if ( empty( $exclude ) ) {
			break;
		}

		if ( ! isset( $exclude[0] ) ) { // if not array of arrays
			$exclude = array( $exclude );
		}

		foreach ( $exclude as $rule ) {
			if ( ! count( $rule ) ) {
				continue;
			}

			$match   = true;
			$counter = 0;

			foreach ( $rule as $r_key => $r_val ) {
				if ( ! isset( $available_options[ $r_key ] ) ) {
					continue;
				}

				if ( gettype( $r_val ) != 'array' ) {
					$r_val = array( $r_val );
				}

				$counter ++;

				if ( ! in_array( $current_screen->{$r_key}, $r_val ) ) {
					$match = false;
					break;
				}
			}

			if ( $match && $counter ) {
				return false;
			}
		}
	} while ( false );

	return true;
}

/**
 * Search relative path in child then in parent theme directory and return URI
 *
 * @param  string $rel_path '/some/path_to_dir' or '/some/path_to_file.php'
 *
 * @return string URI
 */
function attr_locate_theme_path_uri( $rel_path ) {
	if ( is_child_theme() && file_exists( get_stylesheet_directory() . $rel_path ) ) {
		return get_stylesheet_directory_uri() . $rel_path;
	} elseif ( file_exists( get_template_directory() . $rel_path ) ) {
		return get_template_directory_uri() . $rel_path;
	} else {
		return 'about:blank#theme-file-not-found:' . $rel_path;
	}
}

/**
 * Search relative path in child then in parent theme directory and return full path
 *
 * @param  string $rel_path '/some/path_to_dir' or '/some/path_to_file.php'
 *
 * @return string URI
 */
function attr_locate_theme_path( $rel_path ) {
	if ( is_child_theme() && file_exists( get_stylesheet_directory() . $rel_path ) ) {
		return get_stylesheet_directory() . $rel_path;
	} elseif ( file_exists( get_template_directory() . $rel_path ) ) {
		return get_template_directory() . $rel_path;
	} else {
		return false;
	}
}

/**
 * There is a theme which does: if (!defined('ATTR')): function attr_render_view() { ... } endif;
 * It works fine, except in this case
 *
 * it throws fatal error because this function here is defined after that
 */
if ( ! function_exists( 'attr_render_view' ) ):
	/**
	 * Safe render a view and return html
	 * In view will be accessible only passed variables
	 * Use this function to not include files directly and to not give access to current context variables (like $this)
	 *
	 * @param string $file_path
	 * @param array $view_variables
	 * @param bool $return In some cases, for memory saving reasons, you can disable the use of output buffering
	 *
	 * @return string HTML
	 */
	function attr_render_view( $file_path, $view_variables = array(), $return = true ) {

		if ( ! is_file( $file_path ) ) {
			return '';
		}

		extract( $view_variables, EXTR_REFS );
		unset( $view_variables );

		if ( $return ) {
			ob_start();
			require $file_path;

			return ob_get_clean();
		} else {
			require $file_path;
		}

		return '';
	}
endif;

/**
 * Safe load variables from an file
 * Use this function to not include files directly and to not give access to current context variables (like $this)
 *
 * @param string $file_path
 * @param array $_extract_variables Extract these from file array('variable_name' => 'default_value')
 * @param array $_set_variables Set these to be available in file (like variables in view)
 *
 * @return array
 */
function attr_get_variables_from_file( $file_path, array $_extract_variables, array $_set_variables = array() ) {
	extract( $_set_variables, EXTR_REFS );
	unset( $_set_variables );

	require $file_path;

	foreach ( $_extract_variables as $variable_name => $default_value ) {
		if ( isset( $$variable_name ) ) {
			$_extract_variables[ $variable_name ] = $$variable_name;
		}
	}

	return $_extract_variables;
}

/**
 * Use this function to not include files directly and to not give access to current context variables (like $this)
 *
 * @param string $file_path
 * @param bool $once
 *
 * @return bool If was included or not
 */
function attr_include_file_isolated( $file_path, $once = false ) {
	if ( file_exists( $file_path ) ) {
		if ( (bool) $once ) {
			include_once $file_path;
		} else {
			include $file_path;
		}

		return true;
	} else {
		return false;
	}
}

/**
 * Extract only input options (without containers)
 *
 * @param array $options
 *
 * @return array {option_id => option}
 */
function attr_extract_only_options( array $options ) {
	$collected = array();

	attr_collect_options( $collected, $options );

	return $collected;
}

/**
 * Collect correct options from the first level of the array and group them
 *
 * @param array $collected Will be filled with found correct options
 * @param array $options
 *
 * @deprecated
 * It is deprecated since 2.4 because container types were added and there can be any type of containers
 * but this function is hardcoded only for tab,box,group.
 * Use attr_collect_options()
 */
function attr_collect_first_level_options( &$collected, &$options ) {
	if ( empty( $options ) ) {
		return;
	}

	if ( empty( $collected ) ) {
		$collected['tabs'] =
		$collected['boxes'] =
		$collected['groups'] =
		$collected['options'] =
		$collected['groups_and_options'] =
		$collected['all'] = array();
	}

	foreach ( $options as $option_id => &$option ) {
		if ( isset( $option['options'] ) ) {
			// this is container for other options

			switch ( $option['type'] ) {
				case 'tab':
					$collected['tabs'][ $option_id ] =& $option;
					break;
				case 'box':
					$collected['boxes'][ $option_id ] =& $option;
					break;
				case 'group':
					$collected['groups'][ $option_id ]             =& $option;
					$collected['groups_and_options'][ $option_id ] =& $option;
					break;
				default:
					trigger_error( 'Invalid option container type: ' . $option['type'], E_USER_WARNING );
					continue 2;
			}

			$collected['all'][ $option['type'] . ':~:' . $option_id ] = array(
				'type'   => $option['type'],
				'id'     => $option_id,
				'option' => &$option,
			);
		} elseif (
			is_int( $option_id )
			&&
			is_array( $option )
			&&
			/**
			 * make sure the array key was generated automatically
			 * and it's not an associative array with numeric keys created like this: $options[1] = array();
			 */
			isset( $options[0] )
		) {
			/**
			 * Array "without key" containing options.
			 *
			 * This happens when options are returned into array from a function:
			 * $options = array(
			 *  'foo' => array('type' => 'text'),
			 *  'bar' => array('type' => 'textarea'),
			 *
			 *  // this is our case
			 *  // go inside this array and extract the options as they are on the same array level
			 *  array(
			 *      'hello' => array('type' => 'text'),
			 *  ),
			 *
			 *  // there can be any nested arrays
			 *  array(
			 *      array(
			 *          array(
			 *              'h1' => array('type' => 'text'),
			 *          ),
			 *      ),
			 *  ),
			 * )
			 */
			attr_collect_first_level_options( $collected, $option );
		} elseif ( isset( $option['type'] ) ) {
			// simple option, last possible level in options array
			$collected['options'][ $option_id ]            =& $option;
			$collected['groups_and_options'][ $option_id ] =& $option;

			$collected['all'][ 'option' . ':~:' . $option_id ] = array(
				'type'   => 'option',
				'id'     => $option_id,
				'option' => &$option,
			);
		} else {
			trigger_error( 'Invalid option: ' . $option_id, E_USER_WARNING );
		}
	}
	unset( $option );
}

/**
 * @param array $result
 * @param array $options
 * @param array $settings
 * @param array $_recursion_data (private) for internal use
 */
function attr_collect_options( &$result, &$options, $settings = array(), $_recursion_data = array() ) {
	static $default_settings = array(
		/**
		 * @type bool Wrap the result/collected options in arrays will useful info
		 *
		 * If true:
		 * $result = array(
		 *   '(container|option):{id}' => array(
		 *      'id' => '{id}',
		 *      'level' => int, // from which nested level this option is
		 *      'group' => 'container|option',
		 *      'option' => array(...),
		 *   )
		 * )
		 *
		 * If false:
		 * $result = array(
		 *   '{id}' => array(...),
		 *   // Warning: There can be options and containers with the same id (array key will be replaced)
		 * )
		 */
		'info_wrapper'          => false,
		/**
		 * @type int Nested options level limit. For e.g. use 1 to collect only first level. 0 is for unlimited.
		 */
		'limit_level'           => 0,
		/**
		 * @type false|array('option-type', ...) Empty array will skip all types
		 */
		'limit_option_types'    => false,
		/**
		 * @type false|array('container-type', ...) Empty array will skip all types
		 */
		'limit_container_types' => array(),
		/**
		 * @type int Limit the number of options that will be collected
		 */
		'limit'                 => 0,
		/**
		 * @type callable Executed on each collected option
		 * @since 2.6.0
		 */
		'callback'              => null,
	);

	static $access_key = null;

	if ( empty( $options ) ) {
		return;
	}

	if ( empty( $_recursion_data ) ) {
		if ( is_null( $access_key ) ) {
			$access_key = new ATTR_Access_Key( 'attr_collect_options' );
		}

		$settings = array_merge( $default_settings, $settings );

		$_recursion_data = array(
			'level'      => 1,
			'access_key' => $access_key,
			// todo: maybe add 'parent' => array('id' => '{id}', 'type' => 'container|option') ?
		);
	} elseif ( ! (
		isset( $_recursion_data['access_key'] )
		&&
		( $_recursion_data['access_key'] instanceof ATTR_Access_Key )
		&&
		( $_recursion_data['access_key']->get_key() === 'attr_collect_options' )
	)
	) {
		trigger_error( 'Call not allowed', E_USER_ERROR );
	}

	if (
		$settings['limit_level']
		&&
		$_recursion_data['level'] > $settings['limit_level']
	) {
		return;
	}

	foreach ( $options as $option_id => &$option ) {
		if ( isset( $option['options'] ) ) { // this is a container
			do {
				if (
					is_array( $settings['limit_container_types'] )
					&&
					(
						// Customizer options can contain options with not existing or empty $option['type']
						empty( $option['type'] )
						||
						! in_array( $option['type'], $settings['limit_container_types'] )
					)
				) {
					break;
				}

				if (
					$settings['limit']
					&&
					count( $result ) >= $settings['limit']
				) {
					return;
				}

				if ( $settings['info_wrapper'] ) {
					$result[ 'container:' . $option_id ] = array(
						'group'  => 'container',
						'id'     => $option_id,
						'option' => &$option,
						'level'  => $_recursion_data['level'],
					);
				} else {
					$result[ $option_id ] = &$option;
				}

				if ( $settings['callback'] ) {
					call_user_func_array( $settings['callback'],
						array(
							array(
								'group'  => 'container',
								'id'     => $option_id,
								'option' => &$option,
							)
						) );
				}
			} while ( false );

			attr_collect_options(
				$result,
				$option['options'],
				$settings,
				array_merge( $_recursion_data, array( 'level' => $_recursion_data['level'] + 1 ) )
			);
		} elseif (
			is_int( $option_id )
			&&
			is_array( $option )
			&&
			/**
			 * make sure the array key was generated automatically
			 * and it's not an associative array with numeric keys created like this: $options[1] = array();
			 */
			isset( $options[0] )
		) {
			/**
			 * Array "without key" containing options.
			 *
			 * This happens when options are returned into array from a function:
			 * $options = array(
			 *  'foo' => array('type' => 'text'),
			 *  'bar' => array('type' => 'textarea'),
			 *
			 *  // this is our case
			 *  // go inside this array and extract the options as they are on the same array level
			 *  array(
			 *      'hello' => array('type' => 'text'),
			 *  ),
			 *
			 *  // there can be any nested arrays
			 *  array(
			 *      array(
			 *          array(
			 *              'h1' => array('type' => 'text'),
			 *          ),
			 *      ),
			 *  ),
			 * )
			 */
			attr_collect_options( $result, $option, $settings, $_recursion_data );
		} elseif ( isset( $option['type'] ) ) { // option
			if (
				is_array( $settings['limit_option_types'] )
				&&
				! in_array( $option['type'], $settings['limit_option_types'] )
			) {
				continue;
			}

			if (
				$settings['limit']
				&&
				count( $result ) >= $settings['limit']
			) {
				return;
			}

			if ( $settings['info_wrapper'] ) {
				$result[ 'option:' . $option_id ] = array(
					'group'  => 'option',
					'id'     => $option_id,
					'option' => &$option,
					'level'  => $_recursion_data['level'],
				);
			} else {
				$result[ $option_id ] = &$option;
			}

			if ( $settings['callback'] ) {
				call_user_func_array( $settings['callback'],
					array(
						array(
							'group'  => 'option',
							'id'     => $option_id,
							'option' => &$option,
						)
					) );
			}
		} else {
			trigger_error( 'Invalid option: ' . $option_id, E_USER_WARNING );
		}
	}
}

/**
 * Get correct values from input (POST) for given options
 * This values can be saved in db then replaced with $option['value'] for each option
 *
 * @param array $options
 * @param array $input_array
 *
 * @return array Values
 */
function attr_get_options_values_from_input( array $options, $input_array = null ) {
	if ( ! is_array( $input_array ) ) {
		$input_array = ATTR_Request::POST( attr()->backend->get_options_name_attr_prefix() );
	}

	$values = array();

	$maybe_new_values = apply_filters(
		'attr:get_options_values_from_input:before',
		null,
		$options, $input_array
	);

	if ($maybe_new_values) {
		return $maybe_new_values;
	}

	foreach ( attr_extract_only_options( $options ) as $id => $option ) {
		$values[ $id ] = attr()->backend->option_type( $option['type'] )->get_value_from_input(
			$option,
			isset( $input_array[ $id ] ) ? $input_array[ $id ] : null
		);

		if ( is_null( $values[ $id ] ) ) {
			// do not save null values
			unset( $values[ $id ] );
		}
	}

	return $values;
}

/**
 * @param $attr_name
 * @param bool $set_mode
 *
 * @return mixed
 */
function attr_html_attr_name_to_array_multi_key( $attr_name, $set_mode = false ) {
	if ( $set_mode ) {
		/**
		 * The key will be used to set value in array
		 * 'hello[world][]' -> 'hello/world/'
		 * $array['hello']['world'][] = $value;
		 */
		$attr_name = str_replace( '[]', '/', $attr_name );
	} else {
		/**
		 * The key will be used to get value from array
		 * 'hello[world][]' -> 'hello/world'
		 * $value = $array['hello']['world'];
		 */
		$attr_name = str_replace( '[]', '', $attr_name );
	}

	$attr_name = str_replace( '][', '/', $attr_name );
	$attr_name = str_replace( '[', '/', $attr_name );
	$attr_name = str_replace( ']', '', $attr_name );

	return $attr_name;
}

/**
 * Used when getting some option value from serialized array saved in a custom place
 * and that option is unreachable for standard WordPress filters by other plugins
 * For e.g. that option cannot be translated by plugins, so we pass its value through this function and do the fixes
 *
 * @param $value
 *
 * @return array
 */
function attr_prepare_option_value( $value ) {
	if ( empty( $value ) ) {
		return $value;
	}

	if ( function_exists( 'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage' ) ) {
		$value = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage( $value );
	}

	return $value;
}

/**
 * This function is used in 'save_post' action
 *
 * Used to check if current post save is a regular "Save" button press
 * not a revision, auto-save or something else
 *
 * @param $post_id
 *
 * @return bool
 *
 * @deprecated
 * save_post action happens also happens on Preview, Revision, Auto-save Restore, ...
 * the verifications in this function simplifies too much the save process,
 * the developers should study and understand better how it works
 * and handle different save cases in their scripts using wp functions
 */
function attr_is_real_post_save( $post_id ) {
	return ! (
		wp_is_post_revision( $post_id )
		|| wp_is_post_autosave( $post_id )
		|| ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		|| ( defined( 'DOING_AJAX' ) && DOING_AJAX )
		|| empty( $_POST )
		|| empty( $_POST['post_ID'] )
		|| $_POST['post_ID'] != $post_id
	);
}

/**
 * @return Array with Google fonts
 */
function attr_get_google_fonts() {
	$cache_key = 'attr_google_fonts';

	try {
		return ATTR_Cache::get( $cache_key );
	} catch ( ATTR_Cache_Not_Found_Exception $e ) {
		$g_fonts   = json_decode( attr_get_google_fonts_v2(), true );
		$old_fonts = include( dirname( __FILE__ ) . '/attr-google-fonts.json.php' );
		$fonts     = array();

		foreach ( $g_fonts['items'] as $font ) {
			$fonts[ $font['family'] ] = array(
				'family'   => $font['family'],
				'variants' => $font['variants'],
				'position' => isset( $old_fonts[ $font['family'] ] )
					? $old_fonts[ $font['family'] ]['position']
					: 99999
			);
		}

		$fonts = apply_filters( 'attr_google_fonts', $fonts );

		ATTR_Cache::set( $cache_key, $fonts );

		return $fonts;
	}
}

/**
 * @return string JSON encoded array with Google fonts
 */
function attr_get_google_fonts_v2() {
	$saved_data = get_option( 'attr_google_fonts', false );
	$ttl        = 7 * DAY_IN_SECONDS;

	if (
		false === $saved_data
		||
		( $saved_data['last_update'] + $ttl < time() )
	) {
		$response = wp_remote_get( apply_filters( 'attr_googleapis_webfonts_url',
			'https://google-webfonts-cache.unyson.io/v1/webfonts' ) );
		$body     = wp_remote_retrieve_body( $response );
      
		if (
			200 === wp_remote_retrieve_response_code( $response )
			&&
			! is_wp_error( $body ) && ! empty( $body )
		) {
			update_option( 'attr_google_fonts',
				array(
					'last_update' => time(),
					'fonts'       => $body
				),
				false );

			return $body;
		} else {
			if ( empty( $saved_data['fonts'] ) ) {
				$saved_data['fonts'] = json_encode( array( 'items' => array() ) );
			}

			update_option( 'attr_google_fonts',
				array(
					'last_update' => time() - $ttl + MINUTE_IN_SECONDS,
					'fonts'       => $saved_data['fonts']
				),
				false );
		}
	}

	return $saved_data['fonts'];
}

/**
 * @return string Current url
 */
function attr_current_url() {
	static $url = null;
	if ( $url === null ) {
		if ( is_multisite() && ! ( defined( 'SUBDOMAIN_INSTALL' ) && SUBDOMAIN_INSTALL ) ) {
			switch_to_blog( 1 );
			$url = get_option( 'home' );
			restore_current_blog();
		} else {
			$url = get_option( 'home' );
		}

		//Remove the "//" before the domain name
		$url = ltrim( attr_get_url_without_scheme( $url ), '/' );

		//Remove the ulr subdirectory in case it has one
		$split = explode( '/', $url );

		//Remove end slash
		$url = rtrim( $split[0], '/' );

		$url .= '/' . ltrim( attr_akg( 'REQUEST_URI', $_SERVER, '' ), '/' );
		$url = set_url_scheme( '//' . $url ); // https fix
	}

	return $url;
}

function attr_is_valid_domain_name( $domain_name ) {
	return ( preg_match( "/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $domain_name ) // valid chars check
	         && preg_match( "/^.{1,253}$/", $domain_name ) // overall length check
	         && preg_match( "/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $domain_name ) ); // length of each label
}

/**
 * Use this id do not want to enter every time same last two parameters
 * Info: Cannot use default parameters because in php 5.2 encoding is not UTF-8 by default
 *
 * @param string $string
 *
 * @return string
 */
function attr_htmlspecialchars( $string ) {
	return htmlspecialchars( $string, ENT_QUOTES, 'UTF-8' );
}

/**
 * Check if current user has one capability from the given list
 *
 * @param array $capabilities list of capabilities to check
 * @param mixed $default_value
 *
 * @return string|bool|mixed
 *  Return first capability that user can.
 *  Else, return default value if it is not null, else return first capability from list.
 *  Use default value false to check if user can some of the capabilities
 */
function attr_current_user_can( $capabilities, $default_value = null ) {
	if ( is_user_logged_in() ) {
		foreach ( $capabilities as $capability ) {
			if ( current_user_can( $capability ) ) {
				return $capability;
			}
		}
	}

	return ( $default_value !== null ? attr_call( $default_value ) : array_shift( $capabilities ) );
}

/**
 * Convert number of seconds to 'X {units}'
 *
 * E.g. 123 => '2 minutes'
 * then you can use this string how you want, for e.g. append ' ago' => '2 minutes ago'
 *
 * @param int $seconds
 *
 * @return string
 */
function attr_human_time( $seconds ) {
	static $translations = null;
	if ( $translations === null ) {
		$translations = array(
			'year'  => __( 'year', 'attr' ),
			'years' => __( 'years', 'attr' ),

			'month'  => __( 'month', 'attr' ),
			'months' => __( 'months', 'attr' ),

			'week'  => __( 'week', 'attr' ),
			'weeks' => __( 'weeks', 'attr' ),

			'day'  => __( 'day', 'attr' ),
			'days' => __( 'days', 'attr' ),

			'hour'  => __( 'hour', 'attr' ),
			'hours' => __( 'hours', 'attr' ),

			'minute'  => __( 'minute', 'attr' ),
			'minutes' => __( 'minutes', 'attr' ),

			'second'  => __( 'second', 'attr' ),
			'seconds' => __( 'seconds', 'attr' ),
		);
	}

	$tokens = array(
		31536000 => 'year',
		2592000  => 'month',
		604800   => 'week',
		86400    => 'day',
		3600     => 'hour',
		60       => 'minute',
		1        => 'second'
	);

	foreach ( $tokens as $unit => $translation_key ) {
		if ( $seconds < $unit ) {
			continue;
		}

		$number_of_units = floor( $seconds / $unit );

		return $number_of_units . ' ' . $translations[ $translation_key . ( $number_of_units != 1 ? 's' : '' ) ];
	}
}

/**
 * Convert bytes to human readable format
 *
 * @param integer $bytes Size in bytes to convert
 * @param integer $precision
 *
 * @return string
 * @since 2.4.17
 */
function attr_human_bytes( $bytes, $precision = 2 ) {
	$kilobyte = 1024;
	$megabyte = $kilobyte * 1024;
	$gigabyte = $megabyte * 1024;
	$terabyte = $gigabyte * 1024;

	if ( ( $bytes >= 0 ) && ( $bytes < $kilobyte ) ) {
		return $bytes . ' B';

	} elseif ( ( $bytes >= $kilobyte ) && ( $bytes < $megabyte ) ) {
		return round( $bytes / $kilobyte, $precision ) . ' KB';

	} elseif ( ( $bytes >= $megabyte ) && ( $bytes < $gigabyte ) ) {
		return round( $bytes / $megabyte, $precision ) . ' MB';

	} elseif ( ( $bytes >= $gigabyte ) && ( $bytes < $terabyte ) ) {
		return round( $bytes / $gigabyte, $precision ) . ' GB';

	} elseif ( $bytes >= $terabyte ) {
		return round( $bytes / $terabyte, $precision ) . ' TB';
	} else {
		return $bytes . ' B';
	}
}

function attr_strlen( $string ) {
	if ( function_exists( 'mb_strlen' ) ) {
		return mb_strlen( $string, 'UTF-8' );
	} else {
		return strlen( $string );
	}
}

/**
 * If currently is a Post Edit page display/submit
 * @return bool
 */
function attr_is_post_edit() {
	static $result = null;

	if ( $result === null ) {
		$result = false;

		if ( is_admin() ) {
			if (
				empty( $_POST )
				&&
				isset( $_GET['action'] )
				&&
				$_GET['action'] === 'edit'
				&&
				isset( $_GET['post'] )
			) {
				// Display Edit Post page
				$result = true;
			} elseif (
				isset( $_POST['action'] )
				&&
				$_POST['action'] === 'editpost'
				&&
				isset( $_POST['post_type'] )
				&&
				isset( $_POST['post_ID'] )
				&&
				strpos( wp_get_referer(), 'action=edit' ) !== false
			) {
				// Submit Edit Post page
				$result = true;
			}
		}
	}

	return $result;
}

/**
 * @param string $dirname 'foo-bar'
 *
 * @return string 'Foo_Bar'
 */
function attr_dirname_to_classname( $dirname ) {
	$class_name = explode( '-', $dirname );
	$class_name = array_map( 'ucfirst', $class_name );
	$class_name = implode( '_', $class_name );

	return $class_name;
}

/**
 * This function is a wrapper function that set correct width and height for iframes from wp_oembed_get() function
 *
 * @param $url
 * @param array $args
 *
 * @return bool|string
 */
function attr_oembed_get( $url, $args = array() ) {
	$html = wp_oembed_get( $url, $args );

	if ( ! empty( $args['width'] ) and ! empty( $args['height'] ) and class_exists( 'DOMDocument' ) and ! empty( $html ) ) {
		$dom_element = new DOMDocument();
		@$dom_element->loadHTML( $html );

		if ( $obj = $dom_element->getElementsByTagName( 'iframe' )->item( 0 ) ) {
			$obj->setAttribute( 'width', $args['width'] );
			$obj->setAttribute( 'height', $args['height'] );
			//saveXml instead of SaveHTML for php version compatibility
			$html = $dom_element->saveXML( $obj, LIBXML_NOEMPTYTAG );
		}
	}

	return $html;
}

/**
 * @var $length
 * @return string
 *
 * Reference
 *
 * Strong cryptography in PHP
 * http://www.zimuel.it/en/strong-cryptography-in-php/
 * > Don't use rand() or mt_rand()
 */
function attr_secure_rand( $length ) {
	if ( function_exists( 'openssl_random_pseudo_bytes' ) ) {
		$rnd = openssl_random_pseudo_bytes( $length, $strong );
		if ( $strong ) {
			return $rnd;
		}
	}

	$sha = '';
	$rnd = '';

	if ( file_exists( '/dev/urandom' ) ) {
		$fp = fopen( '/dev/urandom', 'rb' );
		if ( $fp ) {
			if ( function_exists( 'stream_set_read_buffer' ) ) {
				stream_set_read_buffer( $fp, 0 );
			}
			$sha = fread( $fp, $length );
			fclose( $fp );
		}
	}

	for ( $i = 0; $i < $length; $i ++ ) {
		$sha  = hash( 'sha256', $sha . mt_rand() );
		$char = mt_rand( 0, 62 );
		$rnd .= chr( hexdec( $sha[ $char ] . $sha[ $char + 1 ] ) );
	}

	return $rnd;
}

/**
 * Try to make user friendly title from an id
 *
 * @param string $id 'hello-world'
 *
 * @return string 'Hello world'
 */
function attr_id_to_title( $id ) {
	// mb_ucfirst()
	if ( function_exists( 'mb_strtoupper' ) && function_exists( 'mb_substr' ) && function_exists( 'mb_strlen' ) ) {
		$id = mb_strtoupper( mb_substr( $id, 0, 1, 'UTF-8' ), 'UTF-8' ) . mb_substr( $id,
				1,
				mb_strlen( $id, 'UTF-8' ),
				'UTF-8' );
	} else {
		$id = strtoupper( substr( $id, 0, 1 ) ) . substr( $id, 1, strlen( $id ) );
	}

	return str_replace( array( '_', '-' ), ' ', $id );
}

/**
 * Alias
 *
 * @param string $extension_name
 *
 * @return ATTR_Extension|null
 */
function attr_ext( $extension_name ) {
	return attr()->extensions->get( $extension_name );
}

/*
 * Return URI without scheme
 */
function attr_get_url_without_scheme( $url ) {
	return preg_replace( '/^[^:]+:\/\//', '//', $url );
}

/**
 * Try to find file path by its uri and read the file contents
 *
 * @param string $file_uri
 *
 * @return bool|string false or string - the file contents
 */
function attr_read_file_by_uri( $file_uri ) {
	static $base = null;

	if ( $base === null ) {
		$base                     = array();
		$base['dir']              = WP_CONTENT_DIR;
		$base['uri']              = ltrim( content_url(), '/' );
		$base['uri_prefix_regex'] = '/^' . preg_quote( $base['uri'], '/' ) . '/';
	}

	$file_rel_path = preg_replace( $base['uri_prefix_regex'], '', $file_uri );

	if ( $base['uri'] === $file_rel_path ) {
		// the file is not inside base dir
		return false;
	}

	$file_path = $base['dir'] . '/' . $file_rel_path;

	if ( ! file_exists( $file_path ) ) {
		return false;
	}

	return file_get_contents( $file_path );
}

/**
 * Make stylesheet contents (portable) independent of directory location
 * For e.g. replace relative paths 'url(img/bg.png)' with full paths 'url(http://site.com/assets/img/bg.png)'
 *
 * @param string $href 'http://.../style.css'
 * @param null|string $contents If not specified, will try to read from $href
 *
 * @return bool|string false - on failure; string - stylesheet contents
 */
function attr_make_stylesheet_portable( $href, $contents = null ) {
	if ( is_null( $contents ) ) {
		$contents = attr_read_file_by_uri( $href );

		if ( $contents === false ) {
			return false;
		}
	}

	$dir_uri = dirname( $href );

	/**
	 * Replace relative 'url(img/bg.png)'
	 * with full 'url(http://site.com/assets/img/bg.png)'
	 *
	 * Do not touch if url starts with:
	 * - 'https://'
	 * - 'http://'
	 * - '/' (also matches '//')
	 * - '#' (for css property: "behavior: url(#behaveBinObject)")
	 * - 'data:'
	 */
	$contents = preg_replace(
		'/url\s*\((?!\s*[\'"]?(?:\/|data\:|\#|(?:https?:)?\/\/))\s*([\'"])?/',
		'url($1' . $dir_uri . '/',
		$contents
	);

	return $contents;
}

/**
 * Return all images sizes register by add_image_size() merged with
 * WordPress default image sizes.
 * @link https://codex.wordpress.org/Function_Reference/get_intermediate_image_sizes
 *
 * @param string $size
 *
 * @return array|bool
 */
function attr_get_image_sizes( $size = '' ) {
	global $_wp_additional_image_sizes;

	$sizes                        = array();
	$get_intermediate_image_sizes = get_intermediate_image_sizes();

	// Create the full array with sizes and crop info
	foreach ( $get_intermediate_image_sizes as $_size ) {
		if ( in_array( $_size, array( 'thumbnail', 'medium', 'large' ) ) ) {
			$sizes[ $_size ]['width']  = get_option( $_size . '_size_w' );
			$sizes[ $_size ]['height'] = get_option( $_size . '_size_h' );
			$sizes[ $_size ]['crop']   = (bool) get_option( $_size . '_crop' );
		} elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {
			$sizes[ $_size ] = array(
				'width'  => $_wp_additional_image_sizes[ $_size ]['width'],
				'height' => $_wp_additional_image_sizes[ $_size ]['height'],
				'crop'   => $_wp_additional_image_sizes[ $_size ]['crop']
			);
		}
	}

	// Get only 1 size if found
	if ( $size ) {
		if ( isset( $sizes[ $size ] ) ) {
			return $sizes[ $size ];
		} else {
			return false;
		}
	}

	return $sizes;
}

/**
 * @param string $icon A string that is meant to be an icon (an image, a font icon class, or something else)
 * @param array Additional attributes
 *
 * @return string
 */
function attr_string_to_icon_html( $icon, array $attributes = array() ) {
	if (
		preg_match( '/\.(png|jpg|jpeg|gif|svg|webp)$/', $icon )
		||
		preg_match( '/^data:image\//', $icon )
	) {
		// http://.../image.png
		$tag  = 'img';
		$attr = array(
			'src' => $icon,
			'alt' => 'icon',
		);
	} elseif ( preg_match( '/^[a-zA-Z0-9\-_ ]+$/', $icon ) ) {
		// 'font-icon font-icon-class'
		$tag  = 'span';
		$attr = array(
			'class' => trim( $icon ),
		);
	} else {
		// can't detect. maybe it's raw html '<span ...'
		return $icon;
	}

	foreach ( $attributes as $attr_name => $attr_val ) {
		if ( isset( $attr[ $attr_name ] ) ) {
			if ( $attr_name === 'class' ) {
				$attr[ $attr_name ] .= ' ' . $attr_val;
			} else {
				// ignore. do not overwrite already set attributes
			}
		} else {
			$attr[ $attr_name ] = (string) $attr_val;
		}
	}

	return attr_html_tag( $tag, $attr );
}

/**
 * @return string|null
 * @since 2.4.10
 */
function attr_get_json_last_error_message() {
	switch ( function_exists( 'json_last_error' ) ? json_last_error() : - 1 ) {
		case JSON_ERROR_NONE:
			return null; // __('No errors', 'attr');
			break;
		case JSON_ERROR_DEPTH:
			return __( 'Maximum stack depth exceeded', 'attr' );
			break;
		case JSON_ERROR_STATE_MISMATCH:
			return __( 'Underflow or the modes mismatch', 'attr' );
			break;
		case JSON_ERROR_CTRL_CHAR:
			return __( 'Unexpected control character found', 'attr' );
			break;
		case JSON_ERROR_SYNTAX:
			return __( 'Syntax error, malformed JSON', 'attr' );
			break;
		case JSON_ERROR_UTF8:
			return __( 'Malformed UTF-8 characters, possibly incorrectly encoded', 'attr' );
			break;
		default:
			return __( 'Unknown error', 'attr' );
			break;
	}
}

/**
 * Return mime_types by file extension ex : input : array( 'png', 'jpg', 'jpeg' ) => output : array( 'image/jpeg' ).
 *
 * @param array $type
 *
 * @return array
 */
function attr_get_mime_type_by_ext( $type = array() ) {
	$result = array();

	foreach ( wp_get_mime_types() as $key => $mime_type ) {
		$types = explode( '|', $key );
		foreach ( $type as $item ) {
			if ( in_array( $item, $types ) && ! in_array( $mime_type, $result ) ) {
				$result[] = $mime_type;
			}
		}
	}

	return $result;
}

/**
 * Return types from file extensions ex : input array( 'png', 'jpg', 'zip' ) => output : array( 'image', 'archive' ).
 *
 * @see wp_ext2type() function.
 *
 * @param array $ext_array
 *
 * @return array
 */
function attr_multi_ext2type( $ext_array = array() ) {
	$result = array();

	foreach ( $ext_array as $ext ) {
		if ( ! in_array( $type = wp_ext2type( $ext ), $result ) ) {
			$result[] = $type;
		}
	}

	return $result;
}

if ( ! function_exists( 'attr_resize' ) ) {
	function attr_resize( $url, $width = false, $height = false, $crop = false ) {
		$attr_resize = ATTR_Resize::getInstance();
		$response  = $attr_resize->process( $url, $width, $height, $crop );

		return ( ! is_wp_error( $response ) && ! empty( $response['src'] ) ) ? $response['src'] : $url;
	}
}

/**
 * attr_get_path_url( dirname(__FILE__) .'/test.css' ) --> http://site.url/path/to/test.css
 *
 * @param string $path
 *
 * @return string|null
 * @since 2.6.11
 */
function attr_get_path_url( $path ) {
	try {
		$paths_to_urls = ATTR_Cache::get( $cache_key = 'attr:paths_to_urls' );
	} catch ( ATTR_Cache_Not_Found_Exception $e ) {
		$wp_upload_dir = wp_upload_dir();

		$paths_to_urls = array(
			attr_fix_path( WP_PLUGIN_DIR )             => plugins_url(),
			attr_fix_path( get_theme_root() )          => get_theme_root_uri(),
			attr_fix_path( $wp_upload_dir['basedir'] ) => $wp_upload_dir['baseurl'],
		);

		if ( is_multisite() && WPMU_PLUGIN_DIR ) {
			$paths_to_urls[ attr_fix_path( WPMU_PLUGIN_DIR ) ] = WPMU_PLUGIN_URL;
		}

		ATTR_Cache::set( $cache_key, $paths_to_urls );
	}

	$path = attr_fix_path( $path );

	foreach ( $paths_to_urls as $_path => $_url ) {
		if ( preg_match( $regex = '/^' . preg_quote( $_path, '/' ) . '($|\/)/', $path ) ) {
			return $_url . '/' . preg_replace( $regex, '', $path );
		}
	}

	return null;
}

/**
 * @param string|array $callback Callback function
 * @param array $args Callback arguments
 * @param bool $cache Whenever you want to cache the function value after it's first call or not
 * Recommend when the function call may require many resources or time (database requests) , or the value is small
 * Not recommended using on very large values
 *
 * @return ATTR_Callback
 *
 * @since 2.6.14
 */
function attr_callback( $callback, array $args = array(), $cache = true ) {
	return new ATTR_Callback( $callback, $args, $cache );
}

/**
 * In the value is instance of ATTR_Callback class then it is executed and returns the callback value
 * In other case function returns the provided value
 *
 * @param mixed|ATTR_Callback $value
 *
 * @return mixed
 *
 * @since 2.6.14
 */
function attr_call( $value ) {
	if ( ! attr_is_callback( $value ) ) {
		return $value;
	}

	return ( is_object( $value ) && get_class( $value ) == 'Closure' )
		? $value()
		: $value->execute();
}

/**
 * Check is the current value is instance of ATTR_Callback class
 *
 * @param mixed $value
 *
 * @return bool
 */
function attr_is_callback( $value ) {
	return $value instanceof ATTR_Callback || ( is_object( $value ) && get_class( $value ) == 'Closure' );
}

/**
 * Check for command line interface
 *
 * @return bool
 * @since 2.6.16
 */
function attr_is_cli() {
	return ( php_sapi_name() === 'cli' ) && defined( 'WP_CLI' );
}
// backup

function attr_backups_rmdir_recursive($dir) {
	if (is_dir($dir = attr_fix_path($dir))) {
		if ($files = array_diff(($files = scandir($dir)) ? $files : array(), array('.', '..'))) {
			foreach ( $files as $file ) {
				$file = $dir .'/'. $file;

				if ( is_dir( $file ) ) {
					if ( ! attr_backups_rmdir_recursive( $file ) ) {
						return false;
					}
				} else {
					if ( ! unlink( $file ) ) {
						return false;
					}
				}
			}
		}

		if ( ! rmdir($dir) ) {
			return false;
		}

		return true;
	}

	return false;
}


function attr_backups_is_dir_empty($dir) {
	if (!is_readable($dir)) {
		return null;
	}

	if (false === ($handle = opendir($dir))) {
		return null;
	}

	while (false !== ($entry = readdir($handle))) {
		if ($entry !== '.' && $entry !== '..') {
			return false;
		}
	}

	return true;
}

function attr_backups_copy_dir_recursive($source_dir, $destination_dir) {
	$source_dir = attr_fix_path($source_dir);
	$destination_dir = attr_fix_path($destination_dir);

	$dir_chmod = 0755;

	if (!file_exists($destination_dir)) {
		if (!mkdir($destination_dir, $dir_chmod)) {
			return new WP_Error(
				'mkdir_fail',
				sprintf(__('Failed to create dir: %s'), $destination_dir)
			);
		}
	}

	try {
		foreach (
			$iterator = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator($source_dir),
				RecursiveIteratorIterator::SELF_FIRST
			) as $item 
		) {
			if (in_array(basename($iterator->getSubPathName()), array('.', '..'), true)) {
				continue;
			}

			$destination_path = $destination_dir . '/' . $iterator->getSubPathName();

			if ($item->isDir()) {
				if (!mkdir($destination_path, $dir_chmod)) {
					return new WP_Error(
						'mk_sub_dir_fail',
						sprintf(__('Failed to create dir: %s'), $destination_path)
					);
				}
			} else {
				if (!copy($item->getPathname(), $destination_path)) {
					return new WP_Error(
						'copy_fail',
						sprintf(__('Failed to copy: %s'), $destination_path)
					);
				}
			}
		}
	} catch (UnexpectedValueException $e) {
		return new WP_Error(
			'dir_copy_fail',
			(string)$e->getMessage()
		);
	}

	return true;
}

/**
 * If current user is allowed to make full backup or restore
 * This method must be used before calling $backups->tasks()->do_backup|restore()
 * to not allow simple admins to make full backup|restore on multisite and affect all sites.
 *
 * $backups->tasks()->do_backup|restore() Can't do that check
 * because those methods are also called in cron, when the user is not logged in
 */
function attr_backups_current_user_can_full() {
	if ( is_multisite() ) {
		return is_main_site() &&
		       attr_current_user_can( array( 'manage_network_plugins' ), false ) &&
		       attr_current_user_can( array( 'manage_network_themes' ), false );
	} else {
		return attr_current_user_can( array( 'install_plugins' ), false ) &&
		       attr_current_user_can( array( 'install_themes' ), false );
	}
}

function attr_get_timeout($sum = 0) {
	$timeout = (int)ini_get('max_execution_time');

	return max($timeout + $sum, 1); 
}

function attr_backups_unzip_partial($zip, $destination_dir, $last_entry = '', $timeout = 0) {
	if (!function_exists('zip_open')) {
		return new WP_Error(
			'zip_ext_missing', __('Zip extension missing', 'attr')
		);
	}

	if (!is_resource($zip) && !is_resource($zip = zip_open($zip))) {
		return new WP_Error(
			'cannot_open_zip', sprintf(__('Cannot open zip (Error code: %s)', 'attr'), $zip)
		);
	}

	if ($timeout <= 0) {
		$timeout = 2635455;
	}

	if ($last_entry) {
		while(
			($entry = zip_read($zip))
			&&
			zip_entry_name($entry) !== $last_entry
		);

		if (!$entry) {
			zip_close($zip);
			return new WP_Error(
				'entry_restore_fail',
				sprintf(__('Cannot restore previous zip entry: %s', 'attr'), $last_entry)
			);
		}
	}

	$result = array(
		'finished' => false,
		'last_entry' => $last_entry,
		'extracted_files' => 0,
	);

	$max_time = time() + $timeout;

	while (time() < $max_time) {
		if (!($entry = zip_read($zip))) {
			$result['finished'] = true;
			return $result;
		}

		$name = zip_entry_name($entry);

		if (substr($name, -1) === '/') {
			continue; // it is a directory
		}

		$destination_path = $destination_dir .'/'. $name;

		if (
			!file_exists($dest_dir = dirname($destination_path))
			&&
			!mkdir($dest_dir, 0777, true)
		) {
			zip_close($zip);
			return new WP_Error(
				'mkdir_fail',
				sprintf(__('Cannot create directory: %s', 'attr'), $dest_dir)
			);
		}

		if (false === ($unzipped = fopen($destination_path, 'wb'))) {
			zip_close($zip);
			return new WP_Error(
				'fopen_fail',
				sprintf(__('Cannot create file: %s', 'attr'), $destination_path)
			);
		}

		$size = zip_entry_filesize($entry);

		while ($size > 0) {
			$chunk_size = min($size, 10240);
			$size -= $chunk_size;

			if (false === ($chunk = zip_entry_read($entry, $chunk_size))) {
				fclose($unzipped);
				zip_close($zip);
				return new WP_Error(
					'zip_entry_read_fail',
					sprintf(__('Cannot read chunk from zip entry: %s', 'attr'), $name)
				);
			} else {
				fwrite($unzipped, $chunk);
			}
		}

		if (false === fclose($unzipped)) {
			zip_close($zip);
			return new WP_Error(
				'fclose_fail',
				sprintf(__('Cannot close file: %s', 'attr'), $destination_path)
			);
		}

		$result['last_entry'] = $name;
		++$result['extracted_files'];
	}

	return $result;
}

function attr_backups_destination_directory() {
	$uploads = wp_upload_dir();

	return attr_fix_path( $uploads['basedir'] ) . '/attr-backup';
}
