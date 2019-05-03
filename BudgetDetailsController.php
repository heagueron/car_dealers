<?php
namespace App\Http\Controllers\Budgets;


use App\Http\Controllers\Controller;


use App\Http\Requests\BudgetRequest;
use App\Http\Requests\DefaultRequest;
use App\Http\Requests\AccessoryRequest;
use App\Http\Requests\BudgetDetailRequest;
use App\Http\Requests\DrivingTestRequest;
use App\Http\Requests\WantProductRequest;
use App\Models\Budgets\Budget;
use App\Models\Budgets\Budget_accessory;
use App\Models\Budgets\Budget_agreement;

use App\Models\Budgets\Budget_cash;
use App\Models\Budgets\Budget_check;
use App\Models\Budgets\Budget_credit;
use App\Models\Budgets\Budget_document;
use App\Models\Budgets\Budget_expense;
use App\Models\Budgets\Budget_used;

use App\Models\Budgets\Plan_payment;

use App\Models\Budgets\Budget_detail;

use App\Models\Budgets\Budget_substage;

use App\Models\Budgets\Driving_test;

use App\Models\Task;
use App\Models\Budgets\Comments;
use App\Models\Products\Product;
use App\Models\Empresa;
use App\User;
use App\Models\Client;
use App\Models\Client_type_location;
use App\Models\Client_type_mails;
use App\Models\Client_type_phone;
use App\Models\Client_contact;



use Illuminate\Http\Request;
use DB;
use Datatables;
use Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Database\Eloquent\Collection;
use App\Traits\BudgetTrait;




class BudgetDetailsController extends Controller
{

    use BudgetTrait;


    /*
    |--------------------------------------------------------------------------
    | Section: Products
    |--------------------------------------------------------------------------
    |
    | Los metodos a continuacion estan estrictamente relacionados a la
    | pestaña o TAB de productos en presupuestos.
    | 
    | @author  Carlos Villarroel  -  cevv07@gmail.com
    */
    

    /**
     * $budget_key: clave para manejo de la sesion
     *
     * @var string
     * @author  Carlos Villarroel  -  cevv07@gmail.com
     * */
    protected $budget_key = "";

    /**
     * $structure_default: estructura por defecto para la sesion
     *
     * @var array
     * @author  Carlos Villarroel  -  cevv07@gmail.com
     * */
    protected $structure_default = "";


    /**
     *  Crea una nueva instancia del controlador con la estructura de la sesion a usar
     *
     * @return void
     * @author  Carlos Villarroel  -  cevv07@gmail.com
     * */
    public function __construct()
    {
        $this->budget_key = "budget_detail";  //  budget_store
        //$this->budget_key = "budget_detail" . @\Auth::user()->id;

        $data  = [];
        $data['nuevo']['products']         = [];
        $data['nuevo']['driving_test']     = [];
        $data['nuevo']['accessories']      = [];
        $data['nuevo']['client']           = null;
        $data['nuevo']['type_sale']        = 1;
        $data['nuevo']['type_payment']     = 1;
        $data['nuevo']['type_patenting']   = 1;
        $data['nuevo']['type_expectation'] = 1;
        
        $data['nuevo']['substages']        = [];
        $data['nuevo']['agreements']       = [];
        $data['nuevo']['task']             = [];
            


        $this->structure_default = $data;

        if(!Session::has($this->budget_key)) Session::put($this->budget_key, $data);
    }
    
    
    
    

    /**
     * budget_detail_load :  mediante ajax se busca el producto pre-seleccionado en stock fisico y virtual
     *
     * @param   \Illuminate\Http\Request  $request
     * @return  \Illuminate\Http\Response json
     * @author  Carlos Villarroel  -  cevv07@gmail.com
     * */
    public function budget_detail_load(Request $request)
    {

        if ($request->ajax() || $request->wantsJson()) {

            $id_budget = isset($request->budget_id_master)?$request->budget_id_master:'nuevo';

            $data      = Session::get($this->budget_key);

            $details   = new Collection($data[$id_budget]['products']);


            return Datatables::of($details)
                ->addColumn('action', function ($dato) use ($id_budget, $data) {

                    $model  = @\App\Models\Products\Product::query()->where('id', $dato->id_product)->first();
                    $marca  = $model->brand->name;
                    $modelo = $model->modelo->name;


                    //verificar si el auto ya tiene prueba de manejo
                    $num_test = 0;
                    $budget_manejo = Session::get($this->budget_key);
                    if( isset($budget_manejo[$id_budget]['driving_test'][$dato->id_product]) ){
                        $num_test = 1;
                    }

                    $html  = '';
                    $html .= '<a href="#" 
                                data-id_prod="'.@$dato->id_product.'" 
                                data-id_brand="'.@$model->id_brand.'" 
                                data-id_model="'.@$model->id_model.'" 
                                data-marca="'.@$marca.'" 
                                data-modelo="'.@$modelo.'" 
                                class="btn btn-xs btn-default hint--top pull-left add_test_drive" aria-label="Prueba de manejo" >
                                <i class="fa fa-road"></i><sup>'.@$num_test.'</sup>
                              </a> ';


                    if ( $data[$id_budget]['type_sale'] == 2) {    // venta planes
                        //if (  )
                        $color = isset($dato->id_product_want) ? "text-yellow" : "text-gray";

                        $html .= '<a href="#" 
                                    data-id_brand="'.@$model->id_brand.'" 
                                    data-id_model="'.@$model->id_model.'" 
                                    data-marca="'.@$marca.'" 
                                    data-want="'.(isset($dato->id_product_want) ? $dato->id_product_want : null).'" 
                                    data-key="'.@$dato->id.'" 
                                    class="btn btn-xs btn-default '.$color.' hint--top-left pull-left add_want_product" aria-label="Desea otro producto?" >
                                    <i class="fa fa-refresh"></i>
                                  </a> ';
                    }

                    $html .= '<a href="#" data-iddata='.@$dato->id.' class="btn btn-xs btn-danger pull-left hint--top-left btn-delete"  aria-label="Eliminar"><i class="fa fa-trash"></i></a>';
                    return $html;
                })
                ->editColumn('product', function ($dato) {

                    $model = @\App\Models\Products\Product::query()->where('id', $dato->id_product)->first();

                    //cabines or doors
                    if ($model->id_cabin>0){
                        $cabine = @DB::table('cabines')->where('id', $model->id_cabin)->first()->name;

                        if (strtolower($cabine)=='doble') $cabine='C.D';
                        if (strtolower($cabine)=='simple') $cabine='C.S';
                        $model->doors = $cabine;
                    }else{
                        $model->doors .= 'Ptas';
                    }

                    //tractions
                    if ($model->id_traction>0){
                        $traction = @DB::table('tractions')->where('id', $model->id_traction)->first()->name;
                    }
                    $model->traction   = empty($traction)?'-':$traction;

                    //engine
                    $engine = @\App\Models\Products\Engine::query()->where('id', $model->id_engine)->first()->cylinder->name;
                    $model->engine = empty($engine)?'-':$engine;

                    $fuel   = @\App\Models\Products\Engine::query()->where('id', $model->id_engine)->first()->fuel->name;
                    $model->fuel   = empty($fuel)?'-':$fuel;


                    $brand = @\App\Models\Products\Modelo::query()->where('id', $model->id_model)->first()->brand->name;
                    $modelo = @\App\Models\Products\Modelo::query()->where('id', $model->id_model)->first()->name;
                    //$model->text = $brand.' / '.$modelo.' / '.$model->version.'/'.$model->doors.'/'.$model->engine.'/'.$model->fuel.'/'.$model->year;
                    $model->text =  $brand.' / '.$modelo.' / '.$model->version;

                    $markup = "<div class='car-result clearfix'>" .
                        "<div class='car-result__avatar' style='width: 60px'><img src='" . "https://cdn0.iconfinder.com/data/icons/vehicle-1/48/8-48.png" . "' /></div>" .
                        "<div class='car-result__meta'>".
                        "<div class='car-result__description' style='color: #333 !important;'>" . $model->text . "</div>";

                    $markup .= "<div class='car-result__statistics' style='font-size: 10px;' >" .
                        "<div class='car-result__icon'><i class='fa fa-clock-o'></i> " . $model->year . " </div>" .
                        "<div class='car-result__icon'><i class='fa seller-car-door'></i> " . $model->doors . " </div>" .
                        "<div class='car-result__icon'><i class='fa seller-chassis'></i> " . $model->traction . " </div>" .
                        "<div class='car-result__icon'><i class='fa seller-piston'></i> " . $model->engine . " </div>" .
                        "<div class='car-result__icon'><i class='fa seller-fuel'></i> " . $model->fuel . " </div>" .
                        "</div>" .
                        "</div></div>";

                    return $markup;
                })
                ->editColumn('color', function ($dato) {
                    $color = @DB::table('paints')->where('id', @$dato->id_paint)->first()->name;
                    return (!empty($color)) ? @ucwords(strtolower($color)) : '';
                })
                ->editColumn('use', function ($dato) {
                    return @DB::table('car_uses')->where('id', @$dato->id_car_use)->first()->name;
                })
                ->editColumn('price', function ($dato) use ($request) {
                    //moneda & precio
                    $precio = $dato->code_currency . ' ' . @number_format($dato->price, 0, ",", ".");
                    $precio = '<span class="badge bg-gray hint--top" aria-label="Precio de Lista"> '.$precio.' </span>';

                    $details = "";
                    if ( $request->has('type_sale') && $request->type_sale==2 ){
                        $details = '  <br />
                            <a href="#"   
                                data-id_prod="'.@$dato->id_product.'" 
                                data-id_plan="'.@$dato->data_id_plan.'" 
                                data-plan="'.@$dato->data_plan.'" 
                                class="btn btn-xs btn-default plan_detail hint--top"  
                                 style="margin-bottom: 5px"
                                aria-label="Ver detalles del Plan : '.@$dato->data_plan.'">
                                <i class="fa fa-search-plus fa-lg text-yellow"></i> Ver Cuotas
                            </a> 
                         ';
                    }

                    return  $details . ' ' . $precio;
                })
                ->editColumn('discounts', function ($dato) {
                    return $dato->descuentos;   //'Descuento $1.234 Promocion $12';
                })
                ->editColumn('subtotal', function ($dato) {
                    return $dato->code_currency . ' ' . @number_format($dato->subtotal, 0, ",", ".");
                })
                ->rawColumns(['product', 'color', 'price', 'discounts', 'action'])
                ->with([
                    'type_sale' => $data[$id_budget]['type_sale'],
                    'type_payment' => $data[$id_budget]['type_payment'],
                    'type_patenting' => $data[$id_budget]['type_patenting'],
                    'type_expectation' => $data[$id_budget]['type_expectation'],
                    'accessories' => $data[$id_budget]['accessories']
                ])
                ->make(true);
        }
    }




    /**
     *  muestra la sesion
     *
     * @param   json  id_detail
     * @return  json data
     * @author  Carlos Villarroel  -  cevv07@gmail.com
     * */
    public function index()
    {
        $data    = Session::get($this->budget_key);

        //$data = new Collection($data);

        dd( $data,  Session::all() );
    }




    /**
     * Agrega productos a un presupuesto especifico
     *
     * @param  \App\Http\Requests\BudgetDetailRequest  $request
     * @return \Illuminate\Http\Response
     * @author  Carlos Villarroel  -  cevv07@gmail.com
     */
    public function add_product(BudgetDetailRequest $request)
    {
        $id_budget         = isset($request->preload_id_budget)?$request->preload_id_budget:'nuevo';
        $id_type_sale      = $request->preload_id_type_sale;
        $id_type_payment   = $request->preload_id_type_payment;
        $id_type_patenting = $request->preload_id_type_patenting;
        $id_expectation    = $request->preload_id_expectation;

        $id_product   = $request->preload_id_product;
        $id_car_use   = $request->preload_id_car_use;
        $id_paint     = $request->preload_id_paint;

        $key            = $id_product . "-" . $id_paint;
        $budget_details = Session::get($this->budget_key);

        if( isset($budget_details[$id_budget]['products'][$key]) ){
            $product = $budget_details[$id_budget]['products'][$key];
            $product->quantity += 1;
            $product->subtotal = $product->quantity * $product->price;

            // variables de moneda
            $product->id_currency    = $request->preload_id_currency;
            $product->code_currency  = @DB::table('currency')->where('id', $product->id_currency)->get()->last()->code;  //symbol

        }else{
            $product = new \stdClass();

            $product->id          = $key;
            $product->id_product  = $id_product;
            $product->id_car_use  = $id_car_use;
            $product->id_paint    = $id_paint;
            $product->price       = $request->preload_product_price;
            $product->quantity    = 1;
            $product->discount    = isset($request->preload_discount) ? $request->preload_discount : 0;
            $product->promotion   = isset($request->preload_promotion) ? $request->preload_promotion : 0;

            $product->subtotal    = ($product->quantity * $product->price) - $product->promotion - $product->discount;

            // variables de moneda
            $product->id_currency    = $request->preload_id_currency;
            $product->code_currency  = @DB::table('currency')->where('id', $product->id_currency)->get()->last()->code;  //symbol


            //otros datos
            $product->data_id_sale    = $id_type_sale;
            $product->data_id_payment = $id_type_payment;
            $product->data_id_plan    = $request->preload_id_type_plan ?:0;
            $product->data_plan       = $request->preload_plan_text ?:"";  //@DB::table('type_plans')->where('id', '=', $product->data_id_plan)->first()->name?:'';




            // Metadata
            $type_stock      = empty($request->preload_stock_type)         ? 0 : $request->preload_stock_type;          //  0: virtual  1: fisico
            $stock_virtual   = empty($request->preload_virtual_id)         ? 0 : $request->preload_virtual_id;          //  id de tabla virtual_stocks
            $color_count     = empty($request->preload_color_count)        ? 0 : $request->preload_color_count;         //  cantidad de productos  en stock fisico para el color seleccionado
            $stock_physical  = empty($request->preload_id_stock_physical)  ? 0 : $request->preload_id_stock_physical;   //  id de tabla stocks

            $selection = "generic";
            $status    = "A venir";
            if( $type_stock==1  && $color_count>0 ) {

                $selection  = "physical";
                $status     = "En stock";
            }
            if( $type_stock==0  && $stock_virtual>0 ) {

                $selection  = "virtual";
                //$status   = "En virtual";

                $data = @DB::table('virtual_stocks')
                    ->leftJoin('virtual_stock_process_status', 'virtual_stocks.status', '=', 'virtual_stock_process_status.code')
                    ->leftJoin('virtual_stock_process', 'virtual_stock_process_status.id_virtual_stock_process', '=', 'virtual_stock_process.id')

                    ->select(['virtual_stock_process_status.name as status', 'virtual_stocks.delivery',  'virtual_stock_process_status.detail as detalles', 'virtual_stock_process.name as process', 'virtual_stock_process.detail as css'])
                    ->where([['virtual_stocks.id', '=', $stock_virtual]])
                    ->orderby('virtual_stock_process_status.code', 'desc')
                    ->get()->last();
                    
                $status = @$data->status; 
            }

            $product->metadata   =  [
                'selection'      => $selection,         // generic, physical, virtual
                'stock_physical' => $stock_physical,    // id de stock fisico
                'stock_virtual'  => $stock_virtual,     // id de stock virtual
                'status'         => $status             // status de producto: en stock, a venir,  status segun 116
            ];




            // variables de texto para mostrar
            $product->producto      = $request->preload_product_selected;
            $product->color         = $request->preload_paint_selected;
            //$product->descuentos  = "Descuento $$product->discount <br> Promocion $$product->promotion";
            $product->descuentos    = "Descuento $product->code_currency $product->discount <br> Promocion $product->code_currency $product->promotion";


            // estructura para gastos y pagos
            $product->payments   = [
                'efectivo' => [
                    'sign' => 0,
                    'cash' => 0
                ],
                'credito' => [
                    'credit_bank' => 0,
                    'credit_capital' => 0,
                    'credit_interest' => 0,
                    'credit_cuotas_num' => 0,
                    'credit_cuotas_val' => 0,
                    'credit_total' => 0
                ],
                'cheques' => [
                    'check_bank' => 0,
                    'check_amount' => 0,
                    'check_observation' => ''
                ],
                'documentos' => [
                    'docs_quantity' => 0,
                    'docs_value' => 0,
                    'docs_total' => 0
                ],
                'usado' => [
                    'used_brand' =>'',
                    'used_model' =>'',
                    'used_version' =>'',
                    'used_year' => '',
                    'used_kilometers' => 0,
                    'used_valortoma' => 0

                ],

                'plans_credit_card' => [
                    'cc_bank'   => '',
                    'cc_number' => 0,
                    'cc_amount' => 0
                ],
                'plans_debit_card' =>[
                    'dc_bank'   => '',
                    'dc_number' => 0,
                    'dc_amount' => 0
                ],
                'plans_cash' => [
                    'p_sign' => 0,
                    'p_cash' => 0
                ]
            ];

            $product->expenses   = [
                'freight_forms' => 0,
                'patent' => 0,
                'credit' => 0,
                'inscription' => 0,
                'other' => 0
            ];
        }

        $budget_details[$id_budget]['products'][$product->id] = $product;
        $budget_details[$id_budget]['type_sale']              = $id_type_sale;
        $budget_details[$id_budget]['type_payment']           = $id_type_payment;
        $budget_details[$id_budget]['type_patenting']         = $id_type_patenting;
        $budget_details[$id_budget]['type_expectation']       = $id_expectation;


        Session::put($this->budget_key, $budget_details);

        return response()->json([
            'status' => true,
            'controller'  => 'presupuesto_detalles',
            'title'  => 'Operación Exitosa!',
            'text'  => 'Producto ha sido agregado satisfactoriamente.',
            'insert_id' => $product->id,
            'type' => 'success'
        ],200);
    }



