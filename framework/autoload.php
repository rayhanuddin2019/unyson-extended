<?php if ( ! defined( 'ATTR' ) ) {
	die( 'Forbidden' );
}

spl_autoload_register( '_attr_core_autoload' );
function _attr_core_autoload( $class ) {
	switch ( $class ) {
		case 'ATTR_Manifest' :
		case 'ATTR_Framework_Manifest' :
		case 'ATTR_Theme_Manifest' :
		
			require_once dirname( __FILE__ ) . '/core/class-attr-manifest.php';
			break;
	}
}

spl_autoload_register( '_attr_core_components_autoload' );
function _attr_core_components_autoload( $class ) {
	switch ( $class ) {
		case '_ATTR_Component_Backend' :
			require_once dirname( __FILE__ ) . '/core/components/backend.php';
			break;
	
		case '_ATTR_Component_Theme' :
			require_once dirname( __FILE__ ) . '/core/components/theme.php';
			break;
		case 'ATTR_Settings_Form_Theme' :
			require_once dirname( __FILE__ ) . '/core/components/backend/class-attr-settings-form-theme.php';
			break;
	}
}


spl_autoload_register( '_attr_core_extends_autoload' );
function _attr_core_extends_autoload( $class ) {
	switch ( $class ) {
		case 'ATTR_Container_Type' :
			require_once dirname( __FILE__ ) . '/core/extends/class-attr-container-type.php';
			break;
		case 'ATTR_Option_Type' :
			require_once dirname( __FILE__ ) . '/core/extends/class-attr-option-type.php';
			break;
	
		case 'ATTR_Option_Handler' :
			require_once dirname( __FILE__ ) . '/core/extends/interface-attr-option-handler.php';
			break;
	}
}

spl_autoload_register( '_attr_code_exceptions_autoload' );
function _attr_code_exceptions_autoload( $class ) {
	switch ( $class ) {
		case 'ATTR_Option_Type_Exception' :
		case 'ATTR_Option_Type_Exception_Not_Found' :
		case 'ATTR_Option_Type_Exception_Invalid_Class' :
		case 'ATTR_Option_Type_Exception_Already_Registered' :
			require_once dirname( __FILE__ ) . '/core/exceptions/class-attr-option-type-exception.php';
			break;
	}
}

// Autoload helper classes
function _attr_autoload_helper_classes($class) {
	static $class_to_file = array(
	
		'ATTR_Cache' => 'class-attr-cache',
		'ATTR_Callback' => 'class-attr-callback',
		'ATTR_Access_Key' => 'class-attr-access-key',
		'ATTR_WP_Filesystem' => 'class-attr-wp-filesystem',
		'ATTR_Form' => 'class-attr-form',
		'ATTR_Form_Not_Found_Exception' => 'exceptions/class-attr-form-not-found-exception',
		'ATTR_Form_Invalid_Submission_Exception' => 'exceptions/class-attr-form-invalid-submission-exception',
		'ATTR_Settings_Form' => 'class-attr-settings-form',
		'ATTR_Request' => 'class-attr-request',
		'ATTR_Session' => 'class-attr-session',
		'ATTR_WP_Option' => 'class-attr-wp-option',
		'ATTR_WP_Meta' => 'class-attr-wp-meta',
		'ATTR_Db_Options_Model' => 'class-attr-db-options-model',
		'ATTR_Flash_Messages' => 'class-attr-flash-messages',
		'ATTR_Resize' => 'class-attr-resize',
		'ATTR_Type' => 'type/class-attr-type',
		'ATTR_Type_Register' => 'type/class-attr-type-register',
	);

	if (isset($class_to_file[$class])) {
		require dirname(__FILE__) .'/helpers/'. $class_to_file[$class] .'.php';
	}
}
spl_autoload_register('_attr_autoload_helper_classes');

spl_autoload_register( '_attr_includes_container_types_autoload' );
function _attr_includes_container_types_autoload( $class ) {
	switch ( $class ) {
		case 'ATTR_Container_Type_Undefined' :
			require_once dirname( __FILE__ ) . '/includes/container-types/class-attr-container-type-undefined.php';
			break;
		case 'ATTR_Container_Type_Group' :
			require_once dirname( __FILE__ ) . '/includes/container-types/simple.php';
			break;
		case 'ATTR_Container_Type_Box' :
			require_once dirname( __FILE__ ) . '/includes/container-types/box/class-attr-container-type-box.php';
			break;
		case 'ATTR_Container_Type_Popup' :
			require_once dirname( __FILE__ ) . '/includes/container-types/popup/class-attr-container-type-popup.php';
			break;
		case 'ATTR_Container_Type_Tab' :
			require_once dirname( __FILE__ ) . '/includes/container-types/tab/class-attr-container-type-tab.php';
			break;
	}
}

