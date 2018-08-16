<?php
/**
**Data
**/
function csv_import_wpdatables_data_data() {
  $row_offset = 0;
  $row_limit  = 0;
	if (!check_ajax_referer( 'csvdata_nonce', 'security' )) {
		echo json_encode(array("msg" => "Error"));
		exit;
	}else{

    $row_offset = intval($_POST['row_offset']);
    $row_limit = intval($_POST['row_limit']);
    $put_table_name = sanitize_text_field($_POST['put_table_name']);
    $csv_file = sanitize_text_field($_POST['csv_file']);

    //https://github.com/parsecsv/parsecsv-for-php
    //$data_source = CSVIWPTABLES_ROOT . '/inc/Rates_Future.csv';
    //$data_source = CSVIWPTABLES_ROOT . '/inc/Rates_Current_MA_MN_WI.csv';

    $home_url = home_url('/');
		$data_source = str_replace($home_url, ABSPATH, $csv_file);


    $csv = new ParseCsv\Csv();


    # offset from the beginning of the file,
    # ignoring the first X number of rows.
    $csv->offset = $row_offset;

    # limit the number of returned rows.
    $csv->limit = $row_limit;

    $csv->auto($data_source);

    $total_data = $csv->data;

    $action_total_rows = 0;
    if(isset($total_data)){
        $action_total_rows = count($total_data);

        //put data to database table
        if($action_total_rows > 0){
          foreach ($total_data as $key => $row){
              $data_arr = array();

              //set primary key field values
              $data_arr[] = '';

              //conbine all other fields data
              foreach ($row as $value){
                  $data_arr[] = sanitize_text_field($value);
              }
              //put to database
              if(count($data_arr) > 1){
                  csv_import_put_data($put_table_name, $data_arr);
              }
          }
        }
    }

    //$data_arr = array('', 'Anas1', 'Anas2', 'Anas3', 'Anas4', 'Anas5', 'Anas6');
    //csv_import_put_data($put_table_name, $data_arr);


    //New offset
    $row_new_offset = 0;
    $row_new_offset = $action_total_rows + $row_offset + 1;

    $rows_added_count = 0;
    $rows_added_count = $action_total_rows + $row_offset;

    $data_import_msg = '';
    $data_import_msg = $row_offset .' to '. $rows_added_count .' rows successfully added to table '. $put_table_name;



		echo json_encode(array("test" => $data_arr, "msg" => "Complete", "import_msg" => $data_import_msg, "total_rows" => $total_rows, "count_rows" => $action_total_rows, "offset" => $row_new_offset, "limit" => $row_limit, "datatable" => $put_table_name, "csv_file" => $csv_file));
		exit;
	}

}


add_action('wp_ajax_send-csv-import-wpdatables-data', 'csv_import_wpdatables_data_data');
add_action('wp_ajax_nopriv_send-csv-import-wpdatables-data', 'csv_import_wpdatables_data_data');


function csv_import_datables_script($hook) {
  // Load only on ?page=mypluginname
  if($hook != 'tools_page_csv-import-wpdatables') {
    return;
  }

  if (is_admin ()){
    wp_enqueue_media ();
  }

	//wp_register_style(  'csv-import-datables-loading', CSVIWPTABLES_URL.'/css/loading.css' );
  //wp_enqueue_style( 'csv-import-datables-loading' );
}
add_action( 'admin_enqueue_scripts', 'csv_import_datables_script');
