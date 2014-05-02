<script>
	var asInitVals = new Array();
    var refreshTab;

	$(document).ready(function() {
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

		$('tfoot input').keyup( function () {
			/* Filter on the column (the index) of this element */
			oTable.fnFilter( this.value, $('tfoot input').index(this) );
		} );

		$('tfoot select').change( function () {
			/* Filter on the column (the index) of this element */
			oTable.fnFilter( this.value, $('tfoot select').index(this) );
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

		$( '#assign_deliveryzone' ).autocomplete({
			source: '<?php print site_url('ajax/getzone')?>',
			method: 'post',
			minLength: 2
		});


		$('#search_deliverytime').datepicker({ dateFormat: 'yy-mm-dd' });

		$('#search_deliverytime').change(function(){
			oTable.fnFilter( this.value, $('tfoot input').index(this) );
		});

		/*Delivery process mandatory*/
		$('#search_deliverytime').datepicker({ dateFormat: 'yy-mm-dd' });
		$('#assign_deliverytime').datepicker({ dateFormat: 'yy-mm-dd' });

        $( '#assign_courier' ).autocomplete({
            source: '<?php print site_url('ajax/getcourier')?>',
            method: 'post',
            minLength: 2,
            select:function(event,ui){
                $('#assign_courier_id').val(ui.item.id);
                $('#assign_courier_id_txt').html(ui.item.id);
            }
        });

		$('#doAssign').click(function(){
			var assigns = '';
			var count = 0;
			$('.assign_check:checked').each(function(){
				assigns += '<li style="padding:5px;border-bottom:thin solid grey;margin-left:0px;">'+this.value+'</li>';
				count++;
			});

			if(count > 0){
				$('#trans_list').html(assigns);
				$('#assign_dialog').dialog('open');
			}else{
				alert('Please select one or more delivery orders');
			}
		});

        refreshTab = function(){
            oTable.fnDraw();
        };

		$('table.dataTable').click(function(e){

            if($(e.target).is('.thumb')){
                var delivery_id = e.target.alt;
                var currentTime = new Date();

                var images = [];

                $('.gal_' + delivery_id).each(function(el){
                    images.push(
                        {
                            href : '<?php print base_url();?>public/receiver/' + $(this).val() + '?' + currentTime.getTime(),
                            title : delivery_id
                        }
                    );
                });

                $.fancybox.open(images);

            }

			if ($(e.target).is('.changestatus')) {
				var delivery_id = e.target.id;
				$('#change_id').html(delivery_id);
				$('#changestatus_dialog').dialog('open');
			}

			if ($(e.target).is('.printslip')) {
				var delivery_id = e.target.id;
				$('#print_id').val(delivery_id);
				var src = '<?php print base_url() ?>admin/prints/deliveryslip/' + delivery_id;

				$('#print_frame').attr('src',src);
				$('#print_dialog').dialog('open');
			}

			if ($(e.target).is('.view_detail')) {
				var delivery_id = e.target.id;
				var src = '<?php print base_url() ?>admin/prints/deliveryview/' + delivery_id;

				$('#view_frame').attr('src',src);
				$('#view_dialog').dialog('open');
			}

            if ($(e.target).is('.change_courier')) {
                var previous_courier = e.target.id;
                $('#previous_courier').val(previous_courier);
                $('#courier_reassign_dialog').dialog('open');
            }


			if ($(e.target).is('.reassign')) {
				var delivery_id = e.target.id;

				$.post('<?php print site_url('ajax/getorder');?>',{ delivery_id:delivery_id }, function(data) {
					if(data.result == 'ok'){

						$('#reassign_delivery_id').html(data.data.delivery_id);
						$('#disp_deliverycity').html(data.data.assignment_city);
						$('#disp_deliveryzone').html(data.data.assignment_zone);
						$('#disp_deliverydate').html(data.data.assignment_date);
						$('#current_device').html(data.data.device);
						$('#current_courier').html(data.data.courier);

						var date_assign = data.data.assignment_date;

						var zone_assign = data.data.assignment_zone;

						var city_assign = data.data.buyerdeliverycity;

						$.post('<?php print site_url('admin/delivery/ajaxdevicecap');?>',{
								assignment_date: date_assign,
								assignment_zone: zone_assign,
								assignment_city: city_assign
							}, function(data) {
								$('#reassign_dev_list').html(data.html);
							},'json');
						$('#device_reassign_dialog').dialog('open');
					}
				},'json');
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
				$.post('<?php print site_url('admin/delivery/ajaxdevicecap');?>',{ assignment_date: $('#assign_deliverytime').val(),assignment_zone: $('#assign_deliveryzone').val() }, function(data) {
					$('#dev_list').html(data.html);
				},'json');
			}
		});

		$('#assign_dialog').dialog({
			autoOpen: false,
			height: 300,
			width: 600,
			modal: true,
			buttons: {
				"Assign to Device": function() {
					if($('#assign_deliverytime').val() == ''){
						alert('Please specify date.');
					}else{
						var device_id = $("input[name='dev_id']:checked").val();
						var delivery_ids = [];
						i = 0;
						$('.assign_check:checked').each(function(){
							delivery_ids[i] = $(this).val();
							i++;
						});
						$.post('<?php print site_url('admin/delivery/ajaxassignzone');?>',{ assignment_device_id: device_id,'delivery_id[]':delivery_ids, assignment_zone: $('#assign_deliveryzone').val() }, function(data) {
							if(data.result == 'ok'){
								//redraw table
								oTable.fnDraw();
								$('#assign_dialog').dialog( "close" );
							}
						},'json');
					}
				},
				Cancel: function() {
					$('#dev_list').html("");
					$( this ).dialog( "close" );
				}
			},
			close: function() {
				//allFields.val( "" ).removeClass( "ui-state-error" );
				$('#assign_deliverytime').val('');
			}
		});

		$('#device_reassign_dialog').dialog({
			autoOpen: false,
			height: 300,
			width: 500,
			modal: true,
			buttons: {
				"Re-Assign to Device": function() {

					$.post('<?php print site_url('ajax/reassign');?>',
						{
							delivery_id:$('#reassign_delivery_id').html(),
							assignment_date:$('#disp_deliverydate').html(),
							assignment_device_id: $("input[name='dev_id']:checked").val(),
							assignment_timeslot: $('.timeslot:checked').val(),
							courier_id: 'current'
						},
						function(data) {
							if(data.status == 'OK:REASSIGNED'){
								//redraw table
								oTable.fnDraw();
								$('#device_reassign_dialog').dialog( 'close' );
							}
						},'json');
				},
				Cancel: function() {
					$('#dev_list').html('');
					$( this ).dialog( 'close' );
				}
			},
			close: function() {
				//allFields.val( "" ).removeClass( "ui-state-error" );
				$('#assign_deliverytime').val('');
			}
		});

        $('#courier_reassign_dialog').dialog({
            autoOpen: false,
            height: 200,
            width: 300,
            modal: true,
            buttons: {
                "Hand Over Device": function() {
                    if($("#assign_courier_id").val() == ''){
                        alert('Please specify Courier.')
                    }else{
                        var prev_courier = $('#previous_courier').val();
                        var new_courier = $('#assign_courier_id').val();

                        $.post('<?php print site_url('ajax/handover');?>',{
                            prev_courier: prev_courier,
                            new_courier: new_courier
                            }, function(data) {
                            if(data.status == 'OK:REASSIGNED'){
                                //redraw table
                                oTable.fnDraw();
                                $('#courier_reassign_dialog').dialog( "close" );
                            }
                        },'json');

                    }
                },
                Cancel: function() {
                    $('#assign_courier').val('');
                    $('#assign_courier_id_txt').html('');
                    $( this ).dialog( "close" );
                }
            },
            close: function() {
                //allFields.val( "" ).removeClass( "ui-state-error" );
                $('#assign_courier').val('');
                $('#assign_courier_id_txt').html('');
                $('#assign_deliverytime').val('');
            }
        });


		$('#changestatus_dialog').dialog({
			autoOpen: false,
			height: 250,
			width: 400,
			modal: true,
			buttons: {
				"Confirm Delivery Orders": function() {
					var delivery_id = $('#change_id').html();

					$.post('<?php print site_url('admin/delivery/ajaxchangestatus');?>',{
						'delivery_id':delivery_id,
						'new_status': $('#new_status').val(),
						'actor': $('#actor').val()
					}, function(data) {
						if(data.result == 'ok'){
							//redraw table
							oTable.fnDraw();
							$('#changestatus_dialog').dialog( "close" );
						}
					},'json');
				},
				Cancel: function() {
					$( this ).dialog( "close" );
				}
			},
			close: function() {
				//allFields.val( "" ).removeClass( "ui-state-error" );
				$('#confirm_list').html('');
			}
		});

		$('#print_dialog').dialog({
			autoOpen: false,
			height: 400,
			width: 1050,
			modal: true,
			buttons: {
				Print: function(){
					var pframe = document.getElementById('print_frame');
					var pframeWindow = pframe.contentWindow;
					pframeWindow.print();
				},
				"Download PDF": function(){
					var print_id = $('#print_id').val();
					var src = '<?php print base_url() ?>admin/prints/deliveryslip/' + print_id + '/pdf';
					window.location = src;
					//alert(src);
				},
				Close: function() {
					$( this ).dialog( "close" );
				}
			},
			close: function() {

			}
		});

		$('#view_dialog').dialog({
			autoOpen: false,
			height: 600,
			width: 900,
			modal: true,
			buttons: {
				Save: function(){
					var nframe = document.getElementById('view_frame');
					var nframeWindow = nframe.contentWindow;
					nframeWindow.submitorder();
				},
				Print: function(){
					var pframe = document.getElementById('print_frame');
					var pframeWindow = pframe.contentWindow;
					pframeWindow.print();
				},
				Close: function() {
					oTable.fnDraw();
					$( this ).dialog( "close" );
				}
			},
			close: function() {

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
	} );


</script>
<?php if(isset($add_button)):?>
	<div class="button_nav">
		<?php echo anchor($add_button['link'],$add_button['label'],'class="button add"')?>
	</div>
<?php endif;?>
<?php echo $this->table->generate(); ?>

<div id="assign_dialog" title="Assign Selection to Device">
	<table style="width:100%;border:0;margin:0;">
		<tr>
			<td style="width:50%;border:0;margin:0;">
				Delivery Orders :
			</td>
			<td style="width:50%;border:0;margin:0;">
				Select Zone :<br />
				<input id="assign_deliveryzone" type="text" value=""><br />
				Delivery Time :<br />
				<input id="assign_deliverytime" type="text" value="">
				<?php print form_button('getdevices','Get Devices','id="getDevices"');?>
			</td>
		</tr>
		<tr>
			<td style="overflow:auto;">
				<ul id="trans_list" style="border-top:thin solid grey;list-style-type:none;padding-left:0px;"></ul>
			</td>
			<td>
				<ul id="dev_list" style="border-top:thin solid grey;list-style-type:none;padding-left:0px;"></ul>
			</td>
		</tr>
	</table>
</div>

<div id="courier_reassign_dialog" title="Re-Assign Device to Courier">
    <table style="width:100%;border:0;margin:0;">
        <tr>
            <td style="width:50%;border:0;margin:0;">
                <input id="previous_courier" type="hidden" value="">
                New Courier ID: <strong><span id="assign_courier_id_txt"></span></strong><br />
                <input id="assign_courier" type="text" value=""><br />
                <input id="assign_courier_id" type="hidden" value=""><br />
            </td>
        </tr>
    </table>
</div>


<div id="changestatus_dialog" title="Change Delivery Orders">
	<table style="width:100%;border:0;margin:0;">
		<tr>
			<td style="width:250px;vertical-align:top">
				<strong>Delivery ID : </strong><span id="change_id"></span><br /><br />
				<?php
					$status_list = $this->config->item('status_colors');
					$status_list = array_keys($status_list);

					$sl = array();
					foreach($status_list as $s){
						$sl[$s]=$s;
					}

					$actor = $this->config->item('actors_title');


					print 'Actor <br />';
					print form_dropdown('actor',$actor,'','id="actor"').'<br /><br />';
					print ' New Status<br />';
					print form_dropdown('new_status',$sl,'','id="new_status"');

				?>
			</td>
		</tr>
	</table>
</div>

<div id="device_reassign_dialog" title="Re-Assign Device">
	<table style="margin: 0px;border: 0px;">
		<tr>
			<td>
				Delivery ID : <span id="reassign_delivery_id" style="font-weight: bold"></span><br />
				City : <span id="disp_deliverycity" style="font-weight: bold"></span><br />
				Delivery Date : <span id="disp_deliverydate" style="font-weight: bold" ></span><br />
				Current Device : <span id="current_device" style="font-weight: bold" ></span><br />
				Assigned To : <span id="current_courier" style="font-weight: bold" ></span>
			</td>
		</tr>
		<tr>
			<td>
				Available Devices :<br />
				<ul id="reassign_dev_list" style="border-top:thin solid grey;list-style-type:none;padding-left:0px;">
					Loading...
				</ul>
			</td>
		</tr>
	</table>
</div>

<div id="print_dialog" title="Print" style="overflow:hidden;padding:8px;">
	<input type="hidden" value="" id="print_id" />
	<iframe id="print_frame" name="print_frame" width="100%" height="100%"
    marginWidth="0" marginHeight="0" frameBorder="0" scrolling="auto"
    title="Dialog Title">Your browser does not suppr</iframe>
</div>

<div id="view_dialog" title="Order Detail" style="overflow:hidden;padding:8px;">
	<input type="hidden" value="" id="print_id" />
	<iframe id="view_frame" name="print_frame" width="100%" height="100%"
    marginWidth="0" marginHeight="0" frameBorder="0" scrolling="auto"
    title="Dialog Title">Your browser does not suppr</iframe>
</div>

<div id="setloc_dialog" title="Order Detail" style="overflow:hidden;padding:8px;">
    <input type="hidden" value="" id="print_id" />
    <iframe id="map_frame" name="map_frame" width="100%" height="100%"
    marginWidth="0" marginHeight="0" frameBorder="0" scrolling="auto"
    title="Dialog Title">Your browser does not suppr</iframe>
</div>
