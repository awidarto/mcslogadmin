<script>
	var asInitVals = new Array();
	var dateBlock = <?php print getdateblock();?>;
	var rescheduled_id = 0;
    var refreshTab;
    var reschedulemode = 'incoming';

	$(document).ready(function() {

		$('#assign_all').click(function(){
			if($('#assign_all').is(':checked')){
				$('.assign_check').attr('checked', true);
			}else{
				$('.assign_check').attr('checked', false);
			}
		});

	    var oTable = $('.dataTable').dataTable(
			{
				"bProcessing": true,
		        "bServerSide": true,
		        "sAjaxSource": "<?php print site_url($ajaxurl);?>",
				"oLanguage": { "sSearch": "Search "},
                "sPaginationType": "full_numbers",
                "sDom": 'T<"clear">lfrtip',
				"oTableTools": {
					"sSwfPath": "<?php print base_url();?>assets/swf/copy_csv_xls_pdf.swf"
				},
			<?php if($this->config->item('infinite_scroll')):?>
				"bScrollInfinite": true,
			    "bScrollCollapse": true,
			    "sScrollY": "500px",
			<?php endif; ?>
			<?php if(isset($sortdisable)):?>
				"aoColumnDefs": [
				    { "bSortable": false, "aTargets": [ <?php print $sortdisable; ?> ] }
				 ],
			<?php endif;?>
			    "fnServerData": function ( sSource, aoData, fnCallback ) {
		            $.ajax( {
		                "dataType": 'json',
		                "type": "POST",
		                "url": sSource,
		                "data": aoData,
		                "success": fnCallback
		            } );
		        }
			}
		);

        refreshTab = function(){
            oTable.fnDraw();
        };

		$('tfoot input').keyup( function () {
			/* Filter on the column (the index) of this element */
			oTable.fnFilter( this.value, $('tfoot input').index(this) );
		} );

		/*
		 * Support functions to provide a little bit of 'user friendlyness' to the textboxes in
		 * the footer
		 */
		$('tfoot input').each( function (i) {
			asInitVals[i] = this.value;
		} );

		$('tfoot input').focus( function () {
			if ( this.className == 'search_init' )
			{
				this.className = '';
				this.value = '';
			}
		} );

		$('tfoot input').blur( function (i) {
			if ( this.value == '' )
			{
				this.className = 'search_init';
				this.value = asInitVals[$('tfoot input').index(this)];
			}
		} );

		$('#search_deliverytime').datepicker({ dateFormat: 'yy-mm-dd' });

		$('#search_deliverytime').change(function(){
			oTable.fnFilter( this.value, $('tfoot input').index(this) );
		});

		/*Delivery process mandatory*/
		$('#date_display').datepicker({
			numberOfMonths: 2,
			showButtonPanel: true,
			dateFormat:'yy-mm-dd',
			onSelect:function(dateText, inst){
				if(dateBlock[dateText] == 'weekend'){
					alert('no delivery on weekend');
				}else{
					$('#assign_deliverytime').val(dateText);
				}
			},
			beforeShowDay:getBlocking
		});


		$('#rescheduled_deliverytime').datetimepicker({
			numberOfMonths: 2,
			showButtonPanel: true,
			dateFormat:'yy-mm-dd',
			timeFormat: 'hh:mm:ss',
			onSelect:function(dateText, inst){
				if(dateBlock[dateText] == 'weekend'){
					alert('no delivery on weekend');
				}else if(dateBlock[dateText] == 'full'){
					alert('time slot is full');
				}else{
					$('#rescheduled_deliverytime').val(dateText);
				}
			},
			beforeShowDay:getBlocking
		});

		function getBlocking(d){
			/*
				$.datepicker.formatDate('yy-mm-dd', d);
			*/
			var curr_date = d.getDate();
			var curr_month = d.getMonth() + 1; //months are zero based
			var curr_year = d.getFullYear();

			curr_date = (curr_date < 10)?"0" + curr_date : curr_date;
			curr_month = (curr_month < 10)?"0" + curr_month : curr_month;
			var indate = curr_year + '-' + curr_month + '-' + curr_date;

			var select = 1;
			var css = 'open';
			var popup = 'working day';

			if(window.dateBlock[indate] == 'weekend'){
				select = 0;
				css = 'weekend';
				popup = 'weekend';
			}else if(window.dateBlock[indate] == 'holiday'){
				select = 0;
				css = 'weekend';
				popup = 'holiday';
			}else if(window.dateBlock[indate] == 'blocked'){
				select = 0;
				css = 'blocked';
				popup = 'zero time slot';
			}else if(window.dateBlock[indate] == 'full'){
				select = 0;
				css = 'blocked';
				popup = 'zero time slot';
			}else{
				select = 1;
				css = '';
				popup = 'working day';
			}
			return [select,css,popup];
		}

		//$('#search_deliverytime').datepicker({ dateFormat: 'yy-mm-dd' });
		//$('#assign_deliverytime').datepicker({ dateFormat: 'yy-mm-dd' });

		$('#neworder').click(function(){
			var src = '<?php print base_url() ?>admin/order/neworder';

			$('#neworder_frame').attr('src',src);
			$('#neworder_dialog').dialog('open');
		});

        $('#doLabel').click(function(){
            var assigns = [];
            var count = 0;
            $('.assign_check:checked').each(function(){
                assigns.push(this.value);
                count++;
            });

            if(count > 0){
                $.post(
                    '<?php print base_url().'ajax/printsession'; ?>',
                    { ids: assigns },
                    function(data){
                        if(data.result == 'OK'){
                            var delivery_id = 'SESS:' + data.session;
                            var res = $('#label_resolution').val();
                            var col = $('#label_columns').val();
                            var cell_height = $('#label_cell_height').val();
                            var cell_width = $('#label_cell_width').val();
                            var mright = $('#label_margin_right').val();
                            var mbottom = $('#label_margin_bottom').val();
                            var fsize = $('#label_font_size').val();
                            var codetype = $('#label_code_type').val();

                            $('#label_id').val(delivery_id);
                            var src = '<?php print base_url() ?>admin/prints/label/' + delivery_id  + '/' +  res +'/' +  cell_height + '/' + cell_width + '/' + col +'/'+ mright +'/'+ mbottom +'/'+ fsize +'/'+ codetype;
                            $('#label_frame').attr('src',src);
                            $('#label_dialog').dialog('open');
                        }
                    },'json');

            }else{
                alert('Please select one or more delivery orders');
            }
        });


		$('#doAssign').click(function(){
			var assigns = '';
			var count = 0;
			$('.assign_check:checked').each(function(){

				var deliverydate = $('#'+this.value).html();
				assigns += '<li style="padding:5px;border-bottom:thin solid grey;margin-left:0px;"><strong>'+this.value + '</strong><br />' + deliverydate +'</li>';
				count++;
			});

			if(count > 0){
				$('#trans_list').html(assigns);
				$('#assign_dialog').dialog('open');
			}else{
				alert('Please select one or more delivery orders');
			}
		});

		$('#doArchive').click(function(){
			var assigns = '';
			var count = 0;
			$('.assign_check:checked').each(function(){

				var deliverydate = $('#'+this.value).html();
				assigns += '<li style="padding:5px;border-bottom:thin solid grey;margin-left:0px;"><strong>'+this.value + '</strong><br />' + deliverydate +'</li>';
				count++;
			});

			if(count > 0){
				$('#archive_list').html(assigns);
				$('#archive_dialog').dialog('open');
			}else{
				alert('Please select one or more delivery orders');
			}
		});

		$('#doMarkscan').click(function(){
			var assigns = '';
			var count = 0;
			$('.assign_check:checked').each(function(){
				assigns += '<li style="padding:5px;border-bottom:thin solid grey;margin-left:0px;"><strong>'+this.value + '</strong></li>';
				count++;
			});

			if(count > 0){
				$('#markscan_list').html(assigns);
				$('#markscan_dialog').dialog('open');
			}else{
				alert('Please select one or more delivery orders');
			}
		});

        $('#label_refresh').on('click',function(){
            var delivery_id = $('#label_id').val();
            var res = $('#label_resolution').val();
            var col = $('#label_columns').val();
            var cell_height = $('#label_cell_height').val();
            var cell_width = $('#label_cell_width').val();
            var mright = $('#label_margin_right').val();
            var mbottom = $('#label_margin_bottom').val();
            var fsize = $('#label_font_size').val();
            var codetype = $('#label_code_type').val();

            var src = '<?php print base_url() ?>admin/prints/label/' + delivery_id + '/' + res + '/' +  cell_height + '/' + cell_width + '/' + col +'/'+ mright +'/'+ mbottom +'/'+ fsize +'/'+ codetype;

            $('#label_frame').attr('src',src);
        });

        $('#label_default').on('click',function(){
            var delivery_id = $('#label_id').val();
            var res = $('#label_resolution').val();
            var col = $('#label_columns').val();
            var cell_height = $('#label_cell_height').val();
            var cell_width = $('#label_cell_width').val();
            var mright = $('#label_margin_right').val();
            var mbottom = $('#label_margin_bottom').val();
            var fsize = $('#label_font_size').val();
            var codetype = $('#label_code_type').val();

            $.post(
                '<?php print base_url();?>ajax/printdefault',
                {
                    delivery_id : delivery_id,
                    res : res,
                    col : col,
                    cell_height : cell_height,
                    cell_width : cell_width,
                    mright : mright,
                    mbottom : mbottom,
                    fsize : fsize,
                    codetype : codetype
                },
                function(data){
                    if(data.result == 'OK'){
                        alert('Setting saved as default');
                    }else{
                        alert('Setting can not be saved, sorry.');
                    }
                },'json'
                );

            $('#label_frame').attr('src',src);
        });

        $('#doConfirm').click(function(){
            var assigns = '';
            var count = 0;
            $('.assign_check:checked').each(function(){

                var deliverydate = $('#'+this.value).html();
                assigns += '<li style="padding:5px;border-bottom:thin solid grey;margin-left:0px;"><strong>'+this.value + '</strong><br />' + deliverydate +'</li>';
                count++;
            });

            if(count > 0){
                $('#confirm_list').html(assigns);
                $('#confirm_dialog').dialog('open');
            }else{
                alert('Please select one or more delivery orders');
            }
        });

		$('#doCancel').click(function(){
			var assigns = '';
			var count = 0;
			$('.assign_check:checked').each(function(){

				var deliverydate = $('#'+this.value).html();
				assigns += '<li style="padding:5px;border-bottom:thin solid grey;margin-left:0px;"><strong>'+this.value + '</strong><br />' + deliverydate +'</li>';
				count++;
			});

			if(count > 0){
				$('#cancel_list').html(assigns);
				$('#cancel_dialog').dialog('open');
			}else{
				alert('Please select one or more delivery orders');
			}
		});
<?php
        $this->load->view($this->config->item('auth_views_root') . 'pages/partials/common_button_js');
        $this->load->view($this->config->item('auth_views_root') . 'pages/partials/change_button_js');
?>

		//put all action link functions here
		$('table.dataTable').click(function(e){

<?php
        $this->load->view($this->config->item('auth_views_root') . 'pages/partials/common_tab_js');
        $this->load->view($this->config->item('auth_views_root') . 'pages/partials/change_tab_js');
?>


            if ($(e.target).is('.printslip')) {
                var delivery_id = e.target.id;
                $('#print_id').val(delivery_id);
                var src = '<?php print base_url() ?>admin/prints/deliveryslip/' + delivery_id;

                $('#print_frame').attr('src',src);
                $('#print_dialog').dialog('open');
            }

            if ($(e.target).is('.printlabel')) {
                var delivery_id = e.target.id;
                var res = $('#label_resolution').val();
                var col = $('#label_columns').val();
                var cell_height = $('#label_cell_height').val();
                var cell_width = $('#label_cell_width').val();
                var mright = $('#label_margin_right').val();
                var mbottom = $('#label_margin_bottom').val();
                var fsize = $('#label_font_size').val();
                var codetype = $('#label_code_type').val();
                $('#label_id').val(delivery_id);
                var src = '<?php print base_url() ?>admin/prints/label/' + delivery_id + '/' + res + '/' +  cell_height + '/' + cell_width + '/' + col +'/'+ mright +'/'+ mbottom +'/'+ fsize +'/'+ codetype;

                $('#label_frame').attr('src',src);
                $('#label_dialog').dialog('open');
            }

			if ($(e.target).is('.cancel_link')) {
				var delivery_id = e.target.id;
				var answer = confirm("Are you sure you want to cancel this order ?");
				if (answer){
					$.post('<?php print site_url('admin/delivery/ajaxcancel');?>',{'delivery_id':delivery_id}, function(data) {
						if(data.result == 'ok'){
							//redraw table
							oTable.fnDraw();
							alert(delivery_id + " canceled");
						}
					},'json');
				}else{
					alert(delivery_id + " not canceled");
				}
		   	}

			if ($(e.target).is('.reschedule_link')) {
				var delivery_id = e.target.id;
				rescheduled_id = delivery_id;
				var current_date = $('#cd_'+rescheduled_id).val();

				var assigns = '<li style="padding:5px;border-bottom:thin solid grey;margin-left:0px;"><strong>'+ rescheduled_id + '</strong><br />'+ current_date +'</li>';
				$('#rescheduled_trans_list').html(assigns);
				$('#reschedule_dialog').dialog('open');
		   	}

			if ($(e.target).is('.revoke_link')) {
				var delivery_id = e.target.id;
				var answer = confirm("Are you sure you want to revoke this order ?");
				if (answer){
					$.post('<?php print site_url('admin/delivery/ajaxrevoke');?>',{'delivery_id':delivery_id}, function(data) {
						if(data.result == 'ok'){
							//redraw table
							oTable.fnDraw();
							alert(delivery_id + " revoked");
						}
					},'json');
				}else{
					alert(delivery_id + " not revoked");
				}
		   	}

			if ($(e.target).is('.purge_link')) {
				var delivery_id = e.target.id;
				var answer = confirm("Are you sure you want to purge this order ?");
				if (answer){
					$.post('<?php print site_url('admin/delivery/ajaxpurge');?>',{'delivery_id':delivery_id}, function(data) {
						if(data.result == 'ok'){
							//redraw table
							oTable.fnDraw();
							alert(delivery_id + " purged");
						}
					},'json');
				}else{
					alert(delivery_id + " not purged");
				}
		   	}

			if ($(e.target).is('.view_detail')) {
				var delivery_id = e.target.id;
				var src = '<?php print base_url() ?>admin/prints/deliveryview/' + delivery_id;

				$('#view_frame').attr('src',src);
				$('#view_dialog').dialog('open');
			}

            if ($(e.target).is('.view_log')) {
                var delivery_id = e.target.id;
                var src = '<?php print base_url() ?>admin/log/deliverylog/' + delivery_id;

                $('#view_dialog').attr('title','Delivery Log : ' + delivery_id);
                $('#ui-dialog-title-view_dialog').html('Delivery Log : ' + delivery_id);
                $('#view_frame').attr('src',src);
                $('#view_dialog').dialog('open');
            }

            if ($(e.target).is('.locpick')) {
                var buyer_id = e.target.id;
                $('#setloc_dialog').dialog('open');

                var src = '<?php print base_url() ?>admin/prints/mapview/order/' + buyer_id;

                $('#map_frame').attr('src',src);
                $('#setloc_dialog').dialog('open');
            }


		});

		$('#getDevices').click(function(){
			if($('#assign_deliverytime').val() == ''){
				alert('Please specify intended delivery time');
			}else{
				//alert($('#assign_deliverytime').val());
				$.post('<?php print site_url('admin/delivery/ajaxdevicecap');?>',{ assignment_date: $('#assign_deliverytime').val() }, function(data) {
					$('#dev_list').html(data.html);
				},'json');
			}
		});

        $('#markscan_dialog').dialog({
            autoOpen: false,
            height: 400,
            width: 600,
            modal: true,
            buttons: {
                "Mark Orders": function() {
                    var delivery_ids = [];
                    var device = $('#device-pickup').val();
                    i = 0;
                    $('.assign_check:checked').each(function(){
                        delivery_ids[i] = $(this).val();
                        i++;
                    });
                    $.post('<?php print site_url('admin/delivery/ajaxmarkscan');?>',
                        {
                            'delivery_id[]':delivery_ids,
                            'setmark':1,
                            'device': device
                        }, function(data) {
                        if(data.result == 'ok'){
                            //redraw table
                            oTable.fnDraw();
                            $('#markscan_dialog').dialog( "close" );
                        }
                    },'json');
                },
                Cancel: function() {
                    $( this ).dialog( "close" );
                }
            },
            close: function() {
                //allFields.val( "" ).removeClass( "ui-state-error" );
                $('#markscan_list').html('');
            }
        });

        <?php
            $this->load->view($this->config->item('auth_views_root') . 'pages/partials/common_dialog_init');
            $this->load->view($this->config->item('auth_views_root') . 'pages/partials/change_dialog_init');
        ?>


		$('#neworder_dialog').dialog({
			autoOpen: false,
			height: 600,
			width: 950,
			modal: true,
			buttons: {
				Save: function(){
					var nframe = document.getElementById('neworder_frame');
					var nframeWindow = nframe.contentWindow;
                    $('#sendingorder').hide();
                    $('#sendingstatus').hide();
					nframeWindow.submitorder();
				},
				Close: function() {
					oTable.fnDraw();
					$( this ).dialog( "close" );
					$('#sendingorder').hide();
					$('#sendingstatus').hide();
				}
			},
			close: function() {
				$('#sendingorder').hide();
				$('#sendingstatus').hide();
			}
		});

        $('#setloc_dialog').dialog({
            autoOpen: false,
            height: 600,
            width: 900,
            modal: true,
            buttons: {
                Save: function(){
                    var nframe = document.getElementById('map_frame');
                    var nframeWindow = nframe.contentWindow;
                    nframeWindow.submitlocation();
                },
                Close: function() {
                    oTable.fnDraw();
                    $( this ).dialog( "close" );
                }
            },
            close: function() {

            }
        });


		/*
		function refresh(){
			oTable.fnDraw();
			setTimeout(refresh, 10000);
		}

		refresh();
		*/

        function closeView(){
            $('view_dialog').dialog('close');
        }


	} );


