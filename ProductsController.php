<?php namespace App\Http\Controllers\Products;


use App\Http\Controllers\Controller;

use App\Models\Products\Product;
use App\Models\Products\Modelo;
use App\Models\Products\Product_feature;
use App\Models\Brand;
use App\Http\Requests\ProductRequest;


use Illuminate\Http\Request;
use DB;
use Datatables;
use Auth;


class ProductsController extends Controller
{


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        /** si no es ROOT y no posee algun permiso de empresa: abortar **/
        if( !Auth::user()->hasRole('root') && !Auth::user()->can('producto-*') )  abort(403);


        if ($request->ajax() || $request->wantsJson()) {

            /**  filtros de productos  **/
            $where = [];
            if ($request->has('id_brand'))     $where[] = ['products.id_brand',    '=', $request->id_brand];
            if ($request->has('id_model'))     $where[] = ['products.id_model',    '=', $request->id_model];
            if ($request->has('id_engine'))    $where[] = ['products.id_engine',   '=', $request->id_engine];
            if ($request->has('id_traction'))  $where[] = ['products.id_traction', '=', $request->id_traction];

            if ($request->has('doors'))        $where[] = ['products.doors',       '=', $request->doors];
            if ($request->has('year'))         $where[] = ['products.year',        '=', $request->year];
            if ($request->has('status'))       $where[] = ['products.status',      '=', $request->status];

            if ($request->has('name'))         $where[] = ['products.name',    'like', "%".$request->name."%"];
            if ($request->has('version'))      $where[] = ['products.version', 'like', "%".$request->version."%"];
            if ($request->has('tma'))          $where[] = ['products.tma',     'like', "%".$request->tma."%"];
            if ($request->has('seq'))          $where[] = ['products.seq',     'like', "%".$request->seq."%"];

            $dato = Product::join('brands', 'products.id_brand', '=', 'brands.id')
                ->join('models', 'products.id_model', '=', 'models.id')
                ->select(['products.id', 'products.name', 'products.id_engine', 'products.version', 'products.tma', 'products.seq', 'products.year', 'products.status',
                    'products.id_cabin', 'products.distance_axis', 'products.id_traction', 'products.id_transmission', 'products.origin_module',   'products.doors',
                    'brands.name as brand', 'brands.id as id_brand',
                    'models.name as model', 'models.id as id_model' ])
                ->where($where);


            /*
            $dato = DB::table('products')
                ->join('brands', 'products.id_brand', '=', 'brands.id')
                ->join('models', 'products.id_model', '=', 'models.id')
                ->select(['products.id', 'products.name', 'products.id_engine', 'products.version', 'products.tma', 'products.seq',  'products.status',
                    'brands.name as brand', 'brands.id as id_brand',
                    'models.name as model', 'models.id as id_model' ])
                ->where($where);

            $result = Product
                ::join('contacts', 'users.id', '=', 'contacts.user_id')
                ->join('orders', 'users.id', '=', 'orders.user_id')
                ->select('users.id', 'contacts.phone', 'orders.price')
                ->getQuery() // Optional: downgrade to non-eloquent builder so we don't build invalid User objects.
                ->get();
            */

            return Datatables::of($dato)
                ->addColumn('action', function ($dato) {
                    $html = '';

                    if(  Auth::user()->ability('root', 'producto-status')  ){
                        if( $dato->status === '1' ):
                            $html .= '<a href="#"  data-valor=\'{"id":"'.$dato->id.'", "status":"'.$dato->status.'", "nombre":"'.$dato->name.'" }\' class="btn btn-xs btn-warning status hint--top" aria-label="Deshabilitar"><i class="fa fa-ban"></i></a> ';
                        else:
                            $html .= '<a href="#" data-valor=\'{"id":"'.$dato->id.'", "status":"'.$dato->status.'", "nombre":"'.$dato->name.'" }\'  class="btn btn-xs btn-success status hint--top" aria-label="Habilitar"><i class="fa fa-check-circle-o"></i></a> ';
                        endif;
                    }

                    $html .= '<a href="#" data-iddata="'.$dato->id.'" class="btn btn-xs btn-primary editar hint--top" aria-label="Editar"><i class="fa fa-pencil"></i></a> ';

                    $html .= "<a href='#' data-iddata=".$dato->id." class='btn btn-xs btn-danger eliminar hint--top' aria-label='Eliminar'><i class='fa fa-trash'></i></a>";

                    return $html;
                })
                ->editColumn('id_brand', function ($dato) {
                    return @Brand::query()->where('id', $dato->id_brand)->first()->name;
                })
                ->editColumn('model', function ($dato) {
                    return ucwords(strtolower( $dato->model ));
                })


                ->editColumn('id_engine', function ($dato) {
                    return @DB::table('engines')->where('id', $dato->id_engine)->first()->name;
                    //$engine = @DB::table('engines')->where('id', $dato->id_engine)->first()->name;
                    //return empty($engine)?'':$engine;
                })

                ->editColumn('id_cabin', function ($dato) {
                    return @DB::table('cabines')->where('id', $dato->id_cabin)->first()->name;
                })
                ->editColumn('distance_axis', function ($dato) {
                    $axi = @$dato->distance_axis;
                    return empty($axi)?'':$axi . " MM";
                })
                ->editColumn('id_traction', function ($dato) {
                    return @DB::table('tractions')->where('id', $dato->id_traction)->first()->name;
                })
                ->editColumn('id_transmission', function ($dato) {
                    return @DB::table('transmissions')->where('id', $dato->id_transmission)->first()->name;
                })


                ->addColumn('description', function ($dato) {
                    $value  = "";

                    $value  .= !empty($dato->brand)?  $dato->brand            . " "   :   "";
                    $value  .= !empty($dato->model)?  $dato->modelo->name     . " "   :   "";


                    $engine = @\App\Models\Products\Engine::find($dato->id_engine);

                    $value  .= isset($engine->fuel->name)     ? $engine->fuel->name       . " "   :   "";
                    $value  .= isset($engine->cylinder->name) ? $engine->cylinder->name   . " "   :   "";


                    /** Exntensiones */
                    $model = $dato;
                    //cabines
                    if ($model->id_cabin>0){
                        $cabine = @DB::table('cabines')->where('id', $model->id_cabin)->first()->name;

                        if (strtolower($cabine)=='doble')  $cabine=' C.D ';
                        if (strtolower($cabine)=='simple') $cabine=' C.S ';
                        $value  .= $cabine;
                    }else
                        $value  .= $model->doors . (!empty($model->doors)? ' Ptas ' : null);

                    //tractions
                    if ($model->id_traction>0){
                        $traction = @DB::table('tractions')->where('id', $model->id_traction)->first()->name;
                        $value  .= " $traction ";  // ------->
                    }
                    /** Exntensiones */



                    //$value  .= !empty($dato->doors)?    $dato->doors          . " Ptas "   :   "";
                    $value  .= !empty($dato->version)?  $dato->version        . " "   :   "";

                    return $value;




                        $model = $dato;

                        // motor :
                        if ($model->id_engine>0){
                            $motor = @DB::table('engines')->join('engine_cylinders', 'engines.id_cylinder', '=', 'engine_cylinders.id')->join('engine_fuels', 'engines.id_fuel', '=', 'engine_fuels.id')
                                ->select(['engine_cylinders.name as engine', 'engine_fuels.name as fuel'])->where('engines.id', $model->id_engine)->first();
                            $model->engine = $motor->engine;
                            $model->fuel   = $motor->fuel;
                        }

                        //cabines
                        if ($model->id_cabin>0){
                            $cabine = @DB::table('cabines')->where('id', $model->id_cabin)->first()->name;

                            if (strtolower($cabine)=='doble') $cabine='C.D';
                            if (strtolower($cabine)=='simple') $cabine='C.S';
                            $model->doors = $cabine;
                        }else
                            $model->doors .= 'Ptas';

                        //tractions
                        if ($model->id_traction>0){
                            $traction = @DB::table('tractions')->where('id', $model->id_traction)->first()->name;
                            $model->doors .= '/'.$traction;
                        }

                        return @$model->version.'/'.@$model->doors.'/'.@$model->engine.'/'.@$model->fuel.'/'.@$model->year;
                })

                ->editColumn('origin_module', function ($dato) {
                    if ($dato->status === '1'):
                        return '<label class="label label-success hint--top" data-hint="Habilitado para Venta">'.$dato->origin_module.'</label>             <span class="hint--top-left" data-hint="Registro creado desde el módulo: '.$dato->origin_module.'"><i class="fa fa-info-circle text-primary"></i></span>';

                    elseif ($dato->status == '0'):
                        return '<label class="label label-danger hint--top"  data-hint="Deshabilitado para Venta">'.$dato->origin_module.'</label>          <span class="hint--top-left" data-hint="Registro creado desde el módulo: '.$dato->origin_module.'"><i class="fa fa-info-circle text-primary"></i></span>';

                    else:
                        return '<label class="label label-warning hint--top" data-hint="Faltan completar caracteristicas">'.$dato->origin_module.'</label>  <span class="hint--top-left" data-hint="Registro creado desde el módulo: '.$dato->origin_module.'"><i class="fa fa-info-circle text-primary"></i></span>';
                    endif;
                })
                ->rawColumns(['origin_module', 'action'])
                ->make(true);
        }else
            return view('admin.productos.index')->with('marcas', Brand::query()->orderBy('name', 'ASC')->pluck('name', 'id') );
    }



    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        /** Check Permissions  **/
        if ( !Auth::user()->ability('root', 'producto-create') )  abort(403);

        return view('admin.productos.registrar');
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProductRequest $request)
    {
        $model = new Product();
        $model->id_brand        = $request->prod_id_brand;
        $model->id_model        = $request->prod_id_model;

        $model->id_engine       = $request->prod_id_engine;
        $model->id_traction     = $request->prod_id_traction;
        $model->id_cabin        = $request->prod_id_cabin;
        $model->id_transmission = $request->prod_id_transmission;
        $model->id_clutche      = $request->prod_id_clutche;
        $model->id_axi          = $request->prod_id_axi;
        $model->id_direction    = $request->prod_id_direction;
        $model->id_tire         = $request->prod_id_tire;



        //$model->id_suspension_front = $request->prod_id_suspension_front;
        //$model->id_suspension_back  = $request->prod_id_suspension_back;
        $model['suspension->front']   = $request->prod_id_suspension_front;
        $model['suspension->back']    = $request->prod_id_suspension_back;
        $model['suspension->general'] = $request->prod_id_suspension_general;

        //$model->id_brake       = $request->prod_id_brake;
        $model['brake->front']   = $request->prod_id_brake_front;
        $model['brake->back']    = $request->prod_id_brake_back;
        $model['brake->service'] = $request->prod_id_brake_service;
        $model['brake->parking'] = $request->prod_id_brake_parking;
        $model['brake->engine']  = $request->prod_id_brake_engine;


        $model->doors           = $request->prod_doors;
        $model->version         = strtoupper($request->prod_version);
        $model->year            = $request->prod_year;
        $model->tma             = $request->prod_tma;
        $model->seq             = $request->prod_seq;

        $model->total_length    = $request->prod_total_length;
        $model->total_width     = $request->prod_total_width;
        $model->distance_axis   = $request->prod_distance_axis;
        $model->high            = $request->prod_high;
        $model->ground          = $request->prod_ground;
        $model->weight          = $request->prod_weight;
        $model->capacity        = $request->prod_capacity;
        $model->origin_module   = "products";


        $model->name = Brand::query()->where('id', $model->id_brand)->first()->name.' '. Modelo::query()->where('id', $model->id_model)->first()->name.' '.$model->version;


        //verificar si marca-modelo-version existen
        $repeat = DB::table('products')->where([['id_brand', '=', $model->id_brand],['id_model', '=', $model->id_model], ['version', '=', $model->version]])->get()->count();
        if ($repeat>0){
            return response()->json([
                'status' => true,
                'controller'  => 'productos',
                'title'  => 'Operacion cancelada!',
                'text' => 'Ya existe esta versión registrada. <br>¿Desea ingresar otra versión?',
                'insert_id' => $model->id,
                'type' => 'error'
            ],200);
        }


        $model->save();

        /** add features **/
        if ($request->has('features')) {
            //Resetear
            DB::table('product_feature')->where([['id_product', '=', $model->id]])->delete();


            //Asignar
            foreach ($request->features as $feature){

                DB::table('product_feature')->insert([
                    'id_product'   => $model->id,
                    'id_feature'   => $feature,
                    'created_at'   => date('Y-m-d H:i:s'),
                    'updated_at'   => date('Y-m-d H:i:s'),
                ]);
            }
        }
        //$feature = new Product_feature();
        //$feature->save();

        return response()->json([
            'status' => true,
            'controller'  => 'productos',
            'title'  => 'Operación Exitosa!',
            //'text' => 'Los datos de <b>'.$model->name.'</b> ha sido registrado satisfactoriamente.',
            'text' => '¿Desea agregar una nueva version a este modelo?',
            'type' => 'success'
        ],200);
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        /** Check Permissions  **/
        if ( !Auth::user()->ability('root', 'producto-edit') )  abort(403);


        $model = Product::findorfail($id);

        return view('admin.productos.editar')->with( compact('model') );
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ProductRequest $request, $id)
    {
        $model = Product::findorfail($id);
        $model->id_brand        = $request->prod_id_brand;
        $model->id_model        = $request->prod_id_model;

        $model->id_engine       = $request->prod_id_engine;
        $model->id_traction     = $request->prod_id_traction;
        $model->id_cabin        = $request->prod_id_cabin;
        $model->id_transmission = $request->prod_id_transmission;
        $model->id_clutche      = $request->prod_id_clutche;
        $model->id_axi          = $request->prod_id_axi;
        $model->id_direction    = $request->prod_id_direction;
        $model->id_tire         = $request->prod_id_tire;



        //$model->id_suspension_front = $request->prod_id_suspension_front;
        //$model->id_suspension_back  = $request->prod_id_suspension_back;
        $model['suspension->front']   = $request->prod_id_suspension_front;
        $model['suspension->back']    = $request->prod_id_suspension_back;
        $model['suspension->general'] = $request->prod_id_suspension_general;

        //$model->id_brake       = $request->prod_id_brake;
        $model['brake->front']   = $request->prod_id_brake_front;
        $model['brake->back']    = $request->prod_id_brake_back;
        $model['brake->service'] = $request->prod_id_brake_service;
        $model['brake->parking'] = $request->prod_id_brake_parking;
        $model['brake->engine']  = $request->prod_id_brake_engine;


        $model->doors           = $request->prod_doors;
        $model->version         = strtoupper($request->prod_version);
        $model->year            = $request->prod_year;
        $model->tma             = $request->prod_tma;
        $model->seq             = $request->prod_seq;

        $model->total_length    = $request->prod_total_length;
        $model->total_width     = $request->prod_total_width;
        $model->distance_axis   = $request->prod_distance_axis;
        $model->high            = $request->prod_high;
        $model->ground          = $request->prod_ground;
        $model->weight          = $request->prod_weight;
        $model->capacity        = $request->prod_capacity;
        
        $model->status          = '0'; // revisar


        $model->name = Brand::query()->where('id', $model->id_brand)->first()->name.' '. Modelo::query()->where('id', $model->id_model)->first()->name.' '.$model->version;

        //verificar si marca-modelo-version existen
        $repeat = 0; //DB::table('products')->where([['id_brand', '=', $model->id_brand],['id_model', '=', $model->id_model], ['version', '=', $model->version], ['id', '!=', $id]])->get()->count();
        if ($repeat>0){
            return response()->json([
                'status' => false,
                'controller'  => 'productos',
                'title'  => 'Operacion cancelada!',
                'text' => 'Ya existe esta versión registrada. <br>¿Desea ingresar otra versión?',
                'type' => 'error'
            ],200);
        }

        $model->save();

        /** add features **/
        if ($request->has('features')) {
            //Resetear
            DB::table('product_feature')->where([['id_product', '=', $model->id]])->delete();


            //Asignar
            foreach ($request->features as $feature){

                DB::table('product_feature')->insert([
                    'id_product'   => $model->id,
                    'id_feature'   => $feature,
                    'created_at'   => date('Y-m-d H:i:s'),
                    'updated_at'   => date('Y-m-d H:i:s'),
                ]);
            }
        }
        //$feature = new Product_feature();
        //$feature->save();

        return response()->json([
            'status' => true,
            'controller' => 'productos',
            'title'  => 'Operación exitosa!',
            'text' => 'El registro de <b>'.$model->name.'</b> ha sido actualizado satisfactoriamente.',
            'type' => 'success'
        ],200);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $registro = Product::findorfail($id);

        if(   !Auth::user()->ability('root', 'producto-delete') or count($registro->stocks) > 0  ){

            return response()->json([
                'status' => false,
                'title'  => 'Oops!',
                //'text' => 'Ocurrio un error al intentar eliminar  <b>'.$registro->name.'</b>. Existen registros vinculados a este dato o no posee PERMISO.',
				'text' => 'Ocurrio un error al intentar eliminar. Existen registros vinculados a este dato o no posee PERMISO.',
                'type' => 'error'
            ],200);

        }else{
            $registro->delete();
            return response()->json([
                'status' => true,
                'title'  => 'Eliminado!',
                //'text' => 'El registro de <b>'.$registro->name.'</b> ha sido eliminado correctamente!',
				'text' => 'El registro ha sido eliminado correctamente!',
                'type' => 'success'
            ],200);
        }
    }


    public function status($id){
        /** check permisos **/
        if(!Auth::user()->ability('root', 'producto-status')) return response()->json(['status'=>false, 'title'=>'Acceso restringido!', 'text'=>'No tiene los <b>permisos</b> para ejecutar la operación...', 'type'=>'error'],200);

        $registro = Product::findorfail($id);


        ($registro->status === '1' ? $registro->status = '0' : $registro->status = '1');
        $registro->save();
        return response()->json([
            'status' => true,
            'title'  => $registro->estado,
            'text' => "Se ha ".$registro->estado." a <b>". $registro->name."</b> de forma exitosa.",
            'type' => 'success'
        ],200);
    }



    // en desarrollo

    public function getList(Request $request, $id=0)
    {
        if ( $id>0 ) $where = [['id_brand', '=', $id]];
        else         $where = [];

        if ($request->ajax() || $request->wantsJson()) {
            $data = Product::select(DB::raw('name as nombre, id'))->where($where)->orderBy('name', 'ASC')->get();
            return response()->json($data);
        }
    }


    public function getVersions(Request $request, $id=0)
    {
        $where = [];
        //if ( $id>0 )
        $where = [['id_model', '=', $id]];

        if ($request->ajax() || $request->wantsJson()) {


            //xl 4p natfa 1.6 2018
            $data = Product::distinct()->join('models', 'products.id_model', '=', 'models.id')
                //->join('engines', 'products.id_engine', '=', 'engines.id')
                //->join('engine_cylinders', 'engines.id_cylinder', '=', 'engine_cylinders.id')
                //->join('engine_fuels', 'engines.id_fuel', '=', 'engine_fuels.id')
                ->where($where)->whereNotNull('version')
                ->orderBy('version', 'ASC')
                ->get(['version', 'doors','id_cabin', 'id_traction','year', 'products.name as nombre', 'products.id as id_product']);
                //->get(['engine_cylinders.name as engine', 'engine_fuels.name as fuel', 'version', 'doors','id_cabin', 'id_traction','year', 'products.name as nombre', 'products.id as id_product']);

            foreach ($data as $model){

                // motor :
                if ($model->id_engine>0){
                    $motor = @DB::table('engines')->join('engine_cylinders', 'engines.id_cylinder', '=', 'engine_cylinders.id')->join('engine_fuels', 'engines.id_fuel', '=', 'engine_fuels.id')
                                ->select(['engine_cylinders.name as engine', 'engine_fuels.name as fuel'])->where('engines.id', $model->id_engine)->first();
                    $model->engine = $motor->engine;
                    $model->fuel   = $motor->fuel;
                }

                //cabines
                if ($model->id_cabin>0){
                    $cabine = @DB::table('cabines')->where('id', $model->id_cabin)->first()->name;

                    if (strtolower($cabine)=='doble') $cabine='C.D';
                    if (strtolower($cabine)=='simple') $cabine='C.S';
                    $model->doors = $cabine;
                }else
                    $model->doors .= 'Ptas';

                //tractions
                if ($model->id_traction>0){
                    $traction = @DB::table('tractions')->where('id', $model->id_traction)->first()->name;
                    $model->doors .= '/'.$traction;
                }

                $model->nombre = @$model->version.'/'.@$model->doors.'/'.@$model->engine.'/'.@$model->fuel.'/'.@$model->year;
            }

            return response()->json($data);
        }
    }



    public function getPaints(Request $request, $id=0)
    {
        $where = [];
        //if ( $id>0 )
        $where = [['id_model', '=', $id]];
        
        if ($request->ajax() || $request->wantsJson()) {
            $data = DB::table('paints')
                //->join('paint_models', 'paint_models.id_paint', '=', 'paints.id')

                ->join('paint_codes', 'paints.id', '=', 'paint_codes.id_paint')
                ->join('paint_models', 'paint_models.id_paint_code', '=', 'paint_codes.id')  // paint_codes.code

                ->where($where)
                ->select(DB::raw('paints.name as nombre, paints.id'))
                ->orderBy('nombre', 'ASC')
                ->get();

            //Ucwords
            foreach ($data as $model){
                $model->nombre = @ucwords(strtolower($model->nombre));
            }

            return response()->json($data);
        }

    }



    public function get_stock_by_product(Request $request, $id_product=0, $id_paint=0)
    {
        $where = [];

        //  filtros busqueda : se corresponden a los del metodo:  product_pre_charge_ajax() columna color
        if ( $request->has('id_prod') and  $request->has('id_paint') ){

            // extraer producto
            $model = @\App\Models\Products\Product::query()->where('id', $request->id_prod)->first();

            $where[] = ['stocks.tma',    '=', $model->tma];
            $where[] = ['stocks.seq',    '=', $model->seq];


            // extraer paint
            $color = @DB::table('paints')
                ->join('paint_codes', 'paints.id', '=', 'paint_codes.id_paint')
                ->join('paint_models', 'paint_models.id_paint_code', '=', 'paint_codes.id')  //paint_codes.code
                ->select([ 'paints.id as id_paint', 'paints.name as color', 'paint_codes.code' ])
                ->where([ ['paint_codes.id_paint', '=', $request->id_paint], ['paint_models.id_model', '=', $model->id_model] ])->orderBy('color', 'ASC')->get()->last();

            $where[] = ['stocks.paint_code',    '=', $color->code];
        }

        $where[] = ['stocks.id_companie',    '=', Auth::user()->id_empresa];




        if ($request->ajax() || $request->wantsJson()) {
            $data = DB::table('stocks')
                ->join('paint_codes', 'stocks.paint_code', '=', 'paint_codes.code')
                ->join('paints', 'paints.id', '=', 'paint_codes.id_paint')
                ->where($where)
                ->select([ 'stocks.*' ])
                //->select(DB::raw('stocks.*'))
                ->orderBy('delivery', 'ASC')
                ->get();

            //Ucwords
            foreach ($data as $model){
                $model->nombre = @ucwords(strtolower($model->chasis. " " . $model->motor));
            }

            return response()->json($data);
        }

    }




}
