<?php
namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Sales\Sale;
use App\Models\Products\Product;
use App\Models\Sales\Sale_detail;
use App\Models\Client;
use App\Models\Channel;
use App\Models\Origin;
use App\Models\Task;

use App\Traits\BudgetTrait;
use App\Http\Requests\DefaultRequest;
use Illuminate\Http\Request;
use Datatables;
use Auth;
use DB;
use DateTime;


class SalesController extends Controller
{

    use BudgetTrait;


    /**
     * Muestra un listado de ventas
     *
     * @param   \Illuminate\Http\Request
     * @return  \Illuminate\Http\Response
     * @author  Carlos Villarroel  -  Héctor Agüero
     * */
    public function index(Request $request)
    {
        /** si no es ROOT y no posee algun permiso de empresa: abortar **/
        if( !Auth::user()->hasRole('root') && !Auth::user()->can('venta-*') )  abort(403);



        if ($request->ajax() || $request->wantsJson()) {

            /**  filtros de ventas  **/
            $where = [];
            if ($request->has('filter_fecha'))             $where[] = ['budgets.date',           '>=',$request->filter_fecha      ];
            if ($request->has('filter_fecha_fin'))         $where[] = ['budgets.date',           '<=',$request->filter_fecha_fin  ];
            if ($request->has('filter_sale_type'))         $where[] = ['budgets.id_type_sale',   '=', $request->filter_sale_type  ];

            if ($request->has('filter_sale_status'))       $where[] = ['budgets.sale_id_status', '=', $request->filter_sale_status];
            //if ( !$request->has('filter_sale_status'))     $where[] = ['budgets.sale_id_status', '!=', 2 ];

            if ($request->has('filter_sale_hub'))          $where[] = ['budgets.sale_sent_hub',  '=', $request->filter_sale_hub ];


            if ( !$request->has('filter_fecha') and !$request->has('filter_fecha_fin') ){

                $where[] = ['budgets.date',           '>=', date('Y-m-')."01" ];
                $where[] = ['budgets.date',           '<=', date('Y-m-')."31" ];
            }

            //$where[] = ['budgets.id_company', '=', Auth::user()->id_empresa];


            $dato = Sale::join('clients', 'budgets.id_client', '=', 'clients.id')
                ->select([ DB::raw('budgets.*'), 'budgets.data_closing as percentage', 'clients.id as id_client', 'clients.name as cliente', 'clients.last_name' ])
                ->where($where)->whereIn('budgets.status', [2,3])
                ->whereIn('budgets.id',function($query) use ($where, $request){
                    
                    $filter_prod = [];
                    if ($request->has('filter_prod_id_brand'))     $filter_prod[] = ['products.id_brand',     '=', $request->filter_prod_id_brand ];
                    if ($request->has('filter_prod_id_model'))     $filter_prod[] = ['products.id_model',     '=', $request->filter_prod_id_model ];
                    if ($request->has('filter_prod_version'))      $filter_prod[] = ['products.version',      '=', $request->filter_prod_version  ];

                    $query->select('budgets.id')->from('budgets')
                        ->leftJoin('budget_details', 'budget_details.id_budget', '=', 'budgets.id')
                        ->leftJoin('products', 'budget_details.id_product', '=', 'products.id')
                        ->where($where)
                        ->where($filter_prod);
                });

            return Datatables::of($dato)
                ->addColumn('user', function ($dato) {

                    $date = ($dato->date != null)? (\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $dato->date )->format('d/m/Y H:i')) : $dato->date;

                    return '
                            <div class="dropdown-menu dropdown-anchor-left-center dropdown-has-anchor basic "  id="btn-user-options-'.@$dato->id.'">
                                <table class="bg-basic" style="width: 100%;  /* background: #333; color: whitesmoke; */ ">
                                    <tr>    <th style=" padding: 5px 0;" > <span class="dropdown-title ">Opciones</span> </th>   </tr>
                                </table>
                                <ul>
                                    <li>
                                        <a href="#"   
                                            data-budget="'.@$dato->id.'"    
                                            data-client="'.@$dato->id_client.'"  
                                            class="panel-modal_stage hint--top-right "  
                                            data-hint="'.null.'">
                                             &nbsp; <i class="fa fa-thermometer-half"></i>  &nbsp;&nbsp; Etapas de compra
                                        </a>
                                    </li>
                             
                                    <li>
                                          <a href="#" 
                                            data-budget="'.@$dato->id.'"    
                                            data-client="'.@$dato->id_client.'"  
                                            class="panel-client" >
                                            <i class="fa fa-vcard-o"></i>  &nbsp; Ver panel del cliente
                                        </a>         
                                    </li>
                                </ul>
                            </div>

                            <a href="#"  data-dropdown="#btn-user-options-'.@$dato->id.'"  
                                data-budget="'.@$dato->id.'"    
                                data-client="'.@$dato->id_client.'"  
                                class="NO-panel-client hint--top-right "  
                                data-hint="'.$date.'">
                                 <img class="img-circle border-'.$dato->stage.'" src="'.url('img/users/nofoto.jpg').'" alt="User Avatar" height="30px" />
                            </a>';

                    return '<i class="fa fa-circle  text-yellow"></i>';

                })
                ->addColumn('star', function ($dato) {

                    return '<a href="#"   
                                data-budget="'.@$dato->id.'"    
                                data-client="'.@$dato->id_client.'"  
                                class="panel-modal_category hint--top-right"  data-toggle="modal" data-target=".modalCategory" 
                                data-hint="'.null.'">
                                 <i class="fa fa-star  text-yellow"></i>
                            </a>';
                })
                ->editColumn('cliente', function ($dato) {

                    $client         =  $dato->client; // no funciona porque el Alias tiene el mismo nombre del metodo (client) en el modelo
                    $client         =  \App\Models\Client::where('id', $dato->id_client)->first();;

                    $client_contact =  @$client->mobile    .(empty($client->phone)?'':' / ').    @$client->phone    .((empty($client->email) or empty($client->phone))?'':' / ').    @$client->email ;

                    return '
                            <a href="#"   
                                data-budget="'.@$dato->id.'"    
                                data-client="'.@$dato->id_client.'"  
                                class="open_sale btn btn-xs btn-default hint--top"  
                                aria-label="' .(empty($client_contact)? 'sin datos de contacto' : $client_contact). '">
                                 '.@$dato->cliente . ' ' . @$dato->last_name.'  <i class="fa fa-search-plus  text-yellow"></i>
                            </a> ';

                })
                ->addColumn('e1', function ($dato) {

                    //cargar empatia del cliente
                    $default =  @DB::table('empathy')
                        ->join('empathy_user_client', 'empathy_user_client.id_empathy', '=', 'empathy.id')
                        ->where([['id_process', '=', $dato->id], ['id_user', '=', Auth::user()->id], ['id_client', '=', $dato->id_client] ] )
                        ->orderBy('order', 'asc')->get()->last();


                    // cargas lista de empatias
                    $empatias =  @DB::table('empathy')->orderBy('order', 'asc')->get();

                    $li = "";
                    foreach ($empatias as $item) {
                        $li.= '
                                <li style="padding: 1px;"> &nbsp;&nbsp;&nbsp;
                                    <span >
                                        <input type="radio" class="icheck_empathie" name="empathie'.$dato->id.'[]" id="empathie'.$dato->id.'[]" value="'.$item->id.'"  '.( (@$default->id_empathy==$item->id) ? 'checked':'' ).' 
                                        data-budget="'.@$dato->id.'"   
                                        data-client="'.@$dato->id_client.'"   
                                        data-user="'.Auth::user()->id.'"  
                                        > 
                                         &nbsp; <i class="fa '.$item->icon.' fa-lg" style="color: #'.$item->color.';"></i>  &nbsp;&nbsp; '.$item->name.'
                                    </span>
                                </li>';
                    }



                    //empatia INVERSA : del cliente hacia el vendedor
                    $reverse =  @DB::table('empathy')
                        ->join('empathy_client_user', 'empathy_client_user.id_empathy', '=', 'empathy.id')
                        ->where([['id_process', '=', $dato->id], ['id_user', '=', Auth::user()->id], ['id_client', '=', $dato->id_client] ] )
                        ->orderBy('order', 'asc')->get()->last();

                    $li.= '<li class="divider"></li>
                            <table class="bg-basic" style="width: 100%;  /* background: #333; color: whitesmoke; */ ">
                                 <tr>    <th style=" padding: 5px 0;" > <span class="dropdown-title ">  Como te ve el Cliente?  <span class="hint--top-left hint--medium" data-hint="Esta es la calificación de empatía que el cliente comentó hacia usted."> <i class="fa fa-info-circle"></i></span> </span> </th>   </tr>
                            </table>
                            
                            <ul > 
                               <li style="padding: 1px;">  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    <span class="hint--top" data-hint="" > <i class="fa '.(isset($reverse->icon) ? $reverse->icon : 'fa-meh-o').' fa-lg" style="color:#'.(isset($reverse->color) ? $reverse->color : 'b5bbc8').'"></i> '.(isset($reverse->name) ? $reverse->name : 'sin calificar').' </span>
                               </li>
                           </ul>';



                    // retorna lista
                    return '<div class="dropdown-menu dropdown-anchor-right-top dropdown-has-anchor basic "  id="btn-empathie-options-'.@$dato->id.'" style="z-index: 99999;">
                                <table class="bg-basic" style="width: 100%;  /* background: #333; color: whitesmoke; */ ">
                                    <tr>    <th style=" padding: 5px 0;" > <span class="dropdown-title "> Como te sentiste con el Cliente? <span class="hint--top" data-hint="Como le pareció la empatía del ciente hacia usted?"><i class="fa fa-info-circle"></i></span>  </span> </th>   </tr>
                                </table>
                                <ul >   '.$li.'  </ul>
                            </div>

                            <a href="#"  data-dropdown="#btn-empathie-options-'.@$dato->id.'"   
                                class="hint--top "  
                                data-hint="'. (isset($default->name) ? 'empatía: '.$default->name : 'Asignar empatía al cliente?') .'">

                                 <span class="hint--top" data-hint="" > <i class="fa '.(isset($default->icon) ? $default->icon : 'fa-meh-o').' fa-lg" style="color:#'.(isset($default->color) ? $default->color : 'b5bbc8').'"></i> </span>
                            </a>';


                })

                ->addColumn('product', function ($dato) {

                    $value  = "";
                    foreach ($dato->details as $data){

                        $prod = $data->product;

                        $value  .= !empty($prod->brand->name)  ?  $prod->brand->name      . " "   :   "";
                        $value  .= !empty($prod->modelo->name) ?  $prod->modelo->name     . " "   :   "";


                        $engine = @\App\Models\Products\Engine::find($prod->id_engine);

                        $value  .= isset($engine->fuel->name)     ? $engine->fuel->name       . " "   :   "";
                        $value  .= isset($engine->cylinder->name) ? $engine->cylinder->name   . " "   :   "";



                        //cabines
                        if ($prod->id_cabin>0){
                            $cabine = @DB::table('cabines')->where('id', $prod->id_cabin)->first()->name;

                            if (strtolower($cabine)=='doble')  $cabine=' C.D ';
                            if (strtolower($cabine)=='simple') $cabine=' C.S ';
                            $value  .= $cabine;
                        }else
                            $value  .= $prod->doors . (!empty($prod->doors)? ' Ptas ' : null);

                        //tractions
                        if ($prod->id_traction>0){
                            $traction = @DB::table('tractions')->where('id', $prod->id_traction)->first()->name;
                            $value  .= " $traction ";
                        }

                        //version
                        $value  .= !empty($prod->version)?  $prod->version        . " "   :   "";

                        //paints
                        if ($data->id_paint>0){
                            $paint = @DB::table('paints')->where('id', $data->id_paint)->first()->name;
                            //$value  .= " $paint <br>";
                            $value  .= " " . @ucwords(@strtolower(@$paint)) . " <br>";
                        }
                    }

                    return $value;
                })

                ->addColumn('total', function ($dato) {
                    $html_total      = '<table class="hint--left hint--large" data-hint=" Precio - descuentos + gastos">';
                    $registro        = Sale::findorfail($dato->id);
                    $id_type_payment = $registro->id_type_payment;
                    $id_type_sale    = $registro->id_type_sale;
                    $product_ids     = DB::table('budget_details')->where('id_budget', $dato->id)->pluck('id_product');
                    $registro->total = 0;

                    foreach ($product_ids as $key => $id_product) {
                        $product   = Product::findorfail($id_product);
                        $id_detail = DB::table('budget_details')->where('id_budget', $dato->id)->where('id_product', $id_product)->first()->id;
                        $detail    = Sale_detail::findorfail($id_detail);
                        $product->total = 0;

                        $product->discounted_price = ( $detail->price - $detail->discount_price ) * $detail->quantity;
                        ///
                        //Expenses:
                        if ( $id_type_sale == 1 or $id_type_sale == 3){ //Conventional or Corporative

                            if ( DB::table('budget_expenses')->where('id_budget_detail', $detail->id)->exists() ){
                                $product->freight_forms = DB::table('budget_expenses')->where('id_budget_detail', $detail->id)->first()->freight;
                                $product->patenting = DB::table('budget_expenses')->where('id_budget_detail', $detail->id)->first()->patent;
                                $product->inscription = DB::table('budget_expenses')->where('id_budget_detail', $detail->id)->first()->inscription;
                                $product->other = DB::table('budget_expenses')->where('id_budget_detail', $detail->id)->first()->other;
                            } else {
                                $product->freight_forms = 0; $product->patenting = 0; $product->inscription = 0; $product->other = 0;
                            }

                            if( $id_type_payment == 2 or $id_type_payment == 4 or $id_type_payment == 5 ) {
                                $product->credit_exp = DB::table('budget_expenses')->where('id_budget_detail', $detail->id)->exists() ?
                                    DB::table('budget_expenses')->where('id_budget_detail', $detail->id)->first()->credit : 0;

                            } else { $product->credit_exp = 0; }


                        } else { //Plans (or used)
                            $product->freight_forms = 0; $product->patenting = 0; $product->credit_exp = 0; $product->inscription = 0; $product->other = 0;
                        }

                        //Payments:
                        if( $id_type_payment !== 4 ) {
                            $product->sign = DB::table('budget_cash')->where('id_budget_detail', $detail->id)->exists() ?
                                DB::table('budget_cash')->where('id_budget_detail', $detail->id)->first()->sign : 0;

                            $product->cash = DB::table('budget_cash')->where('id_budget_detail', $detail->id)->exists() ?
                                DB::table('budget_cash')->where('id_budget_detail', $detail->id)->first()->cash : 0;

                            $product->efectivo = $product->sign + $product->cash;

                        } else { $product->efectivo = 0; }

                        if( $id_type_payment == 3 or $id_type_payment == 4 or $id_type_payment == 5 ) {
                            $product->used = DB::table('budget_useds')->where('id_budget_detail', $detail->id)->exists() ?
                                DB::table('budget_useds')->where('id_budget_detail', $detail->id)->first()->take_value : 0;

                        } else { $product->used = 0;}

                        if( $id_type_payment == 2 or $id_type_payment == 4 or $id_type_payment == 5) {
                            $product->credit_pay = DB::table('budget_credit')->where('id_budget_detail', $detail->id)->exists() ?
                                DB::table('budget_credit')->where('id_budget_detail', $detail->id)->first()->capital : 0;

                            $product->check_pay = DB::table('budget_check')->where('id_budget_detail', $detail->id)->exists() ?
                                DB::table('budget_check')->where('id_budget_detail', $detail->id)->first()->amount : 0;

                            $product->documents_pay = DB::table('budget_documents')->where('id_budget_detail', $detail->id)->exists() ?
                                DB::table('budget_documents')->where('id_budget_detail', $detail->id)->first()->total : 0;

                        } else {
                            $product->credit_pay = 0; $product->check_pay = 0; $product->documents_pay = 0;
                        }

                        $prod_exp = $product->freight_forms + $product->patenting + $product->credit_exp + $product->inscription + $product->other;

                        $prod_pay = $product->efectivo + $product->used + $product->credit_pay + $product->check_pay + $product->documents_pay;

                        $product->discounted_exp_price = $product->discounted_price + $product->quantity * $prod_exp;

                        $product->topay = $product->discounted_exp_price - $prod_pay;

                        $product->total = $product->discounted_exp_price; // This does not include payments!
                        if ( $product_ids->count() > 1 ){
                            $html_total .= '<tr><td>P/T'.($key+1).'</td><td>$'.number_format($product->total,0, ",", ".").'</td></tr>';
                        }
                        /////
                        $registro->total += $product->total;
                    }
                    if ( $product_ids->count() > 1 ){
                        $html_total .= '<tr><td>Total</td><td>$'.number_format($registro->total,0, ",", ".").'</td></tr></table>';
                    } else {
                        $html_total .= '<tr><td></td><td>$'.number_format($registro->total,0, ",", ".").'</td></tr></table>';
                    }

                    return $html_total;

                })

                ->addColumn('delivery', function ($dato) {

                    if (empty($dato->id_type_delivery)) return;

                    $days   = @DB::table('type_deliveries')->where('id', $dato->id_type_delivery)->first();

                    $date_delivery =  !empty($dato->date_delivery)
                        ? \Carbon\Carbon::createFromFormat('Y-m-d', $dato->date_delivery )->format('d/m/Y')
                        :  'no especificado!' ;

                    return '<span class="label label-default hint--top"  data-hint="'. @$days->days .' días hábiles">
                                 '. @$days->name .'
                            </span>
                            <span class="hint--top-left" data-hint="fecha aproximada: '.$date_delivery.'"><i class="fa fa-info-circle text-primary"></i></span>';

                })

                ->addColumn('state', function ($dato) {

                    if( !is_null( $dato->sale_id_status ) ) {

                        $sale_status        = DB::table( 'sale_status' )->where( 'id', $dato->sale_id_status )->first()->status;
                        $sale_status_icon   = DB::table( 'sale_status' )->where( 'id', $dato->sale_id_status )->first()->icon;

                    } else {
                        $sale_status        = 'Estado N/D';
                        $sale_status_icon   = 'fa fa-genderless';
                    }

                    //Capture the task id, to close it if the sale is nulled.
                    $id_task = DB::table('tasks')->where( 'id_budget', $dato->id )
                        ->where( 'id_process', '2')
                        ->where( 'is_closed', '0' )->exists()
                        ? DB::table('tasks')->where('id_budget', $dato->id)->where( 'id_process', '2')->where('is_closed', '0')->orderBy('id', 'desc')->first()->id
                        : null;

                    return "<a href='#'
                                data-sale_id            = '".$dato->id."'
                                data-sale_status_id     = '".$dato->sale_id_status."'
                                data-sale_sent_hub      = '".$dato->sale_sent_hub."'
                                data-task_id            = '".$id_task."'
                                class                   ='panel-modal_sale_state hint--left'
                                
                                data-hint               ='".$sale_status."'>
                                
                                <span><i class='$sale_status_icon'></i></span>
                                 
                            </a>";


                    //return $value;

                })

                ->addColumn('make', function ($dato) {


                    $id_task = DB::table('tasks')->where( 'id_budget', $dato->id )
                        ->where( 'id_process', '2')
                        ->where( 'is_closed', '0' )->exists()
                        ? DB::table('tasks')->where('id_budget', $dato->id)->where( 'id_process', '2')->where('is_closed', '0')->orderBy('id', 'desc')->first()->id
                        : null;

                    if( $id_task != null ) {

                        $task       = Task::findorfail($id_task);

                        $task_icon  = DB::table('events')->where('id', $task->id_event)->exists()?
                            DB::table('events')->where('id', $task->id_event)->first()->icon : 'fa fa-phone';

                        $date_hint = (\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $task->date )->format('d/m/Y H:i'));

                        $dateNow = date("Y-m-d H:i:s");

                        if ( $task->date < $dateNow ) {
                            $dato->task_color = '#dd4b39';  //Red
                            $dato->task_hint = 'Tarea vencida / '.$date_hint;
                        }
                        else {
                            $dato->task_color = '#008000';   //Green
                            $dato->task_hint = $date_hint;
                        }
                    }

                    else {

                        $task                   = new Task();
                        $task_icon              = 'fa fa-phone';
                        $dato->task_color       = '#d7dde5';      //Grey
                        $dato->task_hint        = 'Debe agendar tarea';
                        $task->id_event         = 2;
                        $task->id_task_reason   = 7;
                        $task->id_process       = 2;
                        $task->date             = (new DateTime())->format('Y-m-d h:i:s');
                        //dd($task);
                    }

                    if ( ! DB::table('tasks')->where('id_budget', $dato->id)->where( 'id_process', '2')->where('is_closed', '1')->exists() ){
                        $dato->task_hint .= ' / No hay tarea previa';
                    } else {
                        $id_previous_result = DB::table('tasks')->where('id_budget', $dato->id)->where( 'id_process', '2')->where('is_closed', '1')->orderBy('id', 'desc')->first()->id_task_result;
                        $task_previous_result = DB::table('task_results')->where('id', $id_previous_result)->first()->result;
                        $dato->task_hint .= ' / '.$task_previous_result;
                    }

                    return "<a href='#'
                                data-budget     = $dato->id 
                                data-task       = $id_task
                                data-reason     = $task->id_task_reason
                                data-action     = $task->id_event
                                data-process    = '".$task->id_process."'
                                data-date       = '".$task->date."'
                                class           ='panel-modal_task hint--left hint--large'
                                
                                data-hint       ='".$dato->task_hint."'>
                                
                                <span style='color:$dato->task_color'><i class='$task_icon'></i></span>
                                 
                            </a>";


                })

