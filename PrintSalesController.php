<?php

namespace App\Http\Controllers\Prints;

use App\Http\Controllers\Controller;

use App\Models\Budgets\Budget;
use App\Models\Budgets\Budget_detail;
use App\Models\Sales\Sale;
use App\Models\Sales\Sale_detail;


use App\Models\Products\Product;
use App\Models\Origin;
use App\Models\Channel;
use App\Models\Estado;
use App\Models\Localidad;
use App\Models\Document;
use App\Models\Marital_status;
use App\Models\Relation;
use App\Models\Occupation;
use App\Models\Client;
use App\Models\Client_type_location;
use App\Models\Client_type_mails;
use App\Models\Client_type_phone;
use App\Models\Client_contact;
use App\Models\Pais;
use App\Models\Charge;
use App\Models\Payments\Paymentmethod;
use App\Models\Payments\budgetcash;
use App\Models\Payments\budgetcheck;
use App\Models\Payments\budgetcredit;
use App\Models\Payments\budgetdocument;
use App\Models\Payments\budgetexpenses;
use App\Http\Requests\BudgetRequest;
use App\User;
use App\Models\Empresa;
use App\Models\Brand;

use Illuminate\Http\Request;
use DB;
use Datatables;
use Auth;
use DateTime;

use PDF;

