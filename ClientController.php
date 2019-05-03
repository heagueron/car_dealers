<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Empresa;
use App\Models\Recepcion;
use App\Models\Reason;
use App\Models\Client;
use App\Models\Document;
use App\Models\Estado;
use App\Models\Localidad;
use App\Models\Marital_status;
use App\Models\Origin;
use App\Models\Channel;
use App\Models\TypeEnvironment;
use App\Models\Occupation;
use App\Models\Charge;
use App\Models\Industry;
use App\Models\Category;
use App\Models\Client_contact;
use App\Models\Client_address;
use App\Models\ClientEnvironment;
use App\Models\Client_type_mails;
use App\Models\Client_type_phone;
use App\Models\Client_type_location;
use App\Models\Client_networks;
use App\Models\Client_mails;
use App\Models\ClientTypeDetail;
use App\Models\Pais;
use Carbon\Carbon;
use App\Role;
use Datatables;
use App\User;
use Auth;
use DB;

// para imagen manager
use Validator;
use File;
use Image;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Response;

class ClientController extends Controller
{
    public function load_cliente(Request $request){
         $con=$this->conexion_base_datos(\Auth::user()->id_empresa);
         $con->set_charset("utf8");
         $term = $request->input('q') ?: '';
         $sql = "SELECT Id, CONCAT(lower(ifnull(nombre, '')), ' ',lower(ifnull(apellido, '') ), ' / ',lower(ifnull(email, '')), ' / ',ifnull(telefono, '')) as full_name FROM clientes ";
         $sql .= "HAVING full_name LIKE '%".$term."%'";
         //$sql.="where nombre like '%".$term."%' or apellido like '%".$term."%' or email like '%".$term."%' or telefono like '%".$term."%' ";

        $datos_detalles=$con->query($sql);
        $clients=$datos_detalles->fetch_all(MYSQLI_ASSOC);

        $data = [];
        $terminos=explode(' ',$term);
        foreach ( $clients as $tag) {

            if($tag['full_name'] != null){
                foreach($terminos as $palabras){
                    if(strrpos($tag['full_name'],strtolower($palabras))){
                        $data[] = ['id' => $tag['Id'], 'text' => ucwords($tag['full_name']) ];
                    }
                }
            }
        }
        $data=$this->unique_multidim_array($data,'id');
        return json_encode($data);
    }

    public function searchs_email($email,$id){
      $cliente=$this->datos_clientes_email($email,$id);

       if (count($cliente)>0) {
           $datos=$cliente[0]['nombre']." ".$cliente[0]['apellido'];
            return json_encode(['email' => $email,'error' => true, 'message' => 'Ya existe el cliente con el email: '.$email.' su nombre es <b>'.strtoupper($datos).'</b>']);
        }

    }

    protected function unique_multidim_array($array, $campo)  {
    $new = array();
        $exclude = array("");
        for ($i = 0; $i<=count($array)-1; $i++) {
            if (!in_array(trim($array[$i][$campo]) ,$exclude)) { $new[] = $array[$i]; $exclude[] = trim($array[$i][$campo]); }
        }

        return $new;
}

    public function searchs($id){
        $id=$id;
        $recepcion=Recepcion::where('client_id',$id)->where('status',1)->first();
        if(count($recepcion)==0){
            $data=$this->datos_clientes($id);
            $data['id_recepcion']=0;
        }else{
            $data=$this->datos_clientes($id);
            $data['id_recepcion']=$recepcion->id;
            $data['channel_id']=$recepcion->channel_id;
            $data['user_id']=$recepcion->user_id;
            $data['employee_id']=$recepcion->employee_id;
            $data['id_producto']=$recepcion->id_producto;
            $data['reason_id']=$recepcion->reason_id;

        }

        return json_encode($data);
    }

    protected function datos_clientes($id){
        $con=$this->conexion_base_datos(\Auth::user()->id_empresa);
        $con->set_charset("utf8");
        $sql="SELECT *  FROM clientes where Id='".$id."'";
        $datos_detalles=$con->query($sql);
        $clients=$datos_detalles->fetch_all(MYSQLI_ASSOC);
        return $dataCliente[]=array("id"=>$clients[0]['Id'],"first_name"=>$clients[0]['nombre'],"last_name"=>$clients[0]['apellido'],
        "telefono"=>$clients[0]['telefono'],"email"=>$clients[0]['email']);
    }


    protected function datos_clientes_email($email,$id){
        $con=$this->conexion_base_datos(\Auth::user()->id_empresa);
        $con->set_charset("utf8");
        $sql="SELECT *  FROM clientes where id<>'".$id."' and email='".$email."'";
        $datos_detalles=$con->query($sql);
        return $clients=$datos_detalles->fetch_all(MYSQLI_ASSOC);

    }

    protected function conexion_base_datos($id_compania){
         $company = Empresa::where('id',$id_compania)->first();
         $database= $company->database_name;
         $username=$company->database_user;
         $pass=$company->database_pass;
         $dsn="localhost";
         return $conecion = mysqli_connect($dsn, $username, $pass,$database);
    }
/**
 *       El codigo de Recepcion llega hasta acá
 *
**/

    /**
     *
     * Inicio Laravel Clientes
     *
     **/


    /**
     * @description Muestra el listado de clientes
     *
     * @author Sandy Rodriguez
     *
     * @param void
     *
     * @return view
     * */
     public function index(){
        if( !Auth::user()->hasRole('root') && !Auth::user()->can('clientes-*') )  abort(403);
        $estado = new Estado();
        $province = new Localidad();
        $document = new Document();
        $marital = new Marital_status();
        $origin = new Origin();
        $channel = new Channel();
        $client_relation = new TypeEnvironment();
        $occupation = new Occupation();
        $type_location = new Client_type_location();
        $client_type_mails = new Client_type_mails();
        $client_type_phone = new Client_type_phone();
        $client_contact = new Client_contact();
        $pais = new Pais();
        $charge = new Charge();
        $cliente = new Client();

        $documents = $document->get()->pluck('name', 'id');
        $states = array_pluck($estado->listar('13')->toArray(), 'nombre', 'id');
        $marital_status = $marital->orderBy('order')->get()->pluck('name', 'id');
        $origins = $origin->get()->pluck('origin', 'id');
        $channels = $channel->get()->pluck('channel', 'id');
        $client_relations = $client_relation->orderBy('order')->get()->pluck('name','id');
        $occupations = $occupation->orderBy('order')->get()->pluck('name', 'id');
        $type_locations = $type_location->orderBy('order')->get()->pluck('name', 'id');
        $client_type_mails = $client_type_mails->orderBy('order')->get()->pluck('name', 'id');
        $client_type_phone = $client_type_phone->orderBy('order')->get()->pluck('name', 'id');
        $pais = $pais->where('phone_code','>','0')->get()->pluck('phone_code','id');
        $charges = $charge->orderBy('order')->get()->pluck('name', 'id');
        
        return view('admin.clients.index')
            ->with('documents',$documents)
            ->with('states', $states)
            ->with('marital_status', $marital_status)
            ->with('origins', $origins)
            ->with('channels', $channels)
            ->with('client_relations', $client_relations)
            ->with('province',$province)
            ->with('occupations', $occupations)
            ->with('type_locations',$type_locations)
            ->with('client_type_mails',$client_type_mails)
            ->with('client_type_phone',$client_type_phone)
            ->with('client_contact',$client_contact)
            ->with('charges', $charges)
            ->with('pais', $pais);
     }


     /**
     * @description Muestra el formulario de registro de un cliente
     *
     * @author Sandy Rodriguez
     *
     * @param void
     *
     * @return view
     * */
    public function form($id = null){

        $estado = new Estado();
        $province = new Localidad();
        $document = new Document();
        $marital = new Marital_status();
        $origin = new Origin();
        $channel = new Channel();
        $client_relation = new TypeEnvironment();
        $occupation = new Occupation();
        $type_location = new Client_type_location();
        $client_type_mails = new Client_type_mails();
        $client_type_phone = new Client_type_phone();
        $client_contact = new Client_contact();
        $pais = new Pais();
        $charge = new Charge();
        $cliente = new Client();
        $client = $cliente->find($id);

        $documents = $document->get()->pluck('name', 'id');
        $states = array_pluck($estado->listar('13')->toArray(), 'nombre', 'id');
        $marital_status = $marital->orderBy('order')->get()->pluck('name', 'id');
        $origins = $origin->get()->pluck('origin', 'id');
        $channels = $channel->get()->pluck('channel', 'id');
        $client_relations = $client_relation->orderBy('order')->get()->pluck('name','id');
        $occupations = $occupation->orderBy('order')->get()->pluck('name', 'id');
        $type_locations = $type_location->orderBy('order')->get()->pluck('name', 'id');
        $client_type_mails = $client_type_mails->orderBy('order')->get()->pluck('name', 'id');
        $client_type_phone = $client_type_phone->orderBy('order')->get()->pluck('name', 'id');
        $pais = $pais->where('phone_code','>','0')->get()->pluck('phone_code','id');
        $charges = $charge->orderBy('order')->get()->pluck('name', 'id');

        return view('admin.clients.clients_form')
            ->with('documents',$documents)
            ->with('states', $states)
            ->with('marital_status', $marital_status)
            ->with('origins', $origins)
            ->with('channels', $channels)
            ->with('client_relations', $client_relations)
            ->with('province',$province)
            ->with('occupations', $occupations)
            ->with('type_locations',$type_locations)
            ->with('client_type_mails',$client_type_mails)
            ->with('client_type_phone',$client_type_phone)
            ->with('client_contact',$client_contact)
            ->with('charges', $charges)
            ->with('pais', $pais)
            ->with('client', $client);
    }

