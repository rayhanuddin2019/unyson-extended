<?php if (!defined('ATTR')) die('Forbidden');

/**
 * Main framework class that contains everything
 *
 * Convention: All public properties should be only instances of the components (except special property: manifest)
 */
final class _Attr
{
	/** @var bool If already loaded */
	private static $loaded = false;

	/** @var ATTR_Framework_Manifest */
	public $manifest;

	

	/** @var _ATTR_Component_Backend */
	public $backend;

	/** @var _ATTR_Component_Theme */
	public $theme;

	public function __construct()
	{
		if (self::$loaded) {
			trigger_error('Framework already loaded', E_USER_ERROR);
		} else {
			self::$loaded = true;
		}

		$attr_dir = attr_get_framework_directory();

		// manifest
		{
			require $attr_dir .'/manifest.php';
			/** @var array $manifest */

			$this->manifest = new ATTR_Framework_Manifest($manifest);

			add_action('attr_init', array($this, '_check_requirements'), 1);
		}

		// components
		{
		
			$this->backend = new _ATTR_Component_Backend();
			$this->theme = new _ATTR_Component_Theme();
		}
	}

	/**
	 * @internal
	 */
	public function _check_requirements()
	{
		if (is_admin() && !$this->manifest->check_requirements()) {
			ATTR_Flash_Messages::add(
				'attr_requirements',
				__('Framework requirements not met:', 'attr') .' '. $this->manifest->get_not_met_requirement_text(),
				'warning'
			);
		}
	}
}

/**
 * @return _ATTR Framework instance
 */
function attr() {
	static $ATTR = null; // cache

	if ($ATTR === null) {
		$ATTR = new _Attr();
	}

	return $ATTR;
}