class PrintSalesController extends Controller
{
    /**
     * @description Creates a printable of a purchase order
     * @author Héctor Agüero
     * @param Request
     * @return view
     *
     **/
    public function saleprint($id)
    {
  
        
        $budget = Budget::findorfail($id);
        
        //General data:
        if(DB::table('budgets')->where('id', $id)->exists()){
            //$registro = Budget::findorfail($id);
            $id_type_payment = $budget->id_type_payment;
            $id_type_sale = $budget->id_type_sale;
            $id_seller = $budget->id_user;
            $id_client = $budget->id_client;
            $date = (date_create_from_format('Y-m-d H:i:s', $budget->date))->format('d/m/Y');
            //COMPANY Vendria de: 
            $id_company = DB::table('users')->where('id', $id_seller)->first()->id_empresa;
            //Los ids de los productos vendrían de:
            $product_ids = DB::table('budget_details')->where('id_budget', $id)->pluck('id_product');
        } else {
            //Mientras,
            $id_type_payment = 5;
            $id_type_sale = 1;
            $id_seller = 7;
            $id_client = 7;
            $date = (new DateTime())->format('d/m/Y');
            $id_company = 5;
            $product_ids = [490, 501]; //Check if these products exist in table 'products'
        };
        //dd($date);
        
        $seller = User::findorfail($id_seller);
        
        //Seller image:
        $pathSellerImg = 'img/users/'.$seller->dni.'.png';
        if( file_exists($pathSellerImg) ){ $seller->image = $pathSellerImg; } 
            else { $seller->image = 'img/users/grey_camera.png'; }
       
        $empresa = Empresa::findorfail($id_company);
        $pathCompanyLogo = 'img/empresas/'.strtolower($empresa->nombre).'_logo.png';
        if(file_exists($pathCompanyLogo)){
            $empresa->logo = $pathCompanyLogo;
        } else {
            $empresa->logo = '';
        }
        
        //CLIENT
        if(DB::table('clients')->where('id', $id_client)->exists()){
            $client = Client::findorfail($id_client);
            $client->cel_phone = $client->client_contact()->where('id_type_phone','=',1)->first()['phone'];
            $client->home_phone = $client->client_contact()->where('id_type_phone','=',2)->first()['phone'];
            $client->mail = $client->client_mails()->where('principal','=','si')->first()['mail'];
            if ( $client->id_document == 1 ) {
                $client->dni = $client->document_nro;
            } else { $client->dni = '88888888'; }
            $client->cuitcuil = '22-88888888-1';
            
            $client->marital_status = DB::table('marital_status')->where('id',$client->id_maritals_status )->exists()?
                                        DB::table('marital_status')->where('id',$client->id_maritals_status )->first()->name : 'n/d';
            if(DB::table('iva_conditions')->where('id', $client->id_iva)->exists()){
                $client->iva_condition = DB::table('iva_conditions')->where('id', $client->id_iva)->first()->iva_condition;
            } else { $client->iva_condition = 'n/d'; }
            
            if(DB::table('countries')->where('id', $client->id_nationality)->exists()){
                $client->nacionality = DB::table('paises')->where('id', $client->id_nationality)->first()->name;
            } else {
                //Aquí debería ir $client->nacionality = 'N/D';
                //Mientras
                $client->nacionality = 'Argentina';
            }    
            
            if(DB::table('occupations')->where('id', $client->id_occupation)->exists()){
                $client->occupation = DB::table('occupations')->where('id', $client->id_occupation)->first()->name;
            } else { $client->occupation = 'n/d'; }
            
            
            
            
            
        } else {
            //Mientras, mock data
            $client = new Client();
            $client->name = 'Betulio';
            $client->last_name = 'Santana';
            $client->cel_phone = $client->client_contact()->where('id_type_phone','=',1)->first()['phone'];
            $client->home_phone = $client->client_contact()->where('id_type_phone','=',2)->first()['phone'];
            $client->mail = $client->client_mails()->where('principal','=','si')->first()['mail'];
            $client->birthday = '1980/07/07';
            $client->dni = '88888888';
            $client->cuitcuil = '22-88888888-1';
            $client->marital_status = 'Casado';
            $client->iva_condition = 'Respo. Inscr.';
            //Mientras, Falta tabla 'iva'
            $client->nacionality = 'Argentina';
            $client->occupation = 'Viajero'; 
        }
        
        //Client image:
        $pathClientImg = 'img/clients/'.$client->photo.'.png';
        if(file_exists($pathClientImg)){
            $client->image = $pathClientImg;
        } else {
            $client->image = 'img/clients/nofoto.jpg';
        }
        
        //Client Address
        if(DB::table('client_address')->where('client_id', $client->id)->exists()){
            //Just use these queries, no assigment needed.
            $client->street = $client->client_address()->where('id_type',1)->first()->street;
            $client->number_dpto = $client->client_address()->where('id_type',1)->first()->number;
            $client->floor = $client->client_address()->where('id_type',1)->first()->floor;
            $id_locality = DB::table('client_address')->where('client_id', $id_client)->first()->id_locality;
        } else {
            $client->street = 61;
            $client->number_dpto = 06;
            $client->floor = 02;
            $id_locality = 1;
        }
        $client->locality = DB::table('localidades')->where('id',$id_locality )->first()->nombre;
        //$client->city = 'city_table?';
        $id_state = DB::table('localidades')->where('id',$id_locality )->first()->id_estado;
        if(DB::table('estados')->where('id', $id_state)->exists()){
            $client->state = DB::table('estados')->where('id', $id_state)->first()->nombre;
        } else {
            $client->state = 'Florida';
        }
        
        //Conyugue:
        
        if( DB::table('client_relation_groups')->where('id_relations', '1')->
        where('id_client1', $client->id)->orwhere('id_client2', $client->id)->exists()){
            
             $id_relation = DB::table('client_relation_groups')->where('id_relations', '1')->
                where('id_client1', $client->id)->orwhere('id_client2', $client->id)->first()->id;
            //dd( $id_relation );
            
            $id_conyugue = DB::table('client_relation_groups')->where('id', $id_relation)->where('id_client1', $client->id)->exists() ?
                            DB::table('client_relation_groups')->where('id', $id_relation)->first()->id_client2 : 
                             DB::table('client_relation_groups')->where('id', $id_relation)->first()->id_client1 ;   
            
            //dd( $client->id, $id_conyugue );
            if( Client::find($id_conyugue)->exists() ){
                $conyugue = Client::find($id_conyugue)->get();
                $conyugue->document = DB::table('documents')->where('id', $conyugue->id_document)->name;
                //$conyugue->document_nro
                //$conyugue->conyugue_cuit;
                if(DB::table('countries')->where('id', $conyugue->id_nationality)->exists()){
                    $conyugue->nacionality = DB::table('countries')->where('id', $conyugue->id_nationality)->first()->name;
                } else { $conyugue->nacionality = 'n/d';}
                if(DB::table('occupations')->where('id', $conyugue->id_occupation)->exists()){
                    $conyugue->occupation = DB::table('occupations')->where('id', $client->id_occupation)->first()->name;
                } else { $conyugue->occupation = 'n/d'; }
                if(DB::table('iva_conditions')->where('id', $conyugue->id_iva)->exists()){
                    $conyugue->iva_condition = DB::table('iva_conditions')->where('id', $client->id_iva)->first()->iva_condition;
                } else { $conyugue->iva_condition = 'n/d'; }
            } 

               
        } else { 
            
            $conyugue = '';
            //return view('printables.missing_conyugue');
        } 
        
        //Products
        /* As there can be distint products in the same sale order, an array must be set up
         * with data for each one of them.
         ******************************************************************************/
        $productArray = [];
        
        //dd($product_ids);
        
        foreach ($product_ids as $id_product) {
            
            //Mientras no hay productos en tabla 'products', mock data:
            $product = new Product();
        /*    $product = Product::findorfail($id_product);  */
            
            if( DB::table('brands')->where('id', $product->id_brand )->exists()){
                $product->brand = DB::table('brands')->where('id', $product->id_brand)->first()->name;
            } else { $product->brand = 'Brand n/d'; }
            //Mientras
            $product->brand = 'Ford';
        
            //$brand = Brand::findorfail($id_brand);
            $pathBrandLogo = 'img/brands/'.strtolower($product->brand).'/'.strtolower($product->brand).'_logo.png';
            if(file_exists($pathBrandLogo)){
                //$brand->logo = $pathBrandLogo;
                $product->brandlogo = $pathBrandLogo;
            } else {
                //$brand->logo = '';
                $product->brandlogo = '';
            }
            
            if( DB::table('models')->where('id', $product->id_brand )->exists()){
                $product->model = DB::table('models')->where('id', $product->id_model)->first()->name;
            } else {$product->model = 'Model n/d'; }
            //Mientras
            $product->model = 'Ka';
            $product->version = 'XYZ';
            $product->doors = '12';
            $product->year = '2025';
            
            
            //Product color
           
            $id_paint = 59; //Just make sure 'paints' has this.
            if ( DB::table('paints')->where('id', $id_paint)->exists()){
                $product->color = DB::table('paints')->where('id', $id_paint)->first()->name;
            } else { $product->color = 'No Color'; }
            //Mientras
            $product->color = 'Blanco Oxford';
            //dd($product->color);
        
            $pathPNG = 'img/brands/'.strtolower($product->brand).'/'.strtolower($product->model).'/'.strtolower($product->model).'_'.strtolower($product->color).'.png';
            $pathJPG = 'img/brands/'.strtolower($product->brand).'/'.strtolower($product->model).'/'.strtolower($product->model).'_'.strtolower($product->color).'.jpg';
            //dd($pathJPG);
        
            if(file_exists($pathPNG)){
                $product->image = $pathPNG;
            } elseif(file_exists($pathJPG)) {
                $product->image = $pathJPG;
            } else {
                $product->image = 'img/empresas/ford_logo.png';
            }
            
        
            //Engine, 
            $product->id_engine = 3; //Make sure table 'engines' has it.
            
            if ( DB::table('engines')->where('id', $product->id_engine)->exists() ) {
                $id_fuel = DB::table('engines')->where('id', $product->id_engine)->first()->id_fuel;
                $product->fuel = DB::table('fuels')->where('id', $id_fuel)->first()->fuel;
                $id_cylinder = DB::table('engines')->where('id', $product->id_engine)->first()->id_cylinder;
                $product->cylinder = DB::table('cylinders')->where('id', $id_cylinder)->first()->name;
                $product->motor = DB::table('engines')->where('id', $product->id_engine)->first()->name;
            } else { 
                $product->fuel = 'Fuel n/d';
                $product->cylinder = 'Cylinder n/d';
                $product->motor = 'Motor n/d';
            }
            
            
            //Traction 
            $product->id_traction = 1;
            if ( DB::table('tractions')->where('id', $product->id_traction)->exists() ) {
                $product->traction = DB::table('tractions')->where('id', $product->id_traction)->first()->name;
            } else { $product->traction = 'Traction n/d'; }
            //dd($product->traction);
            
            if(DB::table('stocks')->where('id_product', $id_product)->exists()){
                $product->order_number = DB::table('stocks')->where('id_product', $id_product)->first()->order_number;
                $product->serie = DB::table('stocks')->where('id_product', $id_product)->first()->serie;
                $anioarma = DB::table('stocks')->where('id_product', $id_product)->first()->anioarma;
                $factorie_code = DB::table('stocks')->where('id_product', $id_product)->first()->factorie_code;
                $product->VIN =$anioarma.$factorie_code.$product->serie;
                
                $id_depot_details = DB::table('stocks')->where('id_product', $id_product)->first()->id_depot_detail;
                $product->stock_number = DB::table('depot_details')->where('id', $id_depot_details)->exists()? 
                    DB::table('depot_details')->where('id', $id_depot_details)->first()->name : 'n/d';    
                
            } else {
                $product->order_number = 555555;
                $product->serie = 'R3333333';
                $product->VIN = '5555HHHH5555HHHH5';
                
                $product->stock_number = 'ZZ99';
            }
            
            
            //Details
            if(DB::table('sales_details')->where('id_sale', $id)->where('id_product', $id_product)->exists()){
                $product->quantity = DB::table('sales_details')->where('id_sale', $id)->where('id_product', $id_product)->first()->quantity;
                $product->price = DB::table('sales_details')->where('id_sale', $id)->where('id_product', $id_product)->first()->price;
                $product->subtotal = DB::table('sales_details')->where('id_sale', $id)->where('id_product', $id_product)->first()->subtotal;
                //Discount:
                $product->discount = DB::table('sales_details')->where('id_sale', $id)->where('id_product', $id_product)->first()->discount_price;
                
                //$id_sale_detail = DB::table('sales_details')->where('id_sale', $id)->where('id_product', $id_product)->first()->id;
            }   
   
            else {//Mientras,
                $product->quantity = 1;
                $product->price = 25;
                $product->subtotal = 25;
                $product->discount = 5;
            }
        
            $product->discounted_price = ( $product->price - $product->discount ) * $product->quantity;
            //dd( $product->discounted_price );
        
            if(DB::table('sales_details')->where('id_sale', $id)->where('id_product', $id_product)->exists()){
                //Exists a 'sales_detail record':
                $id_sale_detail = DB::table('sales_details')->where('id_sale', $id)->where('id_product', $id_product)->first()->id;
                //A. Expenses:
                if ( $id_type_sale == 1 or $id_type_sale == 3){ //Conventional or Corporative
                
                    $product->freight_forms = DB::table('sale_expenses')->where('id_sale', $id)->where('id_sale_detail', $id_sale_detail)->first()->freight;
                    $product->patenting = DB::table('sale_expenses')->where('id_sale', $id)->where('id_sale_detail', $id_sale_detail)->first()->patent;
                
                    if( $id_type_payment == 2 or $id_type_payment == 4 or $id_type_payment == 5 ) {
                        $product->credit_exp = DB::table('sale_expenses')->where('id_sale', $id)->where('id_sale_detail', $id_sale_detail)->first()->credit;
                    } else { $product->credit_exp = 0; }
                
                    $product->inscription = DB::table('sale_expenses')->where('id_sale', $id)->where('id_sale_detail', $id_sale_detail)->first()->inscription;
                    $product->other = DB::table('sale_expenses')->where('id_sale', $id)->where('id_sale_detail', $id_sale_detail)->first()->other;
                
                } else { //Plans (or used)
                    $product->freight_forms = 0;
                    $product->patenting = 0;
                    $product->credit_exp = 0;
                    $product->inscription = 0;
                    $product->other = 0;
                }
            
            //B. Payments:
                if( $id_type_payment !== 4 ) {
                    $product->sign = DB::table('sale_cash')->where('id_sale_detail', $id_sale_detail)->first()->sign;
                    $product->cash = DB::table('sale_cash')->where('id_sale_detail', $id_sale_detail)->first()->cash;
                    $product->efectivo = $product->sign + $product->cash;
                } else { $product->efectivo = 0; } 
            
                if( $id_type_payment == 3 or $id_type_payment == 4 or $id_type_payment == 5 ) {
                    $product->used = DB::table('sale_usados')->where('id_sale_detail', $id_sale_detail)->first()->take_value;
                } else { $product->used = 0;}
            
                if( $id_type_payment == 2 or $id_type_payment == 4 or $id_type_payment == 5) {
                    $product->credit_pay = DB::table('sale_credit')->where('id_sale_detail', $id_budget_detail)->first()->capital;
                    $product->check_pay = DB::table('sale_credit')->where('id_sale_detail', $id_budget_detail)->first()->amount;
                    $product->documents_pay = DB::table('sale_credit')->where('id_sale_detail', $id_budget_detail)->first()->total;
                } else { 
                    $product->credit_pay = 0;
                    $product->check_pay = 0;
                    $product->documents_pay = 0;
                }
     
        } else {
            //Does not exists a 'sales_detail record':
            //A. Expenses:
                $product->freight_forms = 1;
                $product->patenting = 1;
            
                if( $id_type_payment == 2 or $id_type_payment == 4 or $id_type_payment == 5 ) {
                    $product->credit_exp = 1;
                } else { $product->credit_exp = 0;}
            
                $product->inscription = 0;
                //(Mientras, prueba)
                $product->other = 2;
            
            //B. Payments:
                $product->efectivo = 0; 
                //Mientras (prueba, $product->used debe ser 0 aqui)
                $product->used = 2;
            
                $product->check_pay = 0;
                $product->documents_pay = 0;
            
                if( $id_type_payment == 2 or $id_type_payment == 4 or $id_type_payment == 5) {
                    $product->credit_pay = 10;
                } else { 
                    $product->credit_pay = 0;
                }
            
        }
        
        if ( $product->credit_pay > 0 ) {  
            //if( DB::table('sale_credit')->where('id_budget', $id)->where('id_product', $id_product)->exists()){}
            $product->bank = 'Banco Santander Río S.A.';
            $product->interest = '20%';
            $product->cuotas = 6;
            $product->cuotas_val = 2;
        } else {
            $product->bank = '';
            $product->interest = '0%';
            $product->cuotas = 0;
            $product->cuotas_val = 0;
        }

        $prod_exp = $product->freight_forms + $product->patenting + $product->credit_exp + $product->inscription + $product->other;
        $prod_pay = $product->efectivo + $product->used + $product->credit_pay + $product->check_pay + $product->documents_pay;
        
        $product->discounted_exp_price = $product->discounted_price + $product->quantity * $prod_exp;
        
        $product->topay = $product->discounted_exp_price - $prod_pay;
        
        $product->id_type_sale =$id_type_sale;
        
        $comments = $budget->comments()->get();
        
        array_push($productArray, $product);
        
        } //end of foreach product id 
        
        return view('admin.printables.sales.printsale')
            ->with('budget', $budget)
            ->with('id', $id)
            ->with('date', $date)
            ->with('seller', $seller)
            ->with('client', $client)
            ->with('conyugue', $conyugue)
            ->with('productArray', $productArray)
            ->with('comments', $comments)
            ->with('empresa', $empresa);
            /*->with('brand', $brand);*/
    
    

    }
    
