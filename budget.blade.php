
<input type="hidden" name="budget_id_master" id="budget_id_master" value="">

<!--   Discriminates if the modal is being treated as budget (1), pre sale order (2), aprobed sale (3) or reserve (4) -->
<input type="hidden" name="budget_status" id="budget_status" value="1">

<!--   This hidden input helps identify not saved expenses and payments inputs -->
<input type="hidden" name="not_saved_eps" id="not_saved_eps" value="">

<!--   This hidden input helps identify not saved task inputs -->
<input type="hidden" name="not_saved_task" id="not_saved_task" value="">

<!--   This hidden input helps identify if the budget was succesfully saved -->
<input type="hidden" name="" id="budget_saved" value="">

<div id="modalpresupuesto" class="modal fade modalPresupuestoAdd" role="dialog" aria-labelledby="Modal" style="min-width: 700px!important; overflow-y: scroll;">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">

			{!! Form::open(['route' => 'budgets.store', 'method' => 'POST', 'id' => 'formDirection']) !!}
			<div class="modal-header bg-yellow" id="modal_title-header_class">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="modal_title-label"> PRESUPUESTO </h4>


				<div class="row ">  <!--   hidden  -->
					<div class="col-xs-3">
						<label for="title_x" class="hint--top"  aria-label="Número de Presupuesto">
							<i class="fa fa-hashtag"></i>
							<span id="modal_title-budget"> Nuevo </span>
						</label>
					</div>
					<div class="col-xs-3">
						<label for="title_x" class="hint--top"  aria-label="Cliente">
							<i class="fa fa-user-circle fa-lg text-bold"></i>
							<span id="modal_title-client"> Carlos Villarroel </span>
						</label>
					</div>
					<div class="col-xs-3">
						<label for="title_x" class="hint--top"  aria-label="Productos Agregados">
							<i class="fa fa-shopping-cart  fa-lg "></i>  <!-- product-hunt -->
							<span id="modal_title-product"> 0 Item </span>
						</label>
					</div>
					<div class="col-xs-3">
						<label for="title_x" class="hint--top"  aria-label="Actividades de Seguimiento">
							<i class="fa fa-calendar-data-o fa-lg "></i>
							<span id="modal_title-activity"> 0 Actividad </span>
						</label>
					</div>
				</div>

			</div>

			<div class="modal-body">

				<div class="container-fluid-XXX">
					<div class="row" >
						<div class="board" style="margin: -16px auto;">
							<!--h2>Welcome to SELLER!<sup>™</sup></h2-->
							<div class="board-inner">
								<ul class="nav nav-tabs" id="myTab">
									<div class="liner"></div>
									<li class="active">
										<a href="#client" data-toggle="tab" class="hint--top" aria-label="Cliente" id="tab_client">
											<span class="round-tabs one"><i class="glyphicon glyphicon-user"></i></span>
										</a>
									</li>
									<li>
										<a href="#product" data-toggle="tab" class="hint--top" aria-label="Producto" id="tab_product">
											<span class="round-tabs two"><i class="glyphicon glyphicon-shopping-cart"></i></span>
										</a>
									</li>
									<li>
										<a href="#payment" data-toggle="tab" class="hint--top" aria-label="Gastos" id="tab_payment">
											<span class="round-tabs three"><i class="glyphicon glyphicon-usd"></i></span>
										</a>
									</li>

									<li>
										<a href="#activity" data-toggle="tab" class="hint--top" aria-label="Actividades" id="tab_activity">
											<span class="round-tabs four"><i class="glyphicon glyphicon-calendar"></i></span>
										</a>
									</li>

									<li>
										<a href="#done" data-toggle="tab" class="hint--top" onclick="go_show_printable()" aria-label="Completado" id="tab_done">
											<span class="round-tabs five"><i class="glyphicon glyphicon-ok"></i></span>
										</a>
									</li>
								</ul>
							</div>

							<div class="tab-content">
								<div class="tab-pane fade in active" id="client">
									@include('admin.budgets.modal.__client')


									<!--  Botones -->
									<hr>
									<div class="container-fluid">
										<a class="btn btn-app pull-left hint--top hidden"  aria-label="Ir a la Pestaña Anterior"><i class="fa fa-chevron-circle-left fa-lg text-yellow"></i> Anterior </a>
										<a class="btn btn-app pull-right hint--top" onclick="go_tab_product()" aria-label="Ir a la Siguiente Pestaña"><i class="fa fa-chevron-circle-right fa-lg text-yellow"></i> Siguiente </a>
									</div>
								</div>

								<div class="tab-pane fade" id="product">
									@include('admin.budgets.modal.__product')


									<!--  Botones -->
									<hr>
									<div class="container-fluid">
										<a class="btn btn-app pull-left hint--top" onclick="go_tab_client()"  aria-label="Ir a la Pestaña Anterior"><i class="fa fa-chevron-circle-left fa-lg text-yellow"></i> Anterior </a>
										<a class="btn btn-app pull-right hint--top" onclick="go_tab_payment()"  aria-label="Ir a la Siguiente Pestaña"><i class="fa fa-chevron-circle-right fa-lg text-yellow"></i> Siguiente </a>
									</div>
								</div>

								<div class="tab-pane fade" id="payment">
									@include('admin.budgets.modal.__expense')

									<!--  Botones -->
									<hr>
									<div class="container-fluid">
										<a class="btn btn-app pull-left hint--top"  onclick="go_tab_product()" aria-label="Ir a la Pestaña Anterior"><i class="fa fa-chevron-circle-left fa-lg text-yellow"></i> Anterior </a>
										<a class="btn btn-app pull-right hint--top"  onclick="go_tab_activity()"  aria-label="Ir a la Siguiente Pestaña"><i class="fa fa-chevron-circle-right fa-lg text-yellow"></i> Siguiente </a>
									</div>
								</div>

								<div class="tab-pane fade" id="activity">
									@include('admin.budgets.modal.__activity')
									<!--  Botones -->
									<hr>
									<div class="container-fluid">
										<a class="btn btn-app pull-left hint--top"  onclick="go_tab_payment()" aria-label="Ir a la Pestaña Anterior"><i class="fa fa-chevron-circle-left fa-lg text-yellow"></i> Anterior </a>
										<a class="btn btn-app pull-right hint--top"  onclick="go_tab_done()"  aria-label="Ir a la Siguiente Pestaña"><i class="fa fa-chevron-circle-right fa-lg text-yellow"></i> Siguiente </a>
									</div>
								</div>

								<div class="tab-pane fade" id="done">
									<!--  Resumen -->
									<div id='printable_container'></div>
									<!--  Botones -->
									<hr>
									<div class="container-fluid save-budget">
										<div class="row">
											<div class="col-xs-12 text-center">
												<span class="">
													<a class="btn btn-app pull-left hint--top"  onclick="go_tab_activity()" aria-label="Ir a la Pestaña Anterior"><i class="fa fa-chevron-circle-left fa-lg text-yellow"></i> Anterior </a>
													<button class="btn btn-app hint--top hint--top" id="btn_save-budget"   aria-label="Guardar Datos del Presupuesto"><i class="fa fa-save fa-lg text-yellow"></i> Guardar </button>
													<button class="btn btn-app hint--top hint--top" id="btn_empty-budget"  aria-label="Cancelar Operación"><i class="fa fa-close fa-lg text-yellow"></i> Cancelar </button>
												</span>
											</div>
										</div>
									</div>
								</div>

								<div class="clearfix"></div>
							</div>
						</div>
					</div>
				</div>

			</div>

			<div class="modal-footer hidden"  style="border: 2px solid black;" >
				<button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-close"></i> Cerrar </button>

				<button type="button" class="btn btn-default"><i class="fa fa-save fa-lg text-yellow"></i> Guardar</button>
			</div>
			{!! Form::close() !!}

		</div>
	</div>
