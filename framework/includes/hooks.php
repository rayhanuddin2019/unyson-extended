<?php if ( ! defined( 'ATTR' ) ) {
	die( 'Forbidden' );
}
/**
 * Filters and Actions
 */

/**
 * Option types
 */
{
	/**
	 * @internal
	 */
	function _action_attr_init_option_types() {
		ATTR_Option_Type::register( 'ATTR_Option_Type_Hidden' );
		ATTR_Option_Type::register( 'ATTR_Option_Type_Text' );
		ATTR_Option_Type::register( 'ATTR_Option_Type_Short_Text' );
		ATTR_Option_Type::register( 'ATTR_Option_Type_Password' );
		ATTR_Option_Type::register( 'ATTR_Option_Type_Textarea' );
		ATTR_Option_Type::register( 'ATTR_Option_Type_Html' );
		ATTR_Option_Type::register( 'ATTR_Option_Type_Html_Fixed' );
		ATTR_Option_Type::register( 'ATTR_Option_Type_Html_Full' );
		ATTR_Option_Type::register( 'ATTR_Option_Type_Checkbox' );
		ATTR_Option_Type::register( 'ATTR_Option_Type_Checkboxes' );
		ATTR_Option_Type::register( 'ATTR_Option_Type_Radio' );
		ATTR_Option_Type::register( 'ATTR_Option_Type_Select' );
		ATTR_Option_Type::register( 'ATTR_Option_Type_Short_Select' );
		ATTR_Option_Type::register( 'ATTR_Option_Type_Select_Multiple' );
		ATTR_Option_Type::register( 'ATTR_Option_Type_Unique' );
		ATTR_Option_Type::register( 'ATTR_Option_Type_GMap_Key' );
		ATTR_Option_Type::register( 'ATTR_Option_Type_Addable_Box' );
		ATTR_Option_Type::register( 'ATTR_Option_Type_Addable_Option' );
		ATTR_Option_Type::register( 'ATTR_Option_Type_Addable_Popup' );
		ATTR_Option_Type::register( 'ATTR_Option_Type_Addable_Popup_Full' );
		ATTR_Option_Type::register( 'ATTR_Option_Type_Background_Image' );
		ATTR_Option_Type::register( 'ATTR_Option_Type_Color_Picker' );
		ATTR_Option_Type::register( 'ATTR_Option_Type_Date_Picker' );
		ATTR_Option_Type::register( 'ATTR_Option_Type_Datetime_Picker' );
		ATTR_Option_Type::register( 'ATTR_Option_Type_Datetime_Range' );
		ATTR_Option_Type::register( 'ATTR_Option_Type_Gradient' );
		ATTR_Option_Type::register( 'ATTR_Option_Type_Icon' );
		ATTR_Option_Type::register( 'ATTR_Option_Type_Image_Picker' );
		ATTR_Option_Type::register( 'ATTR_Option_Type_Map' );
		ATTR_Option_Type::register( 'ATTR_Option_Type_Multi' );
		ATTR_Option_Type::register( 'ATTR_Option_Type_Multi_Picker' );
		ATTR_Option_Type::register( 'ATTR_Option_Type_Multi_Upload' );
		ATTR_Option_Type::register( 'ATTR_Option_Type_Popup' );
		ATTR_Option_Type::register( 'ATTR_Option_Type_Radio_Text' );
		ATTR_Option_Type::register( 'ATTR_Option_Type_Range_Slider' );
		ATTR_Option_Type::register( 'ATTR_Option_Type_Rgba_Color_Picker' );
		ATTR_Option_Type::register( 'ATTR_Option_Type_Slider' );
		ATTR_Option_Type::register( 'ATTR_Option_Type_Slider_Short' );
		ATTR_Option_Type::register( 'ATTR_Option_Type_Switch' );
		ATTR_Option_Type::register( 'ATTR_Option_Type_Typography' );
		ATTR_Option_Type::register( 'ATTR_Option_Type_Typography_v2' );
		ATTR_Option_Type::register( 'ATTR_Option_Type_Upload' );
		ATTR_Option_Type::register( 'ATTR_Option_Type_Wp_Editor' );

		{
			$favorites = new ATTR_Icon_V2_Favorites_Manager();
			$favorites->attach_ajax_actions();

			ATTR_Option_Type::register( 'ATTR_Option_Type_Icon_v2' );
		}

		{
			ATTR_Option_Type::register( 'ATTR_Option_Type_Multi_Select' );
		}

		{
			ATTR_Option_Type::register( 'ATTR_Option_Type_Oembed' );
		}
	}

	add_action( 'attr_option_types_init', '_action_attr_init_option_types' );

	/**
	 * Some option-types have add_action('wp_ajax_...')
	 * so init all option-types if current request is ajax
	 * @since 2.6.1
	 */
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		function _action_attr_init_option_types_on_ajax() {
			foreach (attr()->backend->get_option_types() as $type) {
				attr()->backend->option_type($type);
			}
		}

		add_action( 'attr_init', '_action_attr_init_option_types_on_ajax' );
	}

	/**
	 * Prevent Fatal Error if someone is registering option-types in old way (right away)
	 * not in 'attr_option_types_init' action
	 *
	 * @param string $class
	 */
	function _attr_autoload_option_types( $class ) {
		if ( 'ATTR_Option_Type' === $class ) {
			if ( is_admin() && defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				ATTR_Flash_Messages::add(
					'option-type-register-wrong',
					__( "Please register option-types on 'attr_option_types_init' action", 'attr' ),
					'warning'
				);
			}
		} elseif ( 'ATTR_Container_Type' === $class ) {
			if ( is_admin() && defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				ATTR_Flash_Messages::add(
					'container-type-register-wrong',
					__( "Please register container-types on 'attr_container_types_init' action", 'attr' ),
					'warning'
				);
			}
		}
	}

	spl_autoload_register( '_attr_autoload_option_types' );
}

