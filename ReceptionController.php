<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Recepcion;
use App\Models\Empresa;
use App\Models\Reason;
use Datatables;
use Auth;
class ReceptionController extends Controller
{

    public function index(){
        $reasons=New Reason();
        $reasons = $reasons->where('status', '=', '1')->orderBy('order', 'asc')->pluck('reason', 'id');
        $products=$this->producto_all();
        $employed=$this->users_all();
        $channel=$this->chanel_all();
        $dateini = Date('Y-m-').'1';
        $calls = Recepcion::where('id_empresas',\Auth::user()->id_empresa)->where('created_at', '>=', $dateini)->where('channel_id','=','3')->count();
        $salon = Recepcion::where('id_empresas',\Auth::user()->id_empresa)->where('created_at', '>=', $dateini)->where('channel_id','=','4')->count();
        $others = Recepcion::where('id_empresas',\Auth::user()->id_empresa)->where('created_at', '>=', $dateini)->where('channel_id','=','1')->count()+ Recepcion::where('created_at', '>=', $dateini)->where('channel_id','>=','5')->count();
        switch (Auth::user()->id_empresa){
            case 7:
                $calls = $calls + 7;
                $others = $others + 6;
                $salon = $salon + 28;
            break;
            case 4:
                $calls = $calls+2;
            break;
        }

        return view('admin.recepcion.index')
            ->withcalls($calls)
            ->withsalon($salon)
            ->withothers($others)
            ->withReasons($reasons)
            ->withProducts($products)
            ->withEmployed($employed)
            ->withChannel($channel);
    }

    public function grilla(Request $request){

        if( \Auth::user()->hasRole('root') ){
                $where = [ ['status', '=',1] ];
            }else{
                $where = [ ['id_empresas', '=', Auth::user()->id_empresa],['status', '=',1] ];
            }
         $dato = Recepcion::select([
                'id',
                'created_at',
                'client_id',
                'reason_id',
                'channel_id',
                'user_id',
                'employee_id',
                'id_producto',
                'status','mensaje'
            ])->where($where)->orderBy('created_at','DESC');

              return Datatables::eloquent($dato)
                ->addColumn('fecha', function ($dato) {
                    return   \Carbon\Carbon::parse($dato->created_at)->format('d-M')==date('d-M')?'Hoy':\Carbon\Carbon::parse($dato->created_at)->format('d-M');
                })
                ->addColumn('hora', function ($dato) {
                    return    \Carbon\Carbon::parse($dato->created_at)->format('H:i');
                })
                ->addColumn('motivo', function ($dato) {
                    return   $dato->reason->reason;
                })
                ->addColumn('chanel', function ($dato) {
                     $channeles=$this->chanel_id($dato->channel_id);
                    switch($channeles){
                        default:
                            return '<i class="fa fa-user" style="color: #2BBCDE"></i>';
                        break;
                        case 'telefonico':
                            return '<i class="fa fa-phone" style="color: #2BBCDE"></i>';
                        break;
                        case 'email':
                            return '<i class="fa fa-envelope" style="color: #2BBCDE"></i>';
                        break;
                    }

                })
                ->addColumn('comentario', function ($dato) {
                    return '<a   href="#" class="hint--top hint--medium" aria-label="'.strtoupper($dato->mensaje).'" > <i class="glyphicon glyphicon-comment"> </i></a>';
                })
                ->addColumn('nombre', function ($dato) {
                    $clients=$this->datos_clientes($dato->client_id);
                    if (count($clients)>0){
                        return ucwords($clients[0]['nombre']);
                    }else{
                        return ucwords('');
                    }
                })
                ->addColumn('apellido', function ($dato) {
                    $clients=$this->datos_clientes($dato->client_id);
                    if (count($clients)>0){
                        return ucwords($clients[0]['apellido']);
                    }else{
                        return ucwords('');
                    }
                })
                ->addColumn('telefono', function ($dato) {
                    $clients=$this->datos_clientes($dato->client_id);
                    if (count($clients)>0){
                        return $clients[0]['telefono'];
                    }else{
                        return ucwords('');
                    }
                })
                ->addColumn('email', function ($dato) {
                    $clients=$this->datos_clientes($dato->client_id);
                    if (count($clients)>0){
                        return $clients[0]['email'];
                    }else{
                        return ucwords('');
                    }
                })
                ->addColumn('atiende', function ($dato) {
                    $usuario=$this->usuario_atiende($dato->employee_id);
                    return ucwords($usuario[0]['first_name'])." ".ucwords($usuario[0]['last_name']);
                })
                ->addColumn('producto', function ($dato) {
                     $producto=$this->producto($dato->id_producto);
                    return !empty($producto[0]['name'])?$producto[0]['name']:'';
                })
                ->addColumn('E1', function ($dato) {
                    return "";
                })
                ->addColumn('E2', function ($dato) {
                    return "";
                })
                ->addColumn('action', function ($dato) {
                    $html='<button class="guardar fa fa-share-alt" onclick="clientrow(\''.$dato->client_id.'\');"></button>';
                    return $html;
                })->rawColumns(['chanel','action','comentario'])
                ->make(true);


    }

