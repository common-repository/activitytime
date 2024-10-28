<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<div class="wrap see_wrap">

<h1><?php echo __('Manage current active page visits','activitytime'); ?></h1>

<div class="see-wrapper">
    <div class="see-panel see-panel-default">
        <div class="see-panel-heading flex">
            <h3 class="see-panel-title"><?php echo __('Current active','activitytime'); ?></h3>
            <a href="#bulk_remove-form" id="bulk_remove" class="page-title-action pull-right popup-with-form"><i class="fa fa-remove"></i>&nbsp;&nbsp;<?php echo __('Bulk remove','activitytime')?><i class="fa fa-spinner fa-spin fa-custom-ajax-indicator-opc ajax-indicator-masking hidden_opacity"></i></a>
        </div>
        <div class="see-panel-body">

            <!-- Data Table -->
            <div class="box box-without-bottom-padding">
                <div class="tableWrap dataTable table-responsive js-select">
                    <table id="din-table" class="table table-striped" style="width: 100%;">
                        <thead>
                            <tr>
                                <th data-priority="1">#</th>
                                <th data-priority="1"><?php echo __('URI', 'activitytime'); ?></th>
                                <th data-priority="2"><?php echo __('Title', 'activitytime'); ?></th>
                                <th data-priority="2"><?php echo __('User', 'activitytime'); ?></th>
                                <th data-priority="2"><?php echo __('Time Start', 'activitytime'); ?></th>
                                <th data-priority="2"><?php echo __('Last activity', 'activitytime'); ?></th>
                                <th data-priority="3"></th>
                                <th><input type="checkbox" class="selectAll" name="selectAll" value="all"></th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                        <tfoot>
                            <tr>
                                <th><input type="text" name="filter_id" class="dinamic_par"  placeholder="<?php echo __('Filter #', 'activitytime'); ?>" /></th>
                                <th><input type="text" name="filter_request_uri" class="dinamic_par" placeholder="<?php echo __('Filter URI', 'activitytime'); ?>" /></th>
                                <th><input type="text" name="filter_title" class="dinamic_par" placeholder="<?php echo __('Filter Title', 'activitytime'); ?>" /></th>
                                <th><input type="text" name="filter_user" id="filter_user" class="dinamic_par" placeholder="<?php echo __('Filter User', 'activitytime'); ?>" /></th>
                                <th><input type="text" name="filter_time_start" class="dinamic_par" placeholder="<?php echo __('Filter Time Start', 'activitytime'); ?>" /></th>
                                <th><input type="text" name="filter_time_end" class="dinamic_par" placeholder="<?php echo __('Filter Last activity', 'activitytime'); ?>" /></th>
                                <th></th>
                                <th>
                                    <div class="winterlock_save_search_filter <?php if ( !function_exists('activitytimepro_fms') || !activitytimepro_fms()->is_plan_or_trial('activitytimepropro') ) echo 'actt-pro'; ?>">
                                        <div class="winterlock_save_search_filter_btn">
                                            <a href="#" class="btn btn_save"><?php echo __('Save', 'activitytime'); ?></a>
                                            <a href="#" class="btn-toggle"><i class="fa fa-angle-down"></i></a>
                                        </div>
                                        <ul class="winterlock_list_filters">
                                        </ul>
                                    </div>
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="form-inline">
                <div class="footer-btns">
                    <a href="<?php echo admin_url("admin.php?page=actt_current_active&function=export_csv_current"); ?>" class="export_csv btn btn-warning pull-right <?php if ( !function_exists('activitytimepro_fms') || !activitytimepro_fms()->is_plan_or_trial('activitytimepropro') ) echo 'actt-pro'; ?>"><i class="fa fa-download"></i>&nbsp;&nbsp;<?php echo __('Export CSV','activitytime')?></a>
                    <a href="#clear_filters" id="clear_filters" class="btn btn-danger pull-right pull-right "><i class="fa fa-trash"></i>&nbsp;&nbsp;<?php echo __('Clear all filters','activitytime')?></a>
                </div>
            </div>

        </div>
    </div>
    
</div>
</div>


<?php