     /**
     * @description Generates the pdf file for the sale.
     * @author Héctor Agüero
     * @param  int  $id
     * @return \Illuminate\Http\Response
     **/
    public function salepdf($id, Request $request)
    {
        if ( $request->ajax() ) {
            
            $includeFechaEntrega    = $request->showDeliveryDate;
            $includeComentarios     = $request->showComments;
            
        } else {
            
            $includeFechaEntrega    = $_POST["includeFechaEntrega"]; 
            $includeComentarios     = $_POST["includeComentarios"];
        }
        
        $includeExpenses = true;
        
        $sale = Sale::findorfail($id);
        
        
        //General data:
        $type_payment       = $sale->id_type_payment;
        $type_sale          = $sale->id_type_sale;
        
        
        // Date of the sale:
        $date           = (\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $sale->date )->format('d/m/Y'));
        
        
        //Seller
        $seller         = User::findorfail($sale->id_user);
        $seller->image  = file_exists( 'img/users/'.$seller->imagen )
                            ? 'img/users/'.$seller->imagen 
                            : 'img/users/grey_camera.png';
        
        
        //Company
        //$id_empresa     = Auth::user()->id_empresa;
        $empresa        = Empresa::findorfail( $sale->id_company );
        $empresa->logo  = file_exists( 'img/empresas/'.strtolower($empresa->nombre).'_logo.png' )  
                        ? 'img/empresas/'.strtolower($empresa->nombre).'_logo.png' : '';
        $empresa->locality_name = DB::table('localidades')->where('id', $empresa->id_localidad)->exists()
                                    ? DB::table('localidades')->where('id', $empresa->id_localidad)->first()->nombre
                                    : 'n/d';
        
