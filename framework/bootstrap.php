<?php if (!defined('ABSPATH')) die('Forbidden');

if ( defined( 'WP_CLI' ) && WP_CLI && ! isset( $_SERVER['HTTP_HOST'] ) ) {
	$_SERVER['HTTP_HOST'] = 'xpeedstudio.com';
	$_SERVER['SERVER_NAME'] = 'Attr';
	$_SERVER['SERVER_PORT'] = '80';
}

if (defined('ATTR')) {
	/**
	 * The framework is already loaded.
	 */
} else {
	define('ATTR', true);
	add_action('after_setup_theme', '_action_init_framework');

	function _action_init_framework() {
		if (did_action('attr_init')) {
			return;
		}

		do_action('attr_before_init');

		$dir = dirname(__FILE__);

		require $dir .'/autoload.php';

		// Load helper functions
		foreach (array('general', 'meta', 'attr-storage', 'database') as $file) {
			require $dir .'/helpers/'. $file .'.php';
		}

		// Load core
		{
			require $dir .'/core/Attr.php';

			attr();
		}

		// Load backup
		{
			require $dir .'/backup/init.php';

			attr();
		}

		require $dir .'/includes/hooks.php';

		/**
		 * Init components
		 */
		{
			$components = array(
				'theme',
				'backend'
			);

			foreach ($components as $component) {
				attr()->{$component}->_init();
			}

			foreach ($components as $component) {
				attr()->{$component}->_after_components_init();
			}
		}

		do_action('attr_init');
	}
}


