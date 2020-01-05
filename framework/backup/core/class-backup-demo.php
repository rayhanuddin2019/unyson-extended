<?php 

Class Attr_backup_demo_page {

    public function __construct(){
        
    }

    public function _admin_action_admin_menu() {
        
		if (
			!current_user_can('manage_options')
			
		) {
			return;
		}
      
		add_management_page(
			__('Demo Content Install', 'attr'),
			__('Demo Content Install', 'attr'),
			'manage_options',
			'attr-demo-content',
			array($this, '_display_page')
        );
        
        add_management_page(
			__('Backup', 'attr'),
			__('Backup', 'attr'),
			'manage_options',
			'attr-backup-content',
			array($this, '_display_backup_page')
		);
	}
	
	public function _display_page() {
		echo '<div class="wrap">';
           $dir = dirname(__FILE__);
	       require $dir .'/../views/attr-content-install.php';
		echo '</div>';
    }
    public function _display_backup_page() {
		echo '<div class="wrap">';
            $dir = dirname(__FILE__);
        	require $dir .'/../views/attr-backup.php';
		echo '</div>';
	}
}