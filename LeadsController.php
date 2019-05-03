<?php

namespace App\Http\Controllers\Leads;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\UsuariosKohana;
use App\Models\Sellerlead;
use App\Models\KohanaLeads;
use App\Models\Empresa;
use App\Models\Book;
use Datatables;
use Auth;
use DB;
use App\Models\Kohana\Kohanaclient;
use App\Models\Kohana\Kohanabudgets;


//version 2
use App\Models\Api_company;
use App\Traits\ApiTranslateTrait;
use GuzzleHttp;
use App\Models\Leads\Lead;
use Carbon\Carbon;
//Fin version 2

use Illuminate\Support\Facades\Mail;
use App\Traits\LeadsKohanaTrait;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Input;
/*
Controlador para los metodos de asignaciones de los leads
            metodos de asignaciones de leads
            obtener los leads
            cartera de leads
            
*/
class LeadsController extends Controller
{
    
    use ApiTranslateTrait;
    public function index(Request $request){

        if ($request->ajax() || $request->wantsJson()) {


            // actualizar leads atendidos
            //$this->update_leads_attended();

            $dato = Sellerlead::select([
                'id',
                'id_usuario as vendedor',
                'id_companies',
                'nombres',
                'apellidos',
                'correo',
                'cod_area',
                'telefono',
                'cod_movil',
                'movil',
                'modelo',
                'version',
                'comentario',
                'created_at as fecha',
                'origen',
                'color',
                'campana',
                'id_seller'
            ])->where([ ['deleted', '=', '0'], ['id_companies', '=', Auth::user()->id_empresa]]); //->orderBy('fecha', 'DESC'); //where([ ['deleted', '=', '0'], ['id_usuario', '=', null] ])

            return Datatables::eloquent($dato)
                ->editColumn('nombres', function ($dato){
                    $telefono=$dato->cod_area.''.$dato->telefono;
                    $movil=$dato->cod_movil.''.$dato->movil;
                    $mail = $dato->correo;
                    if(($mail=='')or(is_null($mail))){
                        $mail='nada';
                    }
                    if(($movil=='')or(is_null($movil))){
                        $movil='nada';
                    }
                    if(($telefono=='')or(is_null($telefono))){
                        $telefono='nada';
                    }
                    $client = DB::connection('kohana')->table('clientes')
                        ->orwhere('email', $mail)
                        ->orwhere('telefono', $telefono)
                        ->orwhere('celunro', $movil)
                        ->get();

                        if (count($client)==0){

                        }else{
                            foreach($client as $valor){
                                $id=$valor->Id;
                            }

                            $presupuesto = Kohanabudgets::where('id_cliente','=',$id)->get();
                            if(count($presupuesto)==0){

                            }else{
                                foreach($presupuesto as $valor){
                                    $id_usuario=$valor->id_usuario;
                                }
                                $cantidad = DB::connection('kohana')->table('presupuestos')->where('id_cliente','=',$id)->count();
                                $user = UsuariosKohana::find($id_usuario);
                                if(count($user)==0){

                                }else{
                                    $vendedor = $user->last_name;
                                }
                            }
                        }
                        if (isset($vendedor)){
                            $nombre = '<a href="#" class="btn btn-xs btn-default roles hint--top hint--medium" aria-label="'.@$vendedor.'" style="color: #C00;">'.$dato->nombres.'</a>('.@$cantidad.')';
                        }else{
                            $nombre = $dato->nombres;
                        }
                        return $nombre;
                        //return $dato->nombres .' ('. @$vendedor . ')';
                })
                ->editColumn('color', function ($dato) {
                    if ($dato->color=="azul") return '<i class="fa fa-circle text-blue"></i>';
                    if ($dato->color=="verde") return '<i class="fa fa-circle text-green"></i>';
                    if ($dato->color=="rojo") return '<i class="fa fa-circle text-red"></i>';
                    if ($dato->color=="") return '<i class="fa fa-circle text-gray"></i>';
                })
                ->editColumn('fecha', function ($dato) {
                    if($dato->fecha == null) return '0';
                    //return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $dato->fecha )->diffForHumans();
                    return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $dato->fecha )->diffForHumans() . ' <span class="hint--top-rigth" aria-label="'.$dato->fecha.'"><i class="fa fa-info-circle"></i></span>';
                })
                ->editColumn('modelo', function ($dato) {
                    return $dato->modelo ." ". $dato->version;
                })
                ->editColumn('comentario', function ($dato) {
                    if (!empty($dato->comentario))
                        return '<a href="#"  data-valor=\'{"id":"'.$dato->id.'", "nombre":"'.$dato->nombres.' '.$dato->apellidos.'" }\'  class="btn btn-xs btn-default comentar hint--top hint--medium" aria-label="'.trim($dato->comentario).'"><i class="fa fa-comment"></i></a> ';
                })
                ->editColumn('correo', function ($dato) {
                    if (!empty($dato->correo)){
                        return '<a href="#" data-iddata="'.$dato->id.'" class="btn btn-xs btn-default roles hint--top hint--medium" aria-label="'.trim($dato->correo).'"><i class="fa fa-envelope"></i></a> ';
                    }
                })
                ->editColumn('telefono', function ($dato) {
                    if (!empty($dato->telefono) or !empty($dato->movil))
                        return '<a href="#" data-iddata="'.$dato->id.'" class="btn btn-xs btn-default roles hint--top hint--medium" aria-label="'.$dato->cod_area. " " .$dato->telefono ." / " . $dato->cod_movil. " " .$dato->movil.'"><i class="fa fa-phone"></i></a>';
                })
                ->editColumn('vendedor', function ($dato) {
                    if ( !empty($dato->vendedor) ){

                        /** verificar si existe vendedor **/
                        $user = UsuariosKohana::where('id', $dato->vendedor)->first();
                        if (count($user)==0) return '';


                        /** verificar si supervisor asigno el lead a un vendedor **/
                        if ($user->id_level >= 3){

                            $lead = DB::connection('kohana')->table('leads')->where('website_lead_id_hub', $dato->id_seller)->get()->last();

                            if ($lead->id_vendedor_asignado>0 && $lead->id_vendedor_asignado!=$dato->vendedor){
                                //lead fue delegado a un vendedor : actualizar
                                $registro = Sellerlead::find($dato->id);
                                $registro->id_usuario = $lead->id_vendedor_asignado;
                                $registro->save();

                                /** verificar si existe vendedor **/
                                $user = UsuariosKohana::where('id', $lead->id_vendedor_asignado)->first();
                                if (count($user)==0) return '';
                            }
                        }


                        /** mostrar datos del vendedor **/
                        if ($user->id_level >= 3){
                            return $user->first_name." ".$user->last_name;
                        }else{

                            $supervisor = UsuariosKohana::where('id', $user->team_leader_id)->first();

                            if ( count($supervisor) >0 )
                                return $supervisor->first_name." ".$supervisor->last_name  . " / "  .  $user->first_name." ".$user->last_name;

                            else
                                return $user->first_name." ".$user->last_name;
                        }
                    }
                })

                ->addColumn('calificar', function ($dato) {

                    if ( Auth::user()->ability('root', 'lead-calificar') ):
                        return '<a href="#" data-valor=\'{"id":"'.$dato->id.'", "color":"'.$dato->color.'", "nombre":"'.$dato->nombres.' '.$dato->apellidos.'" }\'  class="btn btn-xs btn-success calificar hint--top" aria-label="Calificar"><i class="fa fa-cogs"></i></a> ';
                    endif;
                })
                ->addColumn('action', function ($dato) {
                    $html = '';
                        $telefono=$dato->cod_area.''.$dato->telefono;
                        $movil=$dato->cod_movil.''.$dato->movil;
                        $mail = $dato->correo;
                        if(($mail=='')or(is_null($mail))){
                            $mail='nada';
                        }
                        if(($movil=='')or(is_null($movil))){
                            $movil='nada';
                        }
                        if(($telefono=='')or(is_null($telefono))){
                            $telefono='nada';
                        }
                        $client=0;

                        $client = DB::connection('kohana')->table('clientes')->where('email', $mail)
                        ->orwhere('telefono', $telefono)
                        ->orwhere('celunro', $movil)
                        ->get();
                        if (count($client)==0){

                        }else{
                            $asignado = 'si';
                        }

                    if ( empty($dato->vendedor) ){

                        if ( !empty($dato->color) ){
                            $html .= '<input type="checkbox" name="leads[]" id="leads_'.$dato->id.'" data-iddata="'.$dato->id.'" class="flat-blue checkboxlead" value="'.$dato->id.'" data-color="'.$dato->color.'" data-vendedor="'. @$asignado .'">';
                        }

                    }else{

                        //verificar si fue atendido
                        $lead = DB::connection('kohana')->table('leads')->where('website_lead_id_hub', $dato->id_seller)->get()->last(); //->first();
                        if (@$lead->estado==="Contactado" ){
                            $html .= '.<input type="checkbox" name="leads[]" id="leads_'.$dato->id.'" data-iddata="'.$dato->id.'" class="flat-blue checkboxlead" value="'.$dato->id.'" data-color="'.$dato->color.'" data-vendedor="'. @$asignado .'">';

                        }else{
                            $now = \Carbon\Carbon::now();
                            $fecha = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $dato->fecha );
                            $diferencia = $now->diffInMinutes( $fecha );

                            if ($diferencia > 120 && @$lead->estado!=="Contactado" )
                                $resaltar = 'resaltar hover';
                            else
                                $resaltar = '';

                            $html .= '<input type="checkbox" name="leads[]" id="leads_'.$dato->id.'" data-iddata="'.$dato->id.'" class="flat-blue checkboxlead '.$resaltar.'" value="'.$dato->id.'" data-color="'.$dato->color.'" data-vendedor="'. @$asignado .'">';
                        }
                    }
                    return $html;
                })
                ->rawColumns(['fecha', 'color', 'vendedor','comentario', 'correo', 'telefono', 'clase', 'calificar', 'action', 'nombres'])
                ->make(true);
        }else
            return view('admin.leads.index')->with('conversion', $this->get_conversion() );
    }


   protected function get_conversion(){

        /** carcular conversion  **/
        // @php(  print $leads=\App\Models\Sellerlead::where('deleted', '0')->where('id_companies', \Auth::user()->id_empresa)->count() )

        $leads_laravel = \App\Models\Sellerlead::query()->where([ ['id_companies', '=', Auth::user()->id_empresa] ] )->get();

        $ventas = 0;
        foreach ($leads_laravel as $dato){

            //verificar si fue atendido
            if ( $dato->id_usuario != null ){

                $lead = DB::connection('kohana')->table('leads')->where('website_lead_id_hub', $dato->id_seller)->get()->last(); //->first();
                //if ($lead->estado==="Contactado" && $lead->id_presupuesto>0){
                if ( @$lead->id_presupuesto > 0  ){
                    // verificar si concreto venta
                    $pedido_existe = DB::connection('kohana')->table('pedidos')->where('id_presupuesto', $lead->id_presupuesto)->count();

                    if ( $pedido_existe >0 ){

                        $ventas++;
                        //verificar si viene de target: actualizar venta
                        if ( !empty($dato->id_target)){
                            \App\Models\Target::query()->where('id', $dato->id_target)->update(['is_sale' => '1']);
                        }
                    }

                }
            }
        } //foreach

        $total_leads = @count($leads_laravel);
        $coversion = @($ventas/$total_leads)*100;

        return @number_format($coversion, 2, ".", "");
    }

    /////////////////////////////////////// Desde aqui inicia la V2 ////////////////////////////////////////
    
    /*
    @description Obtener leads desde varias apis
    @author Sandy Rodriguez
    @param id api, id concesionaria, id sucursal opcional
    */
    public function getLeadsFord(Request $request){
        if($request->id_empresa>0){
            $credential = new Api_company();
            $credentials = $credential->where('id_companies','=',$request->id_empresa)
                                        ->where('id_api','=', $request->id_api)
                                        ->first();
            //echo var_dump($credentials). $request->id_api. '/'.$request->id_empresa ;
            $client = new GuzzleHttp\Client();
            $pass = 'Basic '.base64_encode($credentials->user.':'.$credentials->password);
            $url = $credentials->url;
            $urleads = $url.'leads';
            try{
                $res = $client->request('GET', $urleads, ['headers' =>['authorization' =>$pass ]]);
                $array = json_decode($res->getBody());
                //echo var_dump($array);
                $contador = 0;
                foreach ($array as $key) {
                    $contador++;
                    $datos['id_companies'] = $request->id_empresa;
                    $datos['name'] = isset($key->first_name)?$key->first_name:null;
                    $datos['last_name'] = isset($key->last_name)?$key->last_name:null;
                    $datos['id_document'] = isset($key->identification_type)?$this->translateToSeller(1, 'documents', $key->identification_type):null;
                    $datos['document_nro'] = isset($key->identification)?$key->identification:null;
                    $datos['id_gender'] = isset($key->genre)? $this->translateToSeller(1, 'gender', $key->genre):null;
                    $datos['id_channel'] = 6;
                    $datos['id_origin'] = 11;
                    $datos['birthday'] = isset($key->birthday)? $key->birthday: null;
                    $datos['point_of_sale'] = isset($key->point_of_sale_code)?$key->point_of_sale_code:null;
                    $datos['id_provider'] = 1;
                    $datos['lead_id'] = isset($key->lead_id)?$key->lead_id:null;
                    $datos['website_id'] = isset($key->website_id)?$key->website_id:nul;
                    $datos['website_lead_id'] = isset($key->website_lead_id)?$key->website_lead_id:null;
                    $datos['lead_url_origin'] = isset($key->origin_url)?$key->origin_url:null;
                    $datos['vehicle_code'] = isset($key->vehicle_code)?$key->vehicle_code:null;
                    $datos['plan_code'] = isset($key->plan_code)?$key->plan_code:null;
                    $datos['id_type_sale'] = isset($key->plan_code)?'2':'1';
                    $datos['address'] = isset($key->address->description)?$key->address->description:null;
                    $datos['google_maps_place_id'] = isset($key->address->google_maps_place_id)?$key->address->google_maps_place_id:null;
                    $datos['latitude'] = isset($key->address->latitude)?$key->address->latitude:null;
                    $datos['longitude'] = isset($key->address->longitude)?$key->address->longitude:null;
                    $datos['share'] = isset($key->share_with_ford)?$key->share_with_ford:null;
                    $datos['email'] = isset($key->email)?$key->email:null;
                    $datos['pre_phone'] = isset($key->phone)?substr($key->phone, 0, 3):null;
                    $datos['phone'] = isset($key->phone)?$key->phone:null;
                    $datos['pre_mobile'] = isset($key->mobile_phone)?substr($key->mobile_phone, 0, 3):null;
                    $datos['mobile'] = isset($key->mobile_phone)?$key->mobile_phone:null;
                    $datos['comments'] = isset($key->comments)?$key->comments:null;
                    $datos['origin'] = isset($key->origin)?$key->origin:null;
                    $datos['sub_origin'] = isset($key->sub_origin)?$key->sub_origin:null;
                    $datos['id_method'] = 1;
                    $this->create($datos);
                    unset($datos);
                }
                return response()->json(['leads' => $array, 
                                'total' =>$contador]);
            }catch(GuzzleHttp\Exception\ClientException \GuzzleHttp\Exception\BadResponseException \GuzzleHttp\Exception\ErrorException $e){
                $client = $e->getResponse();
                //$server = $f->getResponse();
                $errores = json_decode($client->getBody());
                //$servererror = json_decode($server->getBody());
                
                return response()->json(['status' => 'fail', 'msg' =>$errores]);
            }
        }else{
            return response()->json(['status' => 'fail', 'msg' =>'Debe indicar una empresa'], 500);
        }
    }
    
    /*
    *@description crear lead
    *@author Sandy Rodriguez
    */
    public function create($lead){
        $Lead = new Lead();
        $duplicated = $this->is_duplicated($lead);
        if(!isset($duplicated->is_duplicated)){
            if ($Lead->create($lead)){
                return response()->json(['created' => true, 'algo'=>'duplicado']);
            }else{
                return response()->json(['status' => false, 'msg' => 'datos erroneos']);
            }
        }else{
            return response()->json(['status' => false, 'msg' => 'Lead duplicado']);
        }
    }
    
    public function viewimport(){
        
        return view('admin.leadsv2.index');
    }
    
    public function import(){
        //validar estructura del archivo
        
        //importar el archivo
        
        
    }
    
    public function is_duplicated($lead)
    {
        $Lead = new Lead();
        
        $a = $lead['document_nro'];
        $b = $lead['lead_id'];
        $c = $lead['website_lead_id'];
        $d = $lead['email'];
        $e = $lead['mobile'];
        $f = $lead['phone'];
        
        $date = Carbon::now();
        $date = $date->subDay(2);
        $duplicated = $Lead
                        ->where('created_at', '>=', $date)
                        ->where(function($q) use ($a, $b, $c, $d, $e, $f)  {
                            $q->OrWhere('document_nro', '=', $a)
                            ->OrWhere('lead_id', '=',  $b)
                            ->OrWhere('website_lead_id', '=', $c)
                            ->OrWhere('email', '=',  $d)
                            ->OrWhere('mobile', '=',  $e)
                            ->OrWhere('phone', '=', $f);
                        })
                        ->get();
        if(count($duplicated)>0){
            return response()->json(['is_duplicated' => true, 'duplicates' => $duplicated]);
        }else{
            return response()->json(['is_duplicated' => false]);
        }
    }
}