spl_autoload_register( '_attr_includes_customizer_autoload' );
function _attr_includes_customizer_autoload( $class ) {
	switch ( $class ) {
		case '_ATTR_Customizer_Control_Option_Wrapper' :
			require_once dirname( __FILE__ ) . '/includes/customizer/class--attr-customizer-control-option-wrapper.php';
			break;
		case '_ATTR_Customizer_Setting_Option' :
			require_once dirname( __FILE__ ) . '/includes/customizer/class--attr-customizer-setting-option.php';
			break;
	}
}

spl_autoload_register( '_attr_includes_option_storage_autoload' );
function _attr_includes_option_storage_autoload( $class ) {
	switch ( $class ) {
		case '_ATTR_Option_Storage_Type_Register' :
			require_once dirname( __FILE__ ) . '/includes/option-storage/class--attr-option-storage-type-register.php';
			break;
		case 'ATTR_Option_Storage_Type' :
			require_once dirname( __FILE__ ) . '/includes/option-storage/class-attr-option-storage-type.php';
			break;
		case 'ATTR_Option_Storage_Type_Post_Meta' :
			require_once dirname( __FILE__ ) . '/includes/option-storage/type/class-attr-option-storage-type-post-meta.php';
			break;
		case 'ATTR_Option_Storage_Type_Term_Meta' :
			require_once dirname( __FILE__ ) . '/includes/option-storage/type/class-attr-option-storage-type-term-meta.php';
			break;
		case 'ATTR_Option_Storage_Type_WP_Option' :
			require_once dirname( __FILE__ ) . '/includes/option-storage/type/class-attr-option-storage-type-wp-option.php';
			break;
	}
}

