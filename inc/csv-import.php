<div class="wrap">
<h1>CSV Import to WPDataTables</h1>

<form action="" method="post">

	<div class="button_wrap">

	<?php if(isset($_POST['check_upload']) && isset($_POST['process_csv_file']) && isset($_POST['wp_datatables_table'])){ ?>
		<?php
		$data_file = $_POST['process_csv_file'];
		$table_name = $_POST['wp_datatables_table'];
		?>

		<p>Select Tables: <?php get_wpdatables_tables_dropdown($table_name); ?></p>
		<p>
				<input type="text" name="process_csv_file" value="<?php echo $data_file; ?>" class="regular-text process_csv_file" id="process_csv_file">
				<button class="set_csv_file button">Browse CSV</button>
		</p>

		<?php

			$check_upload = csv_import_check_validation_csv_vs_table($data_file, $table_name);

			$csv_file_name = basename($check_upload['csv_file']);

			if(isset($check_upload['status']) && ($check_upload['status'] == 'yes')){
				echo '<h2>Click below button to import csv '.$csv_file_name.' to '.$check_upload['msg'].' datatable</h2>';
			?>
			<a data-row_offset="0" data-row_limit="99" data-csv_file="<?php echo $check_upload['csv_file']; ?>"  data-put_table_name="<?php echo $check_upload['data_table']; ?>" class="button-upload-process button button-primary" href="javascript:void(0)">Run Import</a>
		<?php }else{ ?>
			<?php echo '<h2>'.$check_upload['msg'].'</h2>'; ?>
		<?php } ?>
	<?php }else{ ?>
		<p>Select Tables: <?php get_wpdatables_tables_dropdown(); ?></p>
		<p>
				<input type="text" name="process_csv_file" value="" class="regular-text process_csv_file" id="process_csv_file">
				<button class="set_csv_file button">Browse CSV</button>
		</p>
		<input type="submit" name="check_upload" class="button button-primary" value="Upload">
	<?php } ?>
	</div>

	<?php $csvdata_nonce = wp_create_nonce( "csvdata_nonce" ); ?>
</form>
<style>
.loading-icon{ font-size: 24px; }
.import-activity-box{ max-width: 500px; text-align: center; height: 500px; overflow: scroll; display: none;}
.import-activity-message{
	border: 1px solid #dadada;
}
.import-activity-message li{
	padding: 5px 10px;
	font-size: 12px;
	text-align: left;
	margin-bottom: 0;
	border-bottom: 1px solid #dadada;
	background-color: #f8f8f8;
}
.import-activity-message li:nth-child(odd) {
	background-color: #F1F1F1;
}
.import-activity-message li.complete{
	font-weight: bold;
	font-size: 16px;
}
.warning{
	color: #ff0000;
}
</style>
<!-- <span class="loading-icon ld ld-ring ld-cycle"></span> -->
<div class="import-activity-box">
	<div class="import-progressing"><img src="<?php echo CSVIWPTABLES_URL; ?>/img/30.svg" alt="Uploading..." /><p class="warning">Do not close this window untill imported done.</p></div>
	<ul class="import-activity-message">

	</ul>
</div>

<?php


//https://github.com/parsecsv/parsecsv-for-php

//$data_source = CSVIWPTABLES_ROOT . '/inc/Rates_Future.csv';

// echo $data_source;
//
// echo "<br />";
//
//
// $home_url = home_url('/', 'https');
// echo $home_url;
// echo "<br />";
// $data_file = 'https://benefitlink.staging.wpengine.com/wp-content/uploads/2018/08/Underwriting_Conditions.csv';
// $data_source = str_replace($home_url, ABSPATH, $data_file);
//
// echo $data_source;
//
// echo "<br />";
//
// echo ABSPATH;
//
// $csv = new ParseCsv\Csv();
//
// # offset from the beginning of the file,
// # ignoring the first X number of rows.
// $csv->offset = 0;
//
// # limit the number of returned rows.
// $csv->limit = 1;
//
// $csv->auto($data_source);
//
// debug($csv->data);


?>

<script type="text/javascript">

		jQuery(document).ready(function() {
		    if (jQuery('.set_csv_file').length > 0) {
		        if ( typeof wp !== 'undefined' && wp.media && wp.media.editor) {
		            jQuery(document).on('click', '.set_csv_file', function(e) {
		                e.preventDefault();
		                var button = jQuery(this);
		                var id = button.prev();
		                wp.media.editor.send.attachment = function(props, attachment) {
		                    id.val(attachment.url);
		                };
		                wp.media.editor.open(button);
		                return false;
		            });
		        }
		    }
		});

    jQuery(document).ready(function($) {
        jQuery('.import-progressing').hide();
        jQuery('.button-upload-process').click(function() {
						jQuery('.import-activity-box').show('slow');
            jQuery('.import-progressing').show('slow');
						jQuery('.button-upload-process').delay(30).fadeOut('slow');
            var dataContainer = {
                put_table_name: jQuery(this).data('put_table_name'),
								csv_file: jQuery(this).data('csv_file'),
								row_offset: jQuery(this).data('row_offset'),
								row_limit: jQuery(this).data('row_limit'),
                security: '<?php echo $csvdata_nonce; ?>',
				        action: 'send-csv-import-wpdatables-data'
            };

						import_csv_data(dataContainer);

						function import_csv_data(dataContainer){
							jQuery.ajax({
									action: "send-csv-import-wpdatables-data",
									type: "POST",
									dataType: "json",
									url: ajaxurl,
									data: dataContainer,
									success: function(data){
										//alert(data.msg);
										if(data.msg == 'Complete'){
											//alert(data.test);
											if(data.count_rows > 0){
													var msg_data_added = '<li>'+ data.import_msg + '</li>';

													jQuery('.import-activity-message').prepend(msg_data_added);

													var newDataContainer = {
															put_table_name: data.datatable,
															csv_file: data.csv_file,
															row_offset: data.offset,
															row_limit: data.limit,
															security: '<?php echo $csvdata_nonce; ?>',
															action: 'send-csv-import-wpdatables-data'
													};

													setTimeout(function(){
														import_csv_data(newDataContainer);
													}, 100);
											}else{
												jQuery('.import-progressing').delay(30).fadeOut('slow');
												jQuery('.import-activity-message').prepend('<li class="complete">All data have been successfully imported!</li>');
											}
										}else{
											jQuery('.import-activity-message').html('<span class="error">Import error...</span>');
											jQuery('.import-progressing').delay(300).fadeOut('slow');
										}
									}
							});
						}

        });

    });
</script>

</div>