                /*->editColumn('percentage', function ($dato) {
                    //plan de ahorro ?
                    if ($dato->id_type_sale == 2) return;

                    $tooltip = 'Porcentaje de cierre: ';
                    foreach ($dato->agreements as $item){
                        $tooltip .= $item->agreement->name . ', ';
                    }

                    //calcular %
                    $porcent  =  @$this->get_percentage ( $dato->id );
                    $porcent  =  @number_format($porcent, 0, ".", "");

                    $html = '<a href="#"
                                data-budget="'.@$dato->id.'"
                                data-client="'.@$dato->id_client.'"
                                class="panel-modal_agreement " style="color: #444; font-size: 11px;"> ';

                    $html .= '<div class="progress-group">';
                    $html .= '<span class="progress-text text-sm hint--top hint--large" aria-label=" '.$tooltip.' "> '.$porcent.'<sup>%</sup> </span>';
                    $html .= '<span class="progress-number text-sm hint--top" aria-label=""> <b></b></span>';
                    $html .= '    <div class="progress progress-xs active" style="background-color: #e1e1e1;">';
                    $html .= '      <div class="progress-bar progress-bar-'.(($porcent>=50) ? 'success' : 'danger').' progress-bar-striped" style="width: '.$porcent.'%"></div>';
                    $html .= '    </div>';
                    $html .= '</div>';

                    $html .= '</a>';


                    return $html;
                })*/