    public function vista(){
        $calls = Recepcion::where('id_empresa',\Auth::user()->id_empresa)->where('created_at', '>=', $dateini)->where('channel_id','=','3')->count();
        $salon = Recepcion::where('id_empresa',\Auth::user()->id_empresa)->where('created_at', '>=', $dateini)->where('channel_id','=','4')->count();
        $others = Recepcion::where('id_empresa',\Auth::user()->id_empresa)->where('created_at', '>=', $dateini)->where('channel_id','=','1')->count()+ Recepcion::where('created_at', '>=', $dateini)->where('channel_id','>=','5')->count();
       return response()->json(['calls'=>$calls,'salon'=>$salon,'otros'=>$others]);
    }
    protected function datos_clientes($id){
        $con=$this->conexion_base_datos(\Auth::user()->id_empresa);
        $con->set_charset("utf8");
        $sql="SELECT *  FROM clientes where id='".$id."'";
        $datos_detalles=$con->query($sql);
		return $clients=$datos_detalles->fetch_all(MYSQLI_ASSOC);
    }
    protected function usuario_atiende($id){
        $con=$this->conexion_base_datos(\Auth::user()->id_empresa);
        $con->set_charset("utf8");
        $sql="SELECT *  FROM users where id='".$id."'";
        $datos_detalles=$con->query($sql);
		return $clients=$datos_detalles->fetch_all(MYSQLI_ASSOC);
    }

    protected function producto($id){
        $con=$this->conexion_base_datos(\Auth::user()->id_empresa);
        $con->set_charset("utf8");
        $sql="SELECT productos.id as id_producto,concat(productos_catalogos.detalle2,' ',productos_catalogos.detalle3,' ',productos_catalogos.year) as name  FROM productos,productos_catalogos where productos.id_catalogo=productos_catalogos.id and productos.id='".$id."' ";
        $datos_detalles=$con->query($sql);
		return $datos_detalles->fetch_all(MYSQLI_ASSOC);
    }

    protected function users_all(){
        $con=$this->conexion_base_datos(\Auth::user()->id_empresa);
        $con->set_charset("utf8");
        $sql="SELECT *  FROM users where id_level='2' and del_logico = '0'";
        $datos_detalles=$con->query($sql);
		$usuarios=$datos_detalles->fetch_all(MYSQLI_ASSOC);
			foreach($usuarios as $list){

		$dtusers[$list['id']]=$list['first_name']." ".$list['last_name'];

			}

		return $dtusers;
    }
    protected function chanel_id($id){
        $con=$this->conexion_base_datos(\Auth::user()->id_empresa);
        $con->set_charset("utf8");
        $sql="SELECT *  FROM clientes_contacto where id='".$id."'";
        $datos_detalles=$con->query($sql);
		$channel=$datos_detalles->fetch_all(MYSQLI_ASSOC);
		return strtolower($channel[0]['tipo']);
    }
    protected function chanel_all(){
        $con=$this->conexion_base_datos(\Auth::user()->id_empresa);
        $con->set_charset("utf8");
        $sql="SELECT *  FROM clientes_contacto order by orden asc";
        $datos_detalles=$con->query($sql);
		$usuarios=$datos_detalles->fetch_all(MYSQLI_ASSOC);
			foreach($usuarios as $list){

		$dtusers[$list['id']]=$list['tipo'];

			}

		return $dtusers;
    }
    protected function producto_all(){
        $con=$this->conexion_base_datos(\Auth::user()->id_empresa);
        $con->set_charset("utf8");
        $sql="SELECT productos.id as id_producto,concat(productos_catalogos.detalle2,' ',productos_catalogos.detalle3,' ',productos_catalogos.year) as name  FROM productos,productos_catalogos where productos.id_catalogo=productos_catalogos.id and productos_catalogos.id_modelo >0";
        $datos_detalles=$con->query($sql);
		$producto=$datos_detalles->fetch_all(MYSQLI_ASSOC);

		foreach($producto as $list){
		    if(trim($list['name'])!=''){
		$dtproducto[$list['id_producto']]=$list['name'];}
		}
		return $dtproducto;
    }