        //Client
        $client         = Client::findorfail( $sale->id_client );
          
        $pathClientImg = 'img/clients/'.$client->photo.'.png';
        $client->image = file_exists($pathClientImg) ? $client->image = $pathClientImg : 'img/clients/nofoto.jpg';
        
        $client->cel_phone      = $client->client_contact()->where('id_type_phone','=',1)->first()['phone'];
        $client->home_phone     = $client->client_contact()->where('id_type_phone','=',2)->first()['phone'];
        $client->mail           = $client->client_mails()->where('principal','=','si')->first()['mail'];
        
        $client->dni = $client->id_document == 1 ? $client->document_nro : 'n/d';
        
        if( ($client->cuit == null or $client->cuit == 0 ) and ($client->cuil == null or $client->cuil == 0 ) ) { $client->cuitcuil = 'n/d'; } 
        else { $client->cuitcuil = ($client->cuit == null or $client->cuit == 0 ) ? $client->cuil : $client->cuit; }
        
        
    
        $client->marital_status = DB::table('marital_status')->where('id',$client->id_maritals_status )->exists()
                                    ? DB::table('marital_status')->where('id',$client->id_maritals_status )->first()->name 
                                    : 'n/d';
        
        $client->iva_condition = DB::table('iva_conditions')->where('id', $client->id_iva)->exists() 
                                    ? DB::table('iva_conditions')->where('id', $client->id_iva)->first()->iva_condition
                                    : 'n/d';
                                    