                ->addColumn('conditions', function ($dato) {
                    $registro        = Sale::findorfail($dato->id);
                    $id_type_payment = $registro->id_type_payment;
                    $id_type_sale    = $registro->id_type_sale;

                    $html_cond  ='';
                    //Type of sale:
                    if ( DB::table('type_sales')->where('id', $id_type_sale)->exists() ){
                        $sale_color      = DB::table('type_sales')->where('id', $id_type_sale)->first()->color;
                        $sale_hint       = DB::table('type_sales')->where('id', $id_type_sale)->first()->name;
                    } else {
                        $sale_color      = '#008000'; $sale_hint = 'n/d';
                    }

                    $html_cond  .= '<span class ="hint--left" data-hint="Tipo de Venta: '.$sale_hint.'" style="color: '.$sale_color.'"><i class="fa fa-car"></i></span>&nbsp;';

                    //Type of payment:
                    if ( DB::table('type_payments')->where('id', $id_type_payment)->exists() ){
                        $pay_symbol      = DB::table('type_payments')->where('id', $id_type_payment)->first()->base_symbol;
                        $pay_hint        = DB::table('type_payments')->where('id', $id_type_payment)->first()->name;

                        $html_cond .= '<span class ="hint--left" data-hint="Tipo de Pago: '.$pay_hint.'">'.$pay_symbol.'</span>';

                        if ( $id_type_payment == 3 or $id_type_payment == 4 or $id_type_payment == 5 ) {
                            $html_cond .= '<span><i class="fa fa-car"></i></span>&nbsp;';
                        } else {
                            $html_cond .= '&nbsp;';
                        }

                    } else {
                        $html_cond .= '<span class ="hint--left" data-hint="Tipo de pago no encontrado">?&nbsp;</span>';
                    }

                    //Sent client:
                    $sent_color = $registro->sent_client == 1? '#008000' : '#d7dde5';
                    $sent_hint  = $registro->sent_client == 1? 'Enviado al cliente' : 'No enviado al cliente';

                    $html_cond .= '<span style      =  "color: '.$sent_color.'" 
                                        class       =  "panel-sent_client hint--left" 
                                        data-hint   =  "'.$sent_hint.'"
                                        data-budget =  "'.$dato->id.'"
                                        data-sent   =  "'.$registro->sent_client.'">
                                        <i class="fa fa-envelope"></i>
                                    </span>&nbsp;';

                    //Sale action: Not required in Sales Listing
                    /* Not required in Sales Listing
                    $conditions_sale_hint   = $dato->status == 4 ? 'Reserva' : 'Venta';
                    $conditions_icon_color  = $dato->status == 4 ? 'blue' : 'black';

                    //We will possibly need the task id to close it in case of convertion
                    $id_task = DB::table('tasks')->where('id_budget', $dato->id)->where('is_closed', '0')->exists()?
                        DB::table('tasks')->where('id_budget', $dato->id)->where('is_closed', '0')->orderBy('id', 'desc')->first()->id : null;
                    $html_cond .= '<span
                                        class       = "panel-convert_sale hint--left"
                                        data-hint   = "'.$conditions_sale_hint.'"
                                        data-task   = "'.$id_task.'"
                                        data-budget =  "'.$dato->id.'" >
                                        <i style="color:'.$conditions_icon_color.'" class="fa fa-bullseye"></i>
                                    </span>';
                    */
                    return $html_cond;
                })