/**
 * Container types
 */
{
	/**
	 * @internal
	 */
	function _action_attr_init_container_types() {
		ATTR_Container_Type::register( 'ATTR_Container_Type_Group' );
		ATTR_Container_Type::register( 'ATTR_Container_Type_Box' );
		ATTR_Container_Type::register( 'ATTR_Container_Type_Popup' );
		ATTR_Container_Type::register( 'ATTR_Container_Type_Tab' );
	}

	add_action( 'attr_container_types_init', '_action_attr_init_container_types' );
}

/**
 * Custom Github API service
 * Provides the same responses but is "unlimited"
 * To prevent error: Github API rate limit exceeded 60 requests per hour
 *
 * @internal
 */
function _attr_filter_github_api_url( $url ) {
	return 'https://github-api-cache.themewinter.io';
}

add_filter( 'attr_github_api_url', '_attr_filter_github_api_url' );

/**
 * Javascript events related to tinymce init
 * @since 2.6.0
 */
{
	add_action( 'wp_tiny_mce_init', '_attr_action_tiny_mce_init' );
	function _attr_action_tiny_mce_init( $mce_settings ) {
		?>
		<script type="text/javascript">
			if (typeof attrEvents != 'undefined') {
				attrEvents.trigger('attr:tinymce:init:before');
			}
		</script>
		<?php
	}

	add_action( 'after_wp_tiny_mce', '_attr_action_after_wp_tiny_mce' );
	function _attr_action_after_wp_tiny_mce( $mce_settings ) {
		?>
		<script type="text/javascript">
			if (typeof attrEvents != 'undefined') {
				attrEvents.trigger('attr:tinymce:init:after');
			}
		</script>
		<?php
	}
}

// ATTR_Form hooks
{
	if ( is_admin() ) {
		/**
		 * Display form errors in admin side
		 * @internal
		 */
		function _action_attr_form_show_errors_in_admin() {
			$form = ATTR_Form::get_submitted();

			if ( ! $form || $form->is_valid() ) {
				return;
			}

			foreach ( $form->get_errors() as $input_name => $error_message ) {
				ATTR_Flash_Messages::add( 'attr-form-admin-' . $input_name, $error_message, 'error' );
			}
		}

		add_action( 'wp_loaded', '_action_attr_form_show_errors_in_admin', 111 );
	} else {
		/**
		 * to disable this use remove_action('wp_print_styles', '_action_attr_form_frontend_default_styles');
		 * @internal
		 */
		function _action_attr_form_frontend_default_styles() {
			$form = ATTR_Form::get_submitted();

			if ( ! $form || $form->is_valid() ) {
				return;
			}

			echo '<style type="text/css">.attr-form-errors { color: #bf0000; }</style>';
		}

		add_action( 'wp_print_styles', '_action_attr_form_frontend_default_styles' );
	}
}