    /**
     * resetear productos por cambio de tipo de venta.
     *
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     * @author  Carlos Villarroel  -  cevv07@gmail.com
     */
    public function reset_product(Request $request)
    {
        $id_budget = $request->has('budget_id_master')
            ? $request->budget_id_master
            : 'nuevo';

        $data = Session::get($this->budget_key);

        $empty = !count($data[$id_budget]['products']) ? : false;

        //eliminar
        /*
        foreach ($data[$id_budget]['products'] as $key => $value){
            unset($data[$id_budget]['products'][$key]);
        }
        */

        if (! $empty){
            //unset($data[$id_budget]['products']);
            $data[$id_budget]['products']  = [];
            $data[$id_budget]['type_sale'] = $request->type_sale;

            Session::put($this->budget_key, $data);
            //Session::save();
        }

        return response()->json([
            'status' => true,
            'data'   => $data,
            'title'  => 'Atención!',
            'text'   => 'Los productos han sido reseteados del listado...',
            'type'   => 'warning',
            'empty'  => $empty,
            'id_budget' => $id_budget
        ],200);

    }



    /**
     *  Agrega prueba de manejo en un presupuesto especifico
     *
     * @param   \App\Http\Requests\DrivingTestRequest
     * @return  \Illuminate\Http\Response
     * @author  Carlos Villarroel  -  cevv07@gmail.com
     * */
    public function add_driving_test(DrivingTestRequest $request)
    {
        $id_budget = isset($request->test_drive_id_budget)?$request->test_drive_id_budget:'nuevo';

        $id_product   = $request->test_drive_stock_id_product;
        
        //$id_product   = $request->test_drive_id_version;
        $date         = $request->test_drive_fecha;
        $id_model     = $request->test_drive_id_model;
        $id           = $request->test_drive_id;


        $budget_manejo = Session::get($this->budget_key);

        if( isset($budget_manejo[$id_budget]['driving_test'][$id_product]) ){
            $driving = $budget_manejo[$id_budget]['driving_test'][$id_product];
            $driving->date = $date;
            $driving->id_model    = $id_model;

        }else{
            $driving = new \stdClass();
            $driving->id          = $id;
            $driving->id_product  = $id_product;
            $driving->date        = $date;
            $driving->id_model    = $id_model;
        }

        $budget_manejo[$id_budget]['driving_test'][$driving->id_product] = $driving;
        Session::put($this->budget_key, $budget_manejo);


        return response()->json([
            'status' => true,
            'controller'  => 'driving_test',
            'title'  => 'Operación Exitosa!',
            'text'  => 'Prueba de manejo creada satisfactoriamente.',
            //'text' => 'Los datos de <b>'.$product->name.'</b> ha sido registrado satisfactoriamente.',
            'insert_id' => $driving->id,
            'type' => 'success'
        ],200);
    }


    /**
     *  Agregar cambio de Modelo para venta de planes
     *
     * @param   \App\Http\Requests\WantProductRequest
     * @return  \Illuminate\Http\Response
     * @author  Carlos Villarroel  -  cevv07@gmail.com
     * */
    public function add_want_product(WantProductRequest $request)
    {
        $id_budget = isset($request->prod_change_id_budget)?$request->prod_change_id_budget:'nuevo';

        $id_product   = $request->prod_change_id_version;
        $id_model     = $request->prod_change_id_model;
        $key          = $request->prod_change_key;


        $data = Session::get($this->budget_key);


        $result = " no ";
        if( isset($data[$id_budget]['products'][$key]) ){
            $product = $data[$id_budget]['products'][$key];
            //$product->id_type_plan   = $id_product;
            $product->id_product_want  = $id_product;
            $data[$id_budget]['products'][$key] = $product;

            Session::put($this->budget_key, $data);
            $result = "";
        }


        return response()->json([
            'status' => true,
            'controller'  => 'want_product',
            'title'  => 'Operación Exitosa!',
            'text'  => "Producto $result se ha asociado satisfactoriamente.",
            'type' => 'success'
        ],200);
    }


    /**
     * Retorna los detalles de un plan especifico para ventas a credito: plan ahorro.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id_plan : no se usa
     * @return \Illuminate\Http\Response
     * @author  Carlos Villarroel  -  cevv07@gmail.com
     */
    public function get_plan_details(Request $request, $id_planX=0)
    {
        if ($request->ajax() || $request->wantsJson()) {

            $id_plan    = $request->id_plan;
            $plan       = $request->plan;
            $id_product = $request->id_prod;

            $view = view('admin.budgets.products.plan_details')->with(compact('id_plan', 'plan', 'id_product'))->renderSections();
            return response()->json([
                'content'    => $view['plan_details'],
                'plan_title' => $plan
            ], 200);
        }
    }


    /**
     *  retorna los detalles de un presupuesto especifico
     *
     * @param   \Illuminate\Http\Request
     * @return  \Illuminate\Http\Response
     * @author  Carlos Villarroel  -  cevv07@gmail.com
     * */
    public function show($id)
    {
        return response()->json([
            'status' => true,
            'data' => Session::get($this->budget_key)
        ],200);
    }


    /**
     * Elimina un producto especifico de los detalles de un presupuesto.
     *
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     * @author  Carlos Villarroel  -  cevv07@gmail.com
     */
    public function destroy(Request $request, $key_product)
    {
        $id_budget = isset($request->budget_id_master)?$request->budget_id_master:'nuevo';

        $budget_details = Session::get($this->budget_key);
        unset($budget_details[$id_budget]['products'][$key_product]);
        Session::put($this->budget_key, $budget_details);

        return response()->json([
            'status' => true,
            'data' => Session::get($this->budget_key),
            'title'  => 'Operación Exitosa!',
            'text' => 'el producto ha sido eliminado del listado satisfactoriamente.',
            'type' => 'success'
        ],200);
    }











    /*
    |--------------------------------------------------------------------------
    | Section: Accessory
    |--------------------------------------------------------------------------
    |
    | Los metodos a continuacion estan estrictamente relacionados a la gestion de:  Accessorios
    |
    | @author  Carlos Villarroel  -  cevv07@gmail.com
    */

    /**
     *  Agrega accesorio a el presupuesto
     *
     * @param   \App\Http\Requests\DrivingTestRequest
     * @return  \Illuminate\Http\Response
     * @author  Carlos Villarroel  -  cevv07@gmail.com
     * */
    public function accessory_add(DefaultRequest $request)
    {
        $id_budget         = isset($request->accessory_id_budget)?$request->accessory_id_budget:'nuevo';


        $key          = $request->acce_name;

        $price        = $request->acce_price;
        $discount     = isset($request->acce_discount) ? $request->acce_discount : 0;


        $accessories  = Session::get($this->budget_key);

        if( isset($accessories[$id_budget]['accessories'][$key]) ){
            $accessory = $accessories[$id_budget]['accessories'][$key];
            $accessory->quantity += 1;
            $accessory->subtotal = $accessory->quantity * $accessory->price;

        }else{
            $accessory = new \stdClass();

            $accessory->id          = $key;
            $accessory->name        = $request->acce_name;
            $accessory->price       = str_replace('.', '', $price);
            $accessory->quantity    = 1;
            $accessory->discount    = str_replace('.', '', $discount);

            $accessory->subtotal    = ($accessory->quantity * $accessory->price) - $accessory->discount;
        }

        $accessories[$id_budget]['accessories'][$accessory->id] = $accessory;
        //$accessories[$id_budget]['accessories'] = [];

        Session::put($this->budget_key, $accessories);

        return response()->json([
            'status' => true,
            'controller'  => 'accessories',
            'title'  => 'Operación Exitosa!',
            'text'  => 'Accessorio ha sido agregado satisfactoriamente.',
            'insert_id' => $accessory->id,
            'type' => 'success'
        ],200);
    }



    /**
     * Elimina accesorio del presupuesto.
     *
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     * @author  Carlos Villarroel  -  cevv07@gmail.com
     */
    public function accessory_delete(Request $request)
    {
        $id_budget = isset($request->budget_id_master)?$request->budget_id_master:'nuevo';

        $key = $request->key;

        $budget_details = Session::get($this->budget_key);
        unset($budget_details[$id_budget]['accessories'][$key]);
        Session::put($this->budget_key, $budget_details);

        return response()->json([
            'status' => true,
            //'data' => Session::get($this->budget_key),
            'title'  => 'Operación Exitosa!',
            'text' => 'el accessorio ha sido eliminado del listado satisfactoriamente.',
            'type' => 'success'
        ],200);
    }











    /*
    |--------------------------------------------------------------------------
    | Section: Budgets
    |--------------------------------------------------------------------------
    |
    | Los metodos a continuacion son para el manejo de sesion y apertura
    | de un presupuesto para su edición.
    |
    */