</script>
<div class="button_nav">
    <a class="button add" id="import" style="cursor:pointer;" href="<?php echo site_url('/admin/import') ?>">Import</a>
    <span class="button add" id="neworder" style="cursor:pointer;">New Order</span>
</div>
<?php print form_checkbox('assign_all',1,FALSE,'id="assign_all"');?> Select All

<?php if(isset($add_button)):?>
	<div class="button_nav">
		<?php echo anchor($add_button['link'],$add_button['label'],'class="button add"')?>
	</div>
<?php endif;?>
<?php echo $this->table->generate(); ?>

<div style="text-align:right;margin-top:12px;">
<?php

    print form_button('do_assign','Assign Delivery Date to Selection','id="doAssign"').
    form_button('do_multi','Change Selection','id="doMultiAction"').
    form_button('do_toscan','Mark for Scanning & Assign to Pick Up Device','id="doMarkscan"').
    form_button('do_pickupassign','Assign Pickup Date to Selection','id="doPickup"').
    form_button('do_confirm','Confirm Selection','id="doConfirm"').
    form_button('do_cancel','Cancel Selection','id="doCancel"').
    form_button('do_label','Print Selection Label','id="doLabel"');

?>
</div>
<div id="assign_dialog" title="Assign Delivery Date to Selection">
	<table style="width:100%;border:0;margin:0;">
		<tr>
			<td style="width:250px;vertical-align:top">
				Delivery Orders :
			</td>
			<td>
				Delivery Date :<br />
			</td>
		</tr>
		<tr>
			<td style="overflow:auto;width:250px;vertical-align:top">
				<ul id="trans_list" style="border-top:thin solid grey;list-style-type:none;padding-left:0px;"></ul>
			</td>
			<td style="border:0;margin:0;">
				<input id="assign_deliverytime" type="text" value=""><br />
				<div id="date_display"></div>
			</td>
		</tr>
	</table>
