<?php 

$backup_page =  new Attr_backup_demo_page();
add_action( 'admin_menu', array( $backup_page, '_admin_action_admin_menu' ) );

 
/*
* Demo content export 
*/
add_action("wp_ajax_attr_content_backup", "attr_content_backup");
add_action("wp_ajax_nopriv_attr_content_backup", "attr_content_backup_login");
function attr_content_backup_login(){
  
    $return = array(
        'message' => __( 'Database exported', 'attr' ),
    );
  
    try{
         
        wp_send_json_success( $return );
    } catch (Exception $e) {
        wp_send_json_error();
    }
    wp_die();
}

function attr_content_backup(){

    $return = array(
        'message' => __( 'Database exported', 'attr' ),
    );
   
    try{
        wp_send_json_success( $return );
    } catch (Exception $e) {
        wp_send_json_error();
    }
    wp_die();
}


