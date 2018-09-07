<?php
add_action('admin_menu', 'csv_wpdatatables_submenu_page');
function csv_wpdatatables_submenu_page(){
    add_submenu_page( 'wpdatatables-administration', 'Zip Search', 'Zip Search', 'manage_options', 'wp-datatables-zip-search', 'csv_wpdatatables_submenu_page_callback');
}

function csv_wpdatatables_submenu_page_callback(){
  echo '<div class="wrap">';
  echo '<h2>Search by Zipcodes</h2>';

  wpdatatables_filter_carrier_form();

  echo '</div>';
}

function wpdatatables_filter_carrier_form(){
  ?>
  <form action="" method="post">
    <p>
      <input type="text" class="medium-text" name="search_by_zip_code" id="search_by_zip_code" placeholder="Zip Code">
      <select name="search_by_sex" id="search_by_sex">
        <option value="">Sex</option>
        <option value="M">Male</option>
        <option value="F">Female</option>
      </select>
      <select name="search_by_tobacco" id="search_by_tobacco">
        <option value="">Tobacco</option>
        <option value="YES">Yes</option>
        <option value="NO">No</option>
      </select>
      <input type="text" class="medium-text" name="search_by_age" id="search_by_age" placeholder="Age">

    <input type="button" value="Search" class="button button-primary button-search-submit"></p>
    <input type="hidden" id="start" value="0" />
    <input type="hidden" id="limit" value="500" />
    <?php $csvfilter_nonce = wp_create_nonce( "csvfilter_nonce" ); ?>
  </form>


  <script type="text/javascript">
      jQuery(document).ready(function($) {
          jQuery('.filter-data-loading').hide();
          jQuery('.button-search-submit').click(function() {
            jQuery('#filter-data-result').html('');
            jQuery('.filter-data-loading').show('slow');
              //alert('Hello');
              //return false;
              var dataContainer = {
                  zip_code: jQuery('#search_by_zip_code').val(),
                  sex: jQuery('#search_by_sex').val(),
                  tobacco: jQuery('#search_by_tobacco').val(),
                  age: jQuery('#search_by_age').val(),
                  start: jQuery('#start').val(),
                  limit: jQuery('#limit').val(),
                  security: '<?php echo $csvfilter_nonce; ?>',
                  action: 'send-csv-filter-wpdatables-data'
              };

              filter_datatables_csv_data(dataContainer);
              function filter_datatables_csv_data(dataContainer){
                jQuery.ajax({
                    action: "send-csv-filter-wpdatables-data",
                    type: "POST",
                    dataType: "json",
                    url: ajaxurl,
                    data: dataContainer,
                    success: function(data){
                      // alert(data.sex);
                      // alert(data.tobacco);
                      // alert(data.age);
                      if(data.msg == 'Complete'){
                        //alert(data.test);
                        var result_data_added = data.result_data;
                        if(result_data_added == ''){
                            result_data_added = '<tr><td colspan="4">Not Found<td></tr>';
                        }
                        //jQuery('#filter-data-result').append(result_data_added);
                        jQuery('#filter-data-result').html(result_data_added);
                        jQuery('.filter-data-loading').delay(300).fadeOut('slow');
                      }else{
                        jQuery('#filter-data-result').html('<span class="error">Not found...</span>');
                        jQuery('.filter-data-loading').delay(300).fadeOut('slow');
                      }
                    }
                });
              }

          });

      });
  </script>

  <style>
  .filter-data-loading{ display: none; }
  </style>

  <div class="table-data-wrap">
  <?php
  // echo '<table class="widefat" id="filter-data-result-head">';
  // echo '<thead><tr><th>Carrier</th><th>Plan F</th><th>Plan G</th><th>Plan N</th></tr></thead>';
  // echo '</table>';
  echo '<table class="widefat" id="filter-data-result">';
  //echo wpdatatables_display_csv_zip_data($lookup_code, $start, $limit);
  echo '</table>';
  ?>
  <div class="filter-data-loading"><img src="<?php echo CSVIWPTABLES_URL; ?>/img/30.svg" alt="Uploading..." /></div>
  </div>

  <?php
}