    protected function conexion_base_datos($id_compania){
		 $company = Empresa::where('id',$id_compania)->first();
		 $database= $company->database_name;
		 $username=$company->database_user;
		 $pass=$company->database_pass;
		 $dsn="localhost";
		 return $conecion = mysqli_connect($dsn, $username, $pass,$database);
	}

    protected function presupuesto($datos,$idcliente){
        $con=$this->conexion_base_datos(\Auth::user()->id_empresa);
        $con->set_charset("utf8");
             switch ($datos['tipo_venta']) {
                case 2:
                    $tipo_venta = 1;
                    break;
                case 4:
                    $tipo_venta = 1;
                    break;
                case 1:
                    $tipo_venta = 2;
                    break;
                case 3:
                    $tipo_venta = 4;
                    break;
                default:
                    $tipo_venta = 1;
                    break;
            }
        $fecha=date("Y-m-d H:m:i");
        $fechabase=date("Y-m-d");
        $sql="delete from presupuestos where id_cliente = '".$idcliente."' and fecha>='".$fechabase."' and enviado_hub <1 and (id_pedido is null or id_pedido=0)";
        $presupuesto=$con->query($sql);
        $sql="insert into presupuestos (id_usuario,id_cliente,tipo_venta,id_entrega,id_presupuesto_subetapa,total)values('".$datos['id_usuario']."','".$idcliente."','".$tipo_venta."','".$datos['id_entrega']."','".$datos['id_presupuesto_subetapa']."','".$datos['total']."')";
        $presupuesto=$con->query($sql);
        $presupuesto_id=$con->insert_id;
        $sql_detalle="insert into presupuestos_detalle (id_presupuesto,id_producto,cantidad,precio,subtotal)values('".$presupuesto_id."','".$datos['producto']."','".$datos['cantidad']."','".$datos['total']."','".$datos['total']."')";
        $con->query($sql_detalle);
        $sql_message="insert into presupuestos_comentarios (id_presupuesto,id_usuario,comentario,date)values('".$presupuesto_id."','".$datos['id_usuario']."','".$datos['menssage']."',$fecha)";
        $con->query($sql_message);
        $cliente =$this->datos_clientes($idcliente);
        $vendedor=$this->usuario_atiende($datos['id_usuario']);
        $supervisor = $vendedor[0]['team_leader_id'];
        $sql="insert into leads (nombre,apellido,email_hub,phone_hub,dni,metodo_insercion, estado, id_vendedor_asignado, id_presupuesto, hora_insercion)values('".$cliente[0]['nombre']."', '".$cliente[0]['apellido']."', '".$cliente[0]['email']."', '".$cliente[0]['telefono']."', '".$cliente[0]['documento']."', 'Recepcion', 'NO CONTACTADO', '".$datos['id_usuario']."', '".$presupuesto_id."', '". date('Y-m-d H:m:i')."')";
        //echo $sql;
        $lead=$con->query($sql);
        return 1;
    }