        $client->nacionality =  DB::table('countries')->where('id', $client->id_nationality)->exists()
                                    ? DB::table('paises')->where('id', $client->id_nationality)->first()->name
                                    : 'n/d';
                                    
        $client->occupation =   DB::table('occupations')->where('id', $client->id_occupation)->exists()
                                    ? DB::table('occupations')->where('id', $client->id_occupation)->first()->name
                                    : 'n/d';
        
        $client->address = DB::table('client_address')->where('client_id', $client->id)->first();

        
        if(DB::table('client_address')->where('client_id', $client->id)->exists()){
            //Just use these queries, no assigment needed.
            $client->street = $client->client_address()->first()->street;
            $client->number_dpto = $client->client_address()->first()->number;
            $client->floor = $client->client_address()->first()->floor;
            $id_locality = DB::table('client_address')->where('client_id', $client->id)->first()->id_locality;
        } else {
            $client->street = 'n/d';
            $client->number_dpto = 'n/d';
            $client->floor = 'n/d';
            $id_locality = 'n/d';
        }
        
        if( isset( $id_locality ) ){
            $client->locality = DB::table('localidades')->where('id',$id_locality )->first()->nombre;
            //$client->city = 'city_table?';
            $id_state = DB::table('localidades')->where('id',$id_locality )->first()->id_estado;
            $client->state = DB::table('estados')->where('id', $id_state)->exists()
                            ? DB::table('estados')->where('id', $id_state)->first()->nombre
                            : 'n/d';
        } else {
           $client->locality    = 'n/d';
           $client->state       = 'n/d';
        }
        
