<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<div class="wrap see_wrap">

<h1><?php echo __('Analytic graphs','activitytime'); ?></h1>

<div class="see-wrapper wal_analyze-box">
    <form action="<?php echo admin_url("admin.php?page=activitytime"); ?>" method="POST" enctype="multipart/form-data" class="above_panel">
        <span><?php echo __('From Date','activitytime'); ?>:</span>
        <input type="text" id="history_date_from" value="<?php echo wmvc_show_data('history_date_from', $db_data, $date_from);?>" name="history_date_from" class="history_date_from history_date" placeholder="<?php echo __('From Date From', 'activitytime'); ?>" />
        <span><?php echo __('To Date','activitytime'); ?>:</span> 
        <input type="text" id="history_date_to" value="<?php echo wmvc_show_data('history_date_to', $db_data, $date_to);?>" name="history_date_to" class="history_date_to history_date" placeholder="<?php echo __('From Date To', 'activitytime'); ?>" />
        <span><?php echo __('Account type','activitytime'); ?>:</span> 
        <?php             
            echo wmvc_select_option('account_type', $roles_prepare, wmvc_show_data('account_type', $db_data, ''), ''); 
        ?>
        <input type="submit" class="page-title-action" value="<?php echo __('Filter','activitytime'); ?>">
    </form>

</div>

<div class="see-wrapper">

    <div class="row">
        <div class="col-md-6">

            <div class="see-panel see-panel-default">
                <div class="see-panel-heading flex">
                    <h3 class="see-panel-title"><?php echo __('Most active users (Top 20) by Sessions Activity','activitytime'); ?></h3>
                    <a href="<?php echo admin_url("admin.php?page=activitytime&function=csv_export_users"); ?>" class="page-title-action pull-right <?php if ( !function_exists('activitytimepro_fms') || !activitytimepro_fms()->is_plan_or_trial('activitytimepropro') ) echo 'actt-pro'; ?>"><span class="dashicons dashicons-download"></span>&nbsp;&nbsp;<?php echo __('Export all CSV','activitytime')?></a>
                </div>
                <div class="see-panel-body">

                <?php if(count($most_active_users) == 0): ?>
                <div class="alert alert-info"><?php echo __('No results found','activitytime'); ?></div>
                <?php else: ?>
                <div id="canvas-holder" style="width:100%">
                    <canvas id="chart-area"></canvas>
                </div>

                <table class="table table-striped" style="width: 100%;">
                    <thead>
                        <tr>
                            <th data-priority="1"><?php echo __('User', 'activitytime'); ?></th>
                            <th data-priority="2"><?php echo __('Active h:m:s', 'activitytime'); ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($most_active_users as $row): ?>
                        <tr>
                            <td><?php echo wp_kses_post($row->user_info); ?></td>
                            <td>
                            <?php 
                                $init = $row->total_time;
                                $hours = floor($init / 3600);
                                $minutes = floor(($init / 60) % 60);
                                $seconds = $init % 60;

                                echo wp_kses_post("$hours:$minutes:$seconds");
                            ?>
                            </td>
                            <td><?php echo btn_read(admin_url("admin.php?page=actt_sessions&filter_user=%23".$row->user_id), ' '); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="alert alert-info"><?php echo __('For all info without limit please export CSV','activitytime'); ?></div>

                <?php endif; ?>

                </div>
            </div>

        </div>
        <div class="col-md-6">

        <div class="see-panel see-panel-default">
                <div class="see-panel-heading flex">
                    <h3 class="see-panel-title"><?php echo __('Most active pages (Top 20)','activitytime'); ?></h3>
                    <a href="<?php echo admin_url("admin.php?page=activitytime&function=csv_export_pages"); ?>" class="page-title-action pull-right <?php if ( !function_exists('activitytimepro_fms') || !activitytimepro_fms()->is_plan_or_trial('activitytimepropro') ) echo 'actt-pro'; ?>"><span class="dashicons dashicons-download"></span>&nbsp;&nbsp;<?php echo __('Export all CSV','activitytime')?></a>
                </div>
                <div class="see-panel-body">

                <?php if(count($most_active_pages) == 0): ?>
                <div class="alert alert-info"><?php echo __('No results found','activitytime'); ?></div>
                <?php else: ?>
                <div id="canvas-holder" style="width:100%">
                    <canvas id="chart-area-pages"></canvas>
                </div>

                <table class="table table-striped" style="width: 100%;">
                    <thead>
                        <tr>
                            <th data-priority="1"><?php echo __('Page', 'activitytime'); ?></th>
                            <th data-priority="2"><?php echo __('Active h:m:s', 'activitytime'); ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($most_active_pages as $row): ?>
                        <tr>
                            <td><?php echo wp_kses_post($row->title); ?></td>
                            <td>
                            <?php 
                                $init = $row->total_time;
                                $hours = floor($init / 3600);
                                $minutes = floor(($init / 60) % 60);
                                $seconds = $init % 60;

                                echo wp_kses_post("$hours:$minutes:$seconds");
                            ?>
                            </td>
                            <td><?php echo btn_read(admin_url("admin.php?page=actt_time_per_page&filter_title=".strip_tags($row->title)), ' '); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="alert alert-info"><?php echo __('For all info without limit please export CSV','activitytime'); ?></div>
                <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>
