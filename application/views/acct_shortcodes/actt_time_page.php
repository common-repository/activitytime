<?php
/**
 * The template for Shortcode Listings list
 * This is the template that Shortcode listings list
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<div class="actt-shortcode" id="wdk_el_<?php echo esc_attr(wmvc_show_data('id', $attr));?>">
    <div class="actt-actt_time_page">
        <table class="actt-actt_time_page--table">
            <thead>
                <tr>
                    <th><?php echo esc_html__('User','winter-activity-log');?></th>
                    <th><?php echo esc_html__('Time Start','winter-activity-log');?></th>
                    <th><?php echo esc_html__('Last activity','winter-activity-log');?></th>
                    <th><?php echo esc_html__('Total (m:s)','winter-activity-log');?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($db_data as $key => $row):?>
                    <?php
                        if(empty($row->time_sec_total_acc))
                        {
                            $time_sec_total = '';
                        }
                        else
                        {
                            $init = $row->time_sec_total_acc;
                            $minutes = floor(($init / 60));
                            $seconds = $init % 60;

                            $time_sec_total = "$minutes:$seconds";
                        }
                    ?>
                    <tr>
                        <td><?php echo wp_kses_post($row->user_info);?></td>
                        <td><?php echo esc_html($row->time_start_min);?></td>
                        <td><?php echo esc_html($row->time_end_max);?></td>
                        <td><?php echo esc_html($time_sec_total);?><?php if($row->is_visit_end_min == 0):?><span class="actt_label actt_label-success puls"><?php echo esc_html__('active','winter-activity-log');?></span><?php endif;?></td>
                    </tr>
                <?php endforeach;?>
            </tbody>
        </table>
    </div>
</div>







