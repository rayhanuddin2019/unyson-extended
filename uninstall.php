<?php if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) die('Forbidden');



function _include_file_isolated($path) {
	include $path;
}

class ATTR_Plugin_Uninstall
{

	private $extensions = array();

	public function __construct()
	{
		$this->read_extensions(
			dirname(__FILE__) .'/framework/extensions',
			$this->extensions
		);

		{
			/** @var wpdb $wpdb */
			global $wpdb;

			$this->uninstall();

			if ( is_multisite() ) { // http://wordpress.stackexchange.com/a/80351/60424
				$original_blog_id = get_current_blog_id();

				foreach (
					$wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" )
					as $blog_id
				) {
					switch_to_blog( $blog_id );

					$this->uninstall();
				}

				switch_to_blog( $original_blog_id );
			}
		}
	}

	private function read_extensions($dir, &$extensions)
	{
		$ext_dirs = glob($dir .'/*', GLOB_ONLYDIR);

		if (empty($ext_dirs)) {
			return;
		}

		foreach ($ext_dirs as $ext_dir) {
			if (
				file_exists($ext_dir .'/manifest.php')
				&&
				file_exists($ext_dir .'/uninstall.php')
			) {
				$extensions[ basename($ext_dir) ] = $ext_dir .'/uninstall.php';
			}

			$this->read_extensions($ext_dir .'/extensions', $extensions);
		}
	}

	private function uninstall()
	{
		// Remove framework data
		{
			// ...
		}

		// Remove extensions data
		foreach ($this->extensions as $uninstall_file) {
			_include_file_isolated($uninstall_file);
		}
	}
}

new ATTR_Plugin_Uninstall();