// ATTR_Flash_Messages hooks
{
	if ( is_admin() ) {
		/**
		 * Start the session before the content is sent to prevent the "headers already sent" warning
		 * @internal
		 */
		function _action_attr_flash_message_backend_prepare() {
			if ( apply_filters( 'attr_use_sessions', true ) && ! session_id()  ) {
				session_start();
			}
		}

		add_action( 'current_screen', '_action_attr_flash_message_backend_prepare', 9999 );

		/**
		 * Display flash messages in backend as notices
		 */
		add_action( 'admin_notices', array( 'ATTR_Flash_Messages', '_print_backend' ) );
	} else {
		/**
		 * Start the session before the content is sent to prevent the "headers already sent" warning
		 * @internal
		 */
		function _action_attr_flash_message_frontend_prepare() {
			if (
			    apply_filters( 'attr_use_sessions', true )
                &&
				/**
				 * In ajax it's not possible to call flash message after headers were sent,
				 * so there will be no "headers already sent" warning.
				 * Also in the Backups extension, are made many internal ajax request,
				 * each creating a new independent request that don't remember/use session cookie from previous request,
				 * thus on server side are created many (not used) new sessions.
				 */
				! ( defined( 'DOING_AJAX' ) && DOING_AJAX )
				&&
				! session_id()
			) {
				session_start();
			}
		}

		add_action( 'send_headers', '_action_attr_flash_message_frontend_prepare', 9999 );

		/**
		 * Print flash messages in frontend if this has not been done from theme
		 */
		function _action_attr_flash_message_frontend_print() {
			if ( ATTR_Flash_Messages::_frontend_printed() ) {
				return;
			}

			if ( ! ATTR_Flash_Messages::_print_frontend() ) {
				return;
			}

			?>
			<script type="text/javascript">
				(function () {
					if (typeof jQuery === "undefined") {
						return;
					}

					jQuery(function ($) {
						var $container;

						// Try to find the content element
						{
							var selector, selectors = [
								'#main #content',
								'#content #main',
								'#main',
								'#content',
								'#content-container',
								'#container',
								'.container:first'
							];

							while (selector = selectors.shift()) {
								$container = $(selector);

								if ($container.length) {
									break;
								}
							}
						}

						if (!$container.length) {
							// Try to find main page H1 container
							$container = $('h1:first').parent();
						}

						if (!$container.length) {
							// If nothing found, just add to body
							$container = $(document.body);
						}

						$(".attr-flash-messages").prependTo($container);
					});
				})();
			</script>
			<style type="text/css">
				.attr-flash-messages .attr-flash-type-error {
					color: #f00;
				}

				.attr-flash-messages .attr-flash-type-warning {
					color: #f70;
				}

				.attr-flash-messages .attr-flash-type-success {
					color: #070;
				}

				.attr-flash-messages .attr-flash-type-info {
					color: #07f;
				}
			</style>
			<?php
		}

		add_action( 'wp_footer', '_action_attr_flash_message_frontend_print', 9999 );
	}
}

// ATTR_Resize hooks
{
	if ( ! function_exists( 'attr_delete_resized_thumbnails' ) ) {
		function attr_delete_resized_thumbnails( $id ) {
			$images = wp_get_attachment_metadata( $id );
			if ( ! empty( $images['resizes'] ) ) {
				$uploads_dir = wp_upload_dir();
				foreach ( $images['resizes'] as $image ) {
					$file = $uploads_dir['basedir'] . '/' . $image;
					@unlink( $file );
				}
			}
		}

		add_action( 'delete_attachment', 'attr_delete_resized_thumbnails' );
	}
}

//WPML Hooks
{
	if ( is_admin() ) {
		add_action( 'icl_save_term_translation', '_attr_action_wpml_duplicate_term_options', 20, 2 );
		function _attr_action_wpml_duplicate_term_options( $original, $translated ) {
			$original_options = attr_get_db_term_option(
				attr_akg( 'term_id', $original ),
				attr_akg( 'taxonomy', $original )
			);

			if ( $original_options !== null ) {
				attr_set_db_term_option(
					attr_akg( 'term_id', $translated ),
					attr_akg( 'taxonomy', $original ),
					null,
					$original_options
				);
			}
		}
	}
}