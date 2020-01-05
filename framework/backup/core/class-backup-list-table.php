<?php

if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Attr_Backup_List_Table extends WP_List_Table
{
    
    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();
        $data = $this->table_data();
       
        $perPage = 10;
        $currentPage = $this->get_pagenum();
        $totalItems = count($data);
        $this->set_pagination_args( array(
            'total_items' => $totalItems,
            'per_page'    => $perPage
        ) );
        $data = array_slice($data,(($currentPage-1)*$perPage),$perPage);
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $data;
    }
  
    public function get_columns()
    {
        $columns = array(
            'title'       => 'Title',
            'description' => 'Description',
        
        );
        return $columns;
    }
   
    public function get_hidden_columns()
    {
        return array();
    }
  
    public function get_sortable_columns()
    {
        return array('title' => array('title', false));
    }
  
    private function table_data()
    {
        $data = array();
        $data[] = array(
                    'id'          => 1,
                    'title'       => 'The Shawshank Redemption',
                    'description' => 'Two imprisoned men bond over a number of years, finding solace and eventual redemption through acts of common decency.',
            );
   
        return $data;
    }
   
    public function column_default( $item, $column_name )
    {
        
        switch( $column_name ) {
            case 'id':
            case 'title':
                return $item[ $column_name ].' '. $item['id'];  
            case 'description':
                return $item[ $column_name ];
            default:
                return print_r( $item, true ) ;
        }
    }
   
}