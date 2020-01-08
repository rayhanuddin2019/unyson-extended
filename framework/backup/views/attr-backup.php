<?php
   $ajax_url = admin_url( 'admin-ajax.php' );
   $nonce = wp_create_nonce("attr_content_backup_nonce");
   $link = admin_url('admin-ajax.php?action=attr_content_backup&nonce='.$nonce);
   $attr_backup_List_Table =  new Attr_Backup_List_Table();
   $attr_backup_List_Table->prepare_items();

   $attr_export = new Attr_Export_Task();
   $state = $attr_export->execute(
      ['dir'=>attr_backups_destination_directory()]
   );

 
   
  // print_r($attr_export->attr_demo_data_export(['posts']));
?>
<div class="attr-backup-content-section">
    <div class="attr-export-status"> </div>
    <br/>
    <?php  echo '<a class="attr-database-export button" data-nonce="' . $nonce . '" href="' . $link . '">'.esc_html__('Backup database content','attr').'</a>'; ?>
    <?php $attr_backup_List_Table->display(); ?>
</div>



<script>

jQuery(document).ready( function() {

jQuery(".attr-database-export").click( function(e) {
   e.preventDefault(); 
  
   var ajax_url = "<?php echo $ajax_url  ?>";
   var nonce = jQuery(this).attr("data-nonce");
   jQuery(".attr-export-status").html('database exporting');
   jQuery.ajax({
      type : "post",
      url : ajax_url,
      data : {action: "attr_content_backup", nonce: nonce},
      success: function(response) {
        
         if(response.success) {
            jQuery(".attr-export-status").html(response.data.message)
         }
         else {
            jQuery(".attr-export-status").html('database exporting failed');
         }
      }
   })   

})

})
  
</script>