                ->addColumn('keys', function ($dato) {
                    $budget         = Sale::findorfail($dato->id);
                    $client         = Client::findorfail($budget->id_client);
                    $html_keys      = '';


                    if ( $client->id_channel != null ){//Channel set
                        $channel    = Channel::findorfail($client->id_channel);
                        $html_keys  .= '<span class ="panel-menu_channels2 hint--left" 
                                            data-hint="Canal: '.$channel->channel.'">
                                            <i class="fa fa-'.$channel->icon.'"></i>
                                        </span>&nbsp;';
                    } else {//Channel not set
                        $html_keys  .=
                            '<div class="dropdown-menu dropdown-anchor-right-center dropdown-has-anchor basic "  id="keys-channels-options-'.@$dato->id.'">
                                <table class="bg-basic" style="width: 100%;  /* background: #333; color: whitesmoke; */ ">
                                    <tr>    <th style=" padding: 5px 0;" > <span class="dropdown-title ">Opciones</span> </th>   </tr>
                                </table>
                                <ul>';
                        $channel_options = DB::table('channels')->orderBy('id', 'ASC')->get(); //pluck('channel', 'id', 'icon');
                        foreach ($channel_options as $channel_option){
                            $html_keys  .=
                                '<li>
                                    <a href="#"   
                                        data-budget="'.@$dato->id.'"    
                                        data-channel="'.@$channel_option->id.'"
                                        data-client="'.@$dato->id_client.'"
                                        class="panel-menu_channels  hint--top-left "  
                                        data-hint="'.null.'">
                                        &nbsp; <i class="fa fa-'.@$channel_option->icon.'"></i>  &nbsp;&nbsp; '.@$channel_option->channel.'
                                    </a>
                                </li>';
                        }

                        $html_keys  .=

                            '</ul>
                            </div>

                            <a href="#"  data-dropdown="#keys-channels-options-'.@$dato->id.'"  
                                    data-budget="'.@$dato->id.'"  
                                    class="hint--top-left "  
                                    data-hint="Canal: no especificado">
                                    <i class="fa fa-circle"></i>
                            </a>&nbsp;';
                    }


                    if ( $client->id_origin != null ){//Origin set
                        $origin    = Origin::findorfail($client->id_origin);
                        $html_keys  .= '<span class ="panel-menu_origins hint--left" 
                                            data-hint="Origen: '.$origin->origin.'">
                                            <i class="'.$origin->icon.'"></i>
                                        </span>&nbsp;';
                    } else {//Origin not set

                        //Main menu header
                        $html_keys  .=
                            '<div class="dropdown-menu dropdown-anchor-right-center dropdown-has-anchor basic"  
                                id="keys-origins-options-'.@$dato->id.'" style   ="height: 200px; overflow: auto;" >
                                <table class="bg-basic" style="width: 100%;  /* background: #333; color: whitesmoke; */ ">
                                    <tr>    <th style=" padding: 5px 0;" > <span class="dropdown-title ">Opciones</span> </th>   </tr>
                                </table>
                                <ul>';

                        //Parent origin options
                        $origin_options = DB::table('origins')->where( 'id_parent', 0)->orderBy('id', 'ASC')->get();
                        foreach ($origin_options as $origin_option){
                            $html_keys  .=
                                '<li>
                                    <a href="#" 
                                        data-dropdown   = "#budget'.@$dato->id.'_origen'.@$origin_option->id.'"
                                        class           = "parent_origin"> &nbsp; 
                                        <i class="'.@$origin_option->icon.'"></i>  &nbsp;&nbsp; '.@$origin_option->origin.'
                                    </a>
                        
                                </li>';

                        }

                        $html_keys  .= '</ul></div>';

                        //Iterate again to build the suborigins sub menus
                        foreach ($origin_options as $origin_option){
                            $html_keys  .=
                                '<div class="dropdown-menu basic seller-origin-submenu"   
                                id      ="budget'.@$dato->id.'_origen'.@$origin_option->id.'" 
                                style   ="height: 200px; width: 50px overflow: auto;" >
                                
                                <table class="bg-basic" style="width: 100%;  /* background: #333; color: whitesmoke; */ ">
                                    <tr>    <th style=" padding: 5px 0;" > <span class="dropdown-title ">Opciones</span> </th>   </tr>
                                </table>
                                <ul>';

                            //Child origin options
                            $child_options = DB::table('origins')->where( 'id_parent', $origin_option->id)->orderBy('id', 'ASC')->get();
                            foreach ($child_options as $child_option){
                                $html_keys  .=
                                    '<li>
                                    <a href="#"   
                                        data-budget = "'.@$dato->id.'"    
                                        data-origin = "'.@$child_option->id.'"
                                        data-client = "'.@$dato->id_client.'"
                                        data-icon   = "'.@$child_option->icon.'"
                                        class       = "panel-child_origin" >&nbsp; 
                                        <i class="'.@$child_option->icon.'"></i>  &nbsp;&nbsp; '.@$child_option->origin.'
                                    </a>
                                </li>';
                            }
                            $html_keys  .= '</ul></div>';}

                        //////////////////
                        //Icon shown on datatable cell:
                        $html_keys  .=
                            '<a href="#"  
                                data-dropdown   = "#keys-origins-options-'.@$dato->id.'"  
                                data-budget     = "'.@$dato->id.'"  
                                class           = "panel-menu_origins hint--top-left "  
                                data-hint       = "Origen: no especificado">
                                <i class="fa fa-circle"></i>
                                </a>&nbsp;';

                    }

                    $last_comment = DB::table( 'comments' )->where( 'id_document', $budget->id )->exists() ?
                        DB::table( 'comments' )->where( 'id_document', $budget->id )->orderBy('id', 'desc')->first()->comment : '';

                    $html_keys   .= '<span 
                                        class       = "panel-show_comments hint--left"
                                        data-sale = "'.$dato->id.'"
                                        data-hint   = "'.$last_comment.'">
                                        <i class="fa fa-commenting-o"></i>
                                     </span>';

                    //return $last_comment;
                    return $html_keys;
                })

                ->rawColumns(['date', 'user', 'star', 'cliente', 'e1', 'percentage', 'make', 'delivery', 'total', 'state', 'product', 'conditions', 'keys', 'action'])
                ->make(true);
        }else

            return view('admin.sales.index');
    }

    /**
     * @description Update the 'id_sale_status' field in storage.
     * @author Héctor Agüero - heagueron@gmail.com
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update_sale_status($id, Request $request)
    {
        $model = Sale::findorfail($id);

        $model->sale_id_status = $request->sale_status;

        $model->save();

        return response()->json([
            'status'        => true,
            'controller'    => 'update_sale_status',
            'title'         => 'Operación exitosa!',
            'text'          => 'El estado de la venta ha sido actualizado satisfactoriamente.',
            'type'          => 'success'
        ],200);
    }



}