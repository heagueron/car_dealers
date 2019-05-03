@extends('admin.layouts.app')

@section('titulo', 'Venta')

@section('css')
	<link rel="stylesheet" href="{{ asset('asset/admin/plugins/dataTables/css/dataTables.bootstrap.min.css') }}">
	<link rel="stylesheet" href="{{ asset('asset/admin/plugins/select2/select2.min.css') }}">
	<link rel="stylesheet" href="{!! asset('asset/admin/plugins/datetimepicker/css/jquery-datetimepicker.min.css') !!}">
	<!--link rel="stylesheet" href="{!! asset('asset/admin/plugins/datetimepicker/css/fix_color_green.css') !!}"-->
	<link rel="stylesheet" href="{!! asset('asset/admin/plugins/iCheck/square/blue.css') !!}">
	<link rel="stylesheet" href="{!! asset('asset/admin/plugins/iCheck/square/green.css') !!}">
	<link rel="stylesheet" href="{!! asset('asset/admin/plugins/iCheck/square/red.css') !!}">
	<link rel="stylesheet" href="{!! asset('asset/admin/plugins/iCheck/flat/_all.css') !!}">
	<link rel="stylesheet" href="{!! asset('asset/admin/plugins/iCheck/minimal/_all.css') !!}">
	
	<!-- Cedano -->
	<link rel="stylesheet" href="{{ asset('asset/admin/plugins/select2/client-template.css') }}">
	<link rel="stylesheet" href="{{ asset('asset/admin/plugins/email-autocomplete/eac.css') }}">
	<link rel="stylesheet" href="{{ asset('asset/admin/plugins/flags/flags.css') }}"> 

	
	<!-- CVillarroel -->
	<link rel="stylesheet" href="{!! asset('asset/admin/plugins/tab/css.css') !!}">
    <link rel="stylesheet" href="{{ asset('asset/admin/plugins/select2/auto-template.css') }}">
	<link rel="stylesheet" href="{{ asset('asset/admin/plugins/select2/fix.css') }}">
	<link rel="stylesheet" href="{{ asset('asset/admin/plugins/select2/select2-bootstrap.css') }}">
	<link rel="stylesheet" href="{{ asset('asset/admin/plugins/car/font/flaticon.css') }}">
	<link rel="stylesheet" href="{{ asset('asset/admin/plugins/accordion/budget.css') }}">
	<link rel="stylesheet" href="{{ asset('asset/admin/plugins/sweetdropdown/jquery.sweet-dropdown.min.css') }}">
	<link rel="stylesheet" href="{{ asset('asset/admin/plugins/sumoselect/sumoselect.min.css') }}">
	
	
	
	<style>
		div.dataTables_wrapper div.dataTables_filter {
			display: none;
		}
		ul.dropdown-menu {
			right: 0 !important;
			left: auto;
		}

		div.settings-form .form-group {
			min-height: 40px; /*  ori:  30  */
			margin: 0;
			padding: 7px 0 1px;
			border-bottom: 1px solid #c0c1c2;
			/* overflow: hidden; */
		}

		select:focus{ outline: none !important;}
		
		.seller-origin-submenu {
    		position: relative;
    		z-index:200;
		}

		.seller-origin-submenu {
			
    		top: -120px;
    		left: -250px;
    		margin-top: 10px;
		}

	</style>
@endsection