        //Conyugue:
        if ( DB::table('client_relation_groups')->where('id_relations', '1')->
            where('id_client1', $client->id)->orwhere('id_client2', $client->id)->exists()){
                //Client is married

                $id_relation = DB::table('client_relation_groups')->where('id_relations', '1')->
                where('id_client1', $client->id)->orwhere('id_client2', $client->id)->first()->id;


                $id_conyugue = DB::table('client_relation_groups')->where('id', $id_relation)->where('id_client1', $client->id)->exists() ?
                    DB::table('client_relation_groups')->where('id', $id_relation)->first()->id_client2 :
                    DB::table('client_relation_groups')->where('id', $id_relation)->first()->id_client1 ;


                $conyugue = @\App\Models\Client::findorfail($id_conyugue);
                
                
                $conyugue->document = DB::table('documents')->where('id', $conyugue->id_document)->name;
                /////////////////
                $conyugue->dni = $client->id_document == 1 ? $conyugue->document_nro : 'n/d';
        
                if( ($conyugue->cuit == null or $conyugue->cuit == 0 ) and ($conyugue->cuil == null or $conyugue->cuil == 0 ) ) { $conyugue->cuitcuil = 'n/d'; } 
                else { $conyugue->cuitcuil = ($conyugue->cuit == null or $conyugue->cuit == 0 ) ? $conyugue->cuil : $conyugue->cuit; }
                /////////////////
                //$conyugue->document_nro
                //$conyugue->conyugue_cuit;
                
                $conyugue->nacionality = DB::table('countries')->where('id', $conyugue->id_nationality)->exists()
                                            ? DB::table('countries')->where('id', $conyugue->id_nationality)->first()->name
                                            : 'n/d';
                
                $conyugue->occupation = DB::table('occupations')->where('id', $conyugue->id_occupation)->exists()
                                            ? DB::table('occupations')->where('id', $client->id_occupation)->first()->name
                                            : 'n/d';
                                            
                $conyugue->iva_condition = DB::table('iva_conditions')->where('id', $conyugue->id_iva)->exists()
                                            ? DB::table('iva_conditions')->where('id', $client->id_iva)->first()->iva_condition
                                            : 'n/d';
                
        }
        ////////////////////////////////////////////
  
        else { 
            
            $conyugue = null;
            //return view('printables.missing_conyugue');
        }

        //Products
        $product_ids = DB::table('budget_details')->where('id_budget', $id)->pluck('id_product');
        
        /* As there can be distint products in the same sale order, an array must be set up
         * with data for each one of them.
         ******************************************************************************/
        $productArray = [];
        
         
        
        
        
        $index = 0;
        