    /**
     * @description permite crear clientes
     *
     * @author Sandy Rodriguez
     *
     * @param request
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request){

        $registro = new Client();
        if(isset($request->client_id)){
            $registro = $registro->find($request->client_id);
        }
        
        $registro->id_origin = $request->id_origin;
        $registro->id_contact = $request->id_contact;
        $registro->person_type = $request->person_type;
        $registro->id_channel = $request->id_channel;
        $registro->id_gender = $request->id_gender;
        $registro->resale = $request->resale;
        $registro->name  = ucwords(strtolower($request->name));
        $registro->last_name = ucwords(strtolower($request->last_name));
        if ( !$request->id_document )
            $registro->id_document = 1;
        else
            $registro->id_document = $request->id_document;
        $registro->document_nro = preg_replace('[\s+]', '', $request->document_nro);
        if ( $request->cuit )
            $registro->cuit = preg_replace('[\s+]', '', $request->cuit);
        
        $registro->id_iva   = $request->id_iva;
        $registro->id_type_invoice = $request->id_type_invoice;
        $registro->id_occupation = $request->id_occupation;
        $registro->id_charge = $request->id_charge;
        $registro->id_industry = $request->id_industry;
        $registro->id_maritals_status = $request->marital_status_id;
        $registro->id_user=Auth::user()->id;
        $registro->birthday = $request->birthday;
        if ( $request->id_ages_range )
            $registro->id_ages_range = $request->id_ages_range;
        $registro->id_module = $request->id_module;
        $registro->id_nationality = $request->id_nationality;
        $registro->company = ucwords(strtolower($request->company)); //empresa en la que trabaja el cliente
        $registro->id_company = Auth::user()->id_empresa; //concecionaria a la que pertenece el cliente
        //$registro->grossincome = $request->grossincome;
        $registro->id_behavior = $request->id_behavior;
        $registro->id_attitude = $request->id_attitude;
        $registro->id_category = $request->id_category;
        $registro->id_number_employees = $request->id_number_employees;
        //$registro->client_type = $request->client_type_id;
        
        $registro->save();

        //if ($request->has('client_type_id')) {
            //Resetear
        $client_types = new ClientTypeDetail();
            $deleted = $client_types
                ->where('client_id', $registro->id)
                ->delete();
            
            //Asignar
                if ( ! $request->client_type_id ) {
                    $type_cli = Db::table('sales')
                            ->where('id_client', $registro->id)
                            ->get();
                    $type_client = 1;
                    if ( count($type_cli) )
                        $type_client = 2;

                    //$client_types->insert(['client_id' => $registro->id, 'client_type_id' => $type_client]);
                    $client_types->client_id = $registro->id;
                    $client_types->client_type_id = $type_client;
                    $client_types->save();

                } else {
                    foreach ($request->client_type_id as $type) {
                        $clienttypes = new ClientTypeDetail();
                        $reg = $clienttypes
                                ->where('client_id', $registro->id)
                                ->where('client_type_id', $type)
                                ->get();

                        if ( ! count($reg) ) {
                            /*
                            $client_types
                                ->insert(['client_id' => $registro->id, 'client_type_id' => $type]);
                            */
                            $clienttypes->client_id = $registro->id;
                            $clienttypes->client_type_id = $type;
                            $clienttypes->save();
                        }
                    }
                }
        //}

        /*$contact = new Client_contact();
        if($request->contact_id){
            $contact=$contact->find($request->contact_id);
        }
        $contact->client_id = $registro->id;
        $contact->area_code = $request->cel_area_code;
        $contact->phone = $request->cel;
        $contact->id_type_phone = 1;
        $contact->save();

        $contact2 = new Client_contact();
        if($request->home_area_code){
            if($request->home_contact_id)
                $contact2 = $contact2->find($request->home_contact_id);
            $contact2->client_id = $registro->id;
            $contact2->area_code = $request->home_area_code;
            $contact2->phone = $request->home_phone;
            $contact2->id_type_phone = 2;
            $contact2->save();
        }
        if($request->email_personal){
            $client_mails = new Client_mails();
            if($request->mail_contact_id)
                $client_mails = $client_mails->find($request->mail_contact_id);
            $client_mails->client_id = $registro->id;
            $client_mails->id_type_mail = 1;
            $client_mails->mail = $request->email_personal;
            $client_mails->principal = 1;
            $client_mails->save();
        }*/

         return response()->json([
             'status' => true,
             'controller'  => 'clients',
             'title'  => 'Operación Exitosa!',
             'text' => 'Los datos del Cliente fueron registrado correctamente.',
             'type' => 'success',
             'client_id' => $registro->id
         ],200);
    }

    /**
      * @description Elimina el dato seleccionado
      *
      * @author Eliecer Cedano
      *
      * @param $id
      *
      * @return json
      * */
    public function delete_client ( $id ) {
        $model = Client::find($id);

        if( !Auth::user()->ability('root', 'client-delete') ){

            return response()->json([
                'status' => false,
                'title'  => 'Oops!',
                'text' => 'Ocurrio un error al intentar eliminar  <b>'.$model->last_name.'</b>. Existen registros vinculados a este dato o no posee PERMISO...',
                'type' => 'error'
            ],200);

        } else {
            if ( Db::table('budgets')->where('id_client', $model->id)->count() )
                return response()->json([
                    'status' => false,
                    'title'  => 'Oops!',
                    'text' => 'El Contacto <b>'.$model->last_name.' '.$model->name.'</b> tiene Presupuestos asociados...',
                    'type' => 'error'
                ],200);
            else {
                if ( Db::table('sales')->where('id_client', $model->id)->count() )
                    return response()->json([
                        'status' => false,
                        'title'  => 'Oops!',
                        'text' => 'El Contacto <b>'.$model->last_name.' '.$model->name.'</b> tiene Ventas asociadas...',
                        'type' => 'error'
                    ],200);
                else {
                    $model->delete();
                    return response()->json([
                        'status' => true,
                        'title'  => 'Eliminado!',
                        'text' => 'El registro de <b>'.$model->last_name.'</b> ha sido eliminado correctamente!',
                        'type' => 'success'
                    ],200);
                }
            }
        }
    }


     /**
     * @description guarda los datos de contacto
     *
     * @author Sandy Rodriguez
     *
     * @param request
     *
     * @return response json
     * */
    public function client_contacts(Request $request){
        $contact = new Client_contact();
        $exist = $contact
                    ->where('phone', $request->phone)
                    ->where('area_code', $request->area_code)
                    ->get();
        if (count($exist)){
            if ($exist[0]->client_id != $request->client_id) {
                return response()->json([
                    'status' => false,
                    'title'  => 'Teléfono Existe!',
                    'text' => 'El Teléfono <b>'.$request->phone.'</b> pertenece a otro usuario.',
                    'type' => 'error'
                ],200);          
            } else {
                $contact = $contact->find($exist[0]->id);
            }
        }

        if(isset($request->phone_contact_id)){
            $contact = $contact->find($request->phone_contact_id);
        }

        if ( $request->id_type_phone == 'J' ) {
            $exist = Db::table('client_type_phone')
                  ->where('name', 'Trabajo')
                  ->get();
              if ( count( $exist ) ) {
                $type_phone = $exist[0]->id;
              } else {
                $type_phone = Db::table('client_type_phone')->insertGetId(['name', 'Trabajo']);
              }
        } else {
            $type_phone = ($request->id_type_phone)?$request->id_type_phone:1;
        }

        $contact->client_id = $request->client_id;
        $contact->id_paises = $request->id_paises;
        $contact->area_code = $request->area_code;
        $contact->phone = $request->phone;
        $contact->id_type_phone = $type_phone;
        $contact->ext_phone = $request->ext_phone;
        if(!isset($request->wsp)){
            $request->wsp = 'NO';
        }
        $contact->wsp = $request->wsp;
        $contact->principal = $request->principal;
        $contact->save();

        return response()->json([
            'status' => true,
            'title'  => 'Operación Exitosa!',
            'text' => 'El Contacto Telefónico ha sido registrado satisfactoriamente.',
            'type' => 'success'
        ],200);
    }

    /**
     * @description Busca un cliente por el email
     *
     * @author Sandy Rodriguez
     *
     * @param String email
     *
     * @return Json
     * **/
    public function search_by_id($id){
        
        $clients = new Client();
        $client = $clients->find($id);
        
        //$client = Client::findorfail($id);
        $cell = $client->client_contact()->orderBy('id_type_phone', 'desc')->first();
        
        $country = '';
        if ( $cell ) {
            $countries = Db::table('paises')->select('phone_code')->where('id', $cell->id_paises)->first();
            if ($countries)
                $country = $countries->phone_code;
        $cell->country = $country;
        }
        
        $nationality = '';
        $countries = Db::table('paises')->select('nationality')->where('id', $client->id_nationality)->first();
        if ($countries)
            $nationality = $countries->nationality;
        $client->nationality = $nationality;

        return response()->json([
            'status' => true|false,
            'client'  => $client,
            'nationality'  => $nationality,
            'id' => $id,
            'cel_phone'  => $cell,
            //'cel_phone'  => $client->client_contact()->where('id_type_phone','=',1)->first(),
            //'home_phone'  => $client->client_contact()->where('id_type_phone','=',2)->first(),
            'mail' => $client->client_mails()->where('principal', 1)->first(),
            'type' => 'success'
        ],200);

    }

    /**
     * @description Busca un cliente por el email
     *
     * @author Sandy Rodriguez
     *
     * @param String email
     *
     * @return Json
     * **/
    public function search_by_email_personal($email_personal = null){
        $email_pers = new Client_mails();
        $email_personal1 = $email_pers->where('mail',$email_personal)->get();
        if (count($email_personal1)){
            $clients = new Client();
            $client = $clients->where('id', $email_personal1[0]->client_id)->first();

            return response()->json([
                 'status' => true|false,
                 'client'  => $client,
                 'type' => 'success'
             ],200);
        }
        return response()->json([
             'status' => true|false,
             'client'  => null,
             'type' => 'success'
         ],200);
         /*return response()->json([
             'status' => true|false,
             'name'  => $email_personal1->Client->name,
             'last_name'  => $email_personal1->Client->last_name,
             'type' => 'success'
         ],200);
         */

    }

    /**
     * @description busca un  cliente por el número de telefono
     *
     * @author Sandy Rodriguez
     *
     * @param String telefono
     *
     * @return json
     * */

    public function search_by_mobile($telefono = null){
        $mobile = new Client_contact();
        $mobile1 = $mobile->where('phone',$telefono)->first();
        return response()->json([
             'status' => true,
             'name'  => $mobile1->Client->name,
             'last_name'  => $mobile1->Client->last_name,
             'id'  => $mobile1->Client->id,
             'type' => 'success'
         ],200);

    }

    /**
     * @description consulta un cliente por el número del telefono de hogar
     *
     * @author Sandy Rodriguez
     *
     * @param
     *
     * @return json
     * */
    public function search_by_home_phone($telefono = null, $codigo = null){
        $home_phone = new Client_contact();
        $home_phone1 = $home_phone->where('phone',$telefono)->where('area_code',$codigo)->first();
        if (count($home_phone1))
            return response()->json([
                 'status' => true,
                 'name'  => $home_phone1->Client->name,
                 'last_name'  => $home_phone1->Client->last_name,
                 'id'  => $home_phone1->Client->id,
                 'type' => 'success'
             ], 200);

    }

    /**
     * @description consulta el numero de documento de un cliente
     *
     * @author Sandy Rodriguez
     *
     * @param
     *
     * @return json
     * */
    public function search_by_document_nro($documento = null){
        $id = $name = $last_name = '';
        $document_nro = new Client();
        $documento = preg_replace('[\s+]', '', $documento);
        $document_nro1 = $document_nro->where('document_nro',$documento)->first();
        if ( count ( $document_nro1 ) > 0 ) {
            $id   = $document_nro1->id;
            $name = $document_nro1->name;
            $last_name = $document_nro1->last_name;
        }
        return response()->json([
             'status' => true,
             'name'  => $name,
             'last_name' => $last_name,
             'id'  => $id,
             'type' => 'success'
         ],200);

    }

    /**
     * @description consulta la localidad de el cliente
     *
     * @author Sandy Rodriguez
     *
     * @param
     *
     * @return json
     */
    public function search_location($id = null){
         $client_address = new Client_address();
         $dato = $client_address->where('id',$id)->first();
         $country = array();
         if ( $dato->id_locality ){
            $country = $client_address
                    ->leftJoin('localidades', 'client_address.id_locality', 'localidades.id')
                    ->leftJoin('estados', 'localidades.id_estado', 'estados.id')
                    ->leftJoin('paises', 'paises.id', 'estados.id_pais')
                    ->select('estados.id as estado', 'paises.id as pais')
                    ->where('client_address.id', $id)
                    ->get();
         }

        return response()->json([
            'status' => true,
            'id' => $dato->id,
            'id_type' => $dato->id_type,
            'id_locality' => $dato->id_locality,
            'street'  => $dato->street,
            'number'  => $dato->number,
            'floor'   => $dato->floor,
            'district'=> $dato->district,
            'latitude'   => $dato->latitude,
            'longitude'  => $dato->longitude,
            'department' => $dato->department,
            'zipcode'  => $dato->zipcode,
            'google_place_id' => $dato->google_place_id,
            'country' => $country,
            'type' => 'success'
         ],200);

    }

    /**
     * @description consulta el numero de contacto de el cliente
     *
     * @author Sandy Rodriguez
     *
     * @param
     *
     * @return json
     */
    public function search_contact($id = null){
         $client_contact = new Client_contact();
         $dato = $client_contact->where('id',$id)->first();

        $country = '';
        if ( $dato ) {
            $countries = Db::table('paises')->select('phone_code')->where('id', $dato->id_paises)->first();
            if ($countries)
                $country = $countries->phone_code;
        }

        return response()->json([
            'status' => true,
            'id' => $dato->id,
            'id_paises' => $dato->id_paises,
            'area_code' => $dato->area_code,
            'phone'  => $dato->phone,
            'id_type_phone'  => $dato->id_type_phone,
            'ext_phone' => $dato->ext_phone,
            'principal' => $dato->principal,
            'wsp'=> $dato->wsp,
            'country'=> $country,
            'type' => 'success'
         ],200);

    }

    public function search_relation($id = null, $id2 = null){
        $mail  = null;
        $phone = null;
        $country = '';
         $client_relation = new ClientEnvironment();
         if ( $id2 ) {
            $dato = $client_relation
                ->leftJoin('clients', 'clients.id', 'client_environments.client_id2')
                ->leftJoin('client_roles', 'client_roles.id', 'client_environments.rol_id')
                ->leftJoin('type_environments', 'type_environments.id', 'client_environments.type_environment_id')
                ->where('client_environments.client_id',$id)
                ->where('client_environments.client_id2',$id2)
                ->select('rol_id', 'client_environments.id', 'client_environments.type_environment_id', 'client_roles.name as rol', 'client_roles.icon', 'type_environments.type_relation')
                ->first();

            $mail = Db::table('client_mails')
                    ->where('client_id', $id)
                    ->orderBy('principal')
                    ->orderBy('created_at', 'desc')
                    ->first();

            $phone = Db::table('client_contacts')
                    ->where('client_id', $id)
                    ->orderBy('principal')
                    ->orderBy('created_at', 'desc')
                    ->first();

        } else {
            $dato = $client_relation
                ->leftJoin('clients', 'clients.id', 'client_environments.client_id2')
                ->leftJoin('client_roles', 'client_roles.id', 'client_environments.rol_id')
                ->leftJoin('type_environments', 'type_environments.id', 'client_environments.type_environment_id')
                ->leftJoin('paises', 'paises.id', 'clients.id_nationality')
                ->where('client_environments.id',$id)
                ->select('client_environments.client_id2', 'clients.name', 'clients.last_name', 'clients.birthday', 'clients.id_ages_range', 'clients.id_document', 'clients.document_nro', 'clients.id_nationality', 'paises.nationality as nationality', 'clients.id_iva', 'clients.cuit', 'clients.id_type_invoice','rol_id', 'client_environments.id', 'client_environments.type_environment_id', 'client_roles.name as rol', 'client_roles.icon', 'type_environments.type_relation')
                ->first();

            if ( $dato ) {
                $mail = Db::table('client_mails')
                    ->where('client_id', $dato->client_id2)
                    ->orderBy('principal')
                    ->orderBy('created_at', 'desc')
                    ->first();

                $phone = Db::table('client_contacts')
                    ->where('client_id', $dato->client_id2)
                    ->orderBy('principal')
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ( $phone ) {
                    $countries = Db::table('paises')->select('phone_code')->where('id', $phone->id_paises)->first();
                    if ($countries)
                        $country = $countries->phone_code;
                }
            }
        }
        if ( $phone )
            $phone->country = $country;

        return response()->json([
            'status' => true,
            //'id' => $dato->id,
            'dato' => $dato,
            'mail' => $mail,
            'phone' => $phone,
            'country' => $country,
            'type' => 'success'
         ],200);

    }

     /**
     * @description consulta el correo de  clientes
     *
     * @author Sandy Rodriguez
     *
     * @param String telefono
     *
     * @return json
     */
    public function search_emails($id = null){
         $client_mails = new Client_mails();
         $dato = $client_mails->where('id',$id)->first();

        return response()->json([
            'status' => true,
            'id_type_mail' => $dato->id_type_mail,
            'mail' => $dato->mail,
            'principal'  => $dato->principal,
            'id'  => $dato->id,
            'type' => 'success'
         ],200);

    }
    
    /**
     * @description consulta las redes sociales
     *
     * @author Sandy Rodriguez
     *
     * @param String telefono
     *
     * @return json
     */
    public function search_networks($id = null){
         $clients = new Client();
         $dato = $clients->find($id)->client_networks->first();

        return response()->json([
            'status' => true,
            'networks' => $dato,
            'type' => 'success'
         ],200);

    }

    /**
     * @description Consulta los clientes por multiples campos
     *
     * @author Sandy Rodriguez
     *
     * @param String $request pueden ser documento, nombre, apellido
     *
     * @return json
     * */
    public function searchAjax(Request $request)
    {
        $clients = [];
        $client = new Client();
            if($request->has('q')){
                $search = $request->q;
                $clients = $client->where('id_company', Auth::user()->id_empresa)->where('name','LIKE',"%$search%")->orwhere('last_name','LIKE',"%$search%")->orwhere('document_nro','LIKE',"%$search%")->get()->toArray();
            }else{
                $fecha = date('Y-m-j H:i:s');
                $nuevafecha = strtotime ( '-1 month' , strtotime ( $fecha ) ) ;
                $nuevafecha = date ( 'Y-m-j' , $nuevafecha );
                $clients = $client->where('id_company', Auth::user()->id_empresa)->where('created_at','>=',$nuevafecha)->orwhere('updated_at','>=',$nuevafecha)->take(20)->get()->toArray();
            }
        //}


        return response()->json($clients);

    }
    
    /**
      * @description Muestra los datos de la tabla clientes
      *
      * @author Sandy Rodriguez 2017-12-19
      *
      * @param $request Request
      *
      * @return json
      * */
    public function grid ( Request $request ) {
        $filtro = (isset($request->filtro)) ? $request->filtro : 0;
        
        /** si no es ROOT aisgnar empresa segun usuario **/
        if( Auth::user()->hasRole('root') ){

            if ($request->has('id_empresa')) {
                $where = [['id_empresa', '=', $request->id_empresa]];

                if ($request->has('is_contacto'))
                //if ($request->has('is_contacto') && $request->is_contacto=="1" )
                $where = [ ['id_empresa', '=', $request->id_empresa], ['is_contacto', '=', '1'] ];

            }else
                $where = [];
        }else{
            $where = [ ['id_user', '=', Auth::user()->id] ];
        }
        $where = [];

        $client=new Client();
        //$dato = $client->with('Client_contact')->with('Channel')->with('Origin')->with('Occupation')->where($where);
        if ( $filtro == 2) { // Prospectos
            $dato = $client->with('Client_contact')
                            ->with('Channel')
                            ->with('Origin')
                            ->with('Occupation')
                            ->with('Category')
                            ->where($where)
                            ->where('id_company', Auth::user()->id_empresa)
                            ->whereIn('clients.id', function( $query ) {
                                 $query->select('client_id')
                                    ->from('client_type_details')
                                    ->join('client_type', 'client_type.id', 'client_type_details.client_type_id')
                                    ->where('client_type.name', 'like', '%Prospecto%');
                            })
                            ->orderBy('clients.last_name')
                            ->orderBy('clients.name');
        } else {
            if ( $filtro == 3 ) { // Clientes
                $dato = $client->with('Client_contact')
                            ->with('Channel')
                            ->with('Origin')
                            ->with('Occupation')
                            ->with('Category')
                            ->where($where)
                            ->where('id_company', Auth::user()->id_empresa)
                            ->whereIn('clients.id', function( $query ) {
                                 $query->select('client_id')
                                    ->from('client_type_details')
                                    ->join('client_type', 'client_type.id', 'client_type_details.client_type_id')
                                    ->where('client_type.name', 'like', '%Cliente%');
                            })
                            ->orderBy('clients.last_name')
                            ->orderBy('clients.name');
            } else {
                if ( $filtro == 4 ) { // Reseller
                    $dato = $client->with('Client_contact')
                            ->with('Channel')
                            ->with('Origin')
                            ->with('Occupation')
                            ->with('Category')
                            ->where($where)
                            ->where('id_company', Auth::user()->id_empresa)
                            ->whereIn('clients.id', function( $query ) {                                
                                $query->select('client_id')
                                    ->from('client_type_details')
                                    ->join('client_type', 'client_type.id', 'client_type_details.client_type_id')
                                    ->where('client_type.name', 'like', '%Reseller%');
                            })
                            ->orderBy('clients.last_name')
                            ->orderBy('clients.name');

                } else { // Todos los Contactos
                    $dato = $client->with('Client_contact')
                            ->with('Channel')
                            ->with('Origin')
                            ->with('Occupation')
                            ->with('Category')
                            ->where($where)
                            ->where('id_company', Auth::user()->id_empresa)
                            ->orderBy('clients.last_name')
                            ->orderBy('clients.name');
                }
            }
        }

         return Datatables::eloquent($dato)
            ->addColumn('foto', function ($dato) {

                $html = '';
                $foto = 'nofoto.jpg';
                if(  $dato->photo ) {
                    $foto = $dato->photo;                
                }

                $cad = '';

                $contact =  @DB::table('sales')->where([['id_client', '=', $dato->id]])->count();
                $cli = DB::table('client_type_details')
                            ->join('client_type', 'client_type.id', 'client_type_details.client_type_id')
                            ->select('client_id')
                            ->where('client_type_details.client_id', $dato->id)
                            ->where('client_type.name', 'like', '%Cliente%')
                            ->count();
                if ( $contact || $cli ) {
                    $img_class = 'profile-user-img-min-resale';
                } else {
                    $img_class = 'profile-user-img-min';             
                }
                /*
                $html .= '<div class="hide"><span id="btn_modal_foto" data-toggle="modal" data-target=".modalFoto" > </span></div><div class="text-center"><a href="#" data-id_client="'.$dato->id.'" data-photo="'.$dato->photo.'" class="foto">
                        <img class="'.$img_class.' img-responsive img-circle" src="'.asset('img/users/'.$foto).'" width="200px"   id="profileAvatar'.$dato->id.'" style="width: 30px !important; height: 30px !important;"  alt="Foto"></a>
                    </div>';
                */
                $html .= '<div class="dropdown-menu dropdown-anchor-left-center dropdown-has-anchor basic "  id="btn-user-options-'.@$dato->id.'">
                                <table class="bg-basic" style="width: 100%;  /* background: #333; color: whitesmoke; */ ">
                                    <tr>    <th style=" padding: 5px 0;" > <span class="dropdown-title ">Opciones</span> </th>   </tr>
                                </table>
                                <ul>
                                    <li>
                                          <a href="#" 
                                            data-client="'.@$dato->id.'"  
                                            class="panel-client" >
                                            <i class="fa fa-vcard-o"></i>  &nbsp; Ver Cliente
                                        </a>         
                                    </li>
                                </ul>
                            </div>


                            <a href="#"  data-dropdown="#btn-user-options-'.@$dato->id.'"  
                                data-client="'.@$dato->id.'"  
                                class="NO-panel-client hint--top-right "  
                                data-hint="Seleccione para ver detalles">
                                 <img class="'.$img_class.' img-responsive img-circle" src="'.asset('img/users/'.$foto).'" width="200px"   id="profileAvatar'.$dato->id.'" style="width: 30px !important; height: 30px !important;"  alt="Foto"></a>
                            </a>';                        

                return $html;

                $botones = '<div class="hide">'.$dato->status.'</div>
                            <div class="dropdown-menu dropdown-anchor-left-center dropdown-has-anchor basic "  id="btn-user-options-'.@$dato->id.'">
                                <table class="bg-basic" style="width: 100%;  /* background: #333; color: whitesmoke; */ ">
                                    <tr>    <th style=" padding: 5px 0;" > <span class="dropdown-title ">Opciones</span> </th>   </tr>
                                </table>
                                <ul>
                                    <li>
                                          <a href="#" 
                                            data-stock="'.@$dato->id.'" 
                                            data-client="'.@$dato->id.'"
                                            class="panel-client" >
                                            <i class="fa fa-car"></i>  &nbsp; Ver Producto
                                        </a>         
                                    </li>
                                </ul>
                            </div>

                            <a href="#"  data-dropdown="#btn-user-options-'.@$dato->id.'"  
                                data-stock="'.@$dato->id.'"    
                                data-client="'.@$dato->id.'"
                                class="NO-panel-client hint--top-right "  
                                data-hint="Ver Más Información">
                                 <i class="fa fa-eye"></i>
                            </a>';

                    return $botones;

            })
            ->addColumn('action', function ($dato) {

                $html = '';
                //$ruta = route('budgets', $dato->id);
                $ruta = url('budgets');
                $html .= "<a href='$ruta' data-iddata=".$dato->id." class='btn btn-xs btn-primary presupuesto hint--top' aria-label='Crear Presupuesto'><i class='fa fa-car'></i></a> ";
                if(  Auth::user()->ability('root', 'user-edit')  )
                    $html .= '<a href="#" data-iddata="'.$dato->id.'" class="btn btn-xs btn-primary editar hint--top" aria-label="Editar"><i class="fa fa-pencil"></i></a> ';

                if(  Auth::user()->ability('root', 'user-delete')  )
                    $html .= "<a href='#' data-iddata=".$dato->id." class='btn btn-xs btn-danger eliminar hint--top' aria-label='Eliminar'><i class='fa fa-trash'></i></a>";

                return $html;
            })
            ->addColumn('created_at', function ($dato) {
                $html = '';
                $data = Db::table('tasks')
                    ->leftJoin('users', 'tasks.id_employee', 'users.id')
                    ->leftJoin('events', 'events.id', 'tasks.id_event')
                    ->leftJoin('task_reasons', 'task_reasons.id', 'tasks.id_task_reason')
                    ->leftJoin('task_results', 'task_results.id', 'tasks.id_task_result')        
                    ->select('tasks.date', 'tasks.close_date', DB::raw("tasks.description AS transaction"), DB::raw("CONCAT(users.first_name, ' ', users.last_name) AS user"), 'events.description as event', 'events.icon', 'task_reasons.description as razon', 'task_results.description as resultado', 'tasks.id_budget', 'tasks.id_sale', 'tasks.is_closed', 'tasks.created_at') 
                    ->where('tasks.id_client', $dato->id)
                    ->where('tasks.is_closed', 1)
                    ->where('tasks.id_empresa', Auth::user()->id_empresa)
                    ->orderBy('tasks.date', 'DESC')
                    ->take(1)
                    ->get();

                if ( count ($data) ) {
                    if ( $data[0]->close_date ){
                            $fecha_cierre = $data[0]->close_date;
                            //$fecha_cierre = substr($fecha_cierre, 8,2).'/'.$substr($fecha_cierre,5,2).'/'.substr($fecha_cierre,0,4).' '.substr($fecha_cierre,11,5);
                            $tooltip = ', Cerrada: ' .$fecha_cierre.', '. $data[0]->resultado;
                    } else {
                        $tooltip = ', Pendiente';
                    }

                    $html .= ' <span class="hint--top hint--large" aria-label="Fecha: '. $data[0]->created_at. ', '. $data[0]->user . $tooltip.' "><i class=" '.$data[0]->icon.'"></i></span> ' . Carbon::createFromFormat('Y-m-d H:i:s', $data[0]->created_at )->diffForHumans();
                    
                    //$html = ' <span class="hint--top-rigth" aria-label="Fecha: '. $data[0]->created_at. ' "><i class=" '.$data[0]->icon.'"></i></span> ';
                    
                }
                return $html;
            })
            /*
            ->editColumn('created_at', function ($dato) {
                    //if($dato->created_at == null) return '0';
                    return ' <span class="hint--top-rigth" aria-label="'. $dato->created_at .'"><i class="fa fa-info-circle"></i></span> ' . Carbon::createFromFormat('Y-m-d H:i:s', $dato->created_at )->diffForHumans();
            })
            */
            ->addColumn('nombres', function ($dato) {                    
                    if($dato->person_type!='J'){
                        $name = ucwords(strtolower($dato->last_name)) .' '. ucwords(strtolower($dato->name));
                    }else{
                        $name = ucwords(strtolower($dato->name));
                    }

                    $cad = '<div class="hide">' . $name . '</div>' . $name ;
                    
                    return $cad;
            })
            ->editColumn('canales', function ($dato) {

                /*
                $html = '';
                $html .= @$dato->origin->origin .' <span  class="hint--top-rigth" aria-label="'. @$dato->channel->channel .'"><i class="fa fa-'. @$dato->channel->icon .'"></i></span>';
                */

                //$client         = Client::findorfail($dato->id);
                $html_keys      = '';

                if ( $dato->id_origin != null ){ //Origin set
                        $origin    = Origin::findorfail($dato->id_origin);
                        $html_keys  .= '<span class ="panel-menu_origins hint--top" 
                                            data-hint="Origen: '.$origin->origin.'">
                                            <i class="'.$origin->icon.'"></i>
                                        </span>&nbsp;';
                    } else { //Origin not set
                    
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
                        foreach ($origin_options as $origin_option) {
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
                        foreach ($origin_options as $origin_option) {
                            $html_keys  .= 
                            '<div class="dropdown-menu basic seller-origin-submenu"   
                                id      ="budget'.@$dato->id.'_origen'.@$origin_option->id.'" 
                                style   ="height: 200px; width: 50px overflow: auto;" >
                                
                                <table class="bg-basic" style="width: 100%;  /* background: #333; color: whitesmoke; */ ">
                                    <tr>  <th style=" padding: 5px 0;" > <span class="dropdown-title ">Opciones</span> </th>   </tr>
                                </table>
                                <ul>';
                          
                            //Child origin options
                            $child_options = DB::table('origins')->where( 'id_parent', $origin_option->id)->orderBy('id', 'ASC')->get();
                            foreach ($child_options as $child_option) {
                                $html_keys  .=
                                '<li>
                                    <a href="#"  onclick="save_contact_origin('.@$dato->id.', '.@$child_option->id.')" 
                                        data-budget="'.@$dato->id.'"    
                                        data-origin="'.@$child_option->id.'"
                                        data-client="'.@$dato->id_client.'"
                                        class="panel-child_origin >&nbsp; 
                                        <i class="'.@$child_option->icon.'"></i>  &nbsp;&nbsp; '.@$child_option->origin.'
                                    </a>
                                </li>';
                            }
                            $html_keys  .= '</ul></div>';
                        }
                        
                        //////////////////
                        //Icon shown on datatable cell: 
                        $html_keys  .= 
                            '<a href="#"  
                                data-dropdown   = "#keys-origins-options-'.@$dato->id.'"  
                                data-budget     = "'.@$dato->id.'"  
                                class           = "panel-menu_origins hint--top "  
                                data-hint       = "Origen: no especificado">
                                <i class="fa fa-circle"></i>
                                </a>&nbsp;'; 
                        
                    }
                    
                    
                    if ( $dato->id_channel != null ) {
                        $channel    = Channel::findorfail($dato->id_channel);
                        $html_keys  .= '<span class ="panel-menu_channels2 hint--top" 
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
                                    <a href="#" onclick="save_contact_channel('.@$dato->id.', '.@$channel_option->id.')"  
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
                                    class="hint--top "  
                                    data-hint="Canal: no especificado">
                                    <i class="fa fa-circle"></i>
                            </a>&nbsp;';     
                    }
                    

                    return $html_keys;

                ///return $html;
            })
            ->editColumn('transaccions', function ($dato) {

                $html = '';
                    //$html .= ' <span class="hint--top-rigth" aria-label="presupuesto" ><i class="fa fa-car" style="color: #007fff;"></i></span> /<span class="hint--top-rigth" aria-label="Venta"><i class="fa fa-car" style="color: #ff8000;"></i></span>  ';
                    
                $contact =  @DB::table('budgets')->where([['id_client', '=', $dato->id]])->count();
                $color = '#007fff';

                $html .= ' <span class="hint--top" aria-label="'.$contact.' Presupuestos" ><i class="fa fa-car" style="color: '.$color.';"></i></span>';
                    
                $contact =  @DB::table('sales')->where([['id_client', '=', $dato->id]])->count();
                $cad = '';
                if ( $contact ) {
                    $sales_types = @DB::table('sales')
                        ->leftJoin('type_sales', 'sales.id_type_sale', 'type_sales.id')
                        ->where([['id_client', '=', $dato->id]])
                        ->select('type_sales.id', 'type_sales.name') 
                        ->get();
                    
                    foreach ($sales_types as $sale) {
                        $cad.= $sale->name . '. ';
                    }
                    
                }
                $color = '#ff8000';

                $html .= '  <span class="hint--top" aria-label="'.$contact.' Ventas. '.$cad.'"><i class="fa fa-car" style="color:'.$color.';"></i></span>  ';

                return $html;
            })
            ->editColumn('contactos', function ($dato) {
                $html = '';
                // Hogar
                $contact = @$dato->client_contact()
                        ->leftJoin('client_type_phone', 'client_type_phone.id', 'client_contacts.id_type_phone')
                        ->where('client_type_phone.name', 'like', '%Hogar%')
                        ->select('area_code', 'phone', 'ext_phone', 'client_type_phone.name')
                        ->first();
                if ( ! is_null ( $contact ) ) {
                    $phone  = $contact->area_code. '-'. $contact->phone;
                    if ( $contact->ext_phone ) 
                        $phone .= $contact->ext_phone;

                    $html .= '<a href="tel:'.$contact->area_code.$contact->phone.'" ><span class="hint--top" aria-label="'.$contact->name.': '.$phone .'"><i class="fa fa-phone"></i></span> </a> ';

                }

                // Trabajo
                $contact = @$dato->client_contact()
                        ->leftJoin('client_type_phone', 'client_type_phone.id', 'client_contacts.id_type_phone')
                        ->where('client_type_phone.name', 'like', '%Trabajo%')
                        ->select('area_code', 'phone', 'ext_phone', 'client_type_phone.name')
                        ->first();
                if ( ! is_null ( $contact ) ) {
                    $phone  = $contact->area_code. '-'. $contact->phone;
                    if ( $contact->ext_phone ) 
                        $phone .= $contact->ext_phone;

                    $html .= '<a href="tel:'.$contact->area_code.$contact->phone.'" ><span class="hint--top" aria-label="'. $contact->name.': '.$phone .'"><i class="fa fa-phone-square" style="color: #FF0000;"></i></span></a>  ';

                }
                // Celular
                $contact = @$dato->client_contact()
                        ->leftJoin('client_type_phone', 'client_type_phone.id', 'client_contacts.id_type_phone')
                        ->where('client_type_phone.name', 'like', '%Movil%')
                        ->select('client_type_phone.name','area_code', 'phone', 'ext_phone')
                        ->first();
                if ( ! is_null ( $contact ) ) {
                    $phone  = $contact->name. ': ' . $contact->area_code. '-'. $contact->phone;
                    if ( $contact->wsp == 'SI' ) 
                        $html .= '<span class="hint--top" aria-label="'. $phone .'"><i class="fa fa-mobile" style="color: #FF0000;"></i></span> ';
                    else
                        $html .= '<a href="tel:'.$contact->area_code.$contact->phone.'" ><span class="hint--top" aria-label="'. $phone .'"><i class="fa fa-whatsapp" style="color: #2fff0f;"></i></span></a>  ';
                }

                // Email Laboral
                $contact = @$dato->client_mails()
                        ->leftJoin('client_type_mails', 'client_type_mails.id', 'client_mails.id_type_mail')
                        ->where('client_type_mails.name', 'like', '%Laboral%')
                        ->select('client_type_mails.name','mail')
                        ->first();
                if ( ! is_null ( $contact ) ) {
                    $html .= '<a href="mailto:'.$contact->mail.'"><span class="hint--top" aria-label="'. $contact->name.': '. $contact->mail .'"><i class="fa fa-envelope-o"></i></span></a>  ';

                }
                // Email
                $contact = @$dato->client_mails()
                        ->leftJoin('client_type_mails', 'client_type_mails.id', 'client_mails.id_type_mail')
                        ->where('client_type_mails.name', 'not like', '%Laboral%')
                        ->select('client_type_mails.name', 'mail')
                        ->first();
                if ( ! is_null ( $contact ) ) {
                    $html .= '<a href="mailto:'.$contact->mail.'"><span class="hint--top" aria-label="'. $contact->name.': '. $contact->mail .'"><i class="fa fa-envelope-o" style="color: #FF0000;"></i></span></a>  ';

                }

                // Dirección
                $contact = @$dato->Client_address()
                        ->leftJoin('localidades', 'localidades.id', 'client_address.id_locality')
                        ->leftJoin('estados', 'estados.id', 'localidades.id_estado')
                        ->leftJoin('paises', 'paises.id', 'estados.id_pais')
                        ->select('client_address.*', 'localidades.nombre as locality', 'localidades.codigopostal', 'estados.nombre as province', 'paises.nombre as country')
                        ->orderBy('id')
                        ->first();
                        
                if ( ! is_null ( $contact ) ) {
                    $address = '';
                    if ( $contact->street )                        
                        $address .= 'Calle ' . ucwords(strtolower($contact->street));
                    if ( $contact->number )
                        $address .= '  Nro. ' . $contact->number;
                    if ( $contact->department )
                        $address .= '  Depto. ' . $contact->department;
                    if ( $contact->floor )
                        $address .= '  Piso ' . $contact->floor;
                    if ( $contact->district )
                        $address .= '   ' . ucwords(strtolower($contact->district));
                    if ( $address )
                        $address .= '.   ';
                    if ( $contact->locality )
                        $address .= '   ' . ucwords(strtolower($contact->locality));
                    if ( $contact->province )
                        $address .= '   ' . ucwords(strtolower($contact->province));
                    if ( $contact->country )
                        $address .= ',  ' . ucwords(strtolower($contact->country));
                    if ( $contact->codigopostal )
                        $address .= '.  Código Postal: ' . $contact->codigopostal;
                   
                   $html .= '<span class="hint--top" aria-label="'. $address .'"><i class="fa fa-map-marker"></i></span>  ';

                }

                // Redes Sociales
                $contact = @$dato->Client_networks()
                        ->select('client_networks.*')
                        ->first();

                if ( ! is_null ( $contact ) ) {
                    $social = '';
                    if ( $contact->twitter )
                        $html .=  '<a href="http://www.twitter.com/'.$contact->twitter.'" target="_blank"> <span class="hint--top" aria-label="'. $contact->twitter .'"><i class="fa fa-twitter" style="color: #1299D7 !important;"></i></span></a>  ';

                    if ( $contact->facebook )
                        $html .=  '<a href="http://www.facebook.com/'.$contact->facebook.'" target="_blank"> <span class="hint--top" aria-label="'. $contact->facebook .'"><i class="fa fa-facebook-square" style="color: #0646EB !important;"></i></span> </a> ';

                    if ( $contact->linkedin )
                        $html .=  '<a href="http://www.linkedin.com/in/'.$contact->linkedin.'" target="_blank"> <span class="hint--top" aria-label="'. $contact->linkedin .'"><i class="fa fa-linkedin" style="color: #0934AF !important;"></i></span> </a> ';

                    if ( $contact->instagram )
                        $html .=  '<a href="http://www.instagram.com/'.$contact->instagram.'" target="_blank"> <span class="hint--top" aria-label="'. $contact->instagram .'"><i class="fa fa-instagram" style="color: #9B3402 !important;"></i></span>  </a>';

                    if ( $contact->google )
                        $html .=  '<a href="https://www.plus.google.com/s/'.$contact->google.'/top" target="_blank"> <span class="hint--top" aria-label="'. $contact->google .'"><i class="fa fa-google-plus-square" style="color: #F32F32 !important;"></i></span>  </a>';



                }
                        
                    
                return $html;
            })
            ->editColumn('category', function($dato){
                $html='';

                $categoria = DB::table('client_categories')
                            ->join('client_category_conditions', 'client_categories.id', 'client_category_conditions.id_category')
                            ->join('client_category_details', 'client_category_conditions.id', 'client_category_details.id_category_condition')
                            ->where('client_category_details.client_id', $dato->id)
                            ->orderBy('client_category_details.id_category_condition', 'desc')
                            ->select('client_categories.id', 'client_categories.category', 'client_categories.icon')
                            ->first();
                if ( count ( $categoria ) )
                    $conditions = Db::table('client_category_details')
                          ->leftJoin('client_category_conditions', 'client_category_details.id_category_condition', 'client_category_conditions.id')
                          ->where('client_category_details.client_id', $dato->id)
                          ->select('client_category_conditions.name as condition', 'client_category_conditions.id', 'client_category_conditions.id_category')
                          ->get();                          
                else
                    $conditions = array();
                
                $cad = '<a href="#" onclick="load_contact_category('.@$dato->id.')" class="text-gray hint--top" aria-label="';
                if ( count( $categoria ) ) {
                    $img = asset('asset/admin/img/categories/');
                    $cad .= $categoria->category;
                    $cond = $conditions;
                    $categ = $categoria->id;
                                
                    if ( count( $cond ) ) {
                        $cad .= ': ';
                        foreach ($cond as $entry) {
                            if ( $entry->id_category == $categ )
                                $cad .= $entry->condition . ', ';
                        }
                        $cad = substr($cad, 0, strlen($cad) - 2);
                    }
                    $cad .= '">';
                    $cad .= '<img alt="'.$categoria->category.'"';
                    $cad .= ' src="'.$img.'/'.$categoria->icon.'.png" ';
                    $cad .= 'style="width: 20px !important; height: 20px !important;" /></a>';

                } else {
                    $cad .= 'Sin Categoria"><i class="fa fa-star fa-lg" aria-hidden="true"></i></a>';
                }
                $html = $cad.'&nbsp;';


                //cargar empatia del cliente

                    // Cedano
                        $average = @DB::table('empathy')
                            ->join('empathy_user_client', 'empathy_user_client.id_empathy', 'empathy.id')
                            ->select(DB::raw('avg(total) prom'))
                            ->where('id_client', $dato->id)
                            ->first();

                        $default = @DB::table('empathy')->where('total', round( $average->prom) )
                                ->first();

                        $all = @DB::table('empathy')->orderBy('order', 'DESC')->get();

                        $li = '';
                        $result = array();
                        foreach ($all as $reg) {
                            $n = 0;
                            $det = DB::table('empathy_user_client')
                                ->where('id_client', $dato->id)
                                ->where('id_empathy', $reg->id)
                                ->get();
                            if ( count ( $det ) ) {
                                $n = count ( $det );                                
                            }

                            $li.= '<li style="padding: 1px;"> &nbsp;&nbsp;&nbsp;
                                    <span >
                                        <i class="fa '.$reg->icon.' fa-lg" style="color: #'.$reg->color.';"></i>  &nbsp;&nbsp; ( '.$n.' )'.$reg->name.'
                                    </span>
                                </li>';
                        }

                        $html .= '<div class="dropdown-menu dropdown-anchor-right-top dropdown-has-anchor basic "  id="btn-empathie-options-'.@$dato->id.'" style="z-index: 99999;">
                                <table class="bg-basic" style="width: 100%;  /* background: #333; color: whitesmoke; */ ">
                                    <tr>    <th style=" padding: 5px 0;" > <span class="dropdown-title "> Empatía Cliente <span class="hint--top" data-hint="Empatía General de los Usuarios hacia el Cliente"><i class="fa fa-info-circle"></i></span>  </span> </th>   </tr>
                                </table>
                                <ul >   '.$li.'  </ul>
                            </div>

                            <a href="#"  data-dropdown="#btn-empathie-options-'.@$dato->id.'"   
                                class="hint--top "  
                                data-hint="'. (isset($default->name) ? $default->name : 'No posee Empatía registrada') .'">

                                 <span class="hint--top" data-hint="" > <i class="fa '.(isset($default->icon) ? $default->icon : 'fa-meh-o').' fa-lg" style="color:#'.(isset($default->color) ? $default->color : 'b5bbc8').'"></i> </span>
                            </a>';

                
                return $html;
            })

            ->rawColumns([ 'foto', 'created_at', 'canales', 'action', 'contactos', 'category', 'transaccions', 'nombres'])
            ->make(true);
    }

    public function grid_relations($client = null)
     {
         /** si no es ROOT aisgnar empresa segun usuario **/
         if( Auth::user()->hasRole('root') ){
             $where = [];
         }else{
             $where = [ ['id_empresa', '=', Auth::user()->id_empresa] ];
         }

         $client_contact=new Client();
         //$dato = $client_contact->find($client)->client_relations();

         $dato = Db::table('client_environments')
                    ->leftJoin('clients', 'clients.id', 'client_environments.client_id2')
                    ->leftJoin('type_environments', 'client_environments.type_environment_id', 'type_environments.id')
                    ->select('client_environments.id', 'type_environments.name as relation', 'clients.name', 'clients.last_name', 'clients.birthday', 'clients.created_at', 'clients.updated_at')
                    ->where('client_environments.client_id', $client)
                    ->get();

         return Datatables::of($dato)
            ->addColumn('relation', function($dato){
                return $dato->relation;
            })
            ->addColumn('names', function($dato){
                //$client_type_phone = new Client_type_phone();
                //$Client_type_phone = $client_type_phone->find($dato->id_type_phone);
                //return $Client_type_phone->name;
                
                return $dato->name.' '.$dato->last_name;
            })
            ->addColumn('birthday', function($dato){
                return $dato->birthday;
            })
            ->addColumn('action', function ($dato) {
                 $html = '';
                 if(  Auth::user()->ability('root', 'user-edit')  )
                     $html .= '<a href="#dynamic-table-relations" data-iddata="'.$dato->id.'" class="btn btn-xs btn-primary editar hint--left hint--large" aria-label="Para Editar este registro seleccione este botón y luego en los campos de ubicados en la parte superior se cargará la información para ser modificada, haga los cambios correspondientes y luego seleccione Guardar"><i class="fa fa-pencil"></i></a> ';

                 if(  Auth::user()->ability('root', 'user-delete')  )
                     $html .= "<a href='#dynamic-table-relations' data-iddata=".$dato->id." class='btn btn-xs btn-danger eliminar hint--top' aria-label='Eliminar'><i class='fa fa-trash'></i></a>";
                 return $html;
             })
             ->editColumn('created_at', function ($dato) {
                     if($dato->created_at == null) return '0';
                    return Carbon::createFromFormat('Y-m-d H:i:s', $dato->created_at )->diffForHumans() . ' <span class="hint--top-rigth" aria-label="'. $dato->created_at .'"><i class="fa fa-info-circle"></i></span>';
            })
            ->rawColumns(['relations','names','birthday','created_at','action'])
            ->make(true);
    }


    /**
      * @description Muestra los datos de la tabla
      *
      * @author Sandy Rodriguez 2017-12-19
      *
      * @param $request Request
      *
      * @return json
      * */
     public function gridclientaddres($client = null)
     {
         /** si no es ROOT aisgnar empresa segun usuario **/
         if( Auth::user()->hasRole('root') ){
             $where = [];
         }else{
             $where = [ ['id_empresa', '=', Auth::user()->id_empresa] ];
         }

         $clientaddress=new Client();
         $dato = $clientaddress->find($client)->client_address();

         return Datatables::eloquent($dato)
                ->addColumn('locality', function($dato){
                    $estado = new Estado();
                    //$localidad = $estado->find($dato->id_locality);
                    $localidad = $estado
                                ->leftJoin('localidades', 'estados.id', 'localidades.id_estado')
                                ->where('localidades.id', $dato->id_locality)
                                ->first();

                    return $localidad->nombre;
                })
                ->addColumn('type', function($dato){
                    $client_type_location = new Client_type_location();
                    $Client_type_location = $client_type_location->find($dato->id_type);
                    return $Client_type_location->name;
                })

                ->addColumn('action', function ($dato) {

                     $html = '';
                     if(  Auth::user()->ability('root', 'user-edit')  )
                         $html .= '<a href="#dynamic-table-address" data-iddata="'.$dato->id.'" class="btn btn-xs btn-primary editar hint--left hint--large" aria-label="Para Editar este registro seleccione este botón y luego en los campos de ubicados en la parte superior se cargará la información para ser modificada, haga los cambios correspondientes y luego seleccione Guardar"><i class="fa fa-pencil"></i></a> ';

                     if(  Auth::user()->ability('root', 'user-delete')  )
                         $html .= "<a href='#dynamic-table-address' data-iddata=".$dato->id." class='btn btn-xs btn-danger eliminar hint--top' aria-label='Eliminar'><i class='fa fa-trash'></i></a>";
                     return $html;
                 })
                 ->editColumn('created_at', function ($dato) {
                         if($dato->created_at == null) return '0';
                        return Carbon::createFromFormat('Y-m-d H:i:s', $dato->created_at )->diffForHumans() . ' <span class="hint--top-rigth" aria-label="'. $dato->created_at .'"><i class="fa fa-info-circle"></i></span>';
                })
                ->rawColumns(['locality', 'type','created_at','action'])
                ->make(true);

         
    }

      /**
      * @description Muestra los datos de la tabla
      *
      * @author Sandy Rodriguez 2017-12-19
      *
      * @param $request Request
      *
      * @return json
      * */
     public function grid_Contact_information($client = null)
     {
         /** si no es ROOT aisgnar empresa segun usuario **/
         if( Auth::user()->hasRole('root') ){
             $where = [];
         }else{
             $where = [ ['id_empresa', '=', Auth::user()->id_empresa] ];
         }

         $client_contact=new Client();
         $dato = $client_contact->find($client)->client_contact();


         return Datatables::eloquent($dato)
            ->addColumn('type_phone', function($dato){
                $client_type_phone = new Client_type_phone();
                $Client_type_phone = $client_type_phone->find($dato->id_type_phone);
                return $Client_type_phone->name;
            })
            ->addColumn('phone', function($dato){
                return $dato->phone;
            })
            ->addColumn('principal', function($dato){
                $html = '';
                if($dato->principal==1)
                    $html = ' <i class="fa fa-phone" style="color: #168EF3;"></i>';
                return $html;
            })
            ->addColumn('whatsapp', function($dato){
                $html = '';
                if($dato->wsp=='SI')
                    $html = ' <i class="fa fa-whatsapp" style="color: #0f0;"></i>';
                return $html;
            })
            ->addColumn('action', function ($dato) {
                 $html = '';
                 if(  Auth::user()->ability('root', 'user-edit')  )
                     $html .= '<a href="#dynamic-table-phones" data-iddata="'.$dato->id.'" class="btn btn-xs btn-primary editar hint--left hint--large" aria-label="Para Editar este registro seleccione este botón y luego en los campos de ubicados en la parte superior se cargará la información para ser modificada, haga los cambios correspondientes y luego seleccione Guardar"><i class="fa fa-pencil"></i></a> ';

                 if(  Auth::user()->ability('root', 'user-delete')  )
                     $html .= "<a href='#dynamic-table-phones' data-iddata=".$dato->id." class='btn btn-xs btn-danger eliminar hint--top' aria-label='Eliminar'><i class='fa fa-trash'></i></a>";
                 return $html;
             })
             ->editColumn('created_at', function ($dato) {
                     if($dato->created_at == null) return '0';
                    return Carbon::createFromFormat('Y-m-d H:i:s', $dato->created_at )->diffForHumans() . ' <span class="hint--top-rigth" aria-label="'. $dato->created_at .'"><i class="fa fa-info-circle"></i></span>';
            })
            ->rawColumns(['type_phone','phone','created_at','action', 'principal', 'whatsapp'])
            ->make(true);
    }

      /**
      * @description Muestra los datos de la tabla
      *
      * @author Sandy Rodriguez 2017-12-19
      *
      * @param $request Request
      *
      * @return json
      * */
     public function grid_client_mails($client = null)
     {
         /** si no es ROOT aisgnar empresa segun usuario **/
         if( Auth::user()->hasRole('root') ){
             $where = [];
         }else{
             $where = [ ['id_empresa', '=', Auth::user()->id_empresa] ];
         }

         $client_mails=new Client();
         $dato = $client_mails->find($client)->client_mails();


          return Datatables::eloquent($dato)
            ->addColumn('type_mail', function($dato){
                $client_type_mails = new Client_type_mails();
                $Client_mails = $client_type_mails->find($dato->id_type_mail);
                return $Client_mails->name;
            })
            ->addColumn('action', function ($dato) {

                 $html = '';
                 if(  Auth::user()->ability('root', 'user-edit')  )
                     $html .= '<a href="#dynamic-table-email" data-iddata="'.$dato->id.'" class="btn btn-xs btn-primary editar hint--left hint--large" aria-label="Para Editar este registro seleccione este botón y luego en los campos de ubicados en la parte superior se cargará la información para ser modificada, haga los cambios correspondientes y luego seleccione Guardar"><i class="fa fa-pencil"></i></a> ';

                 if(  Auth::user()->ability('root', 'user-delete')  )
                     $html .= "<a href='#dynamic-table-email' data-iddata=".$dato->id." class='btn btn-xs btn-danger eliminar hint--top' aria-label='Eliminar'><i class='fa fa-trash'></i></a>";
                 return $html;
             })
             ->editColumn('created_at', function ($dato) {
                     if($dato->created_at == null) return '0';
                    return Carbon::createFromFormat('Y-m-d H:i:s', $dato->created_at )->diffForHumans() . ' <span class="hint--top-rigth" aria-label="'. $dato->created_at .'"><i class="fa fa-info-circle"></i></span>';
            })
            ->rawColumns(['type_mail','created_at','action'])
            ->make(true);
    }

    /**
      * @description Muestra los datos de la tabla
      *
      * @author Sandy Rodriguez 2017-12-19
      *
      * @param $request Request
      *
      * @return json
      * */
    public function grid_relation($id = null)
    {
        /** si no es ROOT aisgnar empresa segun usuario **/
        if( Auth::user()->hasRole('root') ){
            $where = [];
        }else{
            $where = [ ['id_empresa', '=', Auth::user()->id_empresa] ];
        }
        $groups = new ClientEnvironment();
        $dato = $groups->where('client_id', '=', '1');

        return Datatables::eloquent($dato)
            ->addColumn('names', function ($dato) {
                $html = '';
                $relclient = new Client();
                $names = $relclient->find($dato->client_id2);
                $html = $names->name . ' ' . $names->last_name;
                return $html;
            })
            ->addColumn('relation', function ($dato) {
                $html = '';
                $relations= new TypeEnvironment();
                $relation = $relations->find($dato->type_environment_id);
                $html = $relation->name;
                return $html;
            })
            ->addColumn('action', function ($dato) {
                $html = '';
                if(  Auth::user()->ability('root', 'user-edit')  )
                    $html .= '<a href="#dynamic-table-relations" data-iddata="'.$dato->id.'" class="btn btn-xs btn-primary editar hint--left hint--large" aria-label="Para Editar este registro seleccione este botón y luego en los campos de ubicados en la parte superior se cargará la información para ser modificada, haga los cambios correspondientes y luego seleccione Guardar"><i class="fa fa-pencil"></i></a> ';

                if(  Auth::user()->ability('root', 'user-delete')  )
                     $html .= "<a href='#dynamic-table-relations' data-iddata=".$dato->id." class='btn btn-xs btn-danger eliminar hint--top' aria-label='Eliminar'><i class='fa fa-trash'></i></a>";
                 return $html;

            })
            ->editColumn('created_at', function ($dato) {
                    if($dato->created_at == null) return '0';
                    return Carbon::createFromFormat('Y-m-d H:i:s', $dato->created_at )->diffForHumans() . ' <span class="hint--top-rigth" aria-label="'. $dato->created_at .'"><i class="fa fa-info-circle"></i></span>';
            })
            ->rawColumns(['relation','names', 'created_at','action'])
            ->make(true);
    }


    /**
     * @description guarda los datos de las direcciones
     *
     * @author Sandy Rodriguez
     *
     * @param request
     *
     * @return response json
     * */
    public function store_address(Request $request){
        $address = new Client_address();
        if ($request->address_contact_id){
            $address = $address->find($request->address_contact_id);
        }
        $address->client_id = $request->client_id;
        $address->id_type = $request->id_type_address;
        $address->id_locality = $request->id_locality;
        $address->street = $request->street;
        $address->number = $request->number;
        $address->floor = $request->floor;
        $address->district = $request->district;
        $address->latitude = $request->latitude;
        $address->longitude = $request->longitude;
        $address->google_place_id = $request->google_place_id;
        $address->save();

        return response()->json([
            'status' => true,
            'controller'  => 'charges',
            'title'  => 'Operación Exitosa!',
            'text' => 'La información de Ubicación ha sido registrada satisfactoriamente.',
            'type' => 'success'
        ],200);
       // return view('admin.clients.charges.index');
    }

    /**
     * @description permite crear clientes basico para relaciones
     *
     * @author Sandy Rodriguez
     *
     * @param request
     *
     * @return \Illuminate\Http\Response
     */
    public function relations(Request $request){

        $registro = new Client();
        $registro->name  = $request->rel_name;
        $registro->last_name = $request->rel_last_name;
        $registro->birthday = $request->rel_birthday;
        if ( $request->id_ages_range )
            $registro->id_ages_range = $request->id_ages_range;
        $registro->id_company = Auth::user()->id_empresa; //concesionaria a la que pertenece el cliente
        $registro->id_user=Auth::user()->id;
        $registro->id_document = 1;
        $registro->document_nro = '';
        $registro->save();
// client_relations($id=0, $client_id, $registro->id, $id_relation)

         return response()->json([
             'status' => true,
             'controller'  => 'clients',
             'title'  => 'Operación Exitosa!',
             'text' => 'El contacto ha sido registrado satisfactoriamente.',
             'type' => 'success',
             'client_id' => $registro->id
         ],200);
    }

    /**
     * @description guarda los datos de las relaciones del cliente
     *
     * @author Sandy Rodriguez
     *
     * @param request
     *
     * @return response json
     * */
    public function client_relations($id=0, $client_id, $client_id2, $id_relation){
        $relation_group = new ClientEnvironment();
        if($id>0){
            $relation_group = $relation_group->find($id);
        }
        $relation_group->client_id = $client_id;
        $relation_group->client_id2 = $client_id2;
        $relation_group->relations_id=$id_relation;
        $relation_group->save();

        return response()->json([
            'status' => true,
            'controller'  => 'charges',
            'title'  => 'Operación Exitosa!',
            'text' => 'El Contacto ha sido registrado satisfactoriamente.',
            'type' => 'success'
        ],200);
       // return view('admin.clients.charges.index');
    }


    public function client_mails(Request $request){
        $client_mails = new Client_mails();
        $exist = $client_mails->where('mail', $request->mail)->get();
        if (count($exist)){
            if ($exist[0]->client_id != $request->client_id){
                return response()->json([
                    'status' => false,
                    'title'  => 'Email Existe!',
                    'text' => 'El Email <b>'.$request->mail.'</b> pertenece a otro usuario.',
                    'type' => 'error'
                ],200);
            }
            $client_mails = $client_mails->find($exist[0]->id);
        }
        if(isset($request->mail_id)){
            $client_mails = $client_mails->find($request->mail_id);
        }

        $id_type_mail = $request->id_type_mail;        
        if ( $request->id_type_mail == 'J' ) {
            $id_type = Db::table('client_type_mails')
                      ->where('name', 'like', '%Laboral%')
                      ->first();

              if (count($id_type))
                $id_type_mail = $id_type->id;
              else
                $id_type_mail = Db::table('client_type_mails')->insertGetId(['name' => 'Laboral']);

        }

        $client_mails->client_id = $request->client_id;
        $client_mails->id_type_mail = $id_type_mail;
        $client_mails->mail = $request->mail;
        $client_mails->principal = $request->principal;
        $client_mails->save();

        return response()->json([
            'status' => true,
            'title'  => 'Operación Exitosa!',
            'text' => 'El Email ha sido registrado satisfactoriamente.',
            'type' => 'success'
        ],200);
       // return view('admin.clients.charges.index');
    }

    /**
     * @description guarda las redes socialead del cliente
     * 
     * @author
     * 
     * param request
     * 
     * @return response json
     * */
    public function client_networks(Request $request){
        $client_networks = new Client_networks();
        $client_networks->where('client_id',$request->client_id)->delete();
        $client_networks->client_id = $request->client_id;
        $client_networks->twitter= $request->twitter;
        $client_networks->facebook = $request->facebook;
        $client_networks->linkedin = $request->linkedin;
        $client_networks->instagram = $request->instagram;
        $client_networks->google = $request->google;
        $client_networks->save();

        return response()->json([
            'status' => true,
            'title'  => 'Operación Exitosa!',
            'text' => 'El Entorno ha sido registrado satisfactoriamente.',
            'type' => 'success'
        ],200);
       // return view('admin.clients.charges.index');
    }

    /**
     * @description permite crear clientes
     *
     * @author Sandy Rodriguez
     *
     * @param request
     *
     * @return \Illuminate\Http\Response
     */
    public function relation_client(Request $request){

        $registro = new Client();
        /*if(isset($request->id)){
            $registro = $registro->find($request->id);
        }*/
        if($request->person_type){
            $person_type = 'F';
        }else{
            $person_type = 'J';
        }
        $registro->person_type = $person_type;
        $registro->id_origin = $request->id_origin;
        $registro->id_channel = $request->id_channel;
        $registro->id_gender = $request->id_gender;
        $registro->resale = $request->resale;
        $registro->name  = $request->name;
        $registro->last_name = $request->last_name;
        $registro->id_maritals_status = $request->id_maritals_status;
        $registro->id_user=Auth::user()->id;
        $registro->birthday = $request->birthday;
        if ( $request->id_ages_range )
            $registro->id_ages_range = $request->id_ages_range;
        $registro->company = $request->company; //empresa en la que trabaja el cliente
        $registro->id_company = Auth::user()->id_empresa; //concecionaria a la que pertenece el cliente
        $registro->id_charge = $request->id_charge;
        $registro->id_iva   = $request->id_iva;
        $registro->id_type_invoice = $request->id_type_invoice;        
        //$registro->grossincome = $request->grossincome;
        $registro->save();

         return response()->json([
             'status' => true,
             'controller'  => 'clients',
             'title'  => 'Operación Exitosa!',
             'text' => 'El Contacto ha sido registrado satisfactoriamente.',
             'type' => 'success',
             'client_id' => $registro->id
         ],200);
    }

    /**
      * @description Elimina el dato seleccionado
      *
      * @author Sandy Rodriguez 2017-12-26
      *
      * @param $id
      *
      * @return json
      * */
    public function delete_mail($id)
    {
        $data = Client_mails::find($id);

        if( !Auth::user()->ability('root', 'charge-delete') ){

            return response()->json([
                'status' => false,
                'title'  => 'Oops!',
                'text' => 'Ocurrio un error al intentar eliminar  <b>'.$data->mail.'</b>. Existen registros vinculados a este dato o no posee PERMISO...',
                'type' => 'error'
            ],200);

        }else{
            $data->delete();
            return response()->json([
                'status' => true,
                'title'  => 'Eliminado!',
                'text' => 'El registro de <b>'.$data->mail.'</b> ha sido eliminado correctamente!',
                'type' => 'success'
            ],200);
        }
    }

    /**
      * @description Elimina el dato seleccionado
      *
      * @author Sandy Rodriguez 2017-12-26
      *
      * @param $id
      *
      * @return json
      * */
    public function delete_relations($id)
    {
        $data = ClientEnvironment::find($id);

        if( !Auth::user()->ability('root', 'charge-delete') ){

            return response()->json([
                'status' => false,
                'title'  => 'Oops!',
                'text' => 'Ocurrio un error al intentar eliminar la relación. Existen registros vinculados a este dato o no posee PERMISO...',
                'type' => 'error'
            ],200);

        }else{
            $data->delete();
            return response()->json([
                'status' => true,
                'title'  => 'Eliminado!',
                'text' => 'El registro del Entorno ha sido eliminado correctamente!',
                'type' => 'success'
            ],200);
        }
    }

    /**
      * @description Elimina el dato seleccionado
      *
      * @author Sandy Rodriguez 2017-12-26
      *
      * @param $id
      *
      * @return json
      * */
    public function delete_contact($id)
    {
        $data = Client_contact::find($id);

        if( !Auth::user()->ability('root', 'charge-delete') ){

            return response()->json([
                'status' => false,
                'title'  => 'Oops!',
                'text' => 'Ocurrio un error al intentar eliminar los datos de el movil <b>'.$data->phone.'</b>. Existen registros vinculados a este dato o no posee PERMISO...',
                'type' => 'error'
            ],200);

        }else{
            $data->delete();
            return response()->json([
                'status' => true,
                'title'  => 'Eliminado!',
                'text' => 'El numero celular <b>'.$data->phone.'</b> ha sido eliminado correctamente!',
                'type' => 'success'
            ],200);
        }
    }

    /**
      * @description Elimina el dato seleccionado
      *
      * @author Sandy Rodriguez 2017-12-26
      *
      * @param $id
      *
      * @return json
      * */
    public function delete_address($id)
    {
        $data = Client_address::find($id);

        if( !Auth::user()->ability('root', 'charge-delete') ){

            return response()->json([
                'status' => false,
                'title'  => 'Oops!',
                'text' => 'Ocurrio un error al intentar eliminar los datos de la dirección. Existen registros vinculados a este dato o no posee PERMISO...',
                'type' => 'error'
            ],200);

        }else{
            $data->delete();
            return response()->json([
                'status' => true,
                'title'  => 'Eliminado!',
                'text' => 'La dirección ha sido eliminado correctamente!',
                'type' => 'success'
            ],200);
        }
    }

    /**
     * Return list of contact types
     *
     * @param  int  $client_id
     * @return \Illuminate\Http\Response
     */
    public function getTypes(Request $request, $client_id=0)
    {
        if($request->ajax()){
            //$data = User::query()->where('id_empresa', '=', $id_empresa)->get();
            $data = Db::table('client_type_details')
                    ->join('client_type', 'client_type_details.client_type_id', 'client_type.id')
                    ->select('client_type.id', 'client_type.name')
                    ->where('client_id', $client_id)
                    ->get();

            return response()->json($data);
        }
    }
    
    /**
     * Return list of document types for select
     * @param  int  $type
     * @return \Illuminate\Http\Response
     */
    public function get_type_document_select ( Request $request, $type = '' )
    {
            if ( $type == 'F' ) 
                $data = Db::table('documents')
                    ->where( 'name', 'NOT LIKE', '%CUIT%' )
                    ->select('name', 'id')
                    ->orderBy('name', 'ASC')
                    ->get();
            else
                if ( $type == 'J' ) 
                    $data = Db::table('documents')
                        ->where( 'name', 'like', '%CUIT%' )
                        ->select('name', 'id')
                        ->orderBy('name', 'ASC')
                        ->get();
                else
                    $data = Db::table('documents')
                        ->select('name', 'id')
                        ->orderBy('name', 'ASC')
                        ->get();

            return $data;        
    }
    
    /**
     * Return list of locations types for select
     * @param  int  $type
     * @return \Illuminate\Http\Response
     */
    public function get_type_locations_select ( Request $request, $type = '' )
    {
        //if ( $request->ajax ( ) ) {
            if ( $type == 'F' ) 
                $data = Db::table('client_type_location')
                    ->where( 'name', 'like', '%Domicilio%' )
                    ->select('name', 'id')
                    ->orderBy('name', 'ASC')
                    ->get();
            else
                if ( $type == 'J' ) 
                    $data = Db::table('client_type_location')
                        ->where( 'name', 'like', '%Oficina%' )
                        ->orWhere( 'name', 'like', '%Sucursal%' )
                        ->select('name', 'id')
                        ->orderBy('name', 'ASC')
                        ->get();
                else
                    $data = Db::table('client_type_location')
                        ->select('name', 'id')
                        ->orderBy('name', 'ASC')
                        ->get();

            return $data;
        //}
    }
    
    /**
     * Return name of quality factory
     * @param  int  $type
     * @return \Illuminate\Http\Response
     */
    public function get_name_quality (  )
    {
        $data = Db::table('quality_factory')
                ->leftJoin('brand_company', 'brand_company.id_brand', 'quality_factory.id_brand')
                ->where( 'brand_company.id_company', \Auth::user()->id_empresa )
                ->where( 'brand_company.default_brand', 1 )
                ->select('quality_factory.name')
                ->orderBy( 'quality_factory.updated_at', 'desc')
                ->orderBy( 'quality_factory.created_at', 'desc')
                ->first();
        
        return response()->json( $data );
        
    }
    
    /**
     * Return name of module
     * @param  int  $type
     * @return \Illuminate\Http\Response
     */
    public function clients_get_module ( $id )
    {
        $data = Db::table('modules')
                ->where( 'id', $id )
                ->select('name')
                ->first();
        
        return response()->json( $data );
        
    }
    
    /**
     * Return get result of quality factory
     * @param  int  $type
     * @return \Illuminate\Http\Response
     */
    public function search_cvp ( $client_id  )
    {
        $data = Db::table('quality_factory_contacts')
                ->where( 'client_id', $client_id )
                ->select('result')
                ->first();
        
        return response()->json( $data );
        
    }
    
    /**
     * Return get id of ages_range table for age
     * @param  int  $type
     * @return \Illuminate\Http\Response
     */
    public function get_age_range ( Request $request  )
    {
        if ( $request->age >= 60 )
            $data = Db::table('ages_range')
                ->where( 'min_age', '>=', $request->age )
                ->select('id')
                ->first();
        else
            $data = Db::table('ages_range')
                ->where( 'min_age', '<=', $request->age )
                ->where( 'max_age', '>=', $request->age )
                ->select('id')
                ->first();
        
        return response()->json( $data );
        
    }
    
    /**
     * Return save quality factory
     * @param  int  $type
     * @return \Illuminate\Http\Response
     */
    public function store_cvp ( Request $request )
    {
        $data = Db::table('quality_factory_contacts')
                ->where( 'client_id', $request->client_id )
                ->get();
        if ( count ( $data ) ) {
            $affected = DB::table('quality_factory_contacts')
                ->where( 'client_id', $request->client_id )
                ->update(array('result' => $request->result));
        } else {
            Db::table('quality_factory_contacts')
                ->insert(['client_id' => $request->client_id, 'result' => $request->result]);
        }
        
        return response()->json([
                'status' => true,
                'title'  => 'Guardado!',
                'text' => 'El resultado ha sido guardado correctamente!',
                'type' => 'success'
            ],200);
        
    }

    public function area_code_search (Request $request)
    {   
        if ($request->ajax() || $request->wantsJson()) {

            $search = "";
            if ($request->has('q')){
                $search = "%".$request->q."%";
            }
            $data = \Cache::remember(Auth::user()->id_empresa.$request->q, 1, function () use ($search) {
                // consultas

                $data = Db::table('paises')
                    ->where('paises.nombre',    'like', $search)
                    ->orWhere('paises.phone_code', 'like', $search)
                    ->orderBy('nombre', 'ASC')
                    ->paginate(10); 

                // formatear
                foreach ($data as $model){
                    $model->text =  $model->phone_code.' '.$model->nombre;
                }

                return $data;
            });


            //return response()->json($data);
            return response()->json(['items' => $data->toArray()['data'], 'pagination' => $data->nextPageUrl() ? true : false]);
        }
    }

    public function nationality_search (Request $request)
    {        
        if ($request->ajax() || $request->wantsJson()) {

            $search = "";
            if ($request->has('q')){
                $search = "%".$request->q."%";
            }
            $data = \Cache::remember(Auth::user()->id_empresa.$request->q, 1, function () use ($search) {
                // consultas

                $data = Db::table('paises')
                    ->where('paises.nationality', 'like', $search)
                    ->orWhere('paises.nombre',    'like', $search)
                    ->orderBy('nationality', 'ASC')
                    ->paginate(10); 

                // formatear
                foreach ($data as $model){
                    $model->text =  $model->nationality;
                }
                
                return $data;
            });


            //return response()->json($data);
            return response()->json(['items' => $data->toArray()['data'], 'pagination' => $data->nextPageUrl() ? true : false]);
        }
    }
    
    public function getDefaultCountry(Request $request){
        $data = DB::table('paises')
                    ->leftJoin('estados', 'estados.id_pais', 'paises.id')
                    ->leftJoin('localidades', 'localidades.id_estado', 'estados.id')
                    ->leftJoin('empresas', 'empresas.id_localidad', 'localidades.id')
                    ->where('empresas.id', \Auth::user()->id_empresa)
                    ->select('paises.id', 'paises.nombre', 'paises.phone_code', 'paises.nationality', 'paises.shortname')
                    ->first();

        if ( $data == null ){
            $data = DB::table('paises')
                    ->leftJoin('estados', 'estados.id_pais', 'paises.id')
                    ->leftJoin('localidades', 'localidades.id_estado', 'estados.id')
                    ->leftJoin('empresas', 'empresas.id_localidad', 'localidades.id')
                    ->where('paises.nombre', 'Argentina')
                    ->select('paises.id', 'paises.nombre', 'paises.phone_code', 'paises.nationality', 'paises.shortname')
                    ->first();
        }
        
        return response()->json([
                'status' => true,
                'title'  => 'Pais!',
                'data' => $data,
                'type' => 'success'
            ],200);
    }

    public function search_spouse(Request $request, $id=null) {
        if ( $id ) {
            $data = array();
            $phone = array();
            $mail = array();

            $marital_status = DB::table('marital_status')
                    ->leftJoin('clients', 'clients.id_maritals_status', 'marital_status.id')
                    ->select('marital_status.name')
                    ->where('clients.id', $id)
                    ->first();
            if ( $marital_status->name == 'Casado' ) {
                $type_env = DB::table('type_environments')
                        ->where('invoice_data', 1)
                        ->first();

                $env = DB::table('client_environments')
                        ->where('client_id', $id)
                        ->where('type_environment_id', $type_env->id)
                        ->first();

                if ( $env ) {
                    $data = DB::table('clients')
                        ->where('clients.id', $env->client_id2)
                        ->first();
                    
                    if ( $data ) {
                        $phone = DB::table('client_contacts')
                            ->where('client_id', $data->id)
                            ->where('principal', 1)
                            ->first();

                        $mail = DB::table('client_mails')
                            ->where('client_id', $data->id)
                            ->where('principal', 1)
                            ->first();
                    }
                }
            }
        }
        
        return response()->json([
                'status' => true,
                'data'  => $data,
                'phone' => $phone,
                'mail'  => $mail,
                'type'  => 'success'
            ],200);
    }

    public function search_area_code ( Request $request ) {
            
        $query = $request->get('query',''); 
        $pais = $request->get('cod_pais',''); 
                
        $codes=DB::table('area_phone_codes')
                ->leftJoin('estados', 'area_phone_codes.id_state', 'estados.id')
                ->leftJoin('paises', 'paises.id', 'estados.id_pais')
                ->distinct()
                ->select('area_code as name')
                ->where('paises.id', $pais)
                ->where('area_code', 'LIKE', $query.'%')
                ->get();
        
        return response()->json($codes);
    }

    public function search_area_codes ( Request $request ) {
        $countrie = $request->country; 
        $code = $request->area_code;
        if ( $countrie && $code ) {
            $codes=DB::table('area_phone_codes')
                ->leftJoin('estados', 'area_phone_codes.id_state', 'estados.id')
                ->leftJoin('paises', 'paises.id', 'estados.id_pais')
                ->distinct()
                ->select('area_code as name')
                ->where('paises.id', $countrie)
                ->where('area_code', $code)
                ->get();

                if (count($codes))
                    return response()->json('Ok');            
        }        
    }

    public function search_area_codes_country ( Request $request, $country=null ) {

        $codes=DB::table('estados')
                ->leftJoin('paises', 'paises.id', 'estados.id_pais')
                ->where('paises.id', $country)
                ->select('estados.nombre', 'estados.id')
                ->get();
        
        return response()->json($codes);
    }

    public function search_country_info ( Request $request, $country=null ) {
        $codes = DB::table('paises')
                ->where('id', $request->country)
                ->first();
        return response()->json($codes);
    }

    /**
     * @description permite crear codigos de área por para una ciudad/provincia
     *
     * @author Eliecer Cedano
     *
     * @param request
     *
     * @return \Illuminate\Http\Response
     */
    public function create_area_code(Request $request){
        if ( $request->area_code && $request->id_state ){
            $exist = DB::table('area_phone_codes')
                ->where('id_state',  $request->id_state)
                ->where('area_code', $request->area_code)
                ->get();
            if ( ! count($exist) ) { 
                DB::table('area_phone_codes')->insert(
                    ['id_state'  => $request->id_state,
                     'area_code' => $request->area_code
                    ]);

                return response()->json([
                    'status' => true,
                    'controller'  => 'clients',
                    'title'  => 'Operación Exitosa!',
                    'text' => 'Los datos del Código de Área fueron registrado correctamente.',
                    'type' => 'success'
                ],200);
            }
        }
    }

    public function getCoordinates(Request $request)
    {
        $address = urlencode($request->address);
        $url = 'http://maps.google.com/maps/api/geocode/json?sensor=false&address='.$address;
        $response = file_get_contents($url);
        $json = json_decode($response, true);
        $formatted_address = $json['results'][0]['formatted_address'];
        $placeId = $json['results'][0]['place_id'];
        $lat = $json['results'][0]['geometry']['location']['lat'];
        $lng = $json['results'][0]['geometry']['location']['lng'];
        return response()->json(['latitude' => $lat, 'longitude' => $lng, 'new_address' => $formatted_address, 'place_id' => $placeId]);
    }

    /**
     * @description Actualiza el Origen en Clientes
     *
     * @author Eliecer
     *
     * @param request
     *
     * @return response json
     * */
    public function client_save_origin (Request $request) {
        $client = new Client();
        if ( $request->id_client ) {
            $client = $client->findorfail($request->id_client);
            $client->id_origin = $request->id_origin;
            $client->save();
            return response()->json([
                'status' => true,
                'controller'  => 'clients',
                'title'  => 'Operación Exitosa!',
                'text' => 'El Origen ha sido actualizado satisfactoriamente.',
                'type' => 'success'
            ],200);
        } 
    }

    /**
     * @description Actualiza el Canal en Clientes
     *
     * @author Eliecer
     *
     * @param request
     *
     * @return response json
     * */
    public function client_save_channel (Request $request) {
        $client = new Client();
        if ( $request->id_client ) {
            $client = $client->findorfail($request->id_client);
            $client->id_channel = $request->id_channel;
            $client->save();
            return response()->json([
                'status' => true,
                'controller'  => 'clients',
                'title'  => 'Operación Exitosa!',
                'text' => 'El Canal ha sido actualizado satisfactoriamente.',
                'type' => 'success'
            ],200);
        } 
    }

    /**
     * Buscar transacciones de clientes para linea de tiempo
     *
     * @param   string $request 
     * @return  string json
     * @author  Eliecer Cedano
     * */
    public function client_get_transactions ( Request $request, $client_id )
    {
        /*
        $data = Db::table('budgets')
            ->leftJoin('budget_details', 'budgets.id', 'budget_details.id_budget')
            ->leftJoin('products', 'products.id', 'budget_details.id_product')
            ->leftJoin('brands', 'brands.id', 'products.id_brand')
            ->leftJoin('models', 'models.id', 'products.id_model')
            ->leftJoin('users', 'budgets.id_user', 'users.id')
            ->select('budgets.date', DB::raw("CONCAT('Presupuesto # ', budgets.id) AS transaction"), DB::raw("CONCAT(brands.name, ' ', models.name, ' ', products.version) AS products"), DB::raw("CONCAT(users.first_name, ' ', users.last_name) AS user"))
            ->where('budgets.id_client', $client_id)
            ->orderBy('budgets.date', 'desc')
            ->get();
        */

        $data = Db::table('tasks')
            ->leftJoin('products', 'products.id', 'tasks.id_product')
            ->leftJoin('brands', 'brands.id', 'products.id_brand')
            ->leftJoin('models', 'models.id', 'products.id_model')
            ->leftJoin('users', 'tasks.id_employee', 'users.id')
            ->leftJoin('events', 'events.id', 'tasks.id_event')
            ->leftJoin('task_reasons', 'task_reasons.id', 'tasks.id_task_reason')
            ->leftJoin('task_results', 'task_results.id', 'tasks.id_task_result')        
            ->select('tasks.date', 'tasks.close_date', DB::raw("tasks.description AS transaction"), DB::raw("CONCAT(brands.name, ' ', models.name, ' ', products.version) AS products"), DB::raw("CONCAT(users.first_name, ' ', users.last_name) AS user"), 'events.description as event', 'events.icon', 'task_reasons.description as razon', 'task_results.description as resultado', 'tasks.id_budget', 'tasks.id_sale', 'tasks.is_closed', 'tasks.created_at') 
            ->where('tasks.id_client', $client_id)
            ->where('tasks.id_empresa', Auth::user()->id_empresa)
            ->orderBy('tasks.date', 'DESC')
            ->get();
        $result = array();

        // Cargar los Acuerdos
        foreach ( $data as $reg ) {
            $agree = Db::table('budget_agreement')
                    ->leftJoin('agreements', 'agreements.id', 'budget_agreement.id_agreement')
                    ->select('budget_agreement.created_at as fecha', 'agreements.name', 'agreements.percentage', 'agreements.id_parent')
                    ->where('budget_agreement.id_budget', $reg->id_budget)
                    ->get();

            $acuerdos = '';
            if ( count( $agree ) ) {
                $details = '';                
                foreach ( $agree as $value ) {
                    $cad = '';                    
                    if ( $value->id_parent ) {
                        $det = Db::table('agreements')
                            ->select('agreements.name')
                            ->where('id', $value->id_parent)
                            ->get();
                            if ( count ( $det ) )
                                $cad = 'class="hint--top" aria-label="' . $value->name . '"';
                            
                            $details = ' <div class="hint--top" aria-label="'.$value->fecha.'"><span class="fa-stack text-success">    <i class="fa fa-check fa-stack-1x" style="margin-left:4px"></i>    <i class="fa fa-check fa-inverse fa-stack-1x" style="margin-left:-3px;"></i>    <i class="fa fa-check  fa-stack-1x" style="margin-left:-4px"></i> </span></div><div '.$cad.'>' . $det[0]->name .'</div>';
                    } else
                        $details = ' <div class="hint--top" aria-label="'.$value->fecha.'"><span class="fa-stack text-success">    <i class="fa fa-check fa-stack-1x" style="margin-left:4px"></i>    <i class="fa fa-check fa-inverse fa-stack-1x" style="margin-left:-3px;"></i>    <i class="fa fa-check  fa-stack-1x" style="margin-left:-4px"></i> </span></div><div >' . $value->name .'</div>';
                    if ( $value->percentage )
                        $details .= ' ' . $value->percentage . '% ';
                    //$details .= ' <div class="hint--top" aria-label="'.$value->fecha.'"><i class="fa fa-calendar"></i></div>';

                    if ( $details )
                        $acuerdos .= $details.' ';
                    

                }

                $acuerdos = ( $acuerdos ) ? substr($acuerdos, 0, strlen($acuerdos) - 2) : '';

                $result[] = [
                    'date'        => $reg->date, 
                    'close_date'  => $reg->close_date, 
                    'transaction' =>  $reg->transaction, 
                    'products'    => $reg->products, 
                    'user'        => $reg->user, 
                    'event'       => $reg->event, 
                    'icon'        => $reg->icon, 
                    'razon'       => $reg->razon, 
                    'resultado'   => $reg->resultado, 
                    'id_budget'   => $reg->id_budget, 
                    'id_sale'     => $reg->id_sale,  
                    'is_closed'   => $reg->is_closed, 
                    'created_at'  => $reg->created_at,
                    'agreements'  => $acuerdos
                ];

            } else {
                $result = $data;
            }
        }



        return $result;
    }


    private function sanitize($string, $force_lowercase = true, $anal = false)
    {
        $strip = array("~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "_", "=", "+", "[", "{", "]",
            "}", "\\", "|", ";", ":", "\"", "'", "&#8216;", "&#8217;", "&#8220;", "&#8221;", "&#8211;", "&#8212;",
            "â€”", "â€“", ",", "<", ".", ">", "/", "?");
        $clean = trim(str_replace($strip, "", strip_tags($string)));
        $clean = preg_replace('/\s+/', "-", $clean);
        $clean = ($anal) ? preg_replace("/[^a-zA-Z0-9]/", "", $clean) : $clean ;

        return ($force_lowercase) ?
            (function_exists('mb_strtolower')) ?
                mb_strtolower($clean, 'UTF-8') :
                strtolower($clean) :
            $clean;
    }

    private function createUniqueFilename( $filename )
    {
        $upload_path = env('UPLOAD_PATH', public_path('/img/users/'));
        $full_image_path = $upload_path . $filename . '.jpg';

        if ( File::exists( $full_image_path ) )
        {
            // Generate token for image
            $image_token = substr(sha1(mt_rand()), 0, 5);
            return $filename . '-' . $image_token;
        }

        return $filename;
    }

    public function postCrop()
    {
        $form_data = Input::all();
        $image_url = $form_data['imgUrl'];

        // resized sizes
        $imgW = $form_data['imgW'];
        $imgH = $form_data['imgH'];
        // offsets
        $imgY1 = $form_data['imgY1'];
        $imgX1 = $form_data['imgX1'];
        // crop box
        $cropW = $form_data['width'];
        $cropH = $form_data['height'];
        // rotation angle
        $angle = $form_data['rotation'];

        $filename_array = explode('/', $image_url);
        $filename = $filename_array[sizeof($filename_array)-1];

        $manager = new ImageManager();
        $image = $manager->make( $image_url );
        $image->resize($imgW, $imgH)->rotate(-$angle)->crop($cropW, $cropH, $imgX1, $imgY1)->save(env('UPLOAD_PATH', public_path('/img/users/')) . 'crop-' . $filename);

        if( !$image) {

            return Response::json([
                'status' => 'error',
                'message' => 'Server error while uploading',
            ], 200);

        }


        /**
         *   Update Registros en BD
         */        

        /*
        $registro = Client::find( $request->id_client );
        $registro->photo = 'crop-' . $filename;
        $registro->save();
        */

        return Response::json([
            'status' => 'success',
            'url' => env('URL') . 'img/users/crop-' . $filename
        ], 200);

    }

    public function postUpload(Request $request)
    {
        $form_data = Input::all();
        $validator = Validator::make($form_data, User::$rules, User::$messages);
        if ($validator->fails()) {

            return Response::json([
                'status' => 'error',
                'message' => $validator->messages()->first(),
            ], 200);

        }

        $photo = $form_data['img'];

        $original_name = $photo->getClientOriginalName();
        $original_name_without_ext = substr($original_name, 0, strlen($original_name) - 4);

        $filename = $this->sanitize($original_name_without_ext);
        $allowed_filename = $this->createUniqueFilename( $filename );

        $filename_ext = $allowed_filename .'.jpg';
        //$filename_ext = 'avatar-' . Auth::user()->id . '.jpg';
        //$filename_ext = 'avatar-' . Auth::user()->id . $allowed_filename .'.jpg';


        $manager = new ImageManager();
        $image = $manager->make( $photo )->encode('jpg')->save(env('UPLOAD_PATH', public_path('/img/users/') ) . $filename_ext );

        if( !$image) {

            return Response::json([
                'status' => 'error',
                'message' => 'Server error while uploading',
            ], 200);

        }
        
        $registro = Client::find( $request->id_client );
        $registro->photo = $filename_ext;
        $registro->save();

        return Response::json([
            'status'    => 'success',
            'url'       => env('URL') . 'img/users/' . $filename_ext,
            'width'     => $image->width(),
            'height'    => $image->height()
        ], 200);
    }

    /**
     * @description Update keys (channel or origin) field in storage.
     * @author Héctor Agüero
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id (client)
     * @return \Illuminate\Http\Response
     */
    public function update_client_keys(Request $request, $id)
    {
        $client = Client::findorfail($id);
        
        if($request->has('channel')){
            $client->id_channel = $request->channel;
        }
        if($request->has('origin')){
            $client->id_origin = $request->origin;
        }
        
        $client->save();

        return response()->json([
            'status' => true,
            'controller' => 'ClientController',
            'title'  => 'Operación exitosa!',
            'text' => 'El registro ha sido actualizado satisfactoriamente.',
            'type' => 'success'
        ],200);
    }

    /*
            |--------------------------------------------------------------------------
            | SECTION: Panel
            |--------------------------------------------------------------------------
            */

            /**
             * Carga la vista de Panel para mostrar datos de Contactos en el listado de Contactos
             *
             * @param  \Illuminate\Http\Request
             * @return \Illuminate\Http\Response
             * @author  Carlos Villarroel  -  cevv07@gmail.com
             */
            public function get_panel_partial(Request $request)
            {
                if ($request->ajax() || $request->wantsJson()) {
                    $id_client  = $request->id_client;

                    $content = view('admin.clients.info')
                        ->with(compact('id_client'))
                        ->renderSections()['panel_partial'];

                    return response()->json([
                        'content'      => $content,
                        'data'         => null
                    ], 200);
                }
            }

    /**
     * Buscar informacion de la Experiencia del contacto para ser mostrada en la modal a partir del panel
     *
     * @param   string $request 
     * @return  string json
     * @author  Eliecer Cedano
     * */
    public function get_panel_experience ( Request $request )
    {
        $id = $request->id_client;
        $channel_origin = Db::table('clients')
            ->leftJoin('channels', 'clients.id_channel', 'channels.id')
            ->leftJoin('origins', 'clients.id_origin', 'origins.id')
            ->select('channels.channel', 'channels.icon as channel_icon', 'origins.icon as origin_icon', 'origins.origin', 'clients.created_at as fecha') 
            ->where('clients.id', $id)
            ->get();


        $arr_budgets = array();
        $budgets = Db::table('budgets')
            ->leftJoin('users', 'budgets.id_user', 'users.id')
            ->select('users.first_name', 'users.last_name', 'budgets.id', 'budgets.date as fecha', 'budgets.sent_client', 'budgets.id_client') 
            ->orderBy('id', 'DESC')
            ->where('budgets.id_client', $id)
            ->get();

        foreach ($budgets as $budget) {
            $tasks = Db::table('tasks')
                ->leftJoin('task_reasons', 'task_reasons.id', 'tasks.id_task_reason')
                ->leftJoin('task_results', 'task_results.id', 'tasks.id_task_result')
                ->leftJoin('events', 'events.id', 'tasks.id_event')
                ->leftJoin('users', 'tasks.id_user', 'users.id')
                ->select('tasks.*', 'task_reasons.description as razon', 'task_results.description as result', 'users.first_name', 'users.last_name', 'events.icon as task_icon', 'events.description as evento')
                ->where('tasks.id_client', $budget->id_client)
                ->where('tasks.id_budget', $budget->id)
                ->where('tasks.id_empresa', Auth::user()->id_empresa)
                ->orderBy('date', 'DESC')
                ->get();

            $send_client = $budget->sent_client;
            $estatus =  $reason = $result =  $is_closed  = $close_date = $end_date = $user = $evento = $task_icon = '';
            $n = count($tasks);
            if ( $n ) {
                $reason     = $tasks[0]->razon;
                $result     = $tasks[0]->result;
                $is_closed  = $tasks[0]->is_closed;
                $close_date = $tasks[0]->close_date;
                $end_date   = $tasks[0]->date;
                $evento     = $tasks[0]->evento;
                $task_icon  = $tasks[0]->task_icon;
                $estatus    = 'Cerrada';
                if ( ! $tasks[0]->is_closed ) {
                    $estatus = ( Carbon::parse($tasks[0]->date)->gt(Carbon::now() ) ) ? 'Pendiente' : 'Vencida';
                }
                $user       = $tasks[0]->last_name . ' ' . $tasks[0]->first_name;
            }

            $arr_budgets[] = [
                'nro'           => $budget->id, 
                'fecha'         => $budget->fecha, 
                'vend_nombre'   => $budget->last_name, 
                'vend_apellido' => $budget->first_name, 
                'n_tasks'       => $n,
                'send_client'   => $send_client,
                'estatus'       => $estatus,
                'is_closed'     => $is_closed,
                'end_date'      => $close_date,
                'vend'          => $user,
                'razon'         => $reason,
                'resultado'     => $result,
                'evento'        => $evento,
                'task_icon'     => $task_icon,
                'fecha_cierre'  => $end_date
            ];
        }


        

        return response()->json([
                'channel_origin' => $channel_origin,
                'budgets'        => $arr_budgets
            ], 200);
    }

    /**
     * Buscar informacion del entorno del contacto para ser mostrada en la modal a partir del panel
     *
     * @param   string $request 
     * @return  string json
     * @author  Eliecer Cedano
     * */
    public function get_panel_environment ( Request $request )
    {
        $id = $request->id_client;
        $data = Db::table('client_environments')
            ->leftJoin('type_environments', 'client_environments.type_environment_id', 'type_environments.id')
            ->leftJoin('client_roles', 'client_roles.id', 'client_environments.rol_id')
            ->select('client_environments.client_id2', 'client_roles.name as rol', 'client_roles.icon as rol_icon', 'type_environment_id', 'type_environments.name as relacion', 'type_environments.type_relation as tipo') 
            ->where('client_environments.client_id', $id)
            ->get();

        $env = array();
        foreach ( $data as $reg ) {
            $client = Db::table('clients')
                ->leftJoin('occupations', 'clients.id_occupation', 'occupations.id')
                ->leftJoin('genders', 'clients.id_gender', 'genders.id')
                ->select('clients.*', 'occupations.name as ocupacion', 'genders.gender as genero')
                ->where('clients.id', $reg->client_id2)
                ->get();

            $descripcion = '';
            if ( $client[0]->ocupacion )
                $descripcion .= $client[0]->ocupacion; 

            $preposic = ( $descripcion ) ? ', ' : '';
            if ( $client[0]->id_charge ) {
                $charge = DB::table('client_charges')->where('id', $client[0]->id_charge)->select('name')->first();
                $descripcion .= ( $descripcion ) ? ', '. $charge->name : $charge->name ; 
                $preposic = ' en ';
            }
            
            if ( $client[0]->company )        
                //$descripcion .= ( $descripcion ) ? ', Empresa: '. $client[0]->company : 'Empresa: '. $client[0]->company ; 
                $descripcion .= $preposic . $client[0]->company; 

            if ( $client[0]->birthday ) {
                $edad = \Carbon\Carbon::parse($client[0]->birthday)->age;
                $descripcion .= ( $descripcion ) ? ', '. $edad . ' años de edad.' : $edad . ' años de edad.' ; 
            }

            $tlf = '';
            $contact = Db::table('client_contacts')
                ->where('client_id', $reg->client_id2)
                ->get();
            $n = count( $contact );
            if ( $n ) {
                for ( $i=0; $i < $n; $i++ ) {
                    $nro  = $contact[$i]->area_code.'-'.$contact[$i]->phone;
                    $nro .= ( $contact[$i]->ext_phone ) ? $contact[$i]->ext_phone : '';

                    $tlf .= ( $contact[$i]->principal == 1 ) ? $nro. ' / ' : $nro . ' / ';
                }

                $tlf = substr( $tlf, 0, strlen ( $tlf ) - 2 );
            }

            $email = '';
            $mails = Db::table('client_mails')
                ->where('client_id', $reg->client_id2)
                ->get(); 
            $n = count( $mails );
            if ( $n ) {
                for ( $i=0; $i < $n; $i++ ) 
                    $email .= $mails[$i]->mail . ' / '; 

                $email = substr( $email, 0, strlen ( $email ) - 2 );
            }

            if ( $reg->relacion == "Cónyuge" || $reg->relacion == "Pareja" ) { // Conyuge o Pareja
                $order = 1;
            }

            if ( $reg->relacion == "Hijo" ) {
                $order = 2;
            }

            if ( $reg->relacion == "Madre" || $reg->relacion == "Padre" ) {
                $order = 3;
            }

            if ( $reg->relacion == "Hermano" || $reg->relacion == "Hermanos" || $reg->relacion == "Primo" ) {
                $order = 4;
            }

            if ( $reg->relacion == "Amigo" ) {
                $order = 5;
            }

            if ( $reg->tipo == 1 ) {
                $order = 6;
            }

            $env[] = [  'relacion' => $reg->relacion,
                        'rol'      => $reg->rol,
                        'rol_icon' => $reg->rol_icon,
                        'name'     => ucwords(strtolower($client[0]->name)),
                        'last_name' => ucwords(strtolower($client[0]->last_name)),
                        'photo'     => $client[0]->photo,
                        'genero'    => $client[0]->genero,
                        'detalle'   => $descripcion,
                        'tlf'       => $tlf,
                        'email'     => $email,
                        'order'     => $order
                    ];

        }

        $env1 = $this->orderMultiDimensionalArray($env, 'order');

        return response()->json([
                'data'      => $env1
            ], 200);
    }


    function orderMultiDimensionalArray ($toOrderArray, $field, $inverse = false) {
        $position = array();
        $newRow = array();
        foreach ($toOrderArray as $key => $row) {
                $position[$key]  = $row[$field];
                $newRow[$key] = $row;
        }
        if ($inverse) {
            arsort($position);
        }
        else {
            asort($position);
        }
        $returnArray = array();
        foreach ($position as $key => $pos) {     
            $returnArray[] = $newRow[$key];
        }
        return $returnArray;
    }


    /**
     * Buscar informacion del entorno del contacto para ser mostrada en la modal a partir del panel
     *
     * @param   string $request 
     * @return  string json
     * @author  Eliecer Cedano
     * */
    public function get_panel_tasks ( Request $request )
    {
        $id = $request->id_client;
        
        $data = Db::table('tasks')
            ->leftJoin('products', 'products.id', 'tasks.id_product')
            ->leftJoin('brands', 'brands.id', 'products.id_brand')
            ->leftJoin('models', 'models.id', 'products.id_model')
            ->leftJoin('users', 'tasks.id_employee', 'users.id')
            ->leftJoin('events', 'events.id', 'tasks.id_event')
            ->leftJoin('task_reasons', 'task_reasons.id', 'tasks.id_task_reason')
            ->leftJoin('task_results', 'task_results.id', 'tasks.id_task_result')        
            ->select('tasks.date', 'tasks.close_date', DB::raw("tasks.description AS transaction"), DB::raw("CONCAT(brands.name, ' ', models.name, ' ', products.version) AS products"), DB::raw("CONCAT(users.first_name, ' ', users.last_name) AS user"), 'events.description as event', 'events.icon', 'task_reasons.description as razon', 'task_results.description as resultado', 'tasks.id_budget', 'tasks.id_sale', 'tasks.is_closed', 'tasks.created_at') 
            ->where('tasks.id_client', $id)
            ->where('tasks.id_empresa', Auth::user()->id_empresa)
            ->orderBy('tasks.date', 'DESC')
            ->get();

        // Cargar los Acuerdos
        foreach ( $data as $reg ) {
            $agree = Db::table('budget_agreement')
                    ->leftJoin('agreements', 'agreements.id', 'budget_agreement.id_agreement')
                    ->select('budget_agreement.created_at as fecha', 'agreements.name', 'agreements.percentage', 'agreements.id_parent')
                    ->where('budget_agreement.id_budget', $reg->id_budget)
                    ->get();

            $acuerdos = '';
            if ( count( $agree ) ) {
                $details = '';                
                foreach ( $agree as $value ) {
                    $cad = '';                    
                    if ( $value->id_parent ) {
                        $det = Db::table('agreements')
                            ->select('agreements.name')
                            ->where('id', $value->id_parent)
                            ->get();
                            if ( count ( $det ) )
                                $cad = 'class="hint--top" aria-label="' . $value->name . '"';
                            
                            $details = ' <div class="hint--top" aria-label="'.$value->fecha.'"><span class="fa-stack text-success">     <i class="fa fa-check fa-stack-1x" style="margin-left:4px"></i>     <i class="fa fa-check fa-inverse fa-stack-1x" style="margin-left:-3px;"></i>     <i class="fa fa-check  fa-stack-1x" style="margin-left:-4px"></i> </span></div><div '.$cad.'>' . $det[0]->name .'</div>';
                    } else
                        $details = ' <div class="hint--top" aria-label="'.$value->fecha.'"><span class="fa-stack text-success">     <i class="fa fa-check fa-stack-1x" style="margin-left:4px"></i>     <i class="fa fa-check fa-inverse fa-stack-1x" style="margin-left:-3px;"></i>     <i class="fa fa-check  fa-stack-1x" style="margin-left:-4px"></i> </span></div><div >' . $value->name .'</div>';
                    if ( $value->percentage )
                        $details .= ' ' . $value->percentage . '% ';
                    //$details .= ' <div class="hint--top" aria-label="'.$value->fecha.'"><i class="fa fa-calendar"></i></div>';

                    if ( $details )
                        $acuerdos .= $details.' ';
                    

                }

                $acuerdos = ( $acuerdos ) ? substr($acuerdos, 0, strlen($acuerdos) - 2) : '';

                $result[] = [
                    'date'        => $reg->date, 
                    'close_date'  => $reg->close_date, 
                    'transaction' =>  $reg->transaction, 
                    'products'    => $reg->products, 
                    'user'        => $reg->user, 
                    'event'       => $reg->event, 
                    'icon'        => $reg->icon, 
                    'razon'       => $reg->razon, 
                    'resultado'   => $reg->resultado, 
                    'id_budget'   => $reg->id_budget, 
                    'id_sale'     => $reg->id_sale,  
                    'is_closed'   => $reg->is_closed, 
                    'created_at'  => $reg->created_at,
                    'agreements'  => $acuerdos
                ];

            } else {
                $result = $data;
            }
        }

        

        return response()->json([
                'data'      => $result
            ], 200);
    }

    public function get_comments( Request $request ) {
            $id = $request->id;
            $comment_set =  Db::table('comments')
                ->whereIn('id_document', function( $query ) use ( $id ) {
                        $query->select('id')
                            ->from('budgets')
                            ->where('id_client', $id);
                })
                ->orWhereIn('id_document', function( $query ) use ( $id ) {
                        $query->select('id')
                            ->from('sales')
                            ->where('id_client', $id);
                })
                ->orderBy('id', 'desc')
                ->get();

            $comments = [];
            
            foreach($comment_set as $comentario){
                
                $comment_row = '';
                
                if ( $comentario->id_users != null ) {
                    
                    $commentator = User::findorfail($comentario->id_users);
                    //if( $comentario->id_users == 12 ){ dd( $$commentator->imagen );}
                    
                    $pathCommentatorImg = 'img/users/'.$commentator->imagen;
                    $commentator->image = file_exists($pathCommentatorImg) ? $pathCommentatorImg : 'img/users/nofoto.jpg';
                    $commentator->first_name = User::query()->where('id', $comentario->id_users)->first()['first_name'];
                    $commentator->last_name = User::query()->where('id', $comentario->id_users)->first()['last_name'];
                    $commentator->fullname = $commentator->first_name.' '.$commentator->last_name;
                    //dd($comentario, $commentator);
                } else { 
                    $commentator = new User();
                    $commentator->image = 'img/users/nofoto.jpg';
                    $commentator->fullname = '';
                }
                
                $formated_date = (\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $comentario->created_at )->format('d/m/Y H:i'));
                
                $comment_row .='<div class=row>
                                    <div class="col-sm-1">
                                        <img class="img-circle" src="'.url($commentator->image).'" alt="Comentador" height="40px" />
                                        
                                    </div>
                                    <div class="col-sm-10">
                                        <p style="margin-bottom: 1px;"><b>' .$commentator->fullname. '</b></p>
                                        <p style="margin-bottom: 1px; color: grey;">' .$comentario->comment. '</p>
                                        <p style="margin-bottom: 1px; color: red; font-size:75%">' .$formated_date. '</p>
                                        <hr style="margin-top: 10px; margin-bottom: 10px;">
                                    </div>
                                </div>';
                                
                array_push($comments, $comment_row);
                
            }
            //dd( $comments );
            return response()->json([
                    'comments' => $comments
                    ],200);
        }
    
}
