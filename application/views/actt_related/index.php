<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<div class="wrap see_wrap">

<h1><?php echo __('Related plugins','activitytime'); ?></h1>

<div class="see-wrapper">
    <div class="see-panel see-panel-default">
        <div class="see-panel-heading flex">
            <h3 class="see-panel-title"><?php echo __('Plugins which may be interested for you','activitytime'); ?></h3>
        </div>
        <div class="see-panel-body">

        <?php foreach($plugins_list as $slug=>$plugin): ?>

        <h2><?php echo esc_html($plugin->name); ?></h2>

        <p><b><?php echo wp_kses_post(join(', ', (array) $plugin->tags)); ?></b></p>

        <a target="_blank" href="https://wordpress.org/plugins/<?php echo esc_attr($slug); ?>/"><img src="https://ps.w.org/<?php echo esc_attr($slug); ?>/assets/banner-772x250.png" alt="<?php echo esc_attr($plugin->name.' '.__('Banner Image','activitytime')); ?>" /></a>

        <p><?php echo wp_kses_post(substr(strip_tags($plugin->sections->description),0,400)).'...'; ?></p>
        
        <?php if(!file_exists(ACTIVITYTIME_PATH.'../'.$slug)): ?>
        <a class="install-now button" href="<?php echo admin_url('update.php?action=install-plugin&plugin='.$slug.'&_wpnonce='.wp_create_nonce( 'install-plugin_'.$slug )); ?>"><?php echo __('Install plugin','activitytime'); ?> <?php echo esc_html($plugin->name); ?></a>
        <?php else: ?>
        <p class="alert alert-info"><?php echo __('Wow! This plugin is found in your installation!','activitytime'); ?></p>
        <?php endif; ?>
        <?php //dump(join(', ', (array) $plugin->tags)); ?>

        <br />
        <?php endforeach; ?>

        </div>
    </div>
    
</div>
</div>


<?php

wp_enqueue_style('activitytime_basic_wrapper');

?>

<script>
 
jQuery(document).ready(function($) {

});

</script>


<style>

.see-wrapper #din-table_wrapper .row
{
    margin:0px;
}

.see-wrapper .dataTable div.dataTables_wrapper label
{
    width:100%;
    padding:10px 0px;
}

.dataTable div.dataTables_wrapper div.dataTables_filter input
{
    display:inline-block;
    width:65%;
    margin: 0 10px;
}

.dataTable div.dataTables_wrapper div.dataTables_length select
{
    display:inline-block;
    width:100px;
    margin: 0 10px;
}

.dataTable td.control
{
    color:#337AB7;
    display:table-cell !important;
    font-weight: bold;
}

.dataTable th.control
{
    display:table-cell !important;
}

.see-wrapper .table > tbody > tr > td, .see-wrapper .table > tbody > tr > th, 
.see-wrapper .table > tfoot > tr > td, .see-wrapper .table > tfoot > tr > th, 
.see-wrapper .table > thead > tr > td, .see-wrapper .table > thead > tr > th {
    vertical-align: middle;
}

table.dataTable tbody > tr.odd.selected, table.dataTable tbody > tr > .odd.selected {
    background-color: #B0BED9;
}

.see-wrapper table.dataTable tbody td.select-checkbox::before, 
.see-wrapper table.dataTable tbody td.select-checkbox::after, 
.see-wrapper table.dataTable tbody th.select-checkbox::before, 
.see-wrapper table.dataTable tbody th.select-checkbox::after {
    display: block;
    position: absolute;
    /*top: 2.5em;*/
    top:50%;
    left: 50%;
    width: 12px;
    height: 12px;
    box-sizing: border-box;
}

.see-wrapper a#bulk_remove:hover,
.see-wrapper a#bulk_remove:focus {
    text-decoration: none;
}

tfoot input{
    width:100%;
    min-width:70px;
}

img.avatar
{
    width: 50px;
    height: 50px;
    border-radius: 50%;
}

.wal-system-icon{
    width: 50px;
    font-size: 50px;
    height: 50px;
}

.dashicons.wal-system-icon.dashicons-before::before {
    display: inline-block;
    font-family: dashicons;
    transition: color .1s ease-in;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    width: 50px;
    font-size: 50px;
    height: 50px;
}

/* sw_log_notify */

.sw_log_notify-box {
    position: fixed;
    right: 15px;
    bottom: 0;
    z-index: 100;
    
    position: fixed;
    z-index: 5000;
    bottom: 10px;
    right: 10px;
}

.sw_log_notify {
    position: relative;
    background: #fffffff7;
    padding: 12px 15px;
    border-radius: 15px;
    width: 250px;
    box-shadow: 0px 1px 0px 0.25px rgba(0, 0, 0, 0.07);
    -webkit-box-shadow: 0px 0 3px 2px rgba(0, 0, 0, 0.08);
    margin: 0;
    margin-bottom: 10px;
    font-size: 16px;
    
    background: #5cb811;
    background: rgba(92, 184, 17, 0.9);
    padding: 15px;
    border-radius: 4px;
    color: #fff;
    text-shadow: -1px -1px 0 rgba(0, 0, 0, 0.5);
    
    -webkit-transition: all 500ms cubic-bezier(0.175, 0.885, 0.32, 1.275);
    -moz-transition: all 500ms cubic-bezier(0.175, 0.885, 0.32, 1.275);
    -ms-transition: all 500ms cubic-bezier(0.175, 0.885, 0.32, 1.275);
    -o-transition: all 500ms cubic-bezier(0.175, 0.885, 0.32, 1.275);
    transition: all 500ms cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.sw_log_notify.error  {
    margin: 0;
    margin-bottom: 10px;
    background: #cf2a0e;
    padding: 12px 15px;
}

.sw_log_notify.loading  {
    background: #5bc0de;
}

.sw_log_notify {
    display: block;
    margin-top: 10px;
    position: relative;
    opacity: 0;
    transform: translateX(120%);
}

.sw_log_notify.show {
    transform: translateX(0);
    opacity: 1;
}
    
/* end sw_log_notify */

.see-wrapper .dataTables_filter .form-control {
    height: 30px;
}


body .see-wrapper .table-responsive {
    overflow-x: visible;
}


body .datepicker table.table-condensed tbody > tr:hover > td:first-child, body .datepicker table.table-condensed tbody > tr.selected > td:first-child {
    border-left: 0px solid #fba56a;
    border-radius: 3px 0 0 3px;
}
body .datepicker table.table-condensed tbody > tr > td:first-child {
    border-left: 0px solid #ffff;
    border-radius: 3px 0 0 3px;
}

</style>

<?php $this->view('general/footer', $data); ?>