</div>


<?php

wp_enqueue_style('activitytime_basic_wrapper');

?>

<script>
 
// Generate table
jQuery(document).ready(function($) {
    $('#din-table input[type="text"]').val('');

});

</script>

<script>
		var config = {
			type: 'doughnut',
			data: {
				datasets: [{
					data: [
                        <?php echo wp_kses_data($most_active_users_data); ?>
					],
					backgroundColor: [
						<?php echo wp_kses_data($most_active_users_colors); ?>
					],
					label: 'Dataset 1'
				}],
				labels: [
					<?php echo wp_kses_data($most_active_users_labels); ?>
				]
			},
			options: {
				responsive: true,
				legend: {
					position: 'bottom',
				},
				title: {
					display: false,
					text: 'Most active users'
				},
				animation: {
					animateScale: true,
					animateRotate: true
				}
			}
        };
        
		var config_pages = {
			type: 'doughnut',
			data: {
				datasets: [{
					data: [
                        <?php echo wp_kses_data($most_active_pages_data); ?>
					],
					backgroundColor: [
						<?php echo wp_kses_data($most_active_pages_colors); ?>
					],
					label: 'Dataset 1'
				}],
				labels: [
					<?php echo wp_kses_data($most_active_pages_labels); ?>
				]
			},
			options: {
				responsive: true,
				legend: {
					position: 'bottom',
				},
				title: {
					display: false,
					text: 'Most active pages'
				},
				animation: {
					animateScale: true,
					animateRotate: true
				}
			}
		};

		window.onload = function() {

            if(document.getElementById('chart-area'))
            {
                var ctx = document.getElementById('chart-area').getContext('2d');
                window.myDoughnut = new Chart(ctx, config);
            }

            if(document.getElementById('chart-area-pages'))
            {
                var ctx_pages = document.getElementById('chart-area-pages').getContext('2d');
			    window.myDoughnut = new Chart(ctx_pages, config_pages);
            }

		};

	</script>
        
<?php
 wp_enqueue_script( 'jquery-ui-datepicker' );
 wp_enqueue_style('jqueryui', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css', false, null );
?>

<script>
  jQuery( function($) {
    $( ".history_date" ).datepicker({ dateFormat: 'yy-mm-dd' });
    $( "#anim" ).on( "change", function() {
      $( ".history_date" ).datepicker( "option", "showAnim", $( this ).val() );
    });
  } );
</script>
<style>

canvas {
    -moz-user-select: none;
    -webkit-user-select: none;
    -ms-user-select: none;
}

input[type="text"].history_date,select[name="account_type"] {
    padding: 4px 10px;
    outline: initial;
    border: 1px solid #cacaca;
    background: #fff;
    -webkit-box-shadow: 0 0 5px 1px rgba(111, 111, 111, 0.28);
    box-shadow: 0 0 5px 1px rgba(111, 111, 111, 0.12);
    margin-right: 15px;
}

input[type="text"].history_date,select[name="account_type"],
.wrap.see_wrap .page-title-action, .wrap.see_wrap .page-title-action:focus, .wrap.see_wrap .page-title-action:active {
    border-radius: 6px;
}

</style>

<?php $this->view('general/footer', $data); ?>