spl_autoload_register( '_attr_includes_option_types_autoload' );
function _attr_includes_option_types_autoload( $class ) {
	switch ( $class ) {
		case 'ATTR_Option_Type_Undefined' :
			require_once dirname( __FILE__ ) . '/includes/option-types/class-attr-option-type-undefined.php';
			break;
		case 'ATTR_Option_Type_Hidden' :
		case 'ATTR_Option_Type_Text' :
		case 'ATTR_Option_Type_Short_Text' :
		case 'ATTR_Option_Type_Password' :
		case 'ATTR_Option_Type_Textarea' :
		case 'ATTR_Option_Type_Html' :
		case 'ATTR_Option_Type_Html_Fixed' :
		case 'ATTR_Option_Type_Html_Full' :
		case 'ATTR_Option_Type_Checkbox' :
		case 'ATTR_Option_Type_Checkboxes' :
		case 'ATTR_Option_Type_Radio' :
		case 'ATTR_Option_Type_Select' :
		case 'ATTR_Option_Type_Short_Select' :
		case 'ATTR_Option_Type_Select_Multiple' :
		case 'ATTR_Option_Type_Unique' :
		case 'ATTR_Option_Type_GMap_Key' :
			require_once dirname( __FILE__ ) . '/includes/option-types/simple.php';
			break;
		case 'ATTR_Option_Type_Addable_Box' :
			require_once dirname( __FILE__ ) . '/includes/option-types/addable-box/class-attr-option-type-addable-box.php';
			break;
		case 'ATTR_Option_Type_Addable_Popup' :
		case 'ATTR_Option_Type_Addable_Popup_Full' :
			require_once dirname( __FILE__ ) . '/includes/option-types/addable-popup/class-attr-option-type-addable-popup.php';
			break;
		case 'ATTR_Option_Type_Addable_Option' :
			require_once dirname( __FILE__ ) . '/includes/option-types/addable-option/class-attr-option-type-addable-option.php';
			break;
		case 'ATTR_Option_Type_Background_Image' :
			require_once dirname( __FILE__ ) . '/includes/option-types/background-image/class-attr-option-type-background-image.php';
			break;
		case 'ATTR_Option_Type_Color_Picker' :
			require_once dirname( __FILE__ ) . '/includes/option-types/color-picker/class-attr-option-type-color-picker.php';
			break;
		case 'ATTR_Option_Type_Date_Picker' :
			require_once dirname( __FILE__ ) . '/includes/option-types/date-picker/class-attr-option-type-wp-date-picker.php';
			break;
		case 'ATTR_Option_Type_Datetime_Picker' :
			require_once dirname( __FILE__ ) . '/includes/option-types/datetime-picker/class-attr-option-type-datetime-picker.php';
			break;
		case 'ATTR_Option_Type_Datetime_Range' :
			require_once dirname( __FILE__ ) . '/includes/option-types/datetime-range/class-attr-option-type-datetime-range.php';
			break;
		case 'ATTR_Option_Type_Gradient' :
			require_once dirname( __FILE__ ) . '/includes/option-types/gradient/class-attr-option-type-gradient.php';
			break;
		case 'ATTR_Option_Type_Icon' :
			require_once dirname( __FILE__ ) . '/includes/option-types/icon/class-attr-option-type-icon.php';
			break;
		case 'ATTR_Option_Type_Icon_v2' :
			require_once dirname( __FILE__ ) . '/includes/option-types/icon-v2/class-attr-option-type-icon-v2.php';
			break;
		case 'ATTR_Option_Type_Image_Picker' :
			require_once dirname( __FILE__ ) . '/includes/option-types/image-picker/class-attr-option-type-image-picker.php';
			break;
		case 'ATTR_Option_Type_Map' :
			require_once dirname( __FILE__ ) . '/includes/option-types/map/class-attr-option-type-map.php';
			break;
		case 'ATTR_Option_Type_Multi' :
			require_once dirname( __FILE__ ) . '/includes/option-types/multi/class-attr-option-type-multi.php';
			break;
		case 'ATTR_Option_Type_Multi_Picker' :
			require_once dirname( __FILE__ ) . '/includes/option-types/multi-picker/class-attr-option-type-multi-picker.php';
			break;
		case 'ATTR_Option_Type_Multi_Select' :
			require_once dirname( __FILE__ ) . '/includes/option-types/multi-select/class-attr-option-type-multi-select.php';
			break;
		case 'ATTR_Option_Type_Multi_Upload' :
			require_once dirname( __FILE__ ) . '/includes/option-types/multi-upload/class-attr-option-type-multi-upload.php';
			break;
		case 'ATTR_Option_Type_Oembed' :
			require_once dirname( __FILE__ ) . '/includes/option-types/oembed/class-attr-option-type-oembed.php';
			break;
		case 'ATTR_Option_Type_Popup' :
			require_once dirname( __FILE__ ) . '/includes/option-types/popup/class-attr-option-type-popup.php';
			break;
		case 'ATTR_Option_Type_Radio_Text' :
			require_once dirname( __FILE__ ) . '/includes/option-types/radio-text/class-attr-option-type-radio-text.php';
			break;
		case 'ATTR_Option_Type_Range_Slider' :
			require_once dirname( __FILE__ ) . '/includes/option-types/range-slider/class-attr-option-type-range-slider.php';
			break;
		case 'ATTR_Option_Type_Rgba_Color_Picker' :
			require_once dirname( __FILE__ ) . '/includes/option-types/rgba-color-picker/class-attr-option-type-rgba-color-picker.php';
			break;
		case 'ATTR_Option_Type_Slider' :
			require_once dirname( __FILE__ ) . '/includes/option-types/slider/class-attr-option-type-slider.php';
			break;
		case 'ATTR_Option_Type_Slider_Short' :
			require_once dirname( __FILE__ ) . '/includes/option-types/slider/class-attr-option-type-short-slider.php';
			break;
		case 'ATTR_Option_Type_Switch' :
			require_once dirname( __FILE__ ) . '/includes/option-types/switch/class-attr-option-type-switch.php';
			break;
		case 'ATTR_Option_Type_Typography' :
			require_once dirname( __FILE__ ) . '/includes/option-types/typography/class-attr-option-type-typography.php';
			break;
		case 'ATTR_Option_Type_Typography_v2' :
			require_once dirname( __FILE__ ) . '/includes/option-types/typography-v2/class-attr-option-type-typography-v2.php';
			break;
		case 'ATTR_Option_Type_Upload' :
			require_once dirname( __FILE__ ) . '/includes/option-types/upload/class-attr-option-type-upload.php';
			break;
		case 'ATTR_Option_Type_Wp_Editor' :
			require_once dirname( __FILE__ ) . '/includes/option-types/wp-editor/class-attr-option-type-wp-editor.php';
			break;
		case 'ATTR_Icon_V2_Favorites_Manager' :
			require_once dirname( __FILE__ ) . '/includes/option-types/icon-v2/includes/class-attr-icon-v2-favorites.php';
			break;
		case 'ATTR_Icon_V2_Packs_Loader' :
			require_once dirname( __FILE__ ) . '/includes/option-types/icon-v2/includes/class-attr-icon-v2-packs-loader.php';
			break;
	}
}

spl_autoload_register( '_attr_includes_backup_autoload' );
function _attr_includes_backup_autoload( $class ) {
	switch ( $class ) {
		case 'Attr_Import_Task' :
			require_once dirname( __FILE__ ) . '/backup/core/class-import-task.php';
			break;
		case 'Attr_Backups_Task' :
			require_once dirname( __FILE__ ) . '/backup/core/class-backup-task.php';
			break;
		case 'Attr_Export_Task' :
			require_once dirname( __FILE__ ) . '/backup/core/class-export-task.php';
			break;
		case 'Attr_backup_demo_page' :
			require_once dirname( __FILE__ ) . '/backup/core/class-backup-demo.php';
			break;
		case 'Attr_Backup_List_Table' :
			require_once dirname( __FILE__ ) . '/backup/core/class-backup-list-table.php';
			break;		
	
	}
}