    /**
     * Agrega todos los detalles (cliente, productos, gatos, pagos y actividades) a un presupuesto especifico
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     * @author  Carlos Villarroel  -  Héctor Agüero
     * */
    public function store(Request $request){
        //BudgetDetailRequest
        $id_budget = $request->has('budget')
            ? $request->budget
            : 'nuevo';


        if ( $request->has('budget') ){
            //
            $id_budget =  $request->budget;
            $model     =  Budget::findorfail($id_budget);

        }else{
            //
            $id_budget =  'nuevo';
            $model     =  new Budget();
        }

        $data = Session::get($this->budget_key);
        $details     = $data[$id_budget];


        //<  Validation
        $message = "";
        if (! $request->has('client') && $details['client'] ){
        //if (! $request->has('client') ){
            $message .= "No ha selecionado un cliente. <br>";
        }
        if (! $request->has('type_sale') ){
            $message .= "No ha selecionado un tipo de venta. <br/>";
        }
        if (! $request->has('type_payment') ){
            $message .= "No ha selecionado un tipo de pago. <br/>";
        }
        if (! $request->has('type_patenting') ){
            $message .= "No ha selecionado quien patenta. <br/>";
        }
        if (! $request->has('type_expectation') ){
            $message .= "No ha selecionado expectativa del cliente. <br/>";
        }
        if (! count($details['products']) ){
            $message .= "No ha selecionado un producto. <br/>";
        }
        if ( $details['task']['date'] < date("Y-m-d H:i:s") ) {
            $message .= "Fecha y hora de tarea de seguimiento deben ser mayores que fecha y hora actual. <br/>";
        }

        if (! empty($message)){
            return response()->json([
                'status'            => false,
                'controller'        => 'budget_storage',
                'title'             => 'Oops!',
                'text'              => 'Faltan datos por completar: <br>' . $message,
                'details_task_date' => $details['task']['date'],
                'type'              => 'warning'
            ],200);
        }
        //>  Validation




        DB::beginTransaction();
        try {

            $model = new Budget();
            $model->id_user             = Auth::user()->id;
            $model->id_client           = $request->client;
            $model->id_type_sale        = $request->type_sale;
            $model->id_type_payment     = $request->type_payment;
            $model->sent_hub            = 0;
            $model->reply_hub           = '';
            $model->id_type_delivery    = $request->type_expectation;
            $add                        = DB::table('type_deliveries')->where('id', $request->type_expectation)->first()->days;
            $model->date_delivery       = addDayBusiness(date('Y-m-d'), $add);
            //$model->observation       = $request->client;
            //$model->id_seguro         = $request->client;
            $model->id_company          = Auth::user()->id_empresa;

            //guardar  Budget
            $model->save();

            //Capture  id Budget
            $id_budget =  $model->id;

            $budget_total_no_acc = 0;
            
           
            //< Gastos y Pagos
            foreach ( $details['products'] as $key => $value ){

                //Budget Details
                $detail    = new Budget_detail();
                $detail->id_budget          = $id_budget;
                $detail->id_product         = $value->id_product;
                $detail->id_paint           = $value->id_paint;
                $detail->id_car_use         = $value->id_car_use;
                $detail->quantity           = $value->quantity;
                $detail->price              = $value->price;
                $detail->promotion          = $value->promotion;
                $detail->discount_price     = $value->discount;
                $detail->subtotal           = $value->subtotal;

                $detail->id_type_plan       = $value->data_id_plan;
                $detail->id_product_want    = isset($value->id_product_want)? $value->id_product_want : 0;
                $detail->observation        = null;
                //$detail->gastos_inscription = null;
                $detail->patenting          = $request->type_patenting;
                //$detail->discount_max     = null;
                //$detail->discount_admin   = null;
                //$detail->price_factory    = null;
                //$detail->printed_at       = null;
                $detail->id_currency        = $value->id_currency;

                // metadata
                $detail->data               = $value->metadata;   // [ 'selection' => value,  'stock_physical' => value, 'stock_virtual' => value, 'status' => value  ];
                
                $detail->save();
                

                //Capture  id Budget_details
                $id_budget_detail =  $detail->id;


                
                $detail->expenses_subtotal = 0;
                $detail->payments_subtotal = 0;
                
                //Budget Expenses
                if( $model->id_type_sale != 2 ) {
                    
                    $expense = new Budget_expense();
                    $expense->id_budget         = $id_budget;
                    $expense->id_budget_detail  = $id_budget_detail;
                    $expense->freight           = $value->expenses->freight_forms;
                    $expense->patent            = $value->expenses->patent;
                    $expense->credit            = $value->expenses->credit;
                    $expense->inscription       = $value->expenses->inscription;
                    $expense->other             = $value->expenses->other;
                    $expense->save();
                    
                    $detail->expenses_subtotal += ( $expense->freight + $expense->patent + $expense->credit + $expense->inscription + $expense->other );
                    
                    //Budget Cash
                    if( $model->id_type_payment != 4 ){
                        
                        $pay_cash = new Budget_cash();
                        $pay_cash->id_budget         = $id_budget;
                        $pay_cash->id_budget_detail  = $id_budget_detail;
                        $pay_cash->sign              = $value->payments['efectivo']->sign;
                        $pay_cash->cash              = $value->payments['efectivo']->cash;
                        $pay_cash->save();
                        
                        $detail->payments_subtotal += ( $pay_cash->sign + $pay_cash->cash );
                        
                    }

                    //Budget Credit
                    if( $model->id_type_payment == 2  or $model->id_type_payment == 4 or $model->id_type_payment == 5){
                        
                        //Bank Credit
                        if( $value->payments['credito']->credit_total > 0 ){
                            
                            $pay_credit = new Budget_credit();
                            $pay_credit->id_budget         = $id_budget;
                            $pay_credit->id_budget_detail  = $id_budget_detail;
                            $pay_credit->id_bank           = $value->payments['credito']->credit_bank;
                            $pay_credit->cuotas            = $value->payments['credito']->credit_cuotas_num;
                            $pay_credit->interest          = $value->payments['credito']->credit_interest;
                            $pay_credit->cuotasval         = $value->payments['credito']->credit_cuotas_val;
                            $pay_credit->capital           = $value->payments['credito']->credit_capital;
                            $pay_credit->total             = $value->payments['credito']->credit_total;
                            $pay_credit->save();
                            
                            $detail->payments_subtotal += $pay_credit->capital;
                            
                        }

                        //Budget Check
                        if ( $value->payments['cheques']->check_amount > 0 ){
                            
                            $pay_check = new Budget_check();
                            $pay_check->id_budget         = $id_budget;
                            $pay_check->id_budget_detail  = $id_budget_detail;
                            $pay_check->id_bank           = $value->payments['cheques']->check_bank;
                            $pay_check->amount            = $value->payments['cheques']->check_amount;
                            $pay_check->observation       = $value->payments['cheques']->check_observation;
                            $pay_check->save();
                            
                            $detail->payments_subtotal += $pay_check->amount;
                            
                        }

                        //Budget Documents
                        if ( $value->payments['documentos']->docs_total >0 ){
                            
                            $pay_docs = new Budget_document();
                            $pay_docs->id_budget         = $id_budget;
                            $pay_docs->id_budget_detail  = $id_budget_detail;
                            $pay_docs->quantity          = $value->payments['documentos']->docs_quantity;
                            $pay_docs->value             = $value->payments['documentos']->docs_value;
                            $pay_docs->total             = $value->payments['documentos']->docs_total;
                            $pay_docs->save();
                            
                            $detail->payments_subtotal += $pay_docs->total;
                            
                        }
                    }

                    //Budget Used
                    if( $model->id_type_payment == 3  or $model->id_type_payment == 4 or $model->id_type_payment == 5){
                        
                        $pay_used = new Budget_used();
                        $pay_used->id_budget         = $id_budget;
                        $pay_used->id_budget_detail  = $id_budget_detail;
                        $pay_used->brand             = $value->payments['usado']->used_brand;
                        $pay_used->model             = $value->payments['usado']->used_model;
                        $pay_used->version           = $value->payments['usado']->used_version;
                        $pay_used->year              = $value->payments['usado']->used_year;
                        $pay_used->kilometers        = $value->payments['usado']->used_kilometers;
                        $pay_used->take_value        = $value->payments['usado']->used_valortoma;
                        $pay_used->save();
                        
                        $detail->payments_subtotal += $pay_used->take_value;
                    }

                } else {
                    //Budget Plan Payment
                    $pay_plan = new Plan_payment();
                    $pay_plan->id_budget          = $id_budget;
                    $pay_plan->id_budget_detail   = $id_budget_detail;
                    $pay_plan->cc_bank            = $value->payments['plans_credit_card']->cc_bank ?:0;
                    $pay_plan->cc_number          = $value->payments['plans_credit_card']->cc_number;
                    $pay_plan->cc_amount          = $value->payments['plans_credit_card']->cc_amount;
                    $pay_plan->dc_bank            = $value->payments['plans_debit_card']->dc_bank ?:0;
                    $pay_plan->dc_number          = $value->payments['plans_debit_card']->dc_number;
                    $pay_plan->dc_amount          = $value->payments['plans_debit_card']->dc_amount;
                    $pay_plan->p_sign             = $value->payments['plans_cash']->p_sign;
                    $pay_plan->p_cash             = $value->payments['plans_cash']->p_cash;
                    $pay_plan->save();
                    
                    $detail->payments_subtotal += ( $pay_plan->cc_amount + $pay_plan->dc_amount + $pay_plan->p_sign + $pay_plan->p_cash );
                }
                
                $detail->total_no_acc = $detail->quantity * ( $detail->price - $detail->discount_price - $detail->promotion + $detail->expenses_subtotal) - $detail->payments_subtotal;
                
                $budget_total_no_acc  += $detail->total_no_acc;
                
            }
            //> Gastos y Pagos


            //< pruebas de manejo
            foreach ($details['driving_test'] as $key => $value){

                $driving = new Driving_test();
                $driving->id_budget   = $id_budget;
                $driving->id_product  = $value->id_product;
                $driving->date        = $value->date;
                $driving->id_model    = $value->id_model;
                $driving->save();
            }
            //> pruebas de manejo


            //< accesorios
            $accesories_total = 0;
            foreach ($details['accessories'] as $key => $value){

                $accessory = new Budget_accessory();
                $accessory->id_budget   = $id_budget;
                $accessory->id_product  = 0;                //$value->id_product;
                $accessory->accessory   = $value->name;
                $accessory->quantity    = $value->quantity;
                $accessory->price       = $value->price;
                $accessory->discount    = $value->discount;
                $accessory->subtotal    = $value->subtotal;
                $accessory->save();
                
                $accesories_total += ( $accessory->quantity * $accessory->price ) - $accessory->discount;
                
            }
            
            //> accesorios


            //< Etapas
            foreach ($details['substages'] as $value){

                $substage = new Budget_substage();
                $substage->id_budget    = $id_budget;
                $substage->id_substage  = $value;
                $substage->save();
            }
            //> Etapas


            //< Acuerdos
            foreach ($details['agreements'] as $value){

                $agreement = new Budget_agreement();
                $agreement->id_budget    = $id_budget;
                $agreement->id_agreement = $value;
                $agreement->save();
            }
            //> Acuerdos


            //< Seguimientos
            $id_user    = Auth::user()->id;
            $id_empresa = Auth::user()->id_empresa;  //User::query()->where('id', $id_user)->first()->id_empresa;

            $id_result = $details['task']['id_result'];

            if ( !DB::table('tasks' )->where( 'id_budget', $id_budget )->exists() ){
                
                //NEW TASK
                $Task_model = new Task();
                $Task_model->id_user      = $id_user;
                $Task_model->id_empresa   = $id_empresa;
                $Task_model->id_employee  = $id_user;
                $Task_model->date         = date_create_from_format('Y-m-d H:i:s', $details['task']['date']);
                $Task_model->manual_entry = 0;
                $Task_model->is_closed    = 0;

                $Task_model->id_client      = DB::table('budgets')->where('id', $id_budget)->first()->id_client;
                $Task_model->id_product     = DB::table('budget_details')->where('id_budget', $id_budget)->where('id', $id_budget_detail)->first()->id_product;
                $Task_model->id_budget      = $id_budget;
                $Task_model->id_event       = $details['task']['id_event'];
                $Task_model->id_task_reason = $details['task']['id_reason'];
                $Task_model->id_process     = $details['task']['id_process'];
                $Task_model->description    = $details['task']['id_process']==1? 'Tarea de presupuesto' :'Tarea de venta';
                $Task_model->save();

                if( !is_null( $details['task']['comment'] ) ){
                    
                    //There is a new comment
                    
                    $Comment_model = new Comments();
                    
                    $Comment_model->comment     = $details['task']['comment'];
                    $Comment_model->id_users    = $id_user;
                    $Comment_model->id_module   = null;
                    $Comment_model->id_document = $id_budget;
                    
                    $Comment_model->save();
                }
                

            } else {
                //THERE IS ALREADY A TASK
                $id_task = $details['task']['id_task'];
                $Task_model = Task::findorfail($id_task);

                if ( $id_result == '' or $id_result== null ){
                    //There is task but no result. Update the task

                    $Task_model->id_event       = $details['task']['id_event'];
                    $Task_model->id_task_reason = $details['task']['id_reason'];
                    $Task_model->save();

                    if( !is_null( $details['task']['comment'] ) ){
                    
                        //There is a new comment
                    
                        $Comment_model = new Comments();
                    
                        $Comment_model->comment     = $details['task']['comment'];
                        $Comment_model->id_users    = $id_user;
                        $Comment_model->id_module   = null;
                        $Comment_model->id_document = $id_budget;
                    
                        $Comment_model->save();
                    }

                } else {
                    //Exits result. Close the task

                    $dateNow = (new DateTime())->format('Y-m-d h:i:s');
                    $Task_model->close_date     = $dateNow;
                    $Task_model->is_closed      = 1;
                    $Task_model->id_task_result = $details['task']['id_result'];
                    $Task_model->save();

                    if( $id_result != 21) {
                        $Task_model = new Task();

                        $Task_model->id_user        = $id_user;
                        $Task_model->id_empresa     = $id_empresa;
                        $Task_model->id_employee    = $id_user;
                        $Task_model->date           = date_create_from_format('Y-m-d H:i:s', $details['task']['date']);
                        $Task_model->manual_entry   = 0;
                        $Task_model->is_closed      = 0;

                        $Task_model->id_client      = DB::table('budgets')->where('id_budget', $id_budget)->first()->id_client;
                        $Task_model->id_product     = DB::table('budget_details')->where('id_budget', $id_budget)->where('id', $id_budget_detail)->first()->id_product;
                        $Task_model->id_budget      = $id_budget;
                        $Task_model->id_event       = $details['task']['id_event'];
                        $Task_model->id_task_reason = $details['task']['id_reason'];
                        $Task_model->id_process     = $details['task']['id_process'];
                        
                        $Task_model->description    = $details['task']['id_process']==1? 'Tarea de presupuesto' : 'Tarea de venta';

                        $Task_model->save();

                    } else {
                        //Non Purchase. Update to 'Non Purchase' and register the no buy reason 
                        $Budget_model                           = Budget::findorfail($id_budget);
                        $Budget_model->status                   = 0;    //Inactive
                        $Budget_model->id_non_purchase_reasons  = $details['task']['id_nobuy_reason'];
                        $Budget_model->save();
                    }
                }
            }
            //> Seguimientos




            //< Empatia cliente
            if( isset( $details['empathy_id'] ) ){

                //set empathy
                $id_empathy = DB::table('empathy_user_client')->insertGetId(
                    [
                        'id_empathy'   => $details['empathy_id'],
                        'id_user'      => \Auth::user()->id,
                        'id_client'    => $details['client'] ,
                        'id_process'   => $id_budget,
                        'reference'    => 0, // set budget
                        'comment'      =>'',

                        'created_at'   => date('Y-m-d H:i:s'),
                        'updated_at'   => date('Y-m-d H:i:s'),
                    ]
                );
            }
            //> Empatia cliente
            



            //Metadata  Budget
            //$model                =  Budget::findorfail( $id_budget );
            $model->data_total      = $budget_total_no_acc + $accesories_total;
            $model->data_stage      = $model->id_stage;
            $model->data_closing    = @$this->get_percentage ( $id_budget );
            $model->save();



            DB::commit();

            //Reset session
            //Session::put($this->budget_key, $this->structure_default);


            return response()->json([
                'status'     => true,
                'controller' => 'budget_storage',
                'title'      => 'Operación exitosa!',
                'text'       => 'Los datos del presupuesto se guardaron satisfactoriamente.',
                'type'       => 'success',
                'id_budget'  => $id_budget ?:0
            ],200);

        } catch (\Exception $e) {

            $error = $e->getMessage();
            DB::rollBack();

            return response()->json([
                'status'     => false,
                'controller' => 'budget_storage',
                'title'      => 'Oops!',
                'text'       => 'Ocurrio un error desconocido.',
                'type'       => 'error',
                'error'      => $error
            ],200);
        }
    }



