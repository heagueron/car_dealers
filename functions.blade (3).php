
<script>
    //extras-functions for the budget list
	@section('js_list_budget')




    /*
    |--------------------------------------------------------------------------
    | FUNCIONES PARA EL LISTADO
    | autor: Carlos villarroel
    |--------------------------------------------------------------------------
    */

    /**
     * Datatable List Budgets
     *
     * @param   void
     * @return  json data
     * @author  Carlos Villarroel  -  cevv07@gmail.com
     * @view    x.index
     * */
        //$.fn.dataTable.ext.errMode = 'none';
    var table_budget_list = $('#budget_list_load').DataTable( {
            language: {  url: '{{asset('asset/admin/plugins/dataTables/venezuela.json')}}'  },
            responsive: true,
            "processing": true,
            "serverSide": true,
            //"ordering": false,     // quitar : quitar order
            //"lengthChange": false, // quitar : cambiar paginado
            //"deferLoading": 0,     // quitar : no carga initial
            searchDelay: 1000,
            ajax: {
                "url": '{{ url("budgets") }}',
                "data": function (d){
                    d.id_stage    = $('#small_box_id_stage').val();
                    d.unattended  = $('#small_box_unattended').val();
                    //d.status      = $("input[name='prod_status']:checked").val();
                }
            },
            columns: [
                /*  {data: 'date',     name: 'date'},  */
                {data: 'user',     name: 'user'},
                {data: 'star',     name: 'star'},
                {data: 'client',     name: 'client'},
                {data: 'e1',     name: 'e1'},
                {data: 'percentage',     name: 'percentage'},
                {data: 'make',     name: 'make'},


                {data: 'product',   name: 'product'},
                {data: 'state',   name: 'state'},
                {data: 'total',    name: 'total', orderable: true, searchable: false},
                {data: 'delivery',    name: 'delivery',  className: ' hidden-md hidden-sm hidden-xs ' },

                {data: 'conditions', name: 'conditions', orderable: true, searchable: false},
                {data: 'keys',    name: 'keys', orderable: false, searchable: false},
                {data: 'updated_at',     name: 'updated_at', visible:false}
            ],
            "order": [[ 12, "desc" ]],
            "drawCallback": function( settings ) {
                $('input[class="icheck_empathie"]').iCheck({ checkboxClass: 'icheckbox_minimal-blue', radioClass: 'iradio_minimal-blue' });
            },
            bAutoWidth: false,
        } );
    $('#busqueda').on('keyup', function () {
        table_budget_list.search( this.value ).draw(); //table_budget_list.draw(); //table.column( [0, 1] ).search( 'Fred' ).draw();
    });
    

    $('#filtrar').on('click', function () {
        table_budget_list.draw(); console.log(' filtrar ');
    });


    table_budget_list.on( 'xhr', function () {
        var d = table_budget_list.ajax.json();

        if (typeof d.count_sin_atender != "undefined"){
            $('#set_num_box-sin_atender').empty().append( d.count_sin_atender );
        }

        if (typeof d.count_averiguando != "undefined"){
            $('#set_num_box-averiguando').empty().append( d.count_averiguando );
        }

        if (typeof d.count_comparando != "undefined"){
            $('#set_num_box-comparando').empty().append( d.count_comparando );
        }
        if (typeof d.count_por_comprar != "undefined"){
            $('#set_num_box-por_comprar').empty().append( d.count_por_comprar );
        }

        console.log( 'xhr list', d );
    });


    let list_filter = ( filter=0 ) => {

        switch (filter) {
            case 0:
                $('#small_box_id_stage').val('');
                $('#small_box_unattended').val(0);
                break;

            case 1:
                $('#small_box_id_stage').val(1);
                $('#small_box_unattended').val('');
                break;

            case 2:
                $('#small_box_id_stage').val(2);
                $('#small_box_unattended').val('');
                break;

            case 3:
                $('#small_box_id_stage').val(3);
                $('#small_box_unattended').val('');
                break;

            default:
                $('#small_box_id_stage').val('');
                $('#small_box_unattended').val('');
                break;
        }

        setTimeout(function(){
            console.log('filter: ' + filter,  'id_stage:' + $('#small_box_id_stage').val(), 'unattended:' + $('#small_box_unattended').val() );
            table_budget_list.order( [ 4, 'desc' ], [ 12, 'desc' ] ).draw();
        }, 500);  //0.5 seg
    };






    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS FOR NEW BUDGET
    | autor: Carlos villarroel
    |--------------------------------------------------------------------------
    */
    $("div").on('click', '.new_budget[data-budget]', function(){
        launch_modal_new_budget( $(this).data('budget') );
    });

    let launch_modal_new_budget = ( id_budget='nuevo' ) => {

        console.log('budget: ' + id_budget );

        let budget_master = $('#budget_id_master').val();

        if ( budget_master!="" ) $('#budget_id_master').val( id_budget );


        console.log('budget_master: ' + budget_master );

        $('#cli_id').val('');
        go_tab_client();
        $("#modalpresupuesto").modal('show');

        setTimeout(function(){   table_load_budget_detail.draw();  }, 500);  //0.5 seg
        setTimeout(function(){   set_titles_modal();               }, 1000);  //1 seg
    };







    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS FOR EDIT BUDGET
    | autor: Carlos villarroel
    |--------------------------------------------------------------------------
    */
    $("table#budget_list_load").on('click', '.open_budget[data-budget]', function(){
        launch_modal_budget_by_edit( $(this).data('budget') );
    });

    let launch_modal_budget_by_edit = ( id_budget=0 ) => {
        //let id_budget       = budget;

        console.log('budget: ' + id_budget );

        $('#budget_status').val( 1 );  // for budget

        $.ajax({
            url : '{{url('budget/details')}}/' + id_budget,
            type : 'GET',
            data: { id_budget: id_budget, budget_status: 1 },
            dataType : 'json',
            beforeSend: function(){
                //$('#modal_content_stage').empty().append('<div class="row"><div class="col-xs-12" style="text-align: center;"> <i class="fa fa-spinner fa-pulse fa-lg fa-5x text-yellow" style="vertical-align: middle;"></i> </div> </div>');
            },
            success : function(response) {
                console.log( response );

                $('#budget_id_master').val( id_budget );

                search_client_by_id(response.client.id);
                //setTimeout(function(){   search_client_by_id(response.client.id);   }, 1000);  //1 seg
                setTimeout(function(){   table_load_budget_detail.draw();           }, 1000);  //1 seg
            },
            complete: function () {

                console.log( 'time: ', new Date().getTime() );

                $("#modalpresupuesto").modal('show');
                go_tab_client();

                setTimeout(function(){   set_titles_modal();   }, 2000);  //2 seg
            }
        });
    };



    @php($open=Request::get('open'))
    @php($sale=Request::get('sale'))
    @if( isset($open) && !empty($open) )

        setTimeout(function(){   launch_modal_budget_by_edit( {{Request::get('open')}} );   }, 1000);  //1 seg
    
        @if( isset($sale) && $sale==1 )
            console.log('Sale from Calendar!');
            setTimeout(function(){
                $('#budget_status').val(2);
                set_title_label();
            }, 3000);
        @endif
    @endif









    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS FOR CONVERT BUDGETS TO SALE
    | autor: Carlos villarroel
    |--------------------------------------------------------------------------
    */
    $("table#budget_list_load").on('click', '.budget_to_sale[data-budget]', function(){
        launch_modal_budget_to_sale( $(this).data('budget') );
    });

    let launch_modal_budget_to_sale = ( id_budget=0 ) => {
        //let id_budget       = budget;

        console.log('budget: ' + id_budget );

        $('#budget_status').val( 2 );  // for sale

        $.ajax({
            url : '{{url('budget/details')}}/' + id_budget,
            type : 'GET',
            data: { id_budget: id_budget, budget_status: 2 },
            dataType : 'json',
            beforeSend: function(){
                //$('#modal_content_stage').empty().append('<div class="row"><div class="col-xs-12" style="text-align: center;"> <i class="fa fa-spinner fa-pulse fa-lg fa-5x text-yellow" style="vertical-align: middle;"></i> </div> </div>');
            },
            success : function(response) {
                console.log( response );

                console.log( 'success: ', new Date().getTime() );

                $('#budget_id_master').val( id_budget );

                search_client_by_id(response.client.id);
                //setTimeout(function(){   search_client_by_id(response.client.id);   console.log('search: ', new Date().getTime() );    }, 1000);  //1 seg
                setTimeout(function(){   table_load_budget_detail.draw();           console.log( 'table: ', new Date().getTime() );    }, 1000);    //1.5 seg
            },
            complete: function () {

                console.log( 'complete: ', new Date().getTime() );

                $("#modalpresupuesto").modal('show');
                go_tab_client();

                setTimeout(function(){   set_titles_modal();   console.log( 'titles: ', new Date().getTime() );    }, 2000);  //2 seg
            }
        });
    };


    @php($open=Request::get('open'))
    @if( isset($open) && !empty($open) )

    setTimeout(function(){   launch_modal_budget_by_edit( {{Request::get('open')}} );   }, 1000);  //1 seg

    @endif











    // FUNCIONES PARA PANEL  -  EMPATHIA
    $("table#budget_list_load").on('ifChecked', '.icheck_empathie[data-budget]', function(e){
        let id_budget   = $(this).data('budget'),
            user        = $(this).data('user'),
            client      = $(this).data('client'),
            valor       = $(this).val();


        var url = '{{url('list-budget/empathy/store')}}';  var token = '{{csrf_token()}}';

        $.ajax({
            url : url, type : 'POST', data: {_method: 'put', _token : token, budget: id_budget, client: client, empathy: valor, user: user }, dataType : 'json',
            success : function(check) {
                table_budget_list.draw();

                setTimeout(function() {    swal({title: check.title, text: check.text, type: check.type, html:true});    }, 1000);
            },
            error : function(xhr, status) {swal("Oops!", "¡El registro no pudo ser eliminado!... Excedió el tiempo sin actividad: actualice la pagina.", "error");},
            complete: function () {  }
        });

        console.log(e.type + ' callback', id_budget, 'client: '+client, 'user: '+user,  valor );
    });





    // FUNCIONES PARA PANEL  -  STAGES
    $("table#budget_list_load").on('click', '.panel-modal_stage[data-budget]', function(){
        launch_modal_stage(this);
    });

    let launch_modal_stage = ( data_panel ) => {
        let id_budget       = $(data_panel).data('budget'),
            id_client       = $(data_panel).data('client'),

            id_type_sale    = $('#budget_id_type_sale').val(),
            id_type_payment = $('#budget_id_type_payment').val();

        console.log('bud: ' + id_budget + '  cli: ' + id_client);

        $.ajax({
            url : '{{url('panel/stage')}}',
            type : 'GET',
            data: { id_budget: id_budget, id_type_sale: id_type_sale, id_type_payment: id_type_payment },
            dataType : 'json',
            beforeSend: function(){
                $('#modal_content_stage').empty().append('<div class="row"><div class="col-xs-12" style="text-align: center;"> <i class="fa fa-spinner fa-pulse fa-lg fa-5x text-yellow" style="vertical-align: middle;"></i> </div> </div>');
            },
            success : function(response) {
                $("#modal_content_stage").html(response.content);
            },
            complete: function () {
                init_stage(); //está en activity

                $('.id_budget_of_list').val( id_budget );
                $(".modal_stage").modal('show');
            }
        });
    };




    // FUNCIONES PARA PANEL  -  AGREEMENT
    $("table#budget_list_load").on('click', '.panel-modal_agreement[data-budget]', function(){
        launch_modal_agreement(this);
    });

    let launch_modal_agreement = ( data_panel ) => {
        let id_budget       = $(data_panel).data('budget'),
            id_client       = $(data_panel).data('client'),

            id_type_sale    = $('#budget_id_type_sale').val(),
            id_type_payment = $('#budget_id_type_payment').val();

        console.log('bud: ' + id_budget + '  cli: ' + id_client);

        $.ajax({
            url : '{{url('panel/agreement')}}',
            type : 'GET',
            data: { id_budget: id_budget, id_type_sale: id_type_sale, id_type_payment: id_type_payment },
            dataType : 'json',
            beforeSend: function(){
                $('#modal_content_agreement').empty().append('<div class="row"><div class="col-xs-12" style="text-align: center;"> <i class="fa fa-spinner fa-pulse fa-lg fa-5x text-yellow" style="vertical-align: middle;"></i> </div> </div>');
            },
            success : function(response) {
                //borrar  de la modal del presupuesto :)
                $('#agreement_content').empty().append('<div class="row"><div class="col-xs-12" style="text-align: center;"> por favor presione nuevamente las pestaña "Acuerdos Parciales" para refrescar </div> </div>');

                $("#modal_content_agreement").html(response.content);
            },
            complete: function () {
                init_agreement(); //está en activity

                $('.id_budget_of_list').val( id_budget );
                $(".modal_agreement").modal('show');
            }
        });
    };

    $("div#modal_content_agreement").on('sumo:closed', 'select[name="agreements[]"]', function(sumo){
        percentage_value(this);
    });














    /*
    |--------------------------------------------------------------------------
    | FUNCIONES PARA EL LISTADO
    | autor: Hector Agüero
    |--------------------------------------------------------------------------
    */

	/**
    *--------------------------------------------------------------------------
    * @description: Handles task in an independant modal launched from budget listing
    * @param   void
    * @return  void
    * @author: Héctor Agüero - heagueron@gmail.com
    *--------------------------------------------------------------------------
    */
	
	// FUNCIONES PARA PANEL  -  TASK
	let bl_ft_table = $('#budget_list_load').DataTable();
	$("table#budget_list_load").on('click', '.panel-modal_task[data-task]', function(){
		var row_clicked     = $(this).closest('tr').index();
		//console.log( 'Clicked on row: '+row_clicked );
        launch_modal_task(this, row_clicked  );
    });

	let launch_modal_task = ( data_panel, rowIndex) => {
		console.log( 'table row index: '+rowIndex );
		$("#bl_ft_rowIndex").val(rowIndex);
		//Changes control:
		$("#change_control2a").val(0);
		$("#change_control2b").val(0);
		$("#sale_task_reason").hide();
		
		let id_budget = $(data_panel).data('budget');
		let id_task = $(data_panel).data('task');
		let id_process  = $(data_panel).data('process');
		
		set_task_modal_header( id_process );
		
		$("#id_task2").val(id_task);
		$("#bl_ft_id_budget").val(id_budget);
		
			console.log('task received: '+id_task);
			console.log('budget received: '+id_budget);
		//$("#modal_content_task").html(id_task);
		
		//The task past comments:
		loadPastComments( id_budget, $("#past_comments_container2") );
		
		console.log ( typeof(id_task) );
		if ( typeof(id_task) != 'number' ){
			$("#resultContainer2").hide();
		} 
		else {
			$("#resultContainer2").show();
			
			//Populate the task modal
    		
    		//The task event:
    		const id_action = $(data_panel).data('action');
        		console.log('id_action recibida to populate: '+id_action);
    		const action = get_action_content(id_action);
        		console.log('action recibida to populate: '+action);
    		$("#select2-ft_action2-container").html(action);
    		
    		$("#id_action2").val( id_action );
    			console.log ('Loaded action: '+ $("#id_action2").val() );
    		
    		//The task reason:
    		const id_reason = $(data_panel).data('reason');
        		console.log('id_reason: '+id_reason);
    		$('#ft_reason2').val(id_reason).trigger('change.select2');
    		
    		//The task date:
    		const date = $(data_panel).data('date');
    			console.log('date: '+date);
    		setDateSession(date, $('#DateBA2'));
    		
			//Set initial result as 'Sin definir'
			$('#ft_result2').val(23).trigger('change.select2');
			
			//Set initial no buy reason as 'Sin definir'
			$('#ft_reason_nc2').val(1).trigger('change.select2');
			
			//$("#resultContainer2").show();
			
			/*
			const el = $('#budget_list_load').find(`[data-task='${id_task}']`)
			$(el).html('hello!');
			*/
		}
		
		activate_event_handlers();
		//confirm_close();
		
		//Set the process parameter in a hidden input inside the task modal component
		$(".modal_task #hiddenProcess").val(1);
		
		$(".modal_task").modal('show');
	}
	
	/**
    *--------------------------------------------------------------------------
    * @description: Sets task modal header background color, label, selectors
    * @param   process
    * @return  void
    * @author: Héctor Agüero - heagueron@gmail.com
    *--------------------------------------------------------------------------
    */
	
	const set_task_modal_header = ( process ) => {
	    console.log( 'set_task_modal_header says: About to set task modal header, process: '+process);
	    switch ( process ){
			case 1:
                label = "Tarea de seguimiento de presupuesto";
                $('#task_modal_header').removeClass('bg-green');
                $('#task_modal_header').addClass('bg-yellow');
                $('#budget_task_reason').show();
                $('#sale_task_reason').hide();
                $('#budget_task_result').show();
                $('#sale_task_result').hide();
                break;
			case 2:
                label = "Tarea de seguimiento de venta";
                $('#task_modal_header').removeClass('bg-yellow');
                $('#task_modal_header').addClass('bg-green');
                $('#budget_task_reason').hide();
                $('#sale_task_reason').show();
                $('#budget_task_result').hide();
                $('#sale_task_result').show();
                break;
			default:
                label = "Tarea de seguimiento de presupuesto";
                $('#task_modal_header').removeClass('bg-green');
                $('#task_modal_header').addClass('bg-yellow');
                $('#budget_task_reason').show();
                $('#sale_task_reason').hide();
                $('#budget_task_result').show();
                $('#sale_task_result').hide();
                break;
		}
		
		$('#task_modal_label').html( label );
		
	}
	
	
	/**
    *--------------------------------------------------------------------------
    * @description: Activates several event handlers
    * @param   void
    * @return  void
    * @author: Héctor Agüero - heagueron@gmail.com
    *--------------------------------------------------------------------------
    */
	
	const activate_event_handlers = () => {
	
		//$("#ft_action2").select2({language:"es", placeholder: 'Seleccione tarea', allowClear: true});
		$("#ft_reason2").select2({language:"es", placeholder: 'Seleccione motivo', allowClear: true});
		$("#ft_result2").select2({language:"es", placeholder: 'Seleccione resultado', allowClear: true});
		$("#ft_reason_nc2").select2({language:"es", placeholder: 'Seleccione motivo no compra', allowClear: true});
		
		$("#ft_reason2_sale").select2({language:"es", placeholder: 'Seleccione motivo', allowClear: true});
		$("#ft_result2_sale").select2({language:"es", placeholder: 'Seleccione resultado', allowClear: true});
		
		//Date
		$('#DateBA2').datetimepicker({
    		locale: 'es',
    		minDate: new Date(),
    		format: 'd-M-Y H:m',
    		currentDate: new Date(),
    
		}).on("change", function() {
    		console.log( "$('#DateBA2').val(): "+$('#DateBA2').val() );
    		$("#change_control2b").val(1);
		});

		//Handler for change result event
		$('#ft_result2').change(function(e) {
    		e.stopPropagation();
    		e.preventDefault();
    		
    		$("#change_control2b").val(1);

    		//e.stopImmediatePropagation();
    		if ( $("#ft_result2 option:selected").val() == 21 ){
    			console.log('no buy en listado');
        		$('#noBuyReasonContainer2').show();
        		return;
    		} 
    
    		$('#noBuyReasonContainer2').hide();
    
    		if ( $("#ft_result2 option:selected").val() == 22 ){
        		//Option 'Compra selected'. Send the budget id and confirm
        		//Second parameter is to indicate where comes from the confirm and validation call 
        		//(1: from inside the budget modal, 2: from budget list task -this-)
        		confirm_buy( $("#bl_ft_id_budget").val(), 2 );
    		}
    
		});	
		
		$('#ft_action2').change(function(e) {
			e.stopPropagation();
    		e.preventDefault();
    		$("#change_control2a").val(1);
		});
		$('#ft_reason2').change(function(e) {
			e.stopPropagation();
    		e.preventDefault();
    		$("#change_control2a").val(1);
    		console.log( 'Task reason changed to: '+$('#ft_reason2').val() );
		});
		$('#bl_ft_comment').change(function(e) {
			e.stopPropagation();
    		e.preventDefault();
    		$("#change_control2a").val(1);
		});
		
	}
	
	/**
    *--------------------------------------------------------------------------
    * @description: Functions to handle budget sending
    * @param   void
    * @return  void
    * @author: Héctor Agüero - heagueron@gmail.com
    *--------------------------------------------------------------------------
    */
    
	// FUNCIONES PARA PANEL  -  Condiciones ( Envío de presupuesto)
	$("table#budget_list_load").on('click', '.panel-sent_client[data-budget]', function(){
		var row_clicked     = $(this).closest('tr').index();
		confirm_send_budget(this, row_clicked  );
    });

	let confirm_send_budget = ( data_panel, rowIndex) => {
		$("#bl_ft_rowIndex").val(rowIndex)
		let id_budget = $(data_panel).data('budget');
		console.log( 'Will show printable for budget: '+ id_budget);
		
		//////////////////////
		$.ajax({
			url : '{{url('budget/details')}}/' + id_budget,
            type : 'GET',
            data: { id_budget: id_budget, budget_status: 1 },
            dataType : 'json',
					
			success: function(data){
			    
				console.log('Budget details mounted on session');
				$('#budget_id_master').val( id_budget );
                search_client_by_id(data.client.id);
                
				setTimeout(function(){   show_printable( $("#budget_printable_container_modal"), true);   }, 1000);  //1 seg
				
			},
			error: function(data) {
        		console.log('Error al montar datos del presupuesto en la sesión');
			}
		});	
	
	}
	
	const send_budget = () => {
	    
		console.log('Send budget confirmed by user');
		let id_budget       = $('#budget_id_master').val();
		const showExpenses  = $("#showExpenses0").prop('checked');
		generate_budget_pdf( id_budget, true ); //Second parameter indicates send the budget (after been generated)
		
	}
	
	const send_budget_confirmed	= () => {
	    let id_budget       = $('#budget_id_master').val();
	    $.ajax({
			url: 'send_budget/'+id_budget,
    		type: "get",
			cache: false,
			data: {},
			dataType: "json",
					
			success: function(data){
				
				
				//console.log('Existe el pdf: '+data.budget_pdf_exists);
				if( data.no_send_reason !='' ){
					swal({title: 'Atención!', 
						text: 'Presupuesto no enviado.'+data.no_send_reason, 
						type: 'error', 
						html: true});
				} else {
					swal({title: 'Atención!', text: 'Presupuesto enviado correctamente', type: 'success', html: true});
					
					//Update field 'sent_client' en tabla 'budgets'
					update_sent_client( id_budget );
					
					//Update cell in budgets listing view
					let bl_ft_table = $( '#budget_list_load' ).DataTable();
					const rowIndex = $( '#bl_ft_rowIndex' ).val();
					bl_ft_table.cell( rowIndex, 10 ).data( 'Actualizando ... ' ).draw();
					
				}
			},
			error: function(data) {
        		console.log('Error al enviar el presupuesto');
			}
		});
	}
		
	const print_budget = () =>{
	    const id_budget         = $('#budget_id_master').val();
	    const includeExpenses   = $("#showExpenses0").prop('checked');
	    console.log('Print budget: '+id_budget+' Include expenses: '+includeExpenses);
	    $("#budgetInput").val(id_budget);
	    $("#includeExpensesInput").val(includeExpenses);
	    
	    //enable_printBudgetButton_handler();
	    
	    setTimeout(function(){   $('#budgetPrintSubmit').click();   }, 200);  //0,2 seg
	    
	}
	
	//$('#budgetPrintSubmit').printPage();
	
	/**
    *--------------------------------------------------------------------------
    * @description: Update sent_client field in 'budgets' table
    * @param   void
    * @return  void
    * @author: Héctor Agüero - heagueron@gmail.com
    *--------------------------------------------------------------------------
    */
	const update_sent_client = ( id_budget ) => {
		console.log('About to update sent_client field in budget: '+id_budget);
		///
		const token = '{{csrf_token()}}';
		$.ajax({
			url: 'update_sent_client/' + id_budget,
			type: "post",	
			cache: false,
			dataType: "json",
			data: {
        		_token: token,
    		},
			success: function(data){
		
				console.log(data.title+' '+data.text);
				//Check send success and update the datatable.
				
				
				let bl_ft_table = $('#budget_list_load').DataTable();
				const rowIndex = $('#bl_ft_rowIndex').val();
				bl_ft_table.cell(rowIndex, 9).data('changed!').draw();

			},
			error:function (xhr, ajaxOptions, thrownError){
				console.log(thrownError);
			}
						
		});	//End of ajax to update sent_client
		///
		
	}
	/**
    *--------------------------------------------------------------------------
    * @description: Functions to handle convertion to sale from budget listing
    * @param   void
    * @return  void
    * @author: Héctor Agüero - heagueron@gmail.com
    *--------------------------------------------------------------------------
    */
	
	// FUNCIONES PARA PANEL  -  Condiciones ( Venta )
	$("table#budget_list_load").on('click', '.panel-convert_sale[data-budget]', function(){
		var row_clicked     = $(this).closest('tr').index();
		bl_convert_sale(this, row_clicked  );
    });

	let bl_convert_sale = ( data_panel, rowIndex) => {
		$("#bl_ft_rowIndex").val(rowIndex)
		let id_budget = $(data_panel).data('budget');
		let id_task = $(data_panel).data('task');
		
		$( "#bl_bts_budget" ).val( id_budget );
		$( "#bl_bts_task" ).val( id_task );
		
		console.log( 'Will try to convert sale in budget: '+ id_budget+' It has task: '+id_task);
		
		//Check requisites
		$.ajax({
			url: 'check_sale_requisites/'+id_budget,
    		type: "get",
			cache: false,
			data: {},
			dataType: "json",
			success: function(data){
			    $( "#bts_missing_conditions").html( data.missing_conditions );
			    $("input[value='bts_SelectVenta']").prop('checked', true);
			    $( ".modal_budget_to_sale" ).modal( "show" );
			},		
			/* success: function(data){
				if ( data.missing_conditions != '') {
					///////////////
					swal({
	    				title: 'Venta! Buen trabajo.',
	    				text: 'Para generar la Orden de Compra debe ingresar: \n'+data.missing_conditions+'\n',
	    				//type: "warning",
	    				
	    				input: 'checkbox',
                        inputPlaceHolder: 'Reserva',
	    				
	    				showCancelButton: true,
	    				confirmButtonClass: "btn-danger",
	    				confirmButtonText: "Sí, completar datos.",
	    				closeOnConfirm: true
    					},
						function(isConfirm){  
	    					if (isConfirm) {
	    					    //console.log('inputValue: '+inputValue);
	                            //Close the budget task with result = 'Compra', if exists
	                            if( id_task != null ){
	                                bl_ft_close(id_task, 22);
	                            }
	                            
	        					console.log(' User will complete the data - Navigate to modal with budget: '+id_budget);
	        					$('#budget_status').val(2);
	        					setTimeout(function(){   launch_modal_budget_to_sale( id_budget );   }, 1000);  //1 seg
	    					} else {
		        				console.log(' User will not complete data right now ');
							}	
						}
					); //End of swal
					//////////////
				}
				else {
				console.log(' Data is complete - Generate the sale order! ');
				}
			},*/
			error: function(data) {
        		console.log('Error al chequear requisitos del presupuestoe - '+thrownError);
			}
		});

	}
	
	/**
    *--------------------------------------------------------------------------
    * @description: Handles budget to sale or reserve convertion 
    * @author:      Héctor Agüero - heagueron@gmail.com
    *--------------------------------------------------------------------------
    */
	const budget_to_sale_confirmed = () =>{
	    
	    const id_budget = $( "#bl_bts_budget" ).val();
	    const id_task = $( "#bl_bts_task" ).val();
	    console.log('budget_to_sale_confirmed TASK: '+id_task);
	    
	    
	    $('#task_quantity').val(0);
	    
	    if ($("input[name='bts_radio']:checked").val() == 'bts_SelectVenta') {
	        
	        console.log('Budget '+id_budget+' goes to sale');
	        if( id_task != null && id_task != '' && id_task != undefined ){
	        	bl_ft_close(id_task, 22);
	    	}
	        $('#budget_status').val(2);
	        $( ".modal_budget_to_sale" ).modal( "hide" );
	        setTimeout(function(){   launch_modal_budget_to_sale( id_budget );   }, 1000);  //1 seg
	        
	    } else{
	    	
	        console.log('Budget '+id_budget+' goes to reserve');
	        if( id_task != null && id_task != '' && id_task != undefined ){
	        	
	        	bl_ft_close(id_task, 24);
	        	
	        	//Update the cell:
				let bl_ft_table = $('#budget_list_load').DataTable();
				let rowIndex	= $("#bl_ft_rowIndex").val();
				bl_ft_table.cell(rowIndex, 5).data('Actualizando ...').draw();
				
	    	}
	        $('#budget_status').val(4);
	        
	        
	        
	        $( ".modal_budget_to_sale" ).modal( "hide" );
	        setTimeout(function(){   launch_modal_budget_to_sale( id_budget );   }, 1000);  //1 seg
	    }	    
	    
	    console.log(' User will complete the data - Navigate to main modal with budget: '+id_budget);
	    
	}
	
	/**
    *--------------------------------------------------------------------------
    * @description: Functions to handle client channel selection 
    * @author:      Héctor Agüero - heagueron@gmail.com
    *--------------------------------------------------------------------------
    */
	/////////////////////////////////////////////////
	
	// FUNCIONES PARA PANEL  -  CLAVES (Canales)
	$("table#budget_list_load").on('click', '.panel-menu_channels[data-budget]', function(){
		var row_clicked     = $(this).closest('tr').index();
		//console.log( 'Clicked on row: '+row_clicked );
        update_channel(this, row_clicked  );
    });
	
	const update_channel = (data_panel, rowIndex  ) => {
		const id_channel = $(data_panel).data('channel');
		const id_client  = $(data_panel).data('client');
		console.log('id_channel: '+id_channel+',   id_client: '+id_client);
		
		///
		const token = '{{csrf_token()}}';
		$.ajax({
			url: 'update_client_keys/' + id_client,
			type: "post",	
			cache: false,
			dataType: "json",
			data: {
        		_token		: token,
        		channel		: id_channel
    		},
			success: function(data){
		
				console.log(data.title+' '+data.text);
				
				//Update the cell:
				let bl_ft_table = $('#budget_list_load').DataTable();
				bl_ft_table.cell(rowIndex, 11).data('Actualizando ...').draw();

			},
			error:function (xhr, ajaxOptions, thrownError){
				console.log('Error al guardar el canal - '+thrownError);
			}
						
		});	//End of ajax to update client channel
		///
	}
	
	/**
    *--------------------------------------------------------------------------
    * @description: Functions to handle client origin selection 
    * @author:      Héctor Agüero - heagueron@gmail.com
    *--------------------------------------------------------------------------
    */
	// FUNCIONES PARA PANEL  -  CLAVES (Orígenes)
	
	//Handler when budget listing cell clicked
	$("table#budget_list_load").on('click', '.panel-menu_origins[data-budget]', function(){
		const row_clicked     = $(this).closest('tr').index();
		console.log('Origin icon cell pressed on row: '+row_clicked);
		console.log( 'this: '+ $(this).data('budget') );
        parent_origin(this, row_clicked  );
    });
	
	const parent_origin = ( data_panel, rowIndex  ) => {
		console.log( "cell #prior_sub_origin: "+$("#prior_sub_origin").val() );
		//Clean the sub_origin hidden input
		$("#prior_sub_origin").val('');
		$("#origin_rowIndex").val(rowIndex);
		
	}
	
	
	//Handler when main origin dropdown menu clicked
	$("table#budget_list_load").on('click', 'a.parent_origin[data-dropdown]', function(e){
		console.log('Clicked parent menu');
    	e.stopPropagation(); //Keeps parent menu open
    	e.preventDefault();
    	activate_origin_menu(this);
    })
	
	const activate_origin_menu = (data_panel) =>{
		console.log( "parent click #prior_sub_origin: "+$("#prior_sub_origin").val() );
		const id_submenu = $(data_panel).data('dropdown');
		console.log( 'id_submenu: '+id_submenu);
		
		if( $("#prior_sub_origin").val() !='') {
			const prior_submenu = $("#prior_sub_origin").val();
			$( prior_submenu ).toggle();
		}
		
		$("#prior_sub_origin").val(id_submenu);
		$(id_submenu).toggle();
	}
	
	//Handler when child sub-origin dropdown menu clicked
	$("table#budget_list_load").on('click', 'a.panel-child_origin[data-budget]', function(e){
		console.log('Clicked child origin');
		origin_selected(this);
	})
	
	const origin_selected = (data_panel) => {
		console.log( "when child clicked #prior_sub_origin: "+$("#prior_sub_origin").val() );	
		const id_origin  = $(data_panel).data('origin');	
		const id_client  = $(data_panel).data('client');
		
		console.log( 'sub-origin icon: '+ $(data_panel).data('icon') );
		
		console.log( 'origin selected: '+ id_origin );
		$("#prior_sub_origin").val('');
		
		/////////////////////////
		//Store selected origin
		
		const token = '{{csrf_token()}}';
		$.ajax({
			url: 'update_client_keys/' + id_client,
			type: "post",	
			cache: false,
			dataType: "json",
			data: {
        		_token		: token,
        		origin		: id_origin
    		},
			success: function(data){
		
				console.log(data.title+' '+data.text);
				
				//Update the cell:
				const origin_rowIndex	= $("#origin_rowIndex").val();
				const bl_ft_table		= $('#budget_list_load').DataTable();
				
				bl_ft_table.cell(origin_rowIndex, 11).data('Actualizando ...').draw();
				$("#origin_rowIndex").val('');
			},
			error:function (xhr, ajaxOptions, thrownError){
				console.log('Error al guardar el origen - '+thrownError);
			}
						
		});	//End of ajax to update client origin
		
	}
	
	///////////////////////////////////////////////////////////////////////////
	/**
    *--------------------------------------------------------------------------
    * @description: Presents the budget comments in an independant modal 
    * @author:      Héctor Agüero - heagueron@gmail.com
    *--------------------------------------------------------------------------
    */
	// FUNCIONES PARA PANEL  -  CLAVES (Comentarios)
	$("table#budget_list_load").on('click', '.panel-show_comments[data-budget]', function(){
		var row_clicked     = $(this).closest('tr').index();
		//console.log( 'Clicked on row: '+row_clicked );
        launch_modal_comments(this, row_clicked  );
    });

	let launch_modal_comments = ( data_panel, rowIndex) => {
		console.log('Launch comments modal!');
		let id_budget       = $(data_panel).data('budget');
		$('#bl_comm_rowIndex').val( rowIndex );
		$('#bl_comm_budget').val( id_budget );
		
		//Get past comments
		loadPastComments(id_budget, $('#past_comments_container') );
		
		//Launch the modal
		$(".modal_comments").modal('show');
		
	}









    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS FOR PANEL
    | autor: Carlos villarroel
    |--------------------------------------------------------------------------
    */
    $("table#budget_list_load").on('click', '.panel-client[data-client]', function(){
        show_panel();
        view_partial_panel(this);
    });

    $("div#panel").on('click', '.remove-panel', function(){
        remove_panel();
    });


    let show_panel = ( ) => {
        $('#list').removeClass('col-md-12');
        $('#list').addClass('col-md-8 widthfull');
        // --- --- --- --- --- --- --- --- --- --- ---
        $('#panel').addClass('col-md-4');
        $('#panel').removeClass('hidden');
    };

    let remove_panel = ( ) => {
        $('#panel').removeClass('col-md-4');
        $('#panel').addClass('hidden');
        // --- --- --- --- --- --- --- --- --- --- ---
        $('#list').removeClass('col-md-8 widthfull');
        $('#list').addClass('col-md-12');
    };

    let view_partial_panel = ( data_panel ) => {
        let id_budget       = $(data_panel).data('budget'),
            id_client       = $(data_panel).data('client');

        console.log('bud: ' + id_budget + '  cli: ' + id_client);

        $.ajax({
            url : '{{url('panel/partial')}}',
            type : 'GET',
            data: { id_budget: id_budget, id_client: id_client, x: '' },
            dataType : 'json',
            beforeSend: function(){
                $('#panel_content').empty().append('<div class="row"><div class="col-xs-12" style="text-align: center;"> <i class="fa fa-spinner fa-pulse fa-lg fa-5x text-yellow" style="vertical-align: middle;"></i> </div> </div>');
            },
            success : function(response) {
                $("#panel_content").html(response.content);
            },
            complete: function () {
                //init_agreement();
            }
        });
    };
    
    
    
    /**
    *--------------------------------------------------------------------------
    * @description: CHECKS PRE-REQUISITES BEFORE SHOW PRINTABLE INSIDE MAIN MODAL
    * @author:      Héctor Agüero
    *--------------------------------------------------------------------------
    */
    const check_before_printable = ( printableElement, modal=false ) => {
    	
    	console.log( 'check_before_printable' );
    	
    	// Check client
    	if(!modal){
            console.log('cli_id: '+$("#cli_id").val() );
            if( $("#cli_id").val()== '' ) {
                swal({ title: 'Atención!', 
					    text: 'El presupuesto aún no tiene cliente.', 
					    type: 'error', 
					    html: true});
            return;
            }
        }
        
    	// Check products and also if expenses and payments have been sent to session 
    	// and are greater than zero.
    	const id_budget         = $('#budget_id_master').val();
    	const id_type_sale      = $('#budget_id_type_sale').val();
    	const id_type_payment   = $('#budget_id_type_payment').val();
    	/////////////////////
    	$.ajax({
        	url : '{{url('expense/partial')}}',
        	type : 'GET',
        	data: { id_budget: id_budget, id_type_sale: id_type_sale, id_type_payment: id_type_payment },
        	dataType : 'json',
        
        	success : function(response) {
            	
            	const eps = response.products_eps;
            	
            	console.log( 'check_before_printable says: Expenses and payments are here, prods qty: '+count_objects(eps) );
            	
            	if( count_objects(eps) == 0 ){
            		//No products yet
            		swal({title: 'Atención!', text: 'Aún no hay productos ingresados', type: 'error', html: true});
            		return;
            	}
            	
            	let msg = '';
            	let total_expenses = [];
            	let total_payments = [];
            
            	Object.keys(eps).forEach(key => {
            		
            		console.log( 'El producto seria: '+eps[key].producto );
            		//Check expenses:
            		let exp = eps[key].expenses;
            		total_expenses[key] = 0;
            		total_expenses[key] +=  ( Number(exp.freight_forms) + Number(exp.patent) + Number(exp.credit) + Number(exp.inscription) + Number(exp.other) );
            	
            		if( total_expenses[key] == 0 ){
            			msg += '<p>No se han guardado los gastos en el producto: '+eps[key].brand+' '+eps[key].model+'</p>';
            		}
                
                	//Check payments
                	let pay = eps[key].payments;
                	total_payments[key] = 0;
                
            		total_payments[key] += ( Number( pay.efectivo.sign ) + Number( pay.efectivo.cash ) );
            	
            		if( [2, 4, 5].includes(id_type_payment) ){ // has credit payment
            			total_payments[key] += Number( pay.credito.credit_capital );
            			total_payments[key] += Number( pay.cheques.check_amount );
            			total_payments[key] += Number( pay.documentos.docs_total );
            		}
            	
            		if( [3, 4, 5].includes(id_type_payment) ){// has used payment
            			total_payments[key] += Number( pay.usado.used_valortoma );
            		}
            	
            		if( total_payments[key] == 0 ){
            			msg += '<p>No se han guardado los pagos en el producto: '+eps[key].brand+' '+eps[key].model+'</p>';
            		}
            	
            	});
            
            
            if( msg != '' ){
            	
                console.log( 'check_before_printable says: '+msg );
                
            	swal({
	    				title: 'Atención',
	    				text: msg+'<p>Desea completar ahora?</p>',
	    				type: "warning",
	    				showCancelButton: true,
	    				confirmButtonClass: "btn-danger",
	    				confirmButtonText: "Sí, completar datos.",
	    				closeOnConfirm: true
    				},
	    			function(isConfirm){  
	    				if (isConfirm) {
	    					console.log(' check_before_printable says: User will complete expenses and payments before printable ');
	    					$("#tab_payment").click();
	            		} else {
		        			console.log(' check_before_printable says: User will not complete expenses and payments before printable ');
		        			show_printable( printableElement, modal);
						}	
					}
	    		); //End of swal
	    		
            } else {
            	
                console.log('check_before_printable says: expenses and payments loaded');
                console.log('check_before_printable says: proceed with printable');
                
                show_printable( printableElement, modal);
                
            }
        },
        	error : function (xhr, ajaxOptions, thrownError){
				console.log('check_before_printable says: Error capturing expenses and payments from session -'+thrownError);	 
			}
        
    	});

    }
    
    /**
    *--------------------------------------------------------------------------
    * @description: Helper function to count elements in an object
    * @author:      Héctor Agüero
    *--------------------------------------------------------------------------
    */
    const count_objects = (obj) => {
    	let count = 0;
    	for( var prop in obj ){
    		if( obj.hasOwnProperty( prop ) )
    			++count;
    	}
    	return count;
    }
    
    
    /**
    *--------------------------------------------------------------------------
    * @description: SHOW PRINTABLE INSIDE MAIN MODAL
    * @author:      Héctor Agüero
    *--------------------------------------------------------------------------
    */
    const show_printable = ( printableElement, modal=false ) => {
        console.log('show_printable');
        let id_budget       = $('#budget_id_master').val() == '' ? 'nuevo' : $('#budget_id_master').val();
        console.log('will show_printable for budget: '+id_budget);
        
        //This will free identifiers
        $( "#budget_printable_container_modal" ).empty();
        $( "#printable_container" ).empty();
        
        /*
        if(!modal){
            console.log('cli_id: '+$("#cli_id").val() );
            if( $("#cli_id").val()== '' ) {
                swal({ title: 'Atención!', 
					    text: 'El presupuesto aún no tiene cliente.', 
					    type: 'error', 
					    html: true});
            return;
            }
        }
        */
        
        $.ajax({
            url : '{{url('show_session_budget_printable')}}',
            type : 'GET',
            data: { id_budget : id_budget },
            dataType : 'json',
            beforeSend: function(){
                $( printableElement ).empty().append('<div class="row"><div class="col-xs-12" style="text-align: center;"> <i class="fa fa-spinner fa-pulse fa-lg fa-5x text-yellow" style="vertical-align: middle;"></i> </div> </div>');
                if( modal ){ $(".modal_budget_printable").modal('show'); }
            },
            success : function(response) {
                //$("#printable_container").empty();
                //console.log ('Debug variable: '+response.debug_variable+', then RETURN 1148 ');
                //return;
                console.log('HTML arrived!');
                let content = '';
                response.budget_printables.forEach(function(item, index, arr){
				    content += item;
				})
				$( printableElement ).html(content);
				activate_expenses_visibility();
				//if( modal ){ $(".modal_budget_printable").modal('show'); }
            },
            error : function (xhr, ajaxOptions, thrownError){
				console.log('Error al generar imprimible del presupuesto -'+thrownError);
				 $( printableElement ).empty().append('<div class="row"><div class="col-xs-12" style="text-align: center;"> <h5>Error al generar imprimible del presupuesto</h5></div> </div>');
			},
            complete: function () {
            }
        });
    }
    
    /**
    *--------------------------------------------------------------------------
    * @description: Toggle expenses inclusion in the budget printable 
    * @author:      Héctor Agüero
    *--------------------------------------------------------------------------
    */
    const activate_expenses_visibility = () => {
        $("[id^=showExpenses]").on('click', function(e){
    		console.log('activate_expenses_visibility');
            var check = $("#showExpenses0").prop('checked');
            if(check==true){
                console.log("Show expenses!");
                $(".budget_expense").show();

            }else{
                console.log("Hide expenses!");
                $(".budget_expense").hide();
            };
            
        });
    }
    
    /**
    *--------------------------------------------------------------------------
    * @description: Generates pdf file for the budget and optionally send it  
    * @author:      Héctor Agüero
    *--------------------------------------------------------------------------
    */
    const generate_budget_pdf = ( id_budget, send=false ) => {
        console.log('generate_budget_pdf says: generate_pdf!');
        console.log('generate_budget_pdf says budget: '+id_budget );
        const showExpenses = $("#showExpenses0").prop('checked');
        console.log('generate_budget_pdf says showExpenses: '+showExpenses );
        
        // Navigate to pdf generation route
		const token = '{{csrf_token()}}';
		$.ajax({
		    url : '{{url('print/budgetpdf')}}/' + id_budget,
			type: "post",	
			cache: false,
			//dataType: 'text',
            //dataType: 'json',
			data: {
        		_token          : token,
        		includeExpenses : showExpenses
    		},
			success: function(data){
		        console.log( 'generate_budget_pdf says: PDF generated and stored at the server! ');
		        if(send) send_budget_confirmed();
			},
			error:function (xhr, ajaxOptions, thrownError){
				console.log('Error generating the pdf file for the budget -'+thrownError);
			}
						
		});	//End of ajax to generate budget pdf
		///

    }
    
    //end extras-functions
    @endsection
	






    /**
     * 	section 	form_ajax_success__list_budget
	 * 	require	validation_beta.blade
	 * 	Funciones para form_ajax_success que se ejecutan despues de enviar un formulario via ajax
     *
     * @author  Carlos Villarroel  -  cevv07@gmail.com
     * */
	@section('form_ajax_success__list_budget')

        if(check.status && check.controller=="modal-stage"){
            auxiliar_form_a_reset.resetear(true);
            table_budget_list.draw();
            $('body').find('.modal_stage').trigger('click');
        }

        if(check.status && check.controller=="modal-agreement"){
            auxiliar_form_a_reset.resetear(true);
            table_budget_list.draw();
            $('body').find('.modal_agreement').trigger('click');
        }

	@endsection
</script>