</div>


<?php
        $this->load->view($this->config->item('auth_views_root') . 'pages/partials/common_dialog');
        $this->load->view($this->config->item('auth_views_root') . 'pages/partials/change_dialog');
?>

<div id="markscan_dialog" title="Mark Orders for Scanning">
    <table style="width:100%;border:0;margin:0;">
        <tr>
            <td style="width:250px;vertical-align:top">
                Delivery Orders :
            </td>
        </tr>
        <tr>
            <td style="overflow:auto;width:250px;vertical-align:top">
                <ul id="markscan_list" style="border-top:thin solid grey;list-style-type:none;padding-left:0px;"></ul>
            </td>
            <td>
                <select id="device-pickup">
                    <?php foreach($devices as $d=>$v):?>
                        <option value="<?php print $d ?>"><?php print $v;?></option>
                    <?php endforeach;?>
                </select>
            </td>
        </tr>
    </table>
</div>



<div id="neworder_dialog" title="New Order" style="overflow:hidden;padding:8px;">
	<input type="hidden" value="" id="print_id" />
	<div id="sendingorder" style="display:none;">
	    <img src="<?php print base_url();?>assets/images/ajax_loader.gif" /> Processing...
	</div>
	<div id="sendingstatus" style="display:none;">
	</div>
	<iframe id="neworder_frame" name="print_frame" width="100%" height="100%"
    marginWidth="0" marginHeight="0" frameBorder="0" scrolling="auto"
    title="New Order">Your browser does not suppr</iframe>
</div>

<div id="setloc_dialog" title="Set Location" style="overflow:hidden;padding:8px;">
    <input type="hidden" value="" id="print_id" />
    <iframe id="map_frame" name="map_frame" width="100%" height="100%"
    marginWidth="0" marginHeight="0" frameBorder="0" scrolling="auto"
    title="Dialog Title">Your browser does not suppr</iframe>
</div>