wp_enqueue_style('activitytime_basic_wrapper');
wp_enqueue_script( 'datatables' );
wp_enqueue_script( 'dataTables-responsive' );
wp_enqueue_script( 'dataTables-select' );

wp_enqueue_style( 'dataTables-select' );
?>

<script>
 
var wal_timer_live_monitoring;
var temp_change = '';

// Generate table
jQuery(document).ready(function($) {
    var table;

    $('#din-table input[type="text"]').val('');

    /* update filters for export */
    /*
    $('[name="filter_user"]').on('change', function(){
        if($(this).val() != '') {
            $('.export_csv').attr('href', '<?php echo get_admin_url() . "admin.php?page=actt_current_active&function=export_csv_current"; ?>&filter_user='+$(this).val());
        } else {
            $('.export_csv').attr('href', '<?php echo get_admin_url() . "admin.php?page=actt_current_active&function=export_csv_current"; ?>');
        }
    });*/

    $('#din-table input[type="text"]').on('keyup', function()
    {
        var query_filter = $('#din-table input[type="text"]').serialize();

        $('.export_csv').attr('href', '<?php echo get_admin_url() . "admin.php?page=actt_current_active&function=export_csv_current"; ?>&'+query_filter);
    });

    /* clear all filters*/
    $('#clear_filters').click(function(e){
        e.preventDefault();
        $('.dinamic_par:not([name="sw_log_count"]):not([name="sw_log_search"])').val('').trigger('change');
        $('.dinamic_par[name="sw_log_count"]').val('10').trigger('change');
        /*fix if set not date */
        //jQuery('#filter_date').data("DateTimePicker").date(new Date());
        $('#filter_date').data("DateTimePicker").clear()
        table.search('');
        table.draw();
        return false;
    });

    //$(".selectAll").unbind();

    $(".selectAll").on( "click", function(e) {
        if ($(this).is( ":checked" )) {
            table.rows(  ).select();        
            //$(this).attr('checked','checked');
        } else {
            table.rows(  ).deselect(); 
            //$(this).attr('checked','');
        }
        //return false;
    });

    $('#bulk_remove').click(function(){
        var count = table.rows( { selected: true } ).count();
        var load_indicator_opc = $('.fa-custom-ajax-indicator-opc');
        load_indicator_opc.removeClass('hidden_opacity');
        if(count == 0)
        {
            alert('<?php echo esc_attr__('Please select listings to remove', 'activitytime'); ?>');
            load_indicator_opc.addClass('hidden_opacity');
            return false;
        }
        else
        {

            if(confirm('<?php echo_js(__('Are you sure?', 'activitytime')); ?>'))
            {
                $('img#ajax-indicator-masking').show();

                var form_selected_listings = table.rows( { selected: true } );
                var ids = table.rows( { selected: true } ).data().pluck( 'idvisited_pages' ).toArray();

                // ajax to remove rows
                $.post('<?php menu_page_url( 'actt_current_active', true ); ?>&function=bulk_remove', { visited_pages_ids: ids }, function(data) {

                    $('img#ajax-indicator-masking').hide();

                    table.ajax.reload();

                }).success(function(){load_indicator_opc.addClass('hidden_opacity');});
            } else {
                load_indicator_opc.addClass('hidden_opacity');
            }
        }

        return false;
    });


	if ($('#din-table').length) {

        sw_log_s_table_load_counter = 0;

        table = $('#din-table').DataTable({
            "ordering": true,
            "responsive": true,
            "processing": true,
            "serverSide": true,
            'ajax': {
                "url": ajaxurl,
                "type": "POST",
                "data": function ( d ) {

                    $(".selectAll").prop('checked', false);

                    return $.extend( {}, d, {
                        "page": 'actt_current_active',
                        "function": 'datatable',
                        "action": 'activitytime_mvc_action'
                    } );
                }
            },
            "language": {
                search: "<?php echo_js(__('Search', 'activitytime')); ?>",
                searchPlaceholder: "<?php echo_js(__('Enter here filter tag for any column', 'activitytime')); ?>"
            },
            "initComplete": function(settings, json) {
            },
            "fnDrawCallback": function (oSettings){

                if(sw_log_s_table_load_counter == 0)
                {
                    sw_log_s_table_load_counter++;
                    if($('#filter_user').val() != '')
                    setTimeout(function(){ table.columns(3).search( $('#filter_user').val() ).draw(); }, 1000);
                    
                }

                $('a.delete_button').click(function(){
                    
                    if(confirm('<?php echo_js(__('Are you sure?', 'activitytime')); ?>'))
                    {
                       // ajax to remove row
                        $.post($(this).attr('href'), function( [] ) {
                            table.row($(this).parent()).remove().draw( false );
                        });
                    }

                   return false;
                });

                if ( table.responsive.hasHidden() )
                {
                    jQuery('table.dataTable td.details-control,table.dataTable td.details-controled').addClass('details-control');
                }
                else
                {
                    jQuery('table.dataTable td.details-control').removeClass('details-control').addClass('details-controled');
                }
                jQuery('.dataTable div.dataTables_wrapper div.dataTables_filter input').addClass("dinamic_par").attr('name','sw_log_search');
                jQuery('.dataTable div.dataTables_wrapper div.dataTables_length select').addClass("dinamic_par").attr('name','sw_log_count');
                
            },
            'columns': [
                {   "className":      'details-control',
                    "defaultContent": '',
                    data: "idvisited_pages"
                 },
                { data: "request_uri" },
                { data: "title" },
                { data: "user_info" },
                { data: "time_start" },
                { data: "time_end" },
                { data: "edit" },
                { data: "checkbox" }
            ],
            columnDefs: [
                { responsivePriority: 1, targets: 0 },
                { responsivePriority: 2, targets: -2 }
            ],
            responsive: {
                details: {
                    type: 'column',
                    target: 0
                }
            },
            order: [[ 0, 'desc' ]],
            columnDefs: [   {
                                //className: 'control',
                                className: 'details-control',
                                orderable: true,
                                targets:   0
                            },
                            {
                                //className: 'control',
                                //className: 'details-control',
                                orderable: true,
                                targets:   1
                            },
                            {
                                //className: 'control',
                                //className: 'details-control',
                                orderable: false,
                                targets:   6
                            },
                            {
                                className: 'select-checkbox',
                                orderable: false,
                                defaultContent: '',
                                targets:   7
                            }
            ],
            select: {
                style:    'multi',
                selector: 'td:last-child'
            },
			'oLanguage': {
				'oPaginate': {
					'sPrevious': '<i class="fa fa-angle-left"></i>',
					'sNext': '<i class="fa fa-angle-right"></i>'
				},
                'sSearch': "<?php echo_js(__('Search', 'activitytime')); ?>",
                "sLengthMenu": "<?php echo_js(__('Show _MENU_ entries', 'activitytime')); ?>",
                "sInfoEmpty": "<?php echo_js(__('Showing 0 to 0 of 0 entries', 'activitytime')); ?>",
                "sInfo": "<?php echo_js( __('Showing _START_ to _END_ of _TOTAL_ entries', 'activitytime')); ?>",
                "sEmptyTable": "<?php echo_js(__('No data available in table', 'activitytime')); ?>",
			},
			'dom': "<'row'<'col-sm-7 col-md-5'f><'col-sm-5 col-md-6'l>>" + "<'row'<'col-sm-12'tr>>" + "<'row'<'col-sm-5'i><'col-sm-7'p>>"
		});
        
//		$('.js-select select:not(.basic-select)').select2({
//			minimumResultsForSearch: Infinity
//		});
        
        // Apply the search
        table.columns().every( function () {
            var that = this;
     
            $( 'input,select', this.footer() ).on( 'keyup change', function () {
                if ( that.search() !== this.value ) {
                    that
                        .search( this.value )
                        .draw();
                }
            } );

        } );

        if ($('#wal_live_monitoring').is(':checked')) {
            //wal_timer_live_monitoring = setInterval(function(){ table.ajax.reload(); }, 10000);
        }
        
        table.on( 'responsive-resize', function ( e, datatable, columns ) {
                if ( datatable.responsive.hasHidden() )
                {
                    jQuery('table.dataTable td.details-control,table.dataTable td.details-controled').addClass('details-control');
                }
                else
                {
                    jQuery('table.dataTable td.details-control').removeClass('details-control').addClass('details-controled');
                }
        } ); 
	}

    // Add event listener for opening and closing details
    $('table.dataTable tbody').on('click', 'td.details-control', function () {
        var tr = $(this).closest('tr');
        var row = table.row( tr );
 
        if ( row.child.isShown() ) {
            // This row is already open - close it
            //row.child.hide();
            tr.removeClass('shown');
        }
        else {
            // Open this row
            //row.child( format(row.data()) ).show();
            tr.addClass('shown');
        }
    });

 /* winterlock_save_search_filter */
    function generate_json_filter()
    {
        var js_gen = '{';
        jQuery('.dinamic_par').each(function(){
            js_gen+= '"'+jQuery(this).attr('name')+'":"'+jQuery(this).val()+'",';
        });
        js_gen = js_gen.slice(0,-1);
        js_gen+= '}';

        return js_gen;
    }

    function sw_log_notify(text, type, popup_place) {
        var $ = jQuery;
        if(!$('.sw_log_notify-box').length) $('body').append('<div class="sw_log_notify-box"></div>')
        if(typeof text=="undefined") var text = 'Undefined text';
        if(typeof type=="undefined") var type = 'success';
        if(typeof popup_place=="undefined") var popup_place = $('.sw_log_notify-box');
        var el_class = '';
        var el_timer= 5000;
        switch(type){
            case "success" : el_class = "success";
                            break
            case "error" : el_class = "error";
                            break
            case "loading" : el_class = "loading";
                             el_timer = 2000;
                            break
            default : el_class = "success";
                            break
        }

        /* notify */
        var html = '';
        html = '<div class="sw_log_notify '+el_class+'">\n\
                       '+text+'\n\
               </div>';
        var notification = $(html).appendTo(popup_place).delay(100).queue(function () {
                            $(this).addClass('show')
                                setTimeout(function() {
                                    notification.removeClass('show')
                                    setTimeout(function() {
                                        notification.remove();
                                    }, 1000);     
                                }, el_timer);  
                            })
        /* end notify */
    }

    function reload_filters()
    {
        var $ = jQuery;
        var data = {
            'function': 'filter_get',
            "page": 'actt_current_active',
            "action": 'activitytime_mvc_action',
        };

        $.post('<?php echo esc_url(admin_url( 'admin-ajax.php' ));?>', data,
        function(data){
            var html ='';
            $('.winterlock_save_search_filter .winterlock_list_filters').html(html);
            if(data.success && data.results){
                $.each(data.results, function(key, value){
                     html +='<li><a href="#" class="btn-load-save" data-filter="">'+value.name+'<textarea class="hidden">'+value.filter_par+'</textarea></a><a href="#" data-fielderid="'+value.filterid+'" class="remove"><i class="fa fa-remove"></i></a></li>';
                    //$('.winterlock_save_search_filter.show .winterlock_list_filters').append(html).find('li').last().find('.btn-load-save').get(0).filter_par =value.filter_par;
                })
            }
            $('.winterlock_save_search_filter .winterlock_list_filters').html(html);
        }, "json").success(function(){
            reload_elements_filter();
});
    }

    
    /* reload save elements with events */
    function reload_elements_filter(){
        var $ = jQuery;

        /* get filters elements */
        $('.winterlock_save_search_filter .winterlock_list_filters a.btn-load-save').
            off().on('click', function(e){
                e.preventDefault();
                var filter_par = JSON.parse($(this).find('textarea').val());
                jQuery('.dinamic_par').each(function(){
                    //sw_log_search
                    if(typeof filter_par[jQuery(this).attr('name')] != 'undefined'){
                        if(jQuery(this).attr('name')=='sw_log_search') {
                            table.search(filter_par[jQuery(this).attr('name')]);
                            table.draw();
                        } else {
                            jQuery(this).val(filter_par[jQuery(this).attr('name')]);
                        }
                    }
                }).trigger('change');

                setTimeout(function(){jQuery('.dinamic_par[name=\"sw_log_search\"]').trigger('change');},1500);

                sw_log_notify('<?php echo __('Loaded filter', 'activitytime'); ?> '+$(this).contents()[0].textContent);
                $(this).closest('.winterlock_save_search_filter').removeClass('show');
        })

        $('.winterlock_save_search_filter .winterlock_list_filters a.remove').
            off().on('click', function(e){
                e.preventDefault();
                var title = $(this).parent().find('.btn-load-save').eq(0).contents()[0].textContent;
                var data = {
                    'function': 'filter_remove',
                    "page": 'actt_current_active',
                    "action": 'activitytime_mvc_action',
                    "filter_id": $(this).attr('data-fielderid') || '',
                };
                sw_log_notify('<?php echo __('Removing filter', 'activitytime'); ?> '+title, 'loading');

                $.post('<?php echo esc_url(admin_url( 'admin-ajax.php' ));?>', data,
                function(data){

                }, "json").success(function(){
                    sw_log_notify('<?php echo __('Removed filter', 'activitytime'); ?> '+title);
                    reload_filters();
                });
        })
    }
    
    $('.winterlock_save_search_filter .winterlock_save_search_filter_btn a.btn_save').on('click', function(e){
        e.preventDefault()
        var is_empty = true;
        $('.dinamic_par:not([name="sw_log_count"])').each(function(){
            if($(this).val() !='') is_empty = false;
        });
        if($('.dinamic_par[name="sw_log_count"]').val() != 10) 
             is_empty = false;
        
        if(is_empty) {
            sw_log_notify('<?php echo __('Fitlers are empty', 'activitytime'); ?>', 'error');
            return false;   
        }
        
        
        $.confirm({
            boxWidth: '400px',
            useBootstrap: false,
            title: '<?php echo __('Save', 'activitytime'); ?>',
            content: '' +
            '<form action="" class="winterlock_list_filters_form formName">' +
            '<div class="form-group">' +
            '<label><?php echo __('Filter name', 'activitytime'); ?></label>' +
            '<input type="text" placeholder="<?php echo __('Filter name', 'activitytime'); ?>" class="filter_name form-control" required />' +
            '</div>' +
            '</form>',
            buttons: {
                formSubmit: {
                    text: '<?php echo __('Save', 'activitytime'); ?>',
                    btnClass: 'btn-blue',
                    action: function () {
                        var filter_name = this.$content.find('.filter_name').val();

                        var object_values = [];
                        $('.dinamic_par').each(function(){
                            object_values.push({name: $(this).attr('name'), value: $(this).val()});
                        });
                        var data = {
                            "page": 'actt_current_active',
                            "function": 'filter_save',
                            "action": 'activitytime_mvc_action',
                            "filter_name": filter_name,
                            "filter_param": generate_json_filter()
                        };

                        $.post('<?php echo esc_url(admin_url( 'admin-ajax.php' ));?>', data,
                        function(data){
                        }, "json").success(function(){
                            sw_log_notify('<?php echo __('Saved filter', 'activitytime'); ?> '+filter_name);
                            reload_filters();
                        } );

                    }
                },
                cancel: {
                    text: '<?php echo __('Cancel', 'activitytime'); ?>',
                    action: function () {
                    }
                }
            },
            onContentReady: function () {
                // bind to events
                var jc = this;
                this.$content.find('form').on('submit', function (e) {
                    // if the user submits the form by pressing enter in the field.
                    e.preventDefault();
                    jc.$$formSubmit.trigger('click'); // reference the button and click it
                });
            }
        });

    });
    
    $('.winterlock_save_search_filter .btn-toggle').on('click', function(e){
        e.preventDefault();
        var $filter_box = $(this).closest('.winterlock_save_search_filter');
        $filter_box.toggleClass('show');
        
    })
    
    $("html").on("click", function(){
        $(".winterlock_save_search_filter").removeClass("show");
    });
    
    $(".winterlock_save_search_filter").on("click", function(e) {
        e.stopPropagation();
    });
    
    reload_filters();
    /* end winterlock_save_search_filter */
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