    protected function new_cliente($datos){
        $con=$this->conexion_base_datos(\Auth::user()->id_empresa);
        $con->set_charset("utf8");
        $sql="INSERT into clientes (nombre,apellido,email,telefono,documento,id_documento, id_contacto)values('".$datos['nombre']."','".$datos['apellido']."','".$datos['email']."','".$datos['telefono']."','0','0','".$datos['id_contacto']."')";
        $result=$con->query($sql);
        return $id=$con->insert_id;
    }
    protected function producto_precio($id){
        $con=$this->conexion_base_datos(\Auth::user()->id_empresa);
        $con->set_charset("utf8");
        $sql="SELECT productos.id as id_producto,productos_catalogos.fae as total FROM productos,productos_catalogos where productos.id_catalogo=productos_catalogos.id and productos.id='".$id."' ";
        $datos_detalles=$con->query($sql);
		return $datos_detalles->fetch_all(MYSQLI_ASSOC);
    }
    protected function update_cliente($datos,$idcliente){
        $con=$this->conexion_base_datos(\Auth::user()->id_empresa);
        $con->set_charset("utf8");
        $sql="UPDATE  clientes SET nombre='".$datos['nombre']."',apellido='".$datos['apellido']."',email='".$datos['email']."',telefono='".$datos['telefono']."', id_contacto='" .$datos['id_contacto']. "' where id=".$idcliente;
        return $result=$con->query($sql);

    }

    public function new_recepcion(Request $request){
        $id_recepcion=$request->id_recepcion;
        if($id_recepcion>0){
            $drecepcion=Recepcion::where('id', $id_recepcion)->firstOrFail();
            $drecepcion->status=0;
            $drecepcion->save();}
            $mensaje=isset($request->message)?$request->message:'';
            $precios=$this->producto_precio($request->id_product);
            if($request->id_cliente==0){
                $datos=array('nombre'=>$request->first_name,'apellido'=>$request->last_name,'email'=>$request->email,'telefono'=>$request->cellphone, 'id_contacto'=>$request->id_contacto);
                $id_cliente=$this->new_cliente($datos);
                $reception = New Recepcion();
                $reception->client_id = $id_cliente;
                $reception->reason_id = $request->id_reason;
                $reception->channel_id = $request->id_contacto;
                $reception->user_id =\Auth::user()->id ;
                $reception->employee_id =$request->id_employee;
                $reception->id_producto = $request->id_product;
                $reception->id_empresas=\Auth::user()->id_empresa;
                $reception->mensaje =$mensaje;
                $reception->status = 1;
                $reception->save();
                $datos_presupuesto=array('producto'=>$request->id_product,'id_usuario'=>$request->id_employee,'tipo_venta'=>$request->id_reason,'id_entrega'=>0,'id_presupuesto_subetapa'=>13,'total'=>$precios[0]['total'],'cantidad'=>1,'menssage'=>$mensaje);
                $presupuesto=$this->presupuesto($datos_presupuesto,$id_cliente);
            }else{
                $reception = New Recepcion();
                $reception->client_id = $request->id_cliente;
                $reception->reason_id = $request->id_reason;
                $reception->channel_id = $request->id_contacto;
                $reception->user_id =\Auth::user()->id ;
                $reception->employee_id =$request->id_employee;
                $reception->id_producto = $request->id_product;
                $reception->id_empresas=\Auth::user()->id_empresa;
                $reception->mensaje =$request->message;
                $reception->status = 1;
                $reception->save();
                $datos=array('nombre'=>$request->first_name,'apellido'=>$request->last_name,'email'=>$request->email,'telefono'=>$request->cellphone, 'id_contacto'=>$request->id_contacto);
                $id_cliente=$this->update_cliente($datos,$request->id_cliente);
                $datos_presupuesto=array('producto'=>$request->id_product,'id_usuario'=>$request->id_employee,'tipo_venta'=>$request->id_reason,'id_entrega'=>0,'id_presupuesto_subetapa'=>13,'total'=>$precios[0]['total'],'cantidad'=>1,'menssage'=>$request->message);
                $presupuesto=$this->presupuesto($datos_presupuesto,$request->id_cliente);
            }

         return response()->json([ 'mensaje' => 'Datos almacenados']);

    }

}