        foreach ($product_ids as $id_product) {
            
            //$product    = @\App\Models\Products\Product::query()->where('id', $id_product)->first();
            
            $product = Product::findorfail($id_product);
            
            $product->brand = $product->brand->name;
            $product->model = $product->modelo->name;
            $product->color = @DB::table('paints')->where('id', @$detail->id_paint)->exists() 
                                ? @DB::table('paints')->where('id', @$detail->id_paint)->first()->name 
                                : 'BLANCO OXFORD';
            
            $pathBrandLogo      = 'img/brands/'.strtolower($product->brand).'/'.strtolower($product->brand).'_logo.png';
            $product->brandlogo = file_exists($pathBrandLogo) ? $pathBrandLogo : '';
            
            $pathPNG = 'img/brands/'.strtolower($product->brand).'/'.strtolower($product->model).'/'.strtolower($product->model).'_'.strtolower($product->color).'.png';
            $pathJPG = 'img/brands/'.strtolower($product->brand).'/'.strtolower($product->model).'/'.strtolower($product->model).'_'.strtolower($product->color).'.jpg';
            //dd($pathJPG);
        
            if(file_exists($pathPNG)){
                $product->image = $pathPNG;
            } elseif(file_exists($pathJPG)) {
                $product->image = $pathJPG;
            } else {
                $product->image = 'img/empresas/grey_camera.png';
            }
            
            //Engine
            if ( DB::table('engines')->where('id', $product->id_engine)->exists() ) {
                $id_fuel = DB::table('engines')->where('id', $product->id_engine)->first()->id_fuel;
                $product->fuel = DB::table('fuels')->where('id', $id_fuel)->first()->fuel;
                $id_cylinder = DB::table('engines')->where('id', $product->id_engine)->first()->id_cylinder;
                $product->cylinder = DB::table('cylinders')->where('id', $id_cylinder)->first()->name;
                $product->motor = DB::table('engines')->where('id', $product->id_engine)->first()->name;
            } else { 
                $product->fuel = 'Fuel n/d';
                $product->cylinder = 'Cylinder n/d';
                $product->motor = 'Motor n/d';
            }
            
            //Traction
            $product->id_traction = 1;
            if ( DB::table('tractions')->where('id', $product->id_traction)->exists() ) {
                $product->traction = DB::table('tractions')->where('id', $product->id_traction)->first()->name;
            } else { $product->traction = 'Traction n/d'; }


            // Stock number

            //$id_product = $detail->id_product;

            if(DB::table('stocks')->where('id_product', $id_product)->exists()){
                
                $product->tmaseq = $product->tma + $product->seq;
                
                $product->order_number = DB::table('stocks')->where('id_product', $id_product)->first()->order_number;
                $product->serie = DB::table('stocks')->where('id_product', $id_product)->first()->serie;
                $anioarma = DB::table('stocks')->where('id_product', $id_product)->first()->anioarma;
                $factorie_code = DB::table('stocks')->where('id_product', $id_product)->first()->factorie_code;
                $product->VIN =$anioarma.$factorie_code.$product->serie;

                $id_depot_details = DB::table('stocks')->where('id_product', $id_product)->first()->id_depot_detail;
                $product->stock_number = DB::table('depot_details')->where('id', $id_depot_details)->exists()?
                    DB::table('depot_details')->where('id', $id_depot_details)->first()->name : 'n/d';

            } else {
                
                $product->tmaseq        = 'n/d';
                $product->order_number  = 'n/d';
                $product->serie         = 'n/d';
                $product->VIN           = 'n/d';
                $product->stock_number  = 'n/d';
            }
            
            
            // Blue cedules
            $product->blue_cedules = [];

            
            //Details
            $detail = $sale->details()->where('id_product', $id_product)->first();
            
            $product->quantity  = $detail->quantity;
            $product->price     = $detail->price;
            $product->subtotal  = $detail->subtotal;
            $product->discount  = $detail->discount;
            
            //A. Expenses:
            if ( $type_sale != 2 ){ // Not by plans
                
                $product->freight_forms =   $detail->sale_expense()->first()->freight;
                
                $product->patenting     =   $detail->patenting == 2 
                                            ? $detail->sale_expense()->first()->patent
                                            : 0 ;
                
                $product->credit_exp    = ( $type_payment == 2 or $type_payment == 4 or $type_payment == 5 )
                                            ? $detail->sale_expense()->first()->credit
                                            : 0 ;
                
                $product->inscription   = $detail->sale_expense()->first()->inscription;
                
                $product->other         = $detail->sale_expense()->first()->other;
                
                $product->discounted_price = ( $product->price - $product->discount ) * $product->quantity;
                
            } else { // Sale by plans
                
                $product->freight_forms     = 0;
                $product->patenting         = 0;
                $product->credit_exp        = 0;
                $product->inscription       = 0;
                $product->other             = 0;
            }
            
            
            //Accesories. Initially, all accesories will be asociated to first product until they are discriminated in the session structure 
                                
            $product->accesories_discount = 0;
            $product->accesories_total    = 0;
                
            $product->accesories = DB::table('budget_accessories')->where('id_budget', $id)->where('id_product', $product->id)->exists()
                                    ? DB::table('budget_accessories')->where('id_budget', $id)->where('id_product', $product->id)->first()
                                    : null;
                
            if( $index == 0 and !is_null( $product->accesories ) ){ 
                    
                foreach ($product->accesories as $accesory){
                    $product->accesories_discount += $accesory->discount * $accesory->quantity;
                    $product->accesories_total    += ( $accesory->price - $accesory->discount) * $accesory->quantity;
                }
                    
            }
            
            $prod_exp = $product->freight_forms + $product->patenting + $product->credit_exp + $product->inscription + $product->other + $product->accesories_total;
            $product->discounted_exp_price = $product->discounted_price + ( $detail->quantity * $prod_exp );
            
            
            //B. Payments:
            
            if( $type_payment !== 4  and $detail->sale_cash()->exists() ) { 
                
                // There is cash pay
                
                $product->efectivo = $detail->sale_cash()->first();
                
                $product->pay_efectivo = $product->efectivo->sign + $product->efectivo->cash;
                
            } else { $product->pay_efectivo = 0; } 
            
            if( ($type_payment == 3 or $type_payment == 4 or $type_payment == 5) and
                    $detail->sale_used()->exists() ) { 
                
                // There is used pay
                
                $product->used = $detail->sale_used()->first();
                
                $product->pay_used = $product->used->used_valortoma;
                
                
                // Used Fuel
                $product->used_fuel_name = $product->used->fuel->fuel;
                
                // Used Color
                $product->used_color_name = $product->used->color->name;
                
                // Used General Status
                $product->used_status_name = $product->used->status->name;
                
            } else { $product->pay_used = 0;}
            
            if( $type_payment == 2 or $type_payment == 4 or $type_payment == 5) { 
                
                // There is credit pay
            
                if( $detail->sale_credit()->exists() ){
                    
                    $product->credit        = $detail->sale_credit()->first();
                    $product->pay_credit    = $product->credit->credit_status == 3 
                                                ? $product->credit->credit_capital    // Credit already aproved
                                                : 0;
                                                
                    // Credit status name
                    $product->credit_status_name    = $product->credit->status->status;
                    
                    // Credit name
                    $product->credit_name    = $product->credit->name->name;
                    
                    // Credit bank name
                    $product->credit_bank_name      = $product->credit->bank->name;
                    
                    // Credit cuota type name
                    $product->credit_cuota_type_name = $product->credit->cuota_type == 1 ? 'Fija' : 'Variable';
                    
                }
                
                if( $detail->sale_check()->exists() ) {
                    
                    $product->check         = $detail->sale_check()->first();
                    $product->pay_check     = $product->check->check_amount;
                    
                }
                
                if( $detail->sale_document()->exists() ) {
                    
                    $product->documents         = $detail->sale_document()->first();
                    $product->pay_documents     = $product->documents->docs_total;
                    
                }
                
            } else {
                
                $product->pay_credit        = 0;
                $product->pay_check         = 0;
                $product->pay_documents     = 0;
                    
            }
            
            
            $prod_pay = $product->pay_efectivo + $product->pay_used + $product->pay_credit + $product->pay_check + $product->pay_documents;
        
            $product->topay = $product->discounted_exp_price - $prod_pay;
        
        
        
            $product->id_type_sale = $type_sale;
        
            $comments = $sale->comments()->get();
        
            array_push($productArray, $product);
            
            $index ++;
            


        
        } //end of foreach product id 
        
        // Optional second color query
        $second_color_id = $sale->second_color_id_paint->exists()
                           ? $sale->second_color_id_paint
                           : null;
        if ( ! is_null( $second_color_id )){
            
            $sale->second_color = @DB::table('paints')->where('id', $second_color_id)->exists()
                                    ? @DB::table('paints')->where('id', $second_color_id)->first()->name
                                    : '__________';
            
        } else { $sale->second_color = '__________'; }
          
        
        //Compensation fee
        $sale->compensation_fee = $sale->compensation_fee->exists()
                                ? $sale->compensation_fee
                                : 0;
        
        $pdf = PDF::loadView('admin.printables.sales.pdfsale', compact(
            'id',
            'sale',
            'date', 
            'seller', 
            'client',
            'conyugue',
            'productArray',
            'comments',
            'empresa', 
            'includeExpenses',
            'includeFechaEntrega',
            'includeComentarios'
            ));
        
        //Put in server:
        $pdf->save( storage_path( 'sales/sale'.$id.'.pdf' ) );
        
        return $pdf->stream();
        
        //return $pdf->download('sale'.$sale->id.'.pdf');
    
    }

    
    
}