    /**
     * Retorna los detalles de un Presupuesto especifico y agrega a la sesion los datos pertinentes
     *
     * @param  int  $id  id_budget
     * @return \Illuminate\Http\Response
     */
    public function get_budget_details(Request $request, $id_budget=0)
    {
        /** Check Permissions  **/
        if ( !Auth::user()->ability('root', 'presupuesto-edit') )  abort(403);

        $id_budget = $request->has('id_budget') ? $request->id_budget : $id_budget ;
        $budget = Budget::findorfail( $id_budget );



        /*
        |--------------------------------------------------------------------------
        | session management:  Start Structure
        |--------------------------------------------------------------------------
        */
        $data  = Session::get($this->budget_key);  // [];
        $data[$id_budget]['products']       = [];
        $data[$id_budget]['driving_test']   = [];
        $data[$id_budget]['accessories']    = [];    
        $data[$id_budget]['client']         = $budget->id_client;

        $data[$id_budget]['type_sale']        = $budget->id_type_sale;
        $data[$id_budget]['type_payment']     = $budget->id_type_payment;
        //$data[$id_budget]['type_patenting'] = $budget->id_type_patenting;     // colocado dentro del foreach de details
        $data[$id_budget]['type_expectation'] = $budget->id_type_delivery;

        $data[$id_budget]['substages']      = [];
        $data[$id_budget]['agreements']     = [];
        $data[$id_budget]['task']           = [];
        Session::put($this->budget_key, $data);

        //start sesion : use general
        $data = Session::get($this->budget_key);


        /*
        |--------------------------------------------------------------------------
        | session management:  Products
        |--------------------------------------------------------------------------
        */
        foreach ($budget->details as $detail){
            //begin add
            $product = new \stdClass();

            $product->id          = $detail->id_product . "-" . $detail->id_paint;  // key
            $product->id_product  = $detail->id_product;
            $product->id_car_use  = $detail->id_car_use;
            $product->id_paint    = $detail->id_paint;
            $product->price       = $detail->price;
            $product->quantity    = $detail->quantity;
            $product->discount    = $detail->discount_price;
            $product->promotion   = $detail->promotion;

            $product->subtotal    = $detail->subtotal;// ($product->quantity * $product->price) - $product->promotion - $product->discount;


            // variables de moneda
            $product->id_currency    = $detail->id_currency;
            $product->code_currency  = @DB::table('currency')->where('id', $product->id_currency)->get()->last()->code;  //symbol


            // metadata
            $product->metadata   = $detail->data;


            //otros datos
            $product->data_id_sale    = $budget->id_type_sale;
            $product->data_id_payment = $budget->id_type_payment;
            $product->data_id_plan    = $detail->id_type_plan ?:0;
            $product->data_plan       = $detail->preload_plan_text ?:"";  //@DB::table('type_plans')->where('id', '=', $product->data_id_plan)->first()->name?:'';

            $product->id_product_want = $budget->id_product_want;

            // variables de texto para mostrar
            $product->producto    = $request->preload_product_selected;
            $product->color       = $request->preload_paint_selected;
            $product->descuentos  = "Descuento $$product->discount <br> Promocion $$product->promotion";


            
            // estructura para gastos y pagos
            $product->payments   = [
                'efectivo' => [
                    'sign' => 0,
                    'cash' => 0
                ],
                'credito' => [
                    'credit_bank' => 0,
                    'credit_capital' => 0,
                    'credit_interest' => 0,
                    'credit_cuotas_num' => 0,
                    'credit_cuotas_val' => 0,
                    'credit_total' => 0
                ],
                'cheques' => [
                    'check_bank' => 0,
                    'check_amount' => 0,
                    'check_observation' => ''
                ],
                'documentos' => [
                    'docs_quantity' => 0,
                    'docs_value' => 0,
                    'docs_total' => 0
                ],
                'usado' => [
                    'used_brand' =>'',
                    'used_model' =>'',
                    'used_version' =>'',
                    'used_year' => '',
                    'used_kilometers' => 0,
                    'used_valortoma' => 0

                ],

                'plans_credit_card' => [
                    'cc_bank'   => '',
                    'cc_number' => 0,
                    'cc_amount' => 0
                ],
                'plans_debit_card' =>[
                    'dc_bank'   => '',
                    'dc_number' => 0,
                    'dc_amount' => 0
                ],
                'plans_cash' => [
                    'p_sign' => 0,
                    'p_cash' => 0
                ]
            ];

            $product->expenses   = [
                'freight_forms' => 0,
                'patent' => 0,
                'credit' => 0,
                'inscription' => 0,
                'other' => 0
            ];
            
            // Session structure extension when generating sale order:
            if( $request->budget_status == 2 ) {
                //Aditionals in credit:
                $product->payments['credito']['credit_status']          = '';
                $product->payments['credito']['credit_name']            = '';
                $product->payments['credito']['credit_cuota_type']      = '';
                
                //Aditionals in used:
                $product->payments['usado']['used_doors']               = '';
                $product->payments['usado']['used_motor']               = '';
                $product->payments['usado']['used_fuel']                = '';
                $product->payments['usado']['used_color']               = '';
                $product->payments['usado']['used_status']              = '';
            }
            
            
            //Here begins loading of expenses and payments from DB - Héctor Agüero
            
            if ( $product->data_id_sale == 2 ) {
                //Sale by plans (No expenses)
                
                //This will add the payments:
                if( $detail->budget_plan_payment()->exists() ){
                    $product->payments['plans_credit_card']['cc_bank']          = $detail->budget_plan_payment()->first()->cc_bank;
                    $product->payments['plans_credit_card']['cc_number']        = $detail->budget_plan_payment()->first()->cc_number;
                    $product->payments['plans_credit_card']['cc_amount']        = $detail->budget_plan_payment()->first()->cc_amount;
                
                    $product->payments['plans_credit_card']['dc_bank']          = $detail->budget_plan_payment()->first()->dc_bank;
                    $product->payments['plans_credit_card']['dc_number']        = $detail->budget_plan_payment()->first()->dc_number;
                    $product->payments['plans_credit_card']['dc_bankc_amount']  = $detail->budget_plan_payment()->first()->dc_amount;
                
                    $product->payments['plans_credit_card']['p_sign']           = $detail->budget_plan_payment()->first()->p_sign;
                    $product->payments['plans_credit_card']['p_cash']           = $detail->budget_plan_payment()->first()->p_cash;
                }
            } else {
                //Sale conventional
                
                //This will add the expenses:
                if( $detail->budget_expense()->exists() ){
                    $product->expenses['freight_forms'] = $detail->budget_expense()->first()->freight;
                    $product->expenses['patent']        = $detail->budget_expense()->first()->patent;
                    $product->expenses['credit']        = $detail->budget_expense()->first()->credit;
                    $product->expenses['inscription']   = $detail->budget_expense()->first()->inscription;
                    $product->expenses['other']         = $detail->budget_expense()->first()->other;
                }
                
                //This will add the payments:
                if( $detail->budget_cash()->exists() ){
                    $product->payments['efectivo']['sign'] = $detail->budget_cash()->first()->p_sign;
                    $product->payments['efectivo']['cash'] = $detail->budget_cash()->first()->p_cash;
                }
                
                if( $detail->budget_credit()->exists() ){
                    $product->payments['credito']['credit_bank']        = $detail->budget_credit()->first()->id_bank;
                    $product->payments['credito']['credit_capital']     = $detail->budget_credit()->first()->capital;
                    $product->payments['credito']['credit_interest']    = $detail->budget_credit()->first()->interest;
                    $product->payments['credito']['credit_cuotas_num']  = $detail->budget_credit()->first()->cuotas;
                    $product->payments['credito']['credit_cuotas_val']  = $detail->budget_credit()->first()->cuotasval;
                    $product->payments['credito']['credit_total']       = $detail->budget_credit()->first()->total;
                    
                    if( $request->budget_status == 2 ) {//Sale Order
                        //Aditionals in credit:
                        $product->payments['credito']['credit_status']          = $detail->budget_credit()->first()->id_credit_status;
                        $product->payments['credito']['credit_name']            = $detail->budget_credit()->first()->id_credit_name;
                        $product->payments['credito']['credit_cuota_type']      = $detail->budget_credit()->first()->cuota_type;
                    }    
                }
                
                if( $detail->budget_check()->exists() ){
                    $product->payments['cheques']['check_bank']         = $detail->budget_check()->first()->id_bank;
                    $product->payments['cheques']['check_amount']       = $detail->budget_check()->first()->amount;
                    $product->payments['cheques']['check_observation']  = $detail->budget_check()->first()->observation;
                }
                
                if( $detail->budget_document()->exists() ){
                    $product->payments['documentos']['docs_quantity']   = $detail->budget_document()->first()->quantity;
                    $product->payments['documentos']['docs_value']      = $detail->budget_document()->first()->value;
                    $product->payments['documentos']['docs_total']      = $detail->budget_document()->first()->total;
                }
                
                if( $detail->budget_used()->exists() ){
                    $product->payments['usado']['used_brand']       = $detail->budget_used()->first()->brand;
                    $product->payments['usado']['used_model']       = $detail->budget_used()->first()->model;
                    $product->payments['usado']['used_version']     = $detail->budget_used()->first()->version;
                    $product->payments['usado']['used_year']        = $detail->budget_used()->first()->year;
                    $product->payments['usado']['used_kilometers']  = $detail->budget_used()->first()->kilometers;
                    $product->payments['usado']['used_valortoma']   = $detail->budget_used()->first()->take_value;
                    
                    if( $request->budget_status == 2 ) {//Sale Order
                
                        //Aditionals in used:
                        $product->payments['usado']['used_doors']       = $detail->budget_used()->first()->doors;
                        $product->payments['usado']['used_motor']       = $detail->budget_used()->first()->id_cylinder;
                        $product->payments['usado']['used_fuel']        = $detail->budget_used()->first()->id_fuel;
                        $product->payments['usado']['used_color']       = $detail->budget_used()->first()->id_paint;
                        $product->payments['usado']['used_status']      = $detail->budget_used()->first()->id_general_status;
                    }
                }
            }
            
            
            
            //End of loading of expenses and payments from DB
            
            // fin add

            $data[$id_budget]['type_patenting']         = $detail->id_type_patenting;
 
            $data[$id_budget]['products'][$product->id] = $product;
            Session::put($this->budget_key, $data);
        }



        /*
        |--------------------------------------------------------------------------
        | session management:  Driving test
        |--------------------------------------------------------------------------
        */
        foreach ($budget->driving_tests as $detail){
            //begin add
            $driving = new \stdClass();
            $driving->id          = $detail->id_product;
            $driving->id_product  = $detail->id_product;
            $driving->date        = $detail->date;
            $driving->id_model    = $detail->id_model;
            // fin add

            $data[$id_budget]['driving_test'][$driving->id_product] = $driving;
            Session::put($this->budget_key, $data);
        }



        /*
        |--------------------------------------------------------------------------
        | session management:  Accessories
        |--------------------------------------------------------------------------
        */
        foreach ($budget->accessories as $detail){
            //begin add
            $accessory = new \stdClass();
            $accessory->id          = $detail->accessory;  // key
            $accessory->name        = $detail->accessory;
            $accessory->price       = str_replace('.', '', $detail->price );
            $accessory->quantity    = $detail->quantity;
            $accessory->discount    = str_replace('.', '', $detail->discount );
            $accessory->subtotal    = $detail->subtotal;
            // fin add

            $data[$id_budget]['accessories'][$accessory->id] = $accessory;
            Session::put($this->budget_key, $data);
        }



        /*
        |--------------------------------------------------------------------------
        | session management:  Substages
        |--------------------------------------------------------------------------
        */
        foreach ($budget->substages as $detail){

            $data[$id_budget]['substages'][] = $detail->id_substage;
            Session::put($this->budget_key, $data);
        }


        /*
        |--------------------------------------------------------------------------
        | session management:  Agreements
        |--------------------------------------------------------------------------
        */
        foreach ($budget->agreements as $detail){

            $data[$id_budget]['agreements'][] = $detail->id_agreement;
            Session::put($this->budget_key, $data);
        }


        /*
        |--------------------------------------------------------------------------
        | session management:  Task and comment
        |--------------------------------------------------------------------------
        */
        $task = $budget->tasks()->where('is_closed', 0)->orderBy('id', 'desc')->first();
        if ( !empty($task)){
            //Put task in the session structure
            $data[$id_budget]['task']['id_budget']          = $budget->id;
            $data[$id_budget]['task']['id_task']            = $task->id;
            $data[$id_budget]['task']['date']               = $task->date;
            $data[$id_budget]['task']['id_event']           = $task->id_event;
            $data[$id_budget]['task']['event']              = DB::table('events')->where( 'id', $task->id_event)->first()->description;
            $data[$id_budget]['task']['id_reason']          = $task->id_task_reason;
            $data[$id_budget]['task']['reason']             = DB::table('task_reasons')->where( 'id', $task->id_task_reason)->first()->description;
            $data[$id_budget]['task']['id_process']         = $task->id_process;
            $data[$id_budget]['task']['id_task_result']     = null;
            $data[$id_budget]['task']['result']             = null;
            $data[$id_budget]['task']['id_nobuy_reason']    = null;
            $data[$id_budget]['task']['nobuy_reason']       = null;
        } else {
            $data[$id_budget]['task']['id_budget']          = $budget->id;
            $data[$id_budget]['task']['id_task']            = null;
            $data[$id_budget]['task']['date']               = '';
            $data[$id_budget]['task']['id_event']           = '';
            $data[$id_budget]['task']['event']              = '';
            $data[$id_budget]['task']['id_reason']          = '';
            $data[$id_budget]['task']['reason']             = '';
            
            if( $request->budget_status == 1 ) {
                $data[$id_budget]['task']['id_process']     = 1;
            } else {
                $data[$id_budget]['task']['id_process']     = 2;
            }
            
            $data[$id_budget]['task']['id_task_result']     = null;
            $data[$id_budget]['task']['result']             = null;
            $data[$id_budget]['task']['id_nobuy_reason']    = null;
            $data[$id_budget]['task']['nobuy_reason']       = null;
        }
        
        //Comment in the session structure only to later save new ones.
        $data[$id_budget]['task']['id_comment'] = '';
        $data[$id_budget]['task']['comment']    = '';
        
        //Past comments are loaded from the js function
        
        Session::put($this->budget_key, $data);
        
        //End of task loading.
        

        return response()->json([
            'status'      => true,
            //'controller'  => 'presupuestos',
            'budget'     =>  $budget,
            'client'     =>  $budget->client,
        ],200);
    }

    
    /**
     * Actualiza ('update') todos los detalles (cliente, productos, gatos, pagos y actividades) de un presupuesto especifico
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     * @author  Carlos Villarroel  -  Héctor Agüero
     * */
    public function update(Request $request){


        //Capture  id Budget
        $id_budget                  =  $request->budget;

        // data
        $data                       = Session::get($this->budget_key);
        $details                    = $data[$id_budget];
        
        //<  Validation
        $message = "";
        if (! $request->has('budget') ){
            $message .= "Presupuesto no identificado. <br/>";
        }
        if (! $request->has('client') && $details['client'] ){
            $message .= "No ha selecionado un cliente. <br>";
        }
        if (! $request->has('type_sale') ){
            $message .= "No ha selecionado un tipo de venta. <br/>";
        }
        if (! $request->has('type_payment') ){
            $message .= "No ha selecionado un tipo de pago. <br/>";
        }
        if (! $request->has('type_patenting') ){
            $message .= "No ha selecionado quien patenta. <br/>";
        }
        if (! $request->has('type_expectation') ){
            $message .= "No ha selecionado expectativa del cliente. <br/>";
        }
        if (! count($details['products']) ){
            $message .= "No ha selecionado un producto. <br/>";
        }
        if ( $details['task']['date'] < date("Y-m-d H:i:s") ) {
            $message .= "Fecha y hora de tarea de seguimiento deben ser mayores que fecha y hora actual. <br/>";
        }

        if ( ! empty($message) ){
            return response()->json([
                'status'            => false,
                'controller'        => 'budget_storage',
                'title'             => 'Oops!',
                'text'              => 'Faltan datos por completar: <br>' . $message,
                'details_task_date' => $details['task']['date'],
                'budget'            => $id_budget,
                'type'              => 'warning'
                
            ],200);
        }
        
        //>  Validation




        //<<  Validation of budget for sale.
        $message = $this->budget_to_sale_validation( $request, $details );

        if ( ! empty($message) ){
            return response()->json([
                'status'     => false,
                'controller' => 'budget_storage',
                'title'      => 'Oops!',
                'text'       => 'Faltan datos por completar la conversión a ventas: <br>' . $message,
                'type'       => 'warning',
                'path'       => 'conversion'
            ],200);
        }
        //>>  Validation of budget for sale.




        DB::beginTransaction();
        try {

            //Capture  id Budget
            $id_budget                  =  $request->budget;
            $model                      =  Budget::findorfail($id_budget);


            // update registers
            $model->id_user             = Auth::user()->id;
            $model->id_client           = $request->client;
            $model->id_type_sale        = $request->type_sale;
            $model->id_type_payment     = $request->type_payment;
            $model->sent_hub            = 0;
            $model->reply_hub           = '';
            $model->id_type_delivery    = $request->type_expectation;
            $add                        = DB::table('type_deliveries')->where('id', $request->type_expectation)->first()->days;
            $model->date_delivery       = addDayBusiness(date('Y-m-d'), $add);
            //$model->observation       = $request->client;
            //$model->id_seguro         = $request->client;
            $model->id_company          = Auth::user()->id_empresa;

            //guardar  Budget
            $model->save();
            
            


            $budget_total_no_acc = 0;


            //< Gastos y Pagos
            foreach ( $details['products'] as $key => $value ){

                //Budget Details
                if ( ! $model->details()->where( 'id_product', $value->id_product )->exists() ){
                    return response()->json([
                        'status'     => false,
                        'controller' => 'budget_update',
                        'title'      => 'Oops!',
                        'text'       => 'Producto: '.$value->id_product.' no tiene detalles ingresados.',
                        'type'       => 'warning'
                    ],200);
                }
                
                $detail  = $model->details()->where('id_product', $value->id_product)->first();
                
                $detail->id_budget          = $id_budget;
                $detail->id_product         = $value->id_product;
                $detail->id_paint           = $value->id_paint;
                $detail->id_car_use         = $value->id_car_use;
                $detail->quantity           = $value->quantity;
                $detail->price              = $value->price;
                $detail->promotion          = $value->promotion;
                $detail->discount_price     = $value->discount;
                $detail->subtotal           = $value->subtotal;

                $detail->id_type_plan       = $value->data_id_plan;
                $detail->id_product_want    = isset($value->id_product_want)? $value->id_product_want : 0;
                $detail->observation        = null;
                //$detail->gastos_inscription = null;
                $detail->patenting          = $request->type_patenting;
                //$detail->discount_max     = null;
                //$detail->discount_admin   = null;
                //$detail->price_factory    = null;
                //$detail->printed_at       = null;
                $detail->id_currency        = $value->id_currency;

                // metadata
                $detail->data               = $value->metadata;   // [ 'selection' => value,  'stock_physical' => value, 'stock_virtual' => value, 'status' => value  ];

                $detail->save();
                
                
                //Capture  id Budget_details
                //$id_budget_detail =  $detail->id;



                $detail->expenses_subtotal = 0;
                $detail->payments_subtotal = 0;

                //Budget Expenses
                if( $model->id_type_sale != 2 ) {
                    
                    //Conventional sale
                    
                    if ( $detail->budget_expense()->where('id_budget_detail', $detail->id)->exists() ) {
                        
                        $expense = $detail->budget_expense()->where('id_budget_detail', $detail->id)->first();
                    
                    } else {
                        
                        $expense = new Budget_expense();
                        
                    }

                    $expense->id_budget         = $id_budget;
                    $expense->id_budget_detail  = $detail->id;
                    $expense->freight           = $value->expenses['freight_forms'];
                    $expense->patent            = $value->expenses['patent'];
                    $expense->credit            = $value->expenses['credit'];
                    $expense->inscription       = $value->expenses['inscription'];
                    $expense->other             = $value->expenses['other'];

                    $expense->save();

                    $detail->expenses_subtotal += ( $expense->freight + $expense->patent + $expense->credit + $expense->inscription + $expense->other );

                    //Budget Cash
                    if( $model->id_type_payment != 4 ){
                        
                        // There is cash pay
                        
                        if ( $detail->budget_cash()->where('id_budget_detail', $detail->id)->exists() ){
                            
                            $pay_cash = $detail->budget_cash()->where('id_budget_detail', $detail->id)->first();
                            
                        } else {
                            
                            $pay_cash = new Budget_cash();
                            
                        }

                        $pay_cash->id_budget         = $id_budget;
                        $pay_cash->id_budget_detail  = $detail->id;
                        ///
                        return response()->json([
                'status'     => true,
                'controller' => 'budget_update',
                'title'      => 'Operación exitosa!',
                'text'       => $pay_cash,
                'type'       => 'success'
            ],200);
                        ///
                        $pay_cash->sign              = $value->payments['efectivo']->sign;
                        $pay_cash->cash              = $value->payments['efectivo']->cash;

                        $pay_cash->save();

                        $detail->payments_subtotal += ( $pay_cash->sign + $pay_cash->cash );

                    }

                    //Budget Credit
                    if( $model->id_type_payment == 2  or $model->id_type_payment == 4 or $model->id_type_payment == 5){

                        //Bank Credit
                        
                        if( $value->payments['credito']->credit_total > 0 ){
                            
                            if ( $detail->budget_credit()->where('id_budget_detail', $detail->id)->exists() ){
                                
                                $pay_credit = $detail->budget_credit()->where('id_budget_detail', $detail->id)->first();
                                
                            } else {
                                
                                $pay_credit = new Budget_credit();
                                
                            }

                            $pay_credit->id_budget         = $id_budget;
                            $pay_credit->id_budget_detail  = $detail->id;
                            $pay_credit->id_bank           = $value->payments['credito']->credit_bank;
                            $pay_credit->cuotas            = $value->payments['credito']->credit_cuotas_num;
                            $pay_credit->interest          = $value->payments['credito']->credit_interest;
                            $pay_credit->cuotasval         = $value->payments['credito']->credit_cuotas_val;
                            $pay_credit->capital           = $value->payments['credito']->credit_capital;
                            $pay_credit->total             = $value->payments['credito']->credit_total;

                            $pay_credit->save();

                            $detail->payments_subtotal += $pay_credit->capital;

                        }

                        //Budget Check
                        if ( $value->payments['cheques']->check_amount > 0 ){
                            
                            if ( $detail->budget_check()->where('id_budget_detail', $detail->id)->exists() ){
                                
                                $pay_check = $detail->budget_check()->where('id_budget_detail', $detail->id)->first();
                                
                            } else {
                                
                                $pay_check = new Budget_check();
                                
                            }

                            $pay_check->id_budget         = $id_budget;
                            $pay_check->id_budget_detail  = $detail->id;
                            $pay_check->id_bank           = $value->payments['cheques']->check_bank;
                            $pay_check->amount            = $value->payments['cheques']->check_amount;
                            $pay_check->observation       = $value->payments['cheques']->check_observation;

                            $pay_check->save();

                            $detail->payments_subtotal += $pay_check->amount;

                        }

                        //Budget Documents
                        if ( $value->payments['documentos']->docs_total >0 ){
                            
                            if ( $detail->budget_document()->where('id_budget_detail', $detail->id)->exists() ){
                                
                                $pay_docs = $detail->budget_document()->where('id_budget_detail', $detail->id)->first();
                                
                            } else {
                                
                                $pay_docs = new Budget_document();
                                
                            }

                            $pay_docs->id_budget         = $id_budget;
                            $pay_docs->id_budget_detail  = $detail->id;
                            $pay_docs->quantity          = $value->payments['documentos']->docs_quantity;
                            $pay_docs->value             = $value->payments['documentos']->docs_value;
                            $pay_docs->total             = $value->payments['documentos']->docs_total;

                            $pay_docs->save();

                            $detail->payments_subtotal += $pay_docs->total;

                        }
                    }

                    //Budget Used
                    if( $model->id_type_payment == 3  or $model->id_type_payment == 4 or $model->id_type_payment == 5){
                        
                        if ( $detail->budget_used()->where('id_budget_detail', $detail->id)->exists() ){
                            
                            $pay_used = $detail->budget_used()->where('id_budget_detail', $detail->id)->first();
                            
                        } else {
                            
                            $pay_used = new Budget_used();
                            
                        }

                        $pay_used->id_budget         = $id_budget;
                        $pay_used->id_budget_detail  = $detail->id;
                        $pay_used->brand             = $value->payments['usado']->used_brand;
                        $pay_used->model             = $value->payments['usado']->used_model;
                        $pay_used->version           = $value->payments['usado']->used_version;
                        $pay_used->year              = $value->payments['usado']->used_year;
                        $pay_used->kilometers        = $value->payments['usado']->used_kilometers;
                        $pay_used->take_value        = $value->payments['usado']->used_valortoma;

                        $pay_used->save();

                        $detail->payments_subtotal += $pay_used->take_value;
                    }

                } else {
                    
                    //Budget Plan Sale
                    
                    if ( $detail->budget_plan_payment()->where('id_budget_detail', $detail->id)->exists() ){
                        
                        $pay_plan = $detail->budget_plan_payment()->where('id_budget_detail', $detail->id)->first();
                        
                    } else {
                        
                        $pay_plan = new Plan_payment();
                        
                    }

                    $pay_plan->id_budget          = $id_budget;
                    $pay_plan->id_budget_detail   = $detail->id;
                    $pay_plan->cc_bank            = $value->payments['plans_credit_card']->cc_bank ?:0;
                    $pay_plan->cc_number          = $value->payments['plans_credit_card']->cc_number;
                    $pay_plan->cc_amount          = $value->payments['plans_credit_card']->cc_amount;
                    $pay_plan->dc_bank            = $value->payments['plans_debit_card']->dc_bank ?:0;
                    $pay_plan->dc_number          = $value->payments['plans_debit_card']->dc_number;
                    $pay_plan->dc_amount          = $value->payments['plans_debit_card']->dc_amount;
                    $pay_plan->p_sign             = $value->payments['plans_cash']->p_sign;
                    $pay_plan->p_cash             = $value->payments['plans_cash']->p_cash;

                    $pay_plan->save();

                    $detail->payments_subtotal += ( $pay_plan->cc_amount + $pay_plan->dc_amount + $pay_plan->p_sign + $pay_plan->p_cash );
                }

                $detail->total_no_acc = $detail->quantity * ( $detail->price - $detail->discount_price - $detail->promotion + $detail->expenses_subtotal) - $detail->payments_subtotal;

                $budget_total_no_acc  += $detail->total_no_acc;

            }
            //> Gastos y Pagos


            //< pruebas de manejo
            DB::table('budget_driving_tests')->where([['id_budget', '=', $id_budget]])->delete();  //Resetear

            foreach ($details['driving_test'] as $key => $value){

                $driving = new Driving_test();
                $driving->id_budget   = $id_budget;
                $driving->id_product  = $value->id_product;
                $driving->date        = $value->date;
                $driving->id_model    = $value->id_model;
                $driving->save();
            }
            //> pruebas de manejo


            //< accesorios
            DB::table('budget_accessories')->where([['id_budget', '=', $id_budget]])->delete();  //Resetear

            $accesories_total = 0;
            foreach ($details['accessories'] as $key => $value){

                $accessory = new Budget_accessory();
                $accessory->id_budget   = $id_budget;
                $accessory->id_product  = 0;                //$value->id_product;
                $accessory->accessory   = $value->name;
                $accessory->quantity    = $value->quantity;
                $accessory->price       = $value->price;
                $accessory->discount    = $value->discount;
                $accessory->subtotal    = $value->subtotal;
                $accessory->save();

                $accesories_total += ( $accessory->quantity * $accessory->price ) - $accessory->discount;

            }

            //> accesorios


            //< Etapas
            DB::table('budget_substage')->where([['id_budget', '=', $id_budget]])->delete();  //Resetear

            foreach ($details['substages'] as $value){

                $substage = new Budget_substage();
                $substage->id_budget    = $id_budget;
                $substage->id_substage  = $value;
                $substage->save();
            }
            //> Etapas


            //< Acuerdos
            DB::table('budget_agreement')->where([['id_budget', '=', $id_budget]])->delete();  //Resetear

            foreach ($details['agreements'] as $value){

                $agreement = new Budget_agreement();
                $agreement->id_budget    = $id_budget;
                $agreement->id_agreement = $value;
                $agreement->save();
            }
            //> Acuerdos


            //< Seguimientos
            $id_user    = Auth::user()->id;
            $id_empresa = Auth::user()->id_empresa;  //User::query()->where('id', $id_user)->first()->id_empresa;

            $id_result = $details['task']['id_result'];

            if ( ! DB::table('tasks' )->where( 'id_budget', $id_budget )->where( 'is_closed', '0' )->exists() ){

                //NEW TASK
                $Task_model = new Task();
                
                $Task_model->id_user      = $id_user;
                $Task_model->id_empresa   = $id_empresa;
                $Task_model->id_employee  = $id_user;
                $Task_model->date         = date_create_from_format('Y-m-d H:i:s', $details['task']['date']);
                $Task_model->manual_entry = 0;
                $Task_model->is_closed    = 0;

                $Task_model->id_client      = DB::table('budgets')->where('id', $id_budget)->first()->id_client;
                $Task_model->id_product     = DB::table('budget_details')->where('id_budget', $id_budget)->first()->id_product;
                $Task_model->id_budget      = $id_budget;
                $Task_model->id_event       = $details['task']['id_event'];
                $Task_model->id_task_reason = $details['task']['id_reason'];
                $Task_model->id_process     = $details['task']['id_process'];
                $Task_model->description    = $details['task']['id_process']==1? 'Tarea de presupuesto' :'Tarea de venta';
                $Task_model->save();

                if( !is_null( $details['task']['comment'] ) ){

                    //There is a new comment

                    $Comment_model = new Comments();

                    $Comment_model->comment     = $details['task']['comment'];
                    $Comment_model->id_users    = $id_user;
                    $Comment_model->id_module   = null;
                    $Comment_model->id_document = $id_budget;

                    $Comment_model->save();
                }


            } else {
                //THERE IS ALREADY A TASK
                $id_task = $details['task']['id_task'];
                $Task_model = Task::findorfail($id_task);

                if ( $id_result == '' or $id_result== null ){
                    //There is task but no result. Update the task

                    $Task_model->id_event       = $details['task']['id_event'];
                    $Task_model->id_task_reason = $details['task']['id_reason'];
                    $Task_model->save();

                    if( !is_null( $details['task']['comment'] ) ){

                        //There is a new comment

                        $Comment_model = new Comments();

                        $Comment_model->comment     = $details['task']['comment'];
                        $Comment_model->id_users    = $id_user;
                        $Comment_model->id_module   = null;
                        $Comment_model->id_document = $id_budget;

                        $Comment_model->save();
                    }

                } else {
                    //Exits result. Close the task

                    $dateNow = (new DateTime())->format('Y-m-d h:i:s');
                    $Task_model->close_date     = $dateNow;
                    $Task_model->is_closed      = 1;
                    $Task_model->id_task_result = $details['task']['id_result'];
                    $Task_model->save();

                    if( $id_result != 21) {
                        $Task_model = new Task();

                        $Task_model->id_user        = $id_user;
                        $Task_model->id_empresa     = $id_empresa;
                        $Task_model->id_employee    = $id_user;
                        $Task_model->date           = date_create_from_format('Y-m-d H:i:s', $details['task']['date']);
                        $Task_model->manual_entry   = 0;
                        $Task_model->is_closed      = 0;

                        $Task_model->id_client      = DB::table('budgets')->where('id_budget', $id_budget)->first()->id_client;
                        $Task_model->id_product     = DB::table('budget_details')->where('id_budget', $id_budget)->first()->id_product;
                        $Task_model->id_budget      = $id_budget;
                        $Task_model->id_event       = $details['task']['id_event'];
                        $Task_model->id_task_reason = $details['task']['id_reason'];
                        $Task_model->id_process     = $details['task']['id_process'];

                        $Task_model->description    = $details['task']['id_process']==1? 'Tarea de presupuesto' : 'Tarea de venta';

                        $Task_model->save();

                    } else {
                        //Non Purchase. Update to 'Non Purchase' and register the no buy reason
                        $Budget_model                           = Budget::findorfail($id_budget);
                        $Budget_model->status                   = 0;    //Inactive
                        $Budget_model->id_non_purchase_reasons  = $details['task']['id_nobuy_reason'];
                        $Budget_model->save();
                    }
                }
            }
            //> Seguimientos




            //< Empatia cliente
            if( isset( $details['empathy_id'] ) ){

                //set empathy
                $id_empathy = DB::table('empathy_user_client')->insertGetId(
                    [
                        'id_empathy'   => $details['empathy_id'],
                        'id_user'      => \Auth::user()->id,
                        'id_client'    => $details['client'] ,
                        'id_process'   => $id_budget,
                        'reference'    => 0, // set budget
                        'comment'      =>'',

                        'created_at'   => date('Y-m-d H:i:s'),
                        'updated_at'   => date('Y-m-d H:i:s'),
                    ]
                );
            }
            //> Empatia cliente




            //Metadata  Budget
            //$model                =  Budget::findorfail( $id_budget );
            $model->data_total      = $budget_total_no_acc + $accesories_total;
            $model->data_stage      = $model->id_stage;
            $model->data_closing    = @$this->get_percentage ( $id_budget );
            $model->save();



            DB::commit();

            //Reset session
            //Session::put($this->budget_key, $this->structure_default);


            return response()->json([
                'status'     => true,
                'controller' => 'budget_update',
                'title'      => 'Operación exitosa!',
                'text'       => 'El presupuesto se actualizó satisfactoriamente.',
                'type'       => 'success',
                'id_budget'  => $id_budget ?:0
            ],200);

        } catch (\Exception $e) {

            $error = $e->getMessage();
            DB::rollBack();

            return response()->json([
                'status'     => false,
                'controller' => 'budget_update',
                'title'      => 'Oh!',
                'text'       => 'Ocurrió un error desconocido.',
                'type'       => 'error',
                'error'      => $error
            ],200);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Section: Clients
    |--------------------------------------------------------------------------
    |
    | Los metodos a continuacion estan estrictamente relacionados a la
    | pestaña o TAB de clientes en presupuestos.
    |
    */


    /**
     * Eliecer Cedano
     * Obtiene el Id del Cliente a partir del presupuesto
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function load_budget_client(Request $request)
    {  
        $id_budget = isset($request->budget_id_master)?$request->budget_id_master:'nuevo';
        $data = Session::get($this->budget_key);
        $id_client = $data[$id_budget]['client'];

        return response()->json([
                'status' => true,
                'controller'  => 'budgets_details',
                'title'  => 'Operación exitosa',
                'id_client' => $id_client,
                'type' => 'success'
        ],200);
    }
    

    /**
     * Eliecer Cedano
     * Almacena en sesión el Id del Cliente seleccionado
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function store_client(Request $request)
    {   
        $id_budget = isset($request->budget_id_master)?$request->budget_id_master:'nuevo';        
        $data = Session::get($this->budget_key);        
        $data[$id_budget]['client'] = isset($request->client_id)?$request->client_id:null;
        $data[$id_budget]['empathy_id'] = isset($request->empathy_id)?$request->empathy_id:null;

        Session::put($this->budget_key, $data);
        
        return response()->json([
                'status' => true,
                'controller'  => 'budgets_details',
                'title'  => 'Operación Exitosa!',
                'type' => 'success'
        ],200); 
    }
    
    
    
    
    

    /*
    |--------------------------------------------------------------------------
    | Section: Expenses & Payments
    |--------------------------------------------------------------------------
    |
    | Los metodos a continuacion estan estrictamente relacionados a la
    | pestaña o TAB de gastos en presupuestos.
    |
    */
    

    /**
     * Refresca la pestaña de Gastos según los productos del presupuesto.
     *
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     * @author  Carlos Villarroel  -  cevv07@gmail.com
     */
    public function get_expense_partial(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {

            //$id_budget       = isset($request->id_budget)?$request->id_budget:'nuevo';
            $id_budget          = $request->id_budget != '' ? $request->id_budget : 'nuevo';    
            $id_type_sale       = $request->id_type_sale;
            $id_type_payment    = $request->id_type_payment;

            $budget_key  = $this->budget_key;
            $data        = Session::get($this->budget_key);
            $details     = $data[$id_budget];


            $data = [];
            foreach ($details['products'] as $key => $value){
                
                $id_product = $value->id_product;
                $data[$id_product] ['expenses']  =  $value->expenses;   // [$id_product]?:[];
                $data[$id_product] ['payments']  =  $value->payments;   // array_key_exists($id_product, $details['payments']) ? $details['payments'][$id_product] : [];
                
                $product    = @\App\Models\Products\Product::query()->where('id', $id_product)->first();
                
                $data[$id_product] ['brand']    = $product->brand->name;
                $data[$id_product] ['model']    = $product->modelo->name;
                
            }

            $content = view('admin.budgets.expenses.__partial')
                     ->with(compact('id_budget', 'budget_key', 'id_product', 'id_type_sale', 'id_type_payment'))
                     ->renderSections()['expense_partial'];

            return response()->json([
                'content'      => $content,
                //'expense_data' => array_only($details, ['payments', 'expenses', 'products']),
                'products_eps' => $data
            ], 200);
        }
    }

    
    /**
     * Receive product expenses and payments from the view and put them in session
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     * @author  Héctor Agüero
     */
    public function store_product_expenses(Request $request)
    {   
        $id_budget = $request->id_budget != '' ? $request->id_budget : 'nuevo';
        
	    $id_product = $request->gp_id_product;
        $data = Session::get($this->budget_key);
        
        $productskeys = array_keys($data[$id_budget]['products']);
        foreach ($productskeys as $productkey) {
            if ($id_product == explode("-",$productkey)[0]) {$targetKey = $productkey;}
        }
        
        if( $request->id_type_sale == 2 ){
            //Sale by plans
            
            //This will add the payments:
            $data[$id_budget]['products'][$targetKey]->payments['plans_credit_card'] = (object)$request->plans_credit_card;
            $data[$id_budget]['products'][$targetKey]->payments['plans_debit_card'] = (object)$request->plans_debit_card;
            $data[$id_budget]['products'][$targetKey]->payments['plans_cash'] = (object)$request->plans_cash;
            
            
        } else {
            //Sale conventional
            
            //This will add the expenses:
            $data[$id_budget]['products'][$targetKey]->expenses = (object)$request->gp_expenses;
        
            //This will add the payments:
            $data[$id_budget]['products'][$targetKey]->payments['efectivo'] = (object)$request->gp_cash;
            $data[$id_budget]['products'][$targetKey]->payments['credito'] = (object)$request->gp_credit;
            $data[$id_budget]['products'][$targetKey]->payments['cheques'] = (object)$request->gp_check;
            $data[$id_budget]['products'][$targetKey]->payments['documentos'] = (object)$request->gp_documents;
            $data[$id_budget]['products'][$targetKey]->payments['usado'] = (object)$request->gp_used;
        }
        
        
        //Put expenses and payments to session object:
        Session::put($this->budget_key, $data);
        
        return response()->json([
                'status' => true,
                'controller'    => 'store_product_expenses',
                'title'         => 'Operación Exitosa!',
                'type'          => 'success',
                //'ct' => $data[$id_budget]['products'][$targetKey]->payments['credito']->credit_total,
                'ts'            => $request->id_type_sale,
                'patent'        => $data[$id_budget]['products'][$targetKey]->expenses->patent,
                'sign'          => $data[$id_budget]['products'][$targetKey]->payments['efectivo']->sign,
                'targetKey'     => $targetKey
                ],200);
 
    }
    

    
    
    
    
    
    /*
    |--------------------------------------------------------------------------
    | Section: Acivities
    |--------------------------------------------------------------------------
    |
    | Los metodos a continuacion estan estrictamente relacionados a la
    | pestaña o TAB de actividades en presupuestos.
    |
    */
    
    /*
    |--------------------------------------------------------------------------
    | TAB: Follow & Task
    |--------------------------------------------------------------------------
    */
    
    /**
     * Refresca la pestaña de Actividades de Seguimiento con información de la 
     * tarea más reciente del presupuesto.
     *
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     * @author  Héctor Agüero
     */
    public function load_session_task(Request $request)
    {
        
        $id_budget   = $request->id_budget != '' ? $request->id_budget : 'nuevo';
        $budget_key  = $this->budget_key;
        $data        = Session::get($this->budget_key);
        
        
        $task        = isset($data[$id_budget]['task'])?$data[$id_budget]['task']:null;
        //dd( $task );
        return response()->json([
                'status' => true,
                'controller'  => 'budgets_details',
                'title'  => 'Operación exitosa',
                'task' => $task,
                'type' => 'success'
        ],200);
    }
    

   
    
    
    /**
     * Héctor Agüero
     * Receive task data from the view and put it in session
     *
     * @return \Illuminate\Http\Response
     * @author  Héctor Agüero
     */
    public function store_task_in_session(Request $request)
    {   
        $id_budget = $request->id_budget != '' ? $request->id_budget : 'nuevo';
        
        $data = Session::get($this->budget_key);
    
        //$data[$id_budget]['task'] = [];
        
        //Put task data to session object:
        
        $data[$id_budget]['task']['id_budget']          = $request->id_budget;
        $data[$id_budget]['task']['id_task']            = $request->task_id;
        
        $data[$id_budget]['task']['date']              = $request->date;
        $data[$id_budget]['task']['id_event']           = $request->id_event;
        $data[$id_budget]['task']['event']              = $request->event;
        $data[$id_budget]['task']['id_reason']          = $request->id_reason;
        $data[$id_budget]['task']['reason']             = $request->reason;
        $data[$id_budget]['task']['id_result']          = $request->id_result;
        $data[$id_budget]['task']['result']             = $request->result;
        
        $data[$id_budget]['task']['id_process']         = $request->id_process;
        
        $data[$id_budget]['task']['id_nobuy_reason']    = $request->id_nobuy_reason;
        $data[$id_budget]['task']['nobuy_reason']       = $request->nobuy_reason;
        
        $data[$id_budget]['task']['comment']            = $request->comment;
        
        Session::put($this->budget_key, $data);
        
        //dd( $data );
        
        return response()->json([
                'status'                    => true,
                'controller'                => 'budgets_details',
                'title'                     => 'Operación Exitosa! Datos de tarea cargados en session',
                'date_received_in_session'  => $data[$id_budget]['task']['date'],
                'budget_received_in_session'=> $id_budget,
                'type'                      => 'success'
                ],200);
 
    }
    
    
    
    
    
    
    
    
    
    
    
    

    /*
    |--------------------------------------------------------------------------
    | TAB: Stages & Agreement
    |--------------------------------------------------------------------------
    */


    /**
     * Refresca la pestaña de Subetapas en: Actividades del presupuesto.
     *
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     * @author  Carlos Villarroel  -  cevv07@gmail.com
     */
    public function get_substage_partial(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {

            $id_budget       = isset($request->id_budget)?$request->id_budget:'nuevo';
            $id_type_sale    = $request->id_type_sale;
            $id_type_payment = $request->id_type_payment;

            $budget_key  = $this->budget_key;
            $data        = Session::get($this->budget_key);
            $details     = $data[$id_budget];

            $sub_select  =  $details['substages'];

            $content = view('admin.budgets.activities.stages')
                ->with(compact('id_budget','id_type_sale', 'sub_select'))
                ->renderSections()['substage_partial'];

            return response()->json([
                'content'      => $content,
                'substages'    => $sub_select
            ], 200);
        }
    }


    /**
     * RRefresca la pestaña de Acuerdos en: Actividades del presupuesto.
     *
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     * @author  Carlos Villarroel  -  cevv07@gmail.com
     */
    public function get_agreement_partial(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {

            $id_budget       = isset($request->id_budget)?$request->id_budget:'nuevo';
            $id_type_sale    = $request->id_type_sale;
            $id_type_payment = $request->id_type_payment;

            $budget_key   = $this->budget_key;
            $data         = Session::get($this->budget_key);
            $details      = $data[$id_budget];

            $agree_select =  $details['agreements'];

            $content = view('admin.budgets.activities.agreements')
                ->with(compact('id_budget','id_type_sale', 'agree_select'))
                ->renderSections()['agreement_partial'];

            return response()->json([
                'content'      => $content,
                'agreements'   => $agree_select
            ], 200);
        }
    }


    
    
    /**
     * Setear subsatges en session
     *
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     * @author  Carlos Villarroel  -  cevv07@gmail.com
     */
    public function set_substage_session(Request $request)
    {
        $id_budget = $request->has('id_budget')
            ? $request->id_budget
            : 'nuevo';


        //get session
        $data = Session::get($this->budget_key);

        //set substages
        $data[$id_budget]['substages'] = $request->has('targets') ? $request->targets : [];

        //set session
        Session::put($this->budget_key, $data);


        return response()->json([
            'status' => true,
            'controller'  => 'substages',
            'title'  => 'Operación Exitosa!',
            'text'  => "Subsatges asociadas satisfactoriamente.",
            'type' => 'success',
            'targets' =>  $request->targets
        ],200);
    }


    /**
     * Setear agreements en session
     *
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     * @author  Carlos Villarroel  -  cevv07@gmail.com
     */
    public function set_agreement_session(Request $request)
    {
        $id_budget = $request->has('id_budget')
            ? $request->id_budget
            : 'nuevo';


        //get session
        $data = Session::get($this->budget_key);

        //set substages
        $data[$id_budget]['agreements'] = $request->has('targets') ? $request->targets : [];
        //foreach ($request->targets as $item){}

        //set session
        Session::put($this->budget_key, $data);


        return response()->json([
            'status' => true,
            'controller'  => 'agreements',
            'title'  => 'Operación Exitosa!',
            'text'  => "Subsatges asociadas satisfactoriamente.",
            'type' => 'success',
            'targets' =>  $request->targets
        ],200);
    }

    /**
     * @description Returns html to show the budget printable from data in session
     *
     * @return \Illuminate\Http\Response
     * @author  Héctor Agüero
     */
    public function show_session_budget_printable(Request $request)
    {
        $id_budget = $request->has('id_budget') ? $request->id_budget : 'nuevo';

        //Get session
        $data               = Session::get($this->budget_key);
        $details            = new Collection($data[$id_budget]['products']);
        $accesories_details = new Collection($data[$id_budget]['accessories']);
        
        if( $id_budget !=  'nuevo' ){
            $stored_date    = DB::table('budgets')->where('id', $id_budget)->first()->date;
            $date           = (\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $stored_date )->format('d/m/Y'));
            $id_seller      = DB::table('budgets')->where('id', $id_budget)->first()->id_user;
        } else { 
            $date           = date("d/m/Y");
            $id_seller      = Auth::user()->id;
        }
        //dd( $id_seller );
        $seller = User::findorfail($id_seller);
        $seller->image = file_exists( 'img/users/'.$seller->imagen )
                        ? 'img/users/'.$seller->imagen : 'img/users/grey_camera.png';
        
        //Company
        $id_empresa     = Auth::user()->id_empresa;
        $empresa        = Empresa::findorfail( $id_empresa );
        $empresa->logo  = file_exists( 'img/empresas/'.strtolower($empresa->nombre).'_logo.png' )  
                        ? 'img/empresas/'.strtolower($empresa->nombre).'_logo.png' : '';
        ///
        
        ///
        
        //Client
        $id_client              = $data[$id_budget]['client'];
        $client                 = Client::findorfail($id_client);
        $client->cel_phone      = $client->client_contact()->where('id_type_phone','=',1)->first()['phone'];
        $client->home_phone     = $client->client_contact()->where('id_type_phone','=',2)->first()['phone'];
        $client->mail           = $client->client_mails()->where('principal','=','si')->first()['mail'];
        //dd( $id_client );
        
        //We will return an array of printables, one for every product in the budget
        $htmls = [];
        $index = 0;
        foreach( $details as $key => $detail ) {
            $product    = @\App\Models\Products\Product::query()->where('id', $detail->id_product)->first();
            $marca      = $product->brand->name;
            $modelo     = $product->modelo->name;
            $color      = @DB::table('paints')->where('id', @$detail->id_paint)->exists() 
                          ? @DB::table('paints')->where('id', @$detail->id_paint)->first()->name : 'BLANCO OXFORD';
            
            $html  =  '';
            $html .= '<div class="container">';
            
            ///
           
            ///
            
            $html .= 
        '<table>
            <tr>
                <td style="width:40vw;">';
                if($empresa->logo ==''){
                    $html .= ' <p style="color:#244BA6; font-size: 50px; text-transform: uppercase; font-weight: bold;">'.$empresa->nombre.'</p>';
                } else{
                    $html .= ' <img src="'.url($empresa->logo).'" class="pull-left" alt="Concesionario" style="width:90%">  ';
                }    
                $html .= '</td>';
            
            $product->brandlogo = file_exists( 'img/brands/'.strtolower($marca).'/'.strtolower($marca).'_logo.png' ) 
                                  ? 'img/brands/'.strtolower($marca).'/'.strtolower($marca).'_logo.png' : '';
            //dd( $product->brandlogo );
            $html .=    
            '<td style="width:20vw">';
                if($product->brandlogo ==''){
                    $html .= ' <p style="color:#244BA6; font-size: 50px; text-transform: uppercase; font-weight: bold;">'.$product->brand.'</p> ';
                }
                else{
                    $html .= ' <img src="'.url($product->brandlogo).'" class="img-responsive" alt="Marca" style="width:80%; padding-left:15px;">  ';
                }
            $html .= '</td>
            </tr>';
            
            $html .= 
            '<tr>
                <td valign="top" style="width:40vw;" rowspan="3">
                    <div style="border-top: 3px solid #244BA6; padding: 10px; padding-top: 1px; padding-bottom: 1px;
                                border-top-left-radius: 0.4em; border-top-right-radius: 0.4em; margin-bottom: 0.5em;">';
                                
                        $pathPNG = 'img/brands/'.strtolower($marca).'/'.strtolower($modelo).'/'.strtolower($modelo).'_'.strtolower($color).'.png';
                        $pathJPG = 'img/brands/'.strtolower($marca).'/'.strtolower($modelo).'/'.strtolower($modelo).'_'.strtolower($color).'.jpg';
                        //dd($pathJPG,$pathPNG);
                        if(file_exists($pathPNG)){$product->image = $pathPNG;} 
                        elseif(file_exists($pathJPG)) {$product->image = $pathJPG;} 
                        else {$product->image = 'img/empresas/grey_camera.png';}
                        
                        if( $product->image == 'img/empresas/grey_camera.png' ){
                            $html .= '<img src="'.url($product->image).'" alt="Imagen producto no encontrada" style="display: block; margin-left: auto; margin-right: auto;" >
                                        <p style="font-size:0.75em; text-align:center;">(No se encontró imagen del producto '.$modelo.' en color '.$color.')</p>';
                        } else {
                           $html .= '<img src="'.url($product->image).'" alt="Imagen producto no encontrada" style="width:95%; display: block; margin-left: auto; margin-right: auto;" >'; 
                        }
                        
                        //tractions
                        if ($product->id_traction>0){
                            $traction = @DB::table('tractions')->where('id', $product->id_traction)->first()->name;
                        }
                        $product->traction   = empty($traction)?'-':$traction;

                        //engine
                        $engine = @\App\Models\Products\Engine::query()->where('id', $product->id_engine)->first()->cylinder->name;
                        $product->engine = empty($engine)?'-':$engine;

                        $fuel   = @\App\Models\Products\Engine::query()->where('id', $product->id_engine)->first()->fuel->name;
                        $product->fuel   = empty($fuel)?'-':$fuel;
                        
                        $product->price_expiration_date = is_null(  DB::table( 'prices' )->where( 'id_product', $product->id )->first()->Vto_date ) ?
                                                            'N/D' : DB::table( 'prices' )->where( 'id_product', $product->id )->first()->Vto_date;
                        /*                                    
                        if( DB::table( 'prices' )->where( 'id_product', $product->id )->exists() ){
                            $Vto_date = DB::table( 'prices' )->where( 'id_product', $product->id )->first()->Vto_date;
                            //dd( $Vto_date );
                            $product->price_expiration_date = date_create_from_format('Y-m-d', $Vto_date)->format('d/m/Y');
                        } else {
                            $product->price_expiration_date = 'N/D';
                        }*/
                        //dd( $product->price_expiration_date );
                        
                        $html .=
                        '<p style="font-size:1.5em; color:lightgrey; text-align:center;">
                            <span>'.$marca.'</span>
                            <span>'.$modelo.'</span>
                            <span>'.$product->version.'</span>
                            <span>'.$product->doors.'</span>
                            <span>'.$product->cylinder.'L</span>
                            <span>'.$product->fuel.'</span>
                            <span>'.$product->traction.'</span>
                            <span>'.$color.'</span>
                            <span>'.$product->year.'</span>
                        </p>
                        <p style="font-size:1em; text-align:center;">
                            Imagen no contractual.
                        </p>';
                        
                    
                    $html .= '</div>
                </td>
                <td valign="top" style="width:20vw">
                    <div style="border-top: 3px solid #244BA6; padding: 10px; padding-top: 1px; padding-bottom: 1px;
                                border-top-left-radius: 0.4em; border-top-right-radius: 0.4em; margin-bottom: 0.5em;">
            	        <p style="margin-bottom: 1px;">Presupuesto: <span>'.$id_budget.'</span>&nbsp;&nbsp;&nbsp;<span>Página '.($index+1).'/'.count($details).'</span></p>
                        <p style="margin-bottom: 1px;">Fecha: <span id="budgetDate">'.$date.'</span></p>
                        <p style="margin-bottom: 1px;">Expira: <span id="budgetDate">'.$product->price_expiration_date.'</span></p>   
                    </div>
                </td>
            </tr>';
            
            $html .= 
            '<tr>
                <td valign="top" style="width:20vw">
                    <div>
                        <img src="'.url($seller->image).'" alt="Vendedor" style="width:50%; display: block; margin-left: auto; margin-right: auto;" >
                    </div>
                    <div style="border: none; padding: 10px; padding-top: 1px; padding-bottom: 1px; margin-bottom: 0.5em;">
            	        <p style="margin-bottom: 1px;">Vendedor: <span>'.$seller->first_name.' '.$seller->last_name.'</span></p>
                        <p style="margin-bottom: 1px;">Teléfono: <span>'.$seller->phone.'</span></p>
                        <p style="margin-bottom: 1px;">Email: <span id="sellerEmail">'.$seller->email.'</span></p>
                    </div>   
                </td>
            </tr>';
            ///
            
            ///
            $html .= 
            '<tr>
                <td valign="top" style="width:20vw">
                    <div style="border-top: 3px solid #244BA6; padding: 10px; padding-top: 1px; padding-bottom: 1px;
                                border-top-left-radius: 0.4em; border-top-right-radius: 0.4em; margin-bottom: 0.5em;">
            	        <p style="margin-bottom: 1px;">Cliente: <span>'.$client->name.' '.$client->last_name.'</span></p>
                        <p style="margin-bottom: 1px;">Email: <span>'.$client->mail.'</span></p>
                        <p style="margin-bottom: 1px;">Teléfono fijo: <span>'.$client->home_phone.'</span></p>
                        <p style="margin-bottom: 1px;">Interno: <span>'.$client->cel_phone.'</span></p>
                    </div>
                </td>
            </tr>';
            
            $html .=
            '<tr> 
                <td style="width:40vw;" valign="top">
                    <div style="border-left: 1px solid lightgrey; border-right: 1px solid lightgrey; border-bottom: 1px solid lightgrey; 
                                border-top: 3px solid #244BA6; padding: 10px; padding-top: 1px; padding-bottom: 1px;
                                border-top-left-radius: 0.4em; border-top-right-radius: 0.4em; margin-bottom: 0.5em;">
                        <p style="font-size: 12px">Este vehículo incluye: </p>
                        <ul style="list-style-type:circle; padding-top: 10px;" >';
                        
                        $featuresArray = [];
                        $feature_ids = DB::table('product_feature')->where('id_product', $product->id)->exists()
                                        ? DB::table('product_feature')->where('id_product', $product->id)->pluck('id_feature') : [];
                        if ( count( $feature_ids ) > 0 ) {
                            foreach ($feature_ids as $fid){
                                $new_feature = DB::table('features')->where('id', $fid)->exists() 
                                                ? DB::table('features')->where('id', $fid)->first()->name : '';
                                array_push( $featuresArray, $new_feature );
                            }
                        }
                        $product->features  = $featuresArray;
                        foreach ($product->features as $pf){
                            $html .= '<li style="font-size: 12px">'.$pf.'</li>';
                        }
                            
                        
                        $html .=
                        '</ul>  
                    </div>
            
                </td>';
                
            $html .=
            
            '<td valign="top" style="width:20vw">
                <div style="border-left: 1px solid lightgrey; border-right: 1px solid lightgrey; border-bottom: 1px solid lightgrey; 
                                border-top: 3px solid #244BA6; padding: 10px; padding-top: 1px; padding-bottom: 1px;
                                border-top-left-radius: 0.4em; border-top-right-radius: 0.4em; margin-bottom: 0.5em;" class="pull-right">
                    <table>
                        <tr>
                            <th>Producto</th>
                            <th class="text-center" style="padding-left: 2rem">Cantidad</th> 
                            <th class="text-right" style="padding-left: 2rem">Precio</th>
                        </tr>
                        <tr>
                            <td>
                                <span>'.$marca.'</span>
                                <span>'.$modelo.'</span>
                                <span>'.$product->version.'</span>
                                <span>'.$product->doors.'P</span>
                                <span>'.$product->fuel.'</span>
                                <span>'.$product->cylinder.'L</span>
                                <span>'.$product->traction.'</span>

                            <td class="text-center" style="padding-left: 2rem">'.$detail->quantity.'</td> 
                            <td class="text-right" style="padding-left: 2rem">'.$detail->price.'</td>
                        </tr>
                        <tr>
                            <td>Precio Total</td>
                            <td class="text-center" style="padding-left: 2rem"></td> 
                            <td class="text-right" style="padding-left: 2rem">'.$detail->subtotal.'</td>
                        </tr>';
                        if( $detail->data_id_sale != 2 ){//Conventional sale
                            if( $detail->discount > 0 ){
                                $product->discounted_price = ( $detail->price - $detail->discount ) * $detail->quantity;
                                $html .= '
                                <tr>
                                    <td colspan="2" style="text-align:right">Bonificación</td>
                                    <td class="text-right" style="padding-left: 2rem">'.$detail->discount.'</td>
                                </tr>
                                
                                <tr>
                                    <td colspan="2" style="text-align:left; font-weight: bold;">Precio Bonificado</td>
                                    <td class="text-right" style="padding-left: 2rem">'.$product->discounted_price.'</td>
                                </tr>';
                            }
                            //dd($detail->expenses, $detail->payments );
                                if ( !is_array( $detail->expenses ) ){ $detail->expenses =  (array) $detail->expenses;}
                                $html .= '<tr class="budget_expense" style="display:none" >
                                            <td colspan="2" style="text-align:right">Fletes y formularios</td>
                                            <td class="text-right" style="padding-left: 2rem">'.$detail->expenses["freight_forms"].'</td></tr> ';
                                       
                                if( $detail->expenses["patent"] > 0 ){
                                    $html .= '<tr class="budget_expense" style="display:none" >
                                                <td colspan="2" style="text-align:right">Patentamiento</td>
                                                <td class="text-right" style="padding-left: 2rem">'.$detail->expenses["patent"].'</td></tr> ';
                                } 
                                
                                if( $detail->expenses["credit"] > 0 ){
                                    $html .= ' <tr class="budget_expense" style="display:none" >
                                                    <td colspan="2" style="text-align:right">Crediticios</td>
                                                    <td class="text-right" style="padding-left: 2rem">'.$detail->expenses["credit"].'</td></tr>';      
                                }
                                
                                if( $detail->expenses["inscription"] > 0 ){
                                    $html .= '<tr class="budget_expense" style="display:none" >
                                                <td colspan="2" style="text-align:right">Inscripción</td>
                                                <td class="text-right" style="padding-left: 2rem">'.$detail->expenses["inscription"].'</td></tr>';  
                                }
                                
                                if( $detail->expenses["other"] > 0 ){
                                    $html .= '<tr class="budget_expense" style="display:none" >
                                            <td colspan="2" style="text-align:right">Otros</td>
                                            <td class="text-right" style="padding-left: 2rem">'.$detail->expenses['other'].'</td></tr>';   
                                }
                                
                                //Accesories. Initially, all accesories will be asociated to first product until they are discriminated in the session structure 
                                
                                $accesories_discount = 0;
                                $accesories_total    = 0;
                                
                                if( $index == 0 ){
                                    
                                    $html .= '<tr class="budget_expense" style="display:none" >';        
                                    foreach ($accesories_details as $accesory){
                                        $accesories_discount += $accesory->discount * $accesory->quantity;
                                        $accesories_total    += ( $accesory->price - $accesory->discount) * $accesory->quantity;
                                        $html .= '<td colspan="2" style="text-align:right">'.$accesory->name.'</td>';
                                        $html .= '<td class="text-right" style="padding-left: 2rem">'.( $accesory->price * $accesory->quantity ).'</td></tr>';
                                    }
                                    if( $accesories_discount > 0 ){
                                        $html .= '<tr class="budget_expense" style="display:none" >
                                                <td colspan="2" style="text-align:right">Dcto. accesorios</td>
                                                <td class="text-right" style="padding-left: 2rem">'.$accesories_discount.'</td></tr>';
                                    }
                                    if( $accesories_total > 0 ){
                                        $html .= '<tr class="budget_expense" style="display:none" >
                                                <td colspan="2" style="text-align:right">Total accesorios</td>
                                                <td class="text-right" style="padding-left: 2rem">'.$accesories_total.'</td></tr>';
                                    }
                                    
                                }
                                
                                $prod_exp = $detail->expenses["freight_forms"] + $detail->expenses["patent"] + $detail->expenses["credit"] + $detail->expenses["inscription"] + $detail->expenses["other"] + $accesories_total;
                                $product->discounted_exp_price = $product->discounted_price + ( $detail->quantity * $prod_exp );
                                ///
                                
                                ///
                                $html .= '<tr class="budget_expense" style="display:none" > ';
                                if( $detail->discount > 0 ){
                                    $html .= '<td colspan="2" style="text-align:left; font-weight: bold;">Precio Bonif. c/Gastos</td>';
                                } else {
                                    $html .= '<td colspan="2" style="text-align:left; font-weight: bold;">Precio c/Gastos</td>';
                                }
                                $html .= '<td class="text-right" style="padding-left: 2rem">'.$product->discounted_exp_price.'</td>';
                                
                                if ( !is_array( $detail->payments["efectivo"] ) ){ $detail->payments["efectivo"] =  (array) $detail->payments["efectivo"];}
                                //dd( $detail->payments["efectivo"] );
                                if( $detail->payments["efectivo"]["sign"] > 0 ){
                                    
                                    $html .= '<tr class="budget_expense" style="display:none" >
                                                <td colspan="2" style="text-align:right">Seña</td>
                                                <td class="text-right" style="padding-left: 2rem">'.$detail->payments["efectivo"]["sign"].'</td></tr>';   
                                }
                                
                                if( $detail->payments['efectivo']['cash'] > 0 ){
                                    $html .= '<tr class="budget_expense" style="display:none" >
                                                <td colspan="2" style="text-align:right">Efectivo</td>
                                                <td class="text-right" style="padding-left: 2rem">'.$detail->payments["efectivo"]["cash"].'</td></tr>';   
                                }
                                
                                if ( !is_array( $detail->payments["usado"] ) ){ $detail->payments["usado"] =  (array) $detail->payments["usado"];}
                                if( $detail->payments["usado"]["used_valortoma"] > 0 ){
                                    
                                    $html .= '<tr class="budget_expense" style="display:none" >
                                                <td colspan="2" style="text-align:right">Usado</td>
                                                <td class="text-right" style="padding-left: 2rem">'.$detail->payments["usado"]["used_valortoma"].'</td></tr>';
                                }
                                
                                if ( !is_array( $detail->payments["credito"] ) ){ $detail->payments["credito"] =  (array) $detail->payments["credito"];}
                                if( $detail->payments["credito"]["credit_capital"] > 0 ){
                                    $html .= '<tr class="budget_expense" style="display:none" >
                                                <td colspan="2" style="text-align:right">Crédito</td>
                                                <td class="text-right" style="padding-left: 2rem">'.$detail->payments["credito"]["credit_capital"].'</td></tr>';
                                }
                                
                                if ( !is_array( $detail->payments["cheques"] ) ){ $detail->payments["cheques"] =  (array) $detail->payments["cheques"];}
                                if( $detail->payments["cheques"]["check_amount"] > 0 ){
                                    $html .= '<tr class="budget_expense" style="display:none" >
                                                <td colspan="2" style="text-align:right">Cheques</td>
                                                <td class="text-right" style="padding-left: 2rem">'.$detail->payments["cheques"]["check_amount"].'</td></tr>';
                                }
                                
                                if ( !is_array( $detail->payments["documentos"] ) ){ $detail->payments["documentos"] =  (array) $detail->payments["documentos"];}
                                if( $detail->payments["documentos"]["docs_total"] > 0 ){
                                    $html .= '<tr class="budget_expense" style="display:none" >
                                                <td colspan="2" style="text-align:right">Documentos</td>
                                                <td class="text-right" style="padding-left: 2rem">'.$detail->payments["documentos"]["docs_total"].'</td></tr>';
                                }
                                
                                $prod_pay = $detail->payments["efectivo"]["sign"] + $detail->payments['efectivo']['cash'] + $detail->payments["usado"]["used_valortoma"] 
                                            + $detail->payments["credito"]["credit_capital"] + $detail->payments["cheques"]["check_amount"] + $detail->payments["documentos"]["docs_total"];
                                
                                $product->topay = $product->discounted_exp_price - $prod_pay;
                                ///
                                 
                                ///
                                $html .= '<tr class="budget_expense" style="display:none" >
                                                <td colspan="2" style="text-align:left; font-weight: bold;">A pagar</td>
                                                <td class="text-right" style="padding-left: 2rem">'.$product->topay.'</td></tr>';
                        } //End of Conventional sale
                        else{//Sale by plans
                            $html .= '
                                <tr>
                                    <th colspan="2" style="text-align:right">Cuota</th>
                                    <th class="text-right" style="padding-left: 2rem">Valor</th>
                                </tr>
                                <tr>
                                    <td colspan="2" style="text-align:right">Cuota 1</td>
                                    <td class="text-right" style="padding-left: 2rem">5</td>
                                </tr>
                                <tr>
                                    <td colspan="2" style="text-align:right">Cuota 2 a 9</td>
                                    <td class="text-right" style="padding-left: 2rem">2</td>
                                </tr>
                                <tr>
                                    <td colspan="2" style="text-align:right">Cuota 10 a 41</td>
                                    <td class="text-right" style="padding-left: 2rem">2</td>
                                </tr>
                                <tr>
                                    <td colspan="2" style="text-align:right">Cuota 42 a 61</td>
                                    <td class="text-right" style="padding-left: 2rem">2</td>
                                </tr>
                                <tr>
                                    <td colspan="2" style="text-align:right">Cuota 62 a 84</td>
                                    <td class="text-right" style="padding-left: 2rem">2</td>
                                </tr>
                            ';
                        }
            $html .=
            
                    '</table>';
                    if( $detail->data_id_sale == 2 ){
                        $html .= ' <p> * Valor promedio de las cuotas del rango detallado.</p> ';
                    }
             $html .= '</div>
             </td>';    
                
            $html .=    
            '</tr>';
            
        //Conditions row
        $html .= '
        <tr>
            <td style="width:40vw;">
                <div>
                    <label>Condiciones</label>
                    <ol>
                        <li>El presente presupuesto tiene una validez de 2 días hábiles y es a sólo efecto informativo sin implicar aceptación contractual. </li>
                        <li>Los precios de venta de los vehículos indicados son los correspondientes a la lista vigente el día de la fecha de emisión y pueden ser variados sin previo aviso.</li>
                        <li>El valor de toma del vehículo usado a entregar es estimado, no definitivo, y está sujeto al peritaje e inspección de la unidad previa entrega de la misma por lo que puede sufrir variaciones. El valor de toma definitivo será el que surja del peritaje efectuado a la fecha de concretar la operación.</li>
                    </ol> 
                </div>
            </td>';
        
        if ( !is_array( $detail->payments["credito"] ) ){ $detail->payments["credito"] =  (array) $detail->payments["credito"];}
        ///
       
        ///
        
        if( $detail->payments["credito"]["credit_capital"] > 0 ){
            $bank_name = DB::table('banks')->where('id', $detail->payments["credito"]["credit_bank"])->first()->name;
            $html .= '
            <td valign="top" style="width:20vw">
                <div style="font-size:75%; border-top:3px solid #244BA6; padding:10px; padding-top:1px;
                        padding-bottom:1px; border-top-left-radius:0.4em; border-top-right-radius:0.4em; margin-bottom:0.5em;">
                    <table>
                        <tr>
                            <th>Banco</th>
                            <th class="text-center" style="padding-left: 2rem">Interés</th> 
                            <th class="text-right" style="padding-left: 1rem">Cuotas</th>
                            <th class="text-right" style="padding-left: 1rem">C/Cuota</th>
                        </tr>
                        <tr>
                            <td>'.$bank_name.'</td>
                            <td class="text-center" style="padding-left: 2rem">'.$detail->payments["credito"]["credit_interest"].'</td> 
                            <td class="text-right" style="padding-left: 1rem">'.$detail->payments["credito"]["credit_cuotas_num"].'</td>
                            <td class="text-right" style="padding-left: 1rem">'.$detail->payments["credito"]["credit_cuotas_val"].'</td>
                        </tr>
                    </table>
                </div>
            </td></tr>    
            ';
        }
        
        if( $detail->data_id_sale == 2 ){
            $html .= '
            <tr>
                <td colspan="2" style="text-align:center"><h3>Empezas a pagar tu 0km con el pago de la primera cuota: 5</h3></td>
            </tr> 
            ';
        }
        ///
        
        ///
        
        $html .= '
        <tr>
            <td style="width:40vw;">
                <h4 class="text-center">Todos los precios son finales iva incluído</h4>
            </td>
        
            <td id="checkContainer'.$index.'" style="width:20vw;">';
            if( $id_budget != 'nuevo'){
                $html .= '
                <form action="'.route("print.budgetpdf", ["id" => $id_budget]).'" method="post" novalidate class="generate_pdf">
  								'.csrf_field().'
    				<div class="checkbox" >
                        <label><input type="checkbox" name="includeExpenses" id="showExpenses'.$index.'"> Incluir gastos</label>
                    </div>
                    <button type="button" class="btn btn-sm btn-primary" id="build_budget_pdf'.$index.'" onclick="generate_budget_pdf('.$id_budget.')">
                        Guardar presupuesto en PDF
                    </button>
  		        </form>
                ';
                
            } else {
                $html .= '
                <div class="checkbox" >
                    <label><input type="checkbox" name="includeExpenses" id="showExpenses'.$index.'"> Incluir gastos</label>
                </div>
                ';
            }
                
  		        
  		    $html .= '    
            </td>
        </tr>  
        ';
        
        
        
        
        
        $html .= '</table>';
        
        $html .= '</div>'; //container end
        array_push($htmls, $html);
        $index ++;
        }
        
        
        
        
        
        
        return response()->json([
            'status'                => true,
            'controller'            => 'BudgetDetailsController',
            'title'                 => 'Operación exitosa!',
            'text'                  => 'Detalles del presupuesto en sesión',
            'budget_printables'      => $htmls,
            'type'                  => 'success',
            'pathJPG'               => $pathJPG,
            'pathPNG'               => $pathPNG,
            'debug_variable'        => 'Success!'
        ],200);
        
    }
    


}//fin de la clase BudgetDetailsController
