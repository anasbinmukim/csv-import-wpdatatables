<?php
function debug($data){
	echo '<pre>';
	print_r($data);
	echo '</pre>';
}

function get_wpdatables_tables_dropdown($select_table = ''){
	global $wpdb;
	$details = array();

	$results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wpdatatables WHERE mysql_table_name != ''", ARRAY_A );

	//debug($results);

	if(!empty($results) && isset($results)){
		echo '<select name="wp_datatables_table" id="wp_datatables_table">';
		echo '<option value="">Select Table</option>';
		foreach ($results as $key => $value) {
				echo '<option '.selected( $select_table, $value['mysql_table_name'] ).' value="'.$value['mysql_table_name'].'">'.$value['title'].' - ( '.$value['mysql_table_name'].' )</option>';
		}
		echo '</select>';
	}
}


function csv_import_check_validation_csv_vs_table($data_file, $table_name){
		$output = array();
		$output['csv_file'] = $data_file;
		$output['data_table'] = $table_name;
		$output['status'] = '';

		$home_url = home_url('/', CSVIWPTABLESHTTP);
		$data_source = str_replace($home_url, ABSPATH, $data_file);



		$csv = new ParseCsv\Csv();

		# offset from the beginning of the file,
		# ignoring the first X number of rows.
		$csv->offset = 0;

		# limit the number of returned rows.
		$csv->limit = 1;

		$csv->auto($data_source);


		//$this->debug($csv->data);

		$csv_heading = array();
		$csv_heading[] = 'wdt_ID';

		$search = array('_', '-');
		$replace = array('', '');

		if(isset($csv->data)){
				foreach ($csv->data as $key => $row){
						foreach ($row as $field => $value){
							$heading = sanitize_title_with_dashes($field);
							$heading = str_replace($search, $replace, $heading);
							$csv_heading[] = $heading;
						}
				}
		}

		//$this->debug($csv_heading);

		global $wpdb;
		// An array of Field names
		$existing_columns = $wpdb->get_col("DESC {$table_name}", 0);
		//$this->debug($existing_columns);

		if(isset($csv_heading) && isset($csv_heading)){
			if(count($csv_heading) == count($existing_columns)){
					if($csv_heading === $existing_columns){
						$output['msg'] = $table_name;
						$output['status'] = 'yes';
					}else{
						$output['msg'] = 'CSV heading and Data table heading doesn\'t match.';
						$output['status'] = '';
					}
			}else{
				$output['msg'] = 'CSV heading and Data table heading doesn\'t match.';
				$output['status'] = '';
			}
		}else{
			$output['msg'] = 'CSV not found!';
			$output['status'] = '';
		}

		return $output;
}

function csv_import_put_data($table_name, $data_arr){
		global $wpdb;
		// An array of Field names
		//$table_name = 'wp_wpdatatable_3';
		$cols_sql = "DESCRIBE $table_name";
		$all_objects = $wpdb->get_results( $cols_sql );
		$existing_types = [];
		$existing_columns = [];
		foreach ( $all_objects as $object ) {
		  // Build an array of Field names
			//debug($object);
			$data_type = $object->Type;
			$data_type = substr($data_type, 0, 3);
			if($data_type == 'int'){
				$data_value_type = '%d';
			}else{
				$data_value_type = '%s';
			}

			$existing_types[] = $data_value_type;
		  $existing_columns[] = $object->Field;
		}

		//$sql_data_types = implode( ', ', $existing_types );
		//$sql_data_field = implode( ', ', $existing_columns );

		$formated_data = array_combine($existing_columns, $data_arr);
		$wpdb->insert( $table_name, $formated_data, $existing_types );

}