@section('content')
	
	
	<div class="row hidden">

	</div>



	{{-- Botones add y extras --}}
	<div class="row" style="margin-bottom: 10px;">
		<div class="col-md-3 col-xs-12 col-sm-3">
			@ability('root', 'venta-create')

				<button type="button" class="btn btn-default new_budget" data-budget="nuevo" ><i class="fa fa-plus-circle fa-lg text-yellow"></i> Nuevo</button>

			@endability


			@php($open=Request::get('open'))
			@if( isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER']) && !empty($open) )

				<a href="{{$_SERVER['HTTP_REFERER']}}" type="button" class="btn btn-default"  ><i class="fa fa-arrow-circle-o-left fa-lg text-yellow"></i> Regresar </a>

			@endif
		</div>

		<div class="col-md-9 col-xs-12 col-sm-9">
			<form class="form-inline pull-right">
				<div class="form-group">
					<div class="input-group hide">
						<input type="text" class="form-control " placeholder="Buscar..." name="busqueda" id="busqueda" value="{{Request::get('busqueda')}}">
						<span class="input-group-btn">
							<button type="button" class="btn btn-warning"><i class="fa fa-search"></i></button>
						</span>
					</div>
				</div>
			</form>
		</div>
	</div>



	{{-- Esta es mi tabla con datatables --}}
	<div class="row">
		<div id="list" class="col-md-12">

			<div class="box box-warning " >
				<div class="box-header">
					<div class="clearfix">
						<h3 class="box-title"> Listado de Venta </h3>
						<div class="pull-right tableTools-container"></div>
					</div>
				</div>

				<div class="box-body">

					<div class="table-responsive">
						<table id="sale_list_load" class="table table-striped table-bordered table-hover " >
							<thead>
								<tr class="bg-yellow-active" >
									
									<th><span class="hint--top-right" data-hint="Etapa"><i class="fa fa-user"></i></span></th>
									
									<th><span class="hint--top" data-hint="Categoria"><i class="fa fa-star"></i></span></th>
									
									<th>Cliente</th>
									
									<th><span class="hint--top" data-hint="Empatía">E</span></th>
									
									<th>Producto</th>
									
									<th>Total</th>
									
									<th>Entrega</th>
									
									<th>Estado</th>
									
									<th><i class='fa fa-phone'></th>
									
									<th>Condiciones</th>
									
									<th>Claves</th>
						
								</tr>
							</thead>
							<tbody role="alert" aria-live="polite" aria-relevant="all"></tbody>
						</table>
					</div>


				</div>

				<div class="box-footer">
					<table align="center">
						<thead>
							<th colspan="6" class="small">
								<span class="btn btn-xs btn-default"><i class="fa fa-search-plus text-yellow"></i></span> Ver venta
								{{--
								<span class="btn btn-xs btn-primary"><i class="fa fa-pencil"></i></span> Editar
								<span class="btn btn-xs btn-success"><i class="fa fa-check-circle-o"></i></span> Habilitar
								<span class="btn btn-xs btn-warning"><i class="fa fa-ban"></i></span> Deshabilitar
								<span class="btn btn-xs btn-danger"><i class="fa fa-trash-o"></i></span> Eliminar
								--}}
							</th>
						</thead>
					</table>
				</div>
			</div>

			<br><br>
		</div>



		<div id="panel" class="hidden">
			<div class="" id="panel_content">
				@yield('panel_partial')
			</div>
		</div>

	</div>
	
	<input type="hidden" id="prior_sub_origin" val='' />
	<input type="hidden" id="origin_rowIndex" val='' />
	
	@include('admin.budgets.modal.budget')


	@include('admin.budgets.panel.modal_agreement')
	@include('admin.budgets.panel.modal_stage')
	@include('admin.budgets.panel.modal_category')
	@include('admin.budgets.panel.modal_task')
	@include('admin.budgets.panel.modal_comments')
	@include('admin.budgets.panel.modal_budget_printable')
	@include('admin.budgets.panel.modal_budget_to_sale')
	
	
	@include('admin.sales.functions')

@endsection


@section('js')
	<!-- DataTables -->
	<script src="{{ asset('asset/admin/plugins/dataTables/js/jquery.dataTables.min.js') }}"></script>
	<script src="{{ asset('asset/admin/plugins/dataTables/js/dataTables.bootstrap.app.js') }}"></script>
	<script src="{{ asset('asset/admin/plugins/dataTables/extensions/TableTools/js/dataTables.tableTools.js') }}"></script>
	<script src="{{ asset('asset/admin/plugins/select2/select2.full.min.js') }}"></script>
	<script src="{{ asset('asset/admin/plugins/select2/i18n/es.js') }}"></script>
	<!-- script src="{!! asset('asset/admin/plugins/moment/moment.min.js')!!}"></script>
	<script src="{!! asset('asset/admin/plugins/moment/locale/es.js')!!}"></script -->
	<script src="{!! asset('asset/admin/plugins/datetimepicker/js/jquery-datetimepicker.full.min.js')!!}"></script>
	<script src="{!! asset('asset/admin/plugins/iCheck/icheck.min.js') !!}"></script>
	<script src="{!! asset('asset/admin/plugins/price_format/jquery.priceformat.min.js') !!}"></script>
	<script src="{{ asset('asset/admin/plugins/input-mask/jquery.inputmask.js') }}"></script>
	<script src="{{ asset('asset/admin/plugins/input-mask/jquery.inputmask.date.extensions.js') }}"></script>
	<script src="{{ asset('asset/admin/plugins/input-mask/jquery.inputmask.extensions.js') }}"></script>

	

	<!-- Cedano -->
	<script src="{{ asset('asset/admin/plugins/email-autocomplete/jquery.email-autocomplete.min.js') }}"></script>
	<script src="{{ asset('asset/admin/js/clients.js') }}"></script>
	<script src="{{ asset('asset/admin/plugins/typeahead/typeahead.min.js') }}"></script>
	

	<!-- CVillarroel -->
	<script src="{{ asset('asset/admin/plugins/sumoselect/jquery.sumoselect.js') }}"></script>
	<script src="{{ asset('asset/admin/plugins/sweetdropdown/jquery.sweet-dropdown.min.js') }}"></script>

	
	
	<script>
		jQuery(function($) {


            /*
            |--------------------------------------------------------------------------
            | FUNCTIONS GENERALS
            | autor: Carlos villarroel
            |--------------------------------------------------------------------------
            */

            //tema en select2
            $.fn.select2.defaults.set( "theme", "bootstrap" );

			{{-- inicial sidebar --}}
            $( "ul.sidebar-menu li" ).each(function() {
                if($(this).text().trim() == 'Venta')   $(this).addClass('active');
            });


            $('[data-mask]').inputmask();




            //$(".modalPresupuestoAdd").modal('show');        $('.nav-tabs a[href="#product"]').tab('show');   //$('.nav-tabs a[href="#payment"]').tab('show');
            //$(".modalProductPreload").modal('show');
            
            

			/** funciones del modulo **/

			@section('mi_funciones')
				@yield('js_tab__product')
				@yield('js_tab__client')
				@yield('js_tab__expense')
				@yield('js_tab__activity')
				@yield('js_tab__modal')
				

            	//list budgets
				@yield('js_list_sale')
			@endsection




            /**  Agrupar funciones para form_ajax_success: solo si se usa validation_beta  **/

			@section('form_ajax_success')
				@yield('form_ajax_success_tab__client')
				@yield('form_ajax_success_tab__product')
				@yield('form_ajax_success_tab__expense')
				@yield('form_ajax_success_tab__activity')
				
				//list budgets
				@yield('form_ajax_success__list_sale')

				//general
                swal({title: check.title, text: check.text, type: check.type, html: true});
			@endsection



		});
	</script>


	@include('vendor.ajax.validacion_beta')
@endsection