function wpdatatables_display_csv_zip_data($zip_code, $sex, $tobacco, $age, $start = 0, $limit = 100){
  global $wpdb;
  $output = '';

  //live
  $table1 = "wp_wpdatatable_11";
  $table2 = "wp_wpdatatable_19";

  $sql_q = "SELECT * FROM $table1 as mastertable ";
  $sql_q .= "INNER JOIN $table2 as ziptable ON mastertable.zipareacode = ziptable.zipareacode ";
  $sql_q .= " WHERE ziptable.zip5 = $zip_code ";
  if($sex != ''){
    $sql_q .= " and mastertable.sex = '$sex' ";
  }
  if($tobacco != ''){
    $sql_q .= " and mastertable.tobacco = '$tobacco' ";
  }
  if($age > 40){
    $sql_q .= " and mastertable.age = $age ";
  }
  $sql_q .= " ORDER BY mastertable.wdt_ID DESC LIMIT $start, $limit ";

  //$lookup_data = $wpdb->get_results("SELECT * FROM $table1 as mastertable INNER JOIN $table2 as ziptable ON mastertable.zipareacode = ziptable.zipareacode WHERE ziptable.zip5 = $zip_code ORDER BY mastertable.wdt_ID DESC LIMIT $start, $limit");
  $lookup_data = $wpdb->get_results($sql_q);

  if(is_array($lookup_data) && (count($lookup_data) > 0)){
    $output .= '<thead><tr><th>Carrier</th><th>Plan F</th><th>Plan G</th><th>Plan N</th></tr></thead>';
    foreach ($lookup_data as $key => $zip_value) {
      $output .= '<tr>';
      $output .= '<td>';
      $output .= $zip_value->carrier;
      $output .= '</td>';
      $output .= '<td>';
      $output .= $zip_value->planf;
      $output .= '</td>';
      $output .= '<td>';
      $output .= $zip_value->plang;
      $output .= '</td>';
      $output .= '<td>';
      $output .= $zip_value->plann;
      $output .= '</td>';
      $output .= '</tr>';
    }
  }

  return $output;

}
function csv_filter_wpdatables_data_callback() {

  $start = 0;
  $limit  = 0;
  $zip_code = '';
	if (!check_ajax_referer( 'csvfilter_nonce', 'security' )) {
		echo json_encode(array("msg" => "Error"));
		exit;
	}else{

    $start = intval($_POST['start']);
    $limit = intval($_POST['limit']);
    $zip_code = sanitize_text_field($_POST['zip_code']);
    $sex = sanitize_text_field($_POST['sex']);
    $tobacco = sanitize_text_field($_POST['tobacco']);
    $age = sanitize_text_field($_POST['age']);



    $result_data = wpdatatables_display_csv_zip_data($zip_code, $sex, $tobacco, $age, $start, $limit);

		echo json_encode(array("msg" => "Complete", "result_data" => $result_data, "start" => $start, "limit" => $limit, "zip_code" => $zip_code, "sex" => $sex, "tobacco" => $tobacco, "age" => $age));
		exit;
	}

}

add_action('wp_ajax_send-csv-filter-wpdatables-data', 'csv_filter_wpdatables_data_callback');
add_action('wp_ajax_nopriv_send-csv-filter-wpdatables-data', 'csv_filter_wpdatables_data_callback');

function wpdatatables_data_csg_filter_shortcode($atts, $content = null) {
	ob_start();
  echo '<script type="text/javascript">
           var ajaxurl = "' . admin_url('admin-ajax.php') . '";
         </script>';
  wpdatatables_filter_carrier_form();
	$csg_filter = ob_get_contents();
	ob_end_clean();
	return '<div class="csg-search-filter-form">'.$csg_filter.'</div>';
}
add_shortcode( 'wpdatatables_data_csg_filter', 'wpdatatables_data_csg_filter_shortcode' );


// (
//     [wdt_ID] => 68531
//     [carrier] => Anthem BCBS of Georgia (Household)
//     [state] => GEORGIA
//     [zipareacode] => 30818
//     [age] => 100
//     [tobacco] => NO
//     [sex] => F
//     [planf] => $235.40
//     [plang] => $156.55
//     [plann] => $153.70
// )


// (
//     [wdt_ID] => 397412
//     [zipareacode] => 31109
//     [zip5] => 46384
// )
