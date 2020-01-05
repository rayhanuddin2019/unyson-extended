<?php 

Class Attr_Import_Task extends Attr_Backups_Task{
    
     public function get_type(){
         return "import";
     }

	 public function get_title(array $args = array(), array $state = array()){
         return "Import database";
     }

	public function execute(array $args, array $state = array()){

    }
    private function do_cleanup(array $args, array $state) {
		global $wpdb;

		$foreigns = $wpdb->get_results( "SELECT constraint_name, column_name, referenced_table_name, referenced_column_name, table_name FROM information_schema.key_column_usage WHERE TABLE_SCHEMA='{$wpdb->dbname}' AND referenced_table_name IS NOT NULL", ARRAY_A );

		foreach ( $foreigns as $foreign ) {
			$wpdb->query( "ALTER TABLE {$foreign['table_name']} DROP FOREIGN KEY {$foreign['constraint_name']}" );
		}

		// delete all tables with temporary prefix $this->get_tmp_table_prefix()
		$table_names = $wpdb->get_col( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $this->get_tmp_table_prefix() ) . '%' ) );

		if ( $table_names ) {

			if (!$wpdb->query('DROP TABLE '. esc_sql(
					$table_name = array_pop($table_names)
				))) {
				return new WP_Error(
					'drop_tmp_table_fail',
					sprintf(__('Cannot drop temporary table: %s', 'attr'), $table_name)
					.($wpdb->last_error ? '. '. $wpdb->last_error : '')
				);
			}

			++$state['step'];

			return $state;
		} else {
			$state['task'] = 'inspect';
			$state['step'] = 0;

			return $state;
		}

		return $state;
	}

    private function array_str_replace_recursive($search, $replace, &$subject) {
		if (is_array($subject)) {
			foreach($subject as &$_subject) {
				$this->array_str_replace_recursive( $search, $replace, $_subject );
			}

			unset($_subject);
		} elseif (is_string($subject)) {
			$_subject = maybe_unserialize( $subject );
			$unserialized = (
				gettype($_subject) !== gettype($subject)
				||
				$_subject !== $subject
			);

			if (is_string($_subject)) {
				$_subject = str_replace($search, $replace, $_subject);
			} else {
				$this->array_str_replace_recursive( $search, $replace, $_subject );
			}

			if ($unserialized) {
				$_subject = serialize($_subject);
			}

			$subject = $_subject;

			unset($_subject);
		}
    }
    
    private function do_inspect(array $args, array $state) {
		global $wpdb; 
		{
			try {
				$fo = new SplFileObject( $args['dir'] . '/database.json.txt' );
			} catch (RuntimeException $e) {
				$fo = null;
				return new WP_Error(
					'cannot_open_file', __('Cannot open db file', 'attr')
				);
			}

			try {
				$fo->seek( $state['step'] );
			} catch (RuntimeException $e) {
				$fo = null;
				return new WP_Error(
					'cannot_move_file_cursor', __( 'Cannot move cursor in db file', 'attr' )
				);
			}
		}

		$max_time = time() + 36000;

		while ( time() < $max_time ) {
			if ( $line = $fo->current() ) {
				if ( is_null( $line = json_decode( $line, true ) ) ) {
					$fo = null;

					return new WP_Error(
						'line_decode_fail',
						sprintf(
							__( 'Failed to decode line %d from db file.', 'attr' ) .' '. attr_get_json_last_error_message(),
							$state['step'] + 1
						)
					);
				}

				if (
					$line['type'] === 'row'
					&&
					$line['data']['table'] === 'options'
					&&
					isset($line['data']['row']['option_name'])
					&&
					in_array($line['data']['row']['option_name'], array(
						'siteurl', 'home', // used to replace imported urls with current
						'template', 'stylesheet', // used to replace imported theme slug with current
					))
				) {
					$state['params'][ $line['data']['row']['option_name'] ] = $line['data']['row']['option_value'];
				} elseif (
					$line['type'] === 'table'
					&&
					!isset($state['tables'][ $line['data']['name'] ])
				) {
					$state['tables'][ $line['data']['name'] ] = true;
				} elseif (
					$line['type'] === 'param'
				) {
					$state['params'][ $line['data']['name'] ] = $line['data']['value'];
				}
			} elseif ( $line === false && ! $fo->eof() ) {
				$fo = null;

				return new WP_Error(
					'line_read_fail',
					sprintf(__( 'Cannot read line %d from db file', 'attr' ), $state['step'] + 1)
				);
			} else {
				if (
					!isset($state['params']['siteurl'])
					||
					!isset($state['params']['home'])
				) {
					return new WP_Error(
						'params_not_found', __( 'Required params not found', 'attr' )
					);
				}

				// decide if it's full backup or not
				{
					$is_full_backup = (
						isset($state['tables']['commentmeta']) &&
						isset($state['tables']['comments']) &&
						isset($state['tables']['links']) &&
						isset($state['tables']['options']) &&
						isset($state['tables']['postmeta']) &&
						isset($state['tables']['posts']) &&
						isset($state['tables']['terms']) &&
						isset($state['tables']['term_relationships']) &&
						isset($state['tables']['term_taxonomy']) &&
						isset($state['tables']['usermeta']) &&
						isset($state['tables']['users'])
					);

					if (is_multisite()) { 
						$is_full_backup = $is_full_backup && (
								isset($state['tables']['blogs']) &&
								isset($state['tables']['blog_versions']) &&
								isset($state['tables']['registration_log']) &&
								isset($state['tables']['signups']) &&
								isset($state['tables']['site']) &&
								isset($state['tables']['sitemeta'])
								
							);
					}

					if (is_null($state['full'])) {
						$state['full'] = $is_full_backup;
					} elseif ($state['full'] && !$is_full_backup) {
						return new WP_Error(
							'full_db_restore_impossible',
							__('Cannot do full db restore because backup is missing some tables', 'attr')
						);
					}
				}

				// skip tables
				{
					$skip_tables = array(
						'users' => true,
						'usermeta' => true
					);

					if (!$state['full']) {
						$skip_tables = array_merge($skip_tables, array(
							'blogs' => true,
							'blog_versions' => true,
							'registration_log' => true,
							'signups' => true,
							'site' => true,
							'sitemeta' => true,
							'sitecategories' => true
						));
					}

					foreach (array_keys($skip_tables) as $table_name) {
						if (isset($state['tables'][$table_name])) {
							$state['tables'][$table_name] = false;
						}
					}

					unset($skip_tables);
				}

				$state['step'] = 0;
				$state['task'] = 'import';

				$fo = null;

				return $state;
			}

			$state['step'] ++;
			$fo->next();
		}

		$fo = null;

		return $state;
    }

    private function do_import(array $args, array $state) {
		global $wpdb; /** @var WPDB $wpdb */

		{
			try {
				$fo = new SplFileObject( $args['dir'] . '/database.json.txt' );
			} catch (RuntimeException $e) {
				$fo = null;
				return new WP_Error(
					'cannot_open_file', __('Cannot open db file', 'attr')
				);
			}

			try {
				$fo->seek( $state['step'] );
			} catch (RuntimeException $e) {
				$fo = null;
				return new WP_Error(
					'cannot_move_file_cursor', __( 'Cannot move cursor in db file', 'attr' )
				);
			}
		}

		{
			$params = array(
				'search' => array(),
				'replace' => array(),
			);
			
			{
				
				$search_replace = array();
				
				if (isset($state['params']['wp_upload_dir_baseurl'])) {
					$wp_upload_dir = wp_upload_dir();

					$search_replace[
					rtrim($state['params']['wp_upload_dir_baseurl'], '/')
					] = rtrim($wp_upload_dir['baseurl'], '/');

					unset($wp_upload_dir);
				}

				foreach (array('siteurl', 'home') as $_wp_option) {
					$search_replace[
					rtrim($state['params'][$_wp_option], '/')
					] = rtrim(get_option($_wp_option), '/');
				}

				foreach ($search_replace as $search => $replace) {
					$search_replace[
					($old_url = attr_get_url_without_scheme($search))
					] = ($new_url = attr_get_url_without_scheme($replace));
				
					if (
						strlen($old_url) !== strlen($new_url)
						&&
						preg_match('/^'. preg_quote($old_url, '/') .'/', $new_url)
					) {
						return new WP_Error(
							'url_replace_fail',
							sprintf(__('Imported url "%s" is prefix of current url', 'attr'), $search)
						);
					}
				}
			}

			
			foreach ($search_replace as $search => $replace) {
				if ($search === $replace) {
					continue;
				}

				$_search_replace = array(
					$search => $replace,
					json_encode($search) => json_encode($replace),
				);
					

				foreach ($_search_replace as $search => $replace) {
					$params['search'][] = $search;
					$params['replace'][] = $replace;

					$params['search'][] = str_replace( '/', '\\/', $search);
					$params['replace'][] = str_replace( '/', '\\/', $replace);

					$params['search'][] = str_replace( '/', '\\\\/', $search);
					$params['replace'][] = str_replace( '/', '\\\\/', $replace);

					$params['search'][] = str_replace( '/', '\\\\\\/', $search);
					$params['replace'][] = str_replace( '/', '\\\\\\/', $replace);
				}

				unset($_search_replace);
			}

			unset($search_replace, $search, $replace);
		}

		{
			$replace_option_names = array();

			{
				$filter_data = array();

				if (
					is_child_theme() // must be: get_stylesheet() !== get_template()
					&&
					! empty($state['params']['stylesheet'])
					&&
					// do nothing if it's the same
					$state['params']['stylesheet'] !== get_stylesheet()
					&&
					// prevent rename stylesheet to template and duplicate wp_option error
					$state['params']['stylesheet'] !== get_template()
				) {
					$filter_data['stylesheet'] = $state['params']['stylesheet'];

					$replace_option_names[
					'theme_mods_'. $state['params']['stylesheet']
					] = 'theme_mods_'. get_stylesheet();
				}

				if (
					! empty($state['params']['template'])
					&&
					// prevent template overwrite stylesheet (prefer stylesheet)
					$state['params']['template'] !== $state['params']['stylesheet']
					&&
					// do nothing if it's the same
					$state['params']['template'] !== get_template()
					&&
					// prevent rename template to stylesheet and duplicate wp_option error
					$state['params']['template'] !== get_stylesheet()
				) {
					$filter_data['template'] = $state['params']['template'];

					$replace_option_names[
					'theme_mods_'. $state['params']['template']
					] = 'theme_mods_'. get_template();
				}
			}

			if ( ! empty($filter_data) ) {
				$replace_option_names = array_merge(
				/** @since 2.0.12 */
					apply_filters('attr_ext_backups_db_restore_option_names_replace', array(), $filter_data),
					$replace_option_names
				);
			}

			unset($filter_data);
		}

		$max_time = time() + 360000;

		while ( time() < $max_time ) {
			if ( $line = $fo->current() ) {
				if ( is_null( $line = json_decode( $line, true ) ) ) {
					$fo = null;

					return new WP_Error(
						'line_decode_fail',
						sprintf(
							__( 'Failed to decode line %d from db file.', 'attr' ) .' '. attr_get_json_last_error_message(),
							$state['step'] + 1
						)
					);
				}

				switch ( $line['type'] ) {
					case 'table':
						if (!$state['tables'][ $line['data']['name'] ]) {
							break; // skip
						}

						$tmp_table_name = $this->get_tmp_table_prefix() . $line['data']['name'];

						if (strlen($tmp_table_name) > 64) { 
							return new WP_Error(
								'tmp_table_name_invalid',
								sprintf( __( 'Table name is more than 64 characters: %s', 'attr' ), $tmp_table_name )
							);
						}

						if ( false === $wpdb->query( 'DROP TABLE IF EXISTS ' . esc_sql( $tmp_table_name ) ) ) {
							$fo = null;

							return new WP_Error(
								'tmp_table_drop_fail',
								sprintf( __( 'Failed to drop tmp table %s', 'attr' ), $tmp_table_name )
								.($wpdb->last_error ? '. '. $wpdb->last_error : '')
							);
						}

						$sql = 'CREATE TABLE `' . esc_sql( $tmp_table_name ) . "` (\n";

						$cols_sql = array();

						foreach ( $line['data']['columns'] as $col_name => $col_opts ) {
							$cols_sql[] = '`'. esc_sql( $col_name ) .'` '. $col_opts;
						}

						foreach ( $line['data']['indexes'] as $index ) {
							$cols_sql[] = $index;
						}

						$sql .= implode( ", \n", $cols_sql );

						unset( $cols_sql );

						$sql .= ') ' . $line['data']['opts'];

						if ( ! $this->is_engine_exists( $this->get_db_field( $sql, 'ENGINE' ) ) ) {
							$new_engine = $this->is_engine_exists( 'InnoDB' ) ? 'InnoDB' : $this->get_db_engine();
							$sql = str_replace( $this->get_db_field( $sql, 'ENGINE' ), $new_engine, $sql );
						}

						$collations = $this->get_db_collations();
						$collate    = $this->get_db_field( $sql, 'COLLATE' );
						$charset    = $this->get_db_field( $sql, 'CHARSET' );

						// The sql request can contains wrong table collate e.g: collate = utf8_general_ci, charset = utf8mb4 - but table with collate utf8_general_ci doesn't support charset utf8mb4
						$is_invalid_charset = isset( $collations[ $collate ] ) && $collations[ $collate ] !== $charset;

						// In case if the db doesn't support imported db collate or charset.
						$not_exists_charset_collate = ! isset( $collations[ $collate ] ) || ! array_search( $charset, $collations );

						if ( $is_invalid_charset || $not_exists_charset_collate ) {

							$character_set = $this->get_db_field( $sql, 'CHARACTER SET' );

							$sql = preg_replace( "/(CHARSET)(=)?(\s)?([^\s\",]+)/i", "$1$2utf8", $sql );
							$sql = preg_replace( "/(COLLATE)(=)?(\s)?([^\s\",]+)/i", "$1 utf8_general_ci", $sql );

							if ( $character_set ) {
								$sql = preg_replace("/(CHARACTER SET)(=)?(\s)?([^\s\",]+)/i", "$1$2 utf8", $sql);
							}
						}

						$wpdb->query( 'SET FOREIGN_KEY_CHECKS=0' );

						$query = $wpdb->query( $sql );

						if ( false === $query ) {
							$fo = null;

							return new WP_Error(
								'tmp_table_create_fail',
								sprintf( __( 'Failed to create tmp table %s', 'attr' ), $tmp_table_name )
								.($wpdb->last_error ? '. '. $wpdb->last_error : '')
							);
						}

						unset( $sql );
						break;
					case 'row':
						if ( ! isset( $state['tables'][ $line['data']['table'] ] ) ) {
							$fo = null;

							return new WP_Error(
								'invalid_table',
								sprintf( __( 'Tried to insert data in table that was not imported %s', 'attr' ), $line['data']['table'] )
							);
						} elseif ( ! $state['tables'][ $line['data']['table'] ] ) {
							break; 
						} elseif (
							'options' === $line['data']['table']
							&&
							apply_filters('attr_backups_db_restore_exclude_option', false,
								$line['data']['row']['option_name'], $state['full']
							)
						) {
							break;
						}

						if ( ! empty($params['search']) ) {
							$this->array_str_replace_recursive(
								$params['search'],
								$params['replace'],
								$line['data']['row']
							);
						}

						if (
							! empty($replace_option_names)
							&&
							$line['data']['table'] === 'options'
						) {
							if (isset($replace_option_names[ $line['data']['row']['option_name'] ])) {
								$line['data']['row']['option_name']
									= $replace_option_names[ $line['data']['row']['option_name'] ];
							} elseif (in_array($line['data']['row']['option_name'], $replace_option_names, true)) {
							
								break;
							}
						}

						if (isset($state['params']['wpdb_prefix'])) {
							
							{
								$column = $search = null;

								switch ($line['data']['table']) {
									case 'options':
										$column = 'option_name';
										$search = array(
											'user_roles',
										);
										break;
									case 'usermeta':
										$column = 'meta_key';
										$search = array(
											'capabilities',
											'user_level',
											'dashboard_quick_press_last_post_id',
											'user-settings',
											'user-settings-time',
										);
										break;
								}

								if ($column && $search) {
									foreach ($search as $name) {
										if (
											substr($line['data']['row'][$column], -strlen($name))
											===
											$name
											&&
											substr($line['data']['row'][$column], 0, strlen($state['params']['wpdb_prefix']))
											===
											$state['params']['wpdb_prefix']
										) {
											$line['data']['row'][$column] = $wpdb->prefix
											                                . substr($line['data']['row'][$column], strlen($state['params']['wpdb_prefix']));
										}
									}
								}
							}
						}

						$tmp_table_name = $this->get_tmp_table_prefix() . $line['data']['table'];

						if ($index_column = $this->get_index_column($tmp_table_name)) {
							
							$value_max_length = 500000;
							$update_count = 0;
							$index_column_value = $line['data']['row'][ $index_column ];
							$row_lengths = array();

							while ($line['data']['row']) {
								$row = array();

								foreach (array_keys($line['data']['row']) as $column_name) {
									$row[ $column_name ] = mb_substr(
										$line['data']['row'][ $column_name ],
										0, $value_max_length
									);

									$row_length = mb_strlen($row[ $column_name ]);

									if (!isset($row_lengths[$column_name])) {
										$row_lengths[ $column_name ] = mb_strlen( $line['data']['row'][ $column_name ] );
									}
								
									while (
										($last_char = mb_substr($row[ $column_name ], -1)) === '\\'
										&&
										$row_length < $row_lengths[ $column_name ]
									) {
										$row[ $column_name ] .= mb_substr(
											$line['data']['row'][ $column_name ],
											$row_length - 1, 1
										);

										$row_length++; 
									}

									$line['data']['row'][ $column_name ] = mb_substr(
										$line['data']['row'][ $column_name ],
										$row_length
									);

									if (empty($line['data']['row'][ $column_name ])) {
										unset($line['data']['row'][ $column_name ]);
									}
								}

								if ($update_count) {
									{
										$set_sql = array();
										foreach (array_keys($row) as $column_name) {
											$set_sql[] = '`'. esc_sql($column_name) .'` = CONCAT( `'. esc_sql($column_name) .'`'
											             .' , '. $wpdb->prepare('%s', $row[$column_name]) .')';
										}
										$set_sql = implode(', ', $set_sql);
									}

									$sql = implode(" \n", array(
										"UPDATE {$tmp_table_name} SET",
										$set_sql,
										'WHERE `'. esc_sql($index_column) .'` = '. $wpdb->prepare('%s', $index_column_value)
									));
								} else {
									$sql = implode(" \n", array(
										"INSERT INTO {$tmp_table_name} (",
										'`'. implode( '`, `', array_map( 'esc_sql', array_keys( $row ) ) ) .'`',
										") VALUES (",
										implode( ', ', array_map( array( $this, '_wpdb_prepare_string' ), $row ) ),
										")"
									));
								}

								if ( false === $wpdb->query( $sql ) ) {
									if (
										! $update_count 
										&&
										$wpdb->last_error
										&&
										strpos( $wpdb->last_error, 'Duplicate' ) !== false
									) {
										break;
									} else {
										$fo = null;

										return new WP_Error(
											'insert_fail',
											sprintf(
												__('Failed to insert row from line %d into table %s', 'attr'),
												$state['step'] + 1, $tmp_table_name
											)
											. ($wpdb->last_error ? '. ' . $wpdb->last_error : '')
										);
									}
								}

								unset( $sql );

								$update_count++;
							}
						} else {
							$sql = implode(" \n", array(
								"INSERT INTO {$tmp_table_name} (",
								'`'. implode( '`, `', array_map( 'esc_sql', array_keys( $line['data']['row'] ) ) ) .'`',
								") VALUES (",
								implode( ', ', array_map( array( $this, '_wpdb_prepare_string' ), $line['data']['row'] ) ),
								")"
							));

							if ( false === $wpdb->query( $sql ) ) {
								$fo = null;
								return new WP_Error(
									'insert_fail',
									sprintf(
										__('Failed to insert row from line %d into table %s', 'attr'),
										$state['step'] + 1, $tmp_table_name
									)
									. ($wpdb->last_error ? '. ' . $wpdb->last_error : '')
								);
							}

							unset( $sql );
						}
						break;
					case 'param':
						break;
					default:
						$fo = null;

						return new WP_Error(
							'invalid_json_type',
							sprintf( __( 'Invalid json type %s in db file', 'attr' ), $line['type'] )
						);
				}
			} elseif ( $line === false && ! $fo->eof() ) {
				$fo = null;

				return new WP_Error(
					'line_read_fail', __( 'Cannot read line from db file', 'attr' )
				);
			} else {
				$fo = null;

				$state['step'] = 0;
				$state['task'] = 'keep:options';

				return $state;
			}

			$state['step']++;
			$fo->next();
		}

		$fo = null;

		return $state;
	}
    
    private function get_tables() {
		global $wpdb; 

		$tables = $wpdb->get_col(
			$wpdb->prepare(
				'SHOW TABLES LIKE %s',
				$wpdb->esc_like($wpdb->prefix) .'%'
			)
		);
	
		$prefix_regex = '/^'. preg_quote($wpdb->prefix, '/') .'/i';

		foreach ($tables as $i => $table) {
			$tables[$i] = preg_replace($prefix_regex, '', $table);

			if (is_numeric($tables[$i]{0})) {
				unset($tables[$i]);
			}
		}

		return array_fill_keys( $tables, array() );
    }
    
    