</div>

{{-- <div class="modal fade modalformUserAdd"  tabindex="-1"  role="dialog" aria-labelledby="Modal" style="min-width: 800px!important;"> --}}



<script>
	@section('js_tab__modal')
    /*
    |--------------------------------------------------------------------------
    | Section: js_tab__modal
    |--------------------------------------------------------------------------
    |
    | Las funciones a continuacion son para procesos  o aspectos más generales de la modal.
    |
    | @author  Carlos Villarroel  -  cevv07@gmail.com
    */


    /**
     * Gestiona los contenido de los titulos de la Modal
     *
     * @param   void
     * @return  void
     * @author  Carlos Villarroel  -  cevv07@gmail.com
     * */
    function set_titles_modal(){
    	console.log('set_titles_modal');
    	
        set_title_label();
        set_title_budget();
        set_title_client();
        set_title_product();
        set_title_activity();
    }

    function set_title_label(){
        let status  =  $('#budget_status').val();
        let label   =  "PRESUPUESTO";

        switch (status){
			case "1":
                label = " PRESUPUESTO ";
                $('#modal_title-header_class').removeClass('bg-green');
                $('#modal_title-header_class').addClass('bg-yellow');
                break;
			case "2":
                label = " VENTA ";
                $('#modal_title-header_class').removeClass('bg-yellow');
                $('#modal_title-header_class').addClass('bg-green');
                break;
			default:
                label = " PRESUPUESTO ";
                $('#modal_title-header_class').removeClass('bg-green');
                $('#modal_title-header_class').addClass('bg-yellow');
                break;
		}

        $('#modal_title-label').html( label );
    }

    function set_title_budget(){
        let budget  = $('#budget_id_master').val();

        if (budget=="") budget = "Nuevo";

        $('#modal_title-budget').html( budget );
    }

    function set_title_client(){
        if ( $('#cli_id').val() ){
            var client    =  $('#cli_name').val() + ' ' + $('#cli_last_name').val();
        } else
            var client    = 'Sin Cliente';

        $('#modal_title-client').html( client );
    }

    function set_title_product(){
        $('#modal_title-product').html( table_load_budget_detail.data().count() + ' Item(s)' );
    }

    function set_title_activity(){
        task_quantity();
        $("#not_saved_eps").val("");
        $("#not_saved_task").val("");
        
    }






    /**
     * Trigger
     *
     * @param   void
     * @return  void
     * @author  Carlos Villarroel  -  cevv07@gmail.com
     * */
     
    function go_tab_client() {
    	console.log('go_tab_client');
        $('.nav-tabs a[href="#client"]').tab('show');
    }

    function go_tab_product() {
    	
    	if( $("#not_saved_eps").val() != "" ){
    		swal({title: 'Atención!', text: 'Debe guardar cambios ingresados en gastos y pagos ', type: 'error', html: true});
            return;
    	}
    	
    	console.log('go_tab_product');
        $('.nav-tabs a[href="#product"]').tab('show');
    }

    function go_tab_payment() {
        
    	if( $("#not_saved_task").val() != "" ){
    		swal({title: 'Atención!', text: 'Debe guardar cambios ingresados en la tarea', type: 'error', html: true});
            return;
    	}
    	
        console.log('go_tab_payment');
        $('.nav-tabs a[href="#payment"]').tab('show');
    }

    function go_tab_activity() {
    	
    	if( $("#not_saved_eps").val() != "" ){
    		swal({title: 'Atención!', text: 'Debe guardar cambios ingresados en gastos y pagos ', type: 'error', html: true});
            return;
    	}
    	
        console.log('go_tab_activity');
        $('.nav-tabs a[href="#activity"]').tab('show');
    }

    function go_tab_done() {
        
        if( $("#not_saved_task").val() != "" ){
    		swal({title: 'Atención!', text: 'Debe guardar cambios ingresados en la tarea', type: 'error', html: true});
            return;
    	}
    	
        console.log('go_tab_done');
        $('.nav-tabs a[href="#done"]').tab('show');
    }
    
    function go_show_printable() {
        console.log('go_tab_printable');
        const budget_status  =  $('#budget_status').val();
        if( budget_status == 1 ){
        	
        	//budget
        	console.log('Show budget printable from main modal');
        	//show_printable( $("#printable_container"), false );
        	check_before_printable( $("#printable_container"), false );
        	
        } else if( budget_status == 2 || budget_status == 3 || budget_status == 4 ){
        
        	// Sale, Reserve or Aprobed sale
        	console.log('Show sale printable from main modal');    
        }
    }
    

    $(document).on('shown.bs.tab', 'a[data-toggle="tab"]', function (e) {
        var tab = $(e.target);
        var contentId = tab.attr("href");

        if ( contentId != '#client' && contentId != '#tab_basics'
            && contentId != '#tab_personal' && tab.parent().hasClass('active')) {
            warning_edit ();
        }

    });
    
    
    /**
    *------------------------------------------------------------------------------------------
    * @description: Check if there are not saved changes in expenses and payments on tab change
    * @author:      Héctor Agüero
    *------------------------------------------------------------------------------------------
    */
    $(document).on('shown.bs.tab', 'a[data-toggle="tab"]', function (e) {
    	
    	var tab = $(e.target);
        var contentId = tab.attr("href");
        
        if ( contentId == "#client" || contentId == "#product" || contentId == "#activity" || contentId == "#done" ) {
        	check_not_saved_eps ();
        }

    });
    
    
    /**
    *-----------------------------------------------------------------------------
    * @description: Check if there are not saved changes in the task on tab change
    * @author:      Héctor Agüero
    *-----------------------------------------------------------------------------
    */
    $(document).on('shown.bs.tab', 'a[data-toggle="tab"]', function (e) {
    	
    	var tab = $(e.target);
        var contentId = tab.attr("href");
        
        if ( contentId != "#activity" ) {
        	check_not_saved_task ();
        }

    });
    
    


    $('.price').priceFormat({
        centsSeparator: ',',
        thousandsSeparator: '.',
        centsLimit: 0,
        clearPrefix: true,
        clearSuffix: true,
        prefix: '$',
        suffix: ''
    });




    /**
     * Guardar presupuesto
     *
     * @param   void
     * @return  modal prices cuotas
     * @author  Carlos Villarroel  -  cevv07@gmail.com
     * */
    $('#btn_save-budget:enabled').on('click', function (e) {
        e.preventDefault();
        button = $(this);
        button_text = $(this).html();
        button.html(' <i class="fa fa-spinner fa-pulse"></i> Guardando...');
        button.prop("disabled",true);


        var budget           = $('#budget_id_master').val();
        var type_sale        = $('#budget_id_type_sale').val();
        var type_payment     = $('#budget_id_type_payment').val();
        var type_patenting   = $('#budget_id_type_patenting').val();
        var type_expectation = $('#budget_id_expectation').val();
        
        var client           = $('#cli_id').val();
        var budget_status    = $('#budget_status').val();
        
        $("#budget_saved").val("");
        
        let url;
        if( budget === ''  || budget == 'Nuevo' ){

            console.log( 'Will save a new budget: ', budget );
            url = '{{route('budgetdetail.store')}}';

        } else {

            console.log( 'Will save an existing budget: ', budget );
            url = '{{route('budgetdetail.update')}}';
        }
        
        var token = '{{csrf_token()}}';
        $.ajax({
            url : url,
            type : 'POST',
            data: { budget: budget, budget_status: budget_status, client: client, type_sale: type_sale, type_payment: type_payment, type_patenting: type_patenting, type_expectation: type_expectation,   _token : token },
            dataType : 'json',
            beforeSend: function(){
                //$('.save-budget').empty().append('<div class="row"><div class="col-xs-12" style="text-align: center;"> <i class="fa fa-spinner fa-pulse fa-lg fa-5x text-yellow" style="vertical-align: middle;"></i> </div> </div>');
            },
            success : function(data) {
                swal({title: data.title, text: data.text, type: data.type, html: true});
                console.log('details_task_date: '+data.details_task_date);
                console.log('budget in update: '+data.budget);
                if(data.status && data.controller=="budget_storage"){
                    $('body').find('.modalPresupuestoAdd').trigger('click');
                    console.log( 'New budget stored!' );
                    
                    // reset list
                    table_budget_list.draw();
                    
                } else if(data.status && data.controller=="budget_update"){
                    console.log( 'Existing budget updated!' );
                    
                    // reset list
                    table_budget_list.draw();
                }
                if( data.message == '' ) { $("#budget_saved").val( 1 ); }
                console.log(`save budget data.message: {data.message}`);
            },
            complete: function () {

                setTimeout(function(){
                    button.prop("disabled",false);  //.removeAttr("disabled");
                    button.html( button_text );
                }, 6000);
            }
        });
    });
    

    $('#btn_empty-budget:enabled').on('click', function (e) {
        e.preventDefault();
        $('.modalPresupuestoAdd').modal('hide');
    });

	@endsection
</script>



@include('admin.budgets.clients.modal_def')

@include('admin.budgets.products.preload')

@include('admin.budgets.products.plan')

@include('admin.budgets.products.add')

@include('admin.budgets.driving_test.add')

@include('admin.budgets.products.add_change')

@include('admin.budgets.products.add_accessory')