function update_user_table($content){
	global $wpdb;
	
	if(!is_array($content)){
		return;
	}

	$table = $wpdb->prefix.'users';
	
	foreach($content as $row){
        $user_exist = get_userdata( $row['ID'] );  
		if( isset($row['ID']) && $row['ID']==1) {
		   continue; 
		}

		if($user_exist && user_can( $row['ID'], 'administrator' )){
           continue;
		}

        if(!$user_exist){
		   $wpdb->insert($table,$row); 
		}
	} 
   
}

function update_usermeta_table($content){
	
	global $wpdb;
	if(!is_array($content)){
		return;
	}
     
	$table = $wpdb->prefix.'usermeta';
	$umeta_id = $wpdb->get_results( 'SELECT umeta_id FROM '. $table, ARRAY_A );
	$usermeta_id =  wp_list_pluck($umeta_id,'umeta_id');

	foreach($content as $row){

		$user_exist = get_userdata( esc_sql($row['user_id']) ); 
		if( isset($row['ID']) && $row['user_id']==1) {
			continue; 
		 }
	 
		 if($user_exist && user_can( $row['user_id'], 'administrator' )){
			continue;
		}

		$column_exist = false;
		if(is_array($usermeta_id) && in_array($row['umeta_id'],$usermeta_id)){
			$column_exist = true;		
		}
	
	    if(!$column_exist){
		   $wpdb->insert($table,$row); 
		}

	} 
   
}

   
    function attr_database_table_data_import($content=[],$tables=['users']){
       return;  
        global $wpdb;
    
        if(is_array($content) && is_array($tables)){

            $user_table_exist = false;
            $user_table_exist = in_array('users',$tables); 

            if(!isset($content['rows'])){
                return false;
            } 	

            $tables_data = $content['rows'];	
            foreach($tables_data as $table_name => $table_row){
                                    
                if($user_table_exist && $table_name=='users'){

                    $this->update_user_table($table_row); 
            
                }elseif($user_table_exist && $table_name=='usermeta'){

                    $this->update_usermeta_table($table_row);
                    
                }else{ 
                    
                } // for other customs tables
                
            }
        
        }
    }
}