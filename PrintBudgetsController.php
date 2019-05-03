<?php

namespace App\Http\Controllers\Prints;

use App\Http\Controllers\Controller;

use App\Models\Budgets\Budget;
use App\Models\Budgets\Budget_detail;
use App\Models\Budgets\Budget_accessory;
use App\Models\Sales\Sale;
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
use Illuminate\Support\Facades\Mail;

use Illuminate\Http\Request;
use DB;
use Datatables;
use Auth;
use DateTime;
use Storage;
use Response;

use PDF;

class PrintBudgetsController extends Controller
{
    /**
     * @description Shows a budget to print.
     * @author Héctor Agüero
     * @param  int  $id
     * @return \Illuminate\Http\Response
     **/
    public function budgetprint($id)
    {
    
        
        //General data:
        if(DB::table('budgets')->where('id', $id)->exists()){
            $registro = Budget::findorfail($id);
            $id_type_payment = $registro->id_type_payment;
            $id_type_sale = $registro->id_type_sale;
            $id_seller = $registro->id_user;
            $id_client = $registro->id_client;
            $date = (date_create_from_format('Y-m-d H:i:s', $registro->date))->format('d/m/Y');
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
            $product_ids = [1011]; //Check if these products exist in table 'products'
        };
        
        $seller = User::findorfail($id_seller);
        
        //Seller image:
        $pathSellerImg = 'img/users/'.$seller->dni.'.png';
        if(file_exists($pathSellerImg)){
            $seller->image = $pathSellerImg;
        } else {
            $seller->image = 'img/users/grey_camera.png';
        }
        
        $empresa = Empresa::findorfail($id_company);
        $pathCompanyLogo = 'img/empresas/'.strtolower($empresa->nombre).'_logo.png';
        if(file_exists($pathCompanyLogo)){
            $empresa->logo = $pathCompanyLogo;
        } else {
            $empresa->logo = '';
        }
        
        //CLIENT     
        $client = Client::findorfail($id_client);
        $client->cel_phone = $client->client_contact()->where('id_type_phone','=',1)->first()['phone'];
        $client->home_phone = $client->client_contact()->where('id_type_phone','=',2)->first()['phone'];
        $client->mail = $client->client_mails()->where('principal','=','si')->first()['mail'];
        //dd($client->mail);
        
        //Products
        /* As there can be distint products in the same budget, an array must be set up
         * with data for each one of them.
         ******************************************************************************/
        $productArray = [];

        foreach ($product_ids as $id_product) {
        
            //$product = new Product();
            $product = Product::findorfail($id_product);
        
            if( DB::table('brands')->where('id', $product->id_brand )->exists()){
                $product->brand = DB::table('brands')->where('id', $product->id_brand)->first()->name;
            } else { $product->brand = 'Brand n/d'; }
            
        
            //$brand = Brand::findorfail($id_brand);
            $pathBrandLogo = 'img/brands/'.strtolower($product->brand).'/'.strtolower($product->brand).'_logo.png';
            if(file_exists($pathBrandLogo)){
                //$brand->logo = $pathBrandLogo;
                $product->brandlogo = $pathBrandLogo;
            } else {
                //$brand->logo = '';
                $product->brandlogo = '';
            }
            //dd($product->brandlogo);
        
        
        
            if( DB::table('models')->where('id', $product->id_model )->exists()){
                $product->model = DB::table('models')->where('id', $product->id_model)->first()->name;
            } else {$product->model = 'Model n/d'; }
            //Mientras
            /*$product->model = 'Ka';
            $product->version = 'XYZ';
            $product->doors = '12';
            $product->year = '2025';*/
            //dd($product->model);
            
            //Product color
            $id_paint = DB::table('budget_details')->where('id_budget', $id)->where('id_product', $id_product)->exists() ?
                        DB::table('budget_details')->where('id_budget', $id)->where('id_product', $id_product)->first()->id_paint : 59;
            $product->color =   DB::table('paints')->where('id', $id_paint)->exists() ?
                                DB::table('paints')->where('id', $id_paint)->first()->name : 'Blanco Oxford';
        
        
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
        
        
            if ( DB::table('engines')->where('id', $product->id_engine)->exists() ) {
                $id_fuel = DB::table('engines')->where('id', $product->id_engine)->first()->id_fuel;
                $product->fuel = DB::table('fuels')->where('id', $id_fuel)->first()->fuel;
                
                $id_cylinder = DB::table('engines')->where('id', $product->id_engine)->first()->id_cylinder;
                //dd( $id_cylinder);
                $product->cylinder = DB::table('cylinders')->where('id', $id_cylinder)->exists() ?
                                    DB::table('cylinders')->where('id', $id_cylinder)->first()->name : '1.7';
                $product->motor = DB::table('engines')->where('id', $product->id_engine)->first()->name;
            } else { 
                $product->fuel = 'Fuel n/d';
                $product->cylinder = 'Cylinder n/d';
                $product->motor = 'Motor n/d';
            }
        
    
            if ( DB::table('tractions')->where('id', $product->id_traction)->exists() ) {
                $product->traction = DB::table('tractions')->where('id', $product->id_traction)->first()->name;
            } else { $product->traction = 'Traction n/d'; }
            
            //Details
            $id_detail = DB::table('budget_details')->where('id_budget', $id)->where('id_product', $id_product)->exists() ?
                         DB::table('budget_details')->where('id_budget', $id)->where('id_product', $id_product)->first()->id : null;    
            if ( $id_detail != null ){
                $detail    = Budget_detail::findorfail($id_detail);
                $product->quantity  = $detail->quantity;
                $product->price     = $detail->price;
                $product->subtotal  = $detail->subtotal;
                $product->discount  = $detail->discount_price;
                
            } else {//No detail
                $product->quantity = 1;
                $product->price = 25;
                $product->subtotal = 25;
                $product->discount = 5;
    
            }
        
            $product->discounted_price = ( $product->price - $product->discount ) * $product->quantity;
            
            if ( $id_detail != null ){
                //Expenses:
                if ( $id_type_sale == 1 or $id_type_sale == 3){ //Conventional or Corporative
                    $product->freight_forms = DB::table('budget_expenses')->where('id_budget_detail', $id_detail)->exists()?
                                                DB::table('budget_expenses')->where('id_budget_detail', $id_detail)->first()->freight : 0;
                    $product->patenting = DB::table('budget_expenses')->where('id_budget_detail', $id_detail)->exists() ?
                                            DB::table('budget_expenses')->where('id_budget_detail', $id_detail)->first()->patent : 0;
                    if( $id_type_payment == 2 or $id_type_payment == 4 or $id_type_payment == 5 ) {
                        $product->credit_exp = DB::table('budget_expenses')->where('id_budget_detail', $id_detail)->exists() ?
                                                DB::table('budget_expenses')->where('id_budget_detail', $id_budget_detail)->first()->credit : 0;
                    } else { $product->credit_exp = 0; }
                    
                    $product->inscription = DB::table('budget_expenses')->where('id_budget_detail', $id_detail)->exists() ?
                                            DB::table('budget_expenses')->where('id_budget_detail', $id_detail)->first()->inscription : 0;
                                            
                    $product->other = DB::table('budget_expenses')->where('id_budget_detail', $id_detail)->exists() ?
                                        DB::table('budget_expenses')->where('id_budget_detail', $id_detail)->first()->other : 0;
                    
                }
                else {//Plans (or used)
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
                
            }
            else {//No detail
            
                //Expenses:
                $product->freight_forms = 1;
                $product->patenting = 1;
            
                if( $id_type_payment == 2 or $id_type_payment == 4 or $id_type_payment == 5 ) {
                    $product->credit_exp = 1;
                } else { $product->credit_exp = 0;}
            
                $product->inscription = 0;
                //(Mientras, prueba)
                $product->other = 2;
            
                //Payments:
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
                //if( DB::table('budget_credit')->where('id_budget', $id)->where('id_product', $id_product)->exists()){}
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
        
        //Features
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
        
        if(DB::table('prices')->where('id_product', $id_product)->exists()){
            $Vto_date = DB::table('prices')->where('id_product', $id_product)->first()->Vto_date;
            
            $product->price_expiration_date = (date_create_from_format('Y-m-d', $Vto_date))->format('d/m/Y');
        } else {
            $product->price_expiration_date = 'N/D';
        }
        
        $product->id_type_sale =$id_type_sale;
        
        array_push($productArray, $product);
        
        } //end of foreach product id
        
      
        return view('admin.printables.Budgets.printbudget')
            /*->with('budget_number', $budget_number);*/
            ->with('id', $id)
            ->with('date', $date)
            ->with('seller', $seller)
            ->with('client', $client)
            ->with('productArray', $productArray)
            ->with('empresa', $empresa);
            /*->with('brand', $brand);*/
    
    }
    
     /**
     * @description Generates the pdf file for the budget.
     * @author Héctor Agüero
     * @param  int  $id
     * @return \Illuminate\Http\Response
     **/
    public function budgetpdf($id, Request $request)
    {
        if ( $request->ajax() ) {
        //if ($request->ajax() || $request->wantsJson()) {
            $includeExpenses = $request->includeExpenses;
        } else {
            if ( isset( $_POST["includeExpenses"] ) ) {
                $includeExpenses = "true";
            } else {$includeExpenses = "false";}
        }
        
        //General data:
        if(DB::table('budgets')->where('id', $id)->exists()){
            $registro = Budget::findorfail($id);
            $id_type_payment = $registro->id_type_payment;
            $id_type_sale = $registro->id_type_sale;
            $id_seller = $registro->id_user;
            $id_client = $registro->id_client;
            $date = (date_create_from_format('Y-m-d H:i:s', $registro->date))->format('d/m/Y');
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
            $product_ids = [1011]; //Check if these products exist in table 'products'
        };
        
        $seller = User::findorfail($id_seller);
        
        //Seller image:
        $pathSellerImg = 'img/users/'.$seller->dni.'.png';
        if(file_exists($pathSellerImg)){
            $seller->image = $pathSellerImg;
        } else {
            $seller->image = 'img/users/grey_camera.png';
        }
        
        $empresa = Empresa::findorfail($id_company);
        $pathCompanyLogo = 'img/empresas/'.strtolower($empresa->nombre).'_logo.png';
        if(file_exists($pathCompanyLogo)){
            $empresa->logo = $pathCompanyLogo;
        } else {
            $empresa->logo = '';
        }
        
        //CLIENT 
        $client = Client::findorfail($id_client);
        $client->cel_phone = $client->client_contact()->where('id_type_phone','=',1)->first()['phone'];
        $client->home_phone = $client->client_contact()->where('id_type_phone','=',2)->first()['phone'];
        $client->mail = $client->client_mails()->where('principal','=','si')->first()['mail'];
        //dd($client->mail);
        
        //Products
        /* As there can be distint products in the same budget, an array must be set up
         * with data for each one of them.
         ******************************************************************************/
        $productArray = [];

        $index = 0;
        foreach ($product_ids as $id_product) {
        
            //$product = new Product();
            $product = Product::findorfail($id_product);
            
            if( DB::table('brands')->where('id', $product->id_brand )->exists()){
                $product->brand = DB::table('brands')->where('id', $product->id_brand)->first()->name;
            } else { $product->brand = 'Brand n/d'; }
            
        
            //$brand = Brand::findorfail($id_brand);
            $pathBrandLogo = 'img/brands/'.strtolower($product->brand).'/'.strtolower($product->brand).'_logo.png';
            if(file_exists($pathBrandLogo)){
                //$brand->logo = $pathBrandLogo;
                $product->brandlogo = $pathBrandLogo;
            } else {
                //$brand->logo = '';
                $product->brandlogo = '';
            }
            //dd($product->brandlogo);
        
            if( DB::table('models')->where('id', $product->id_model )->exists()){
                $product->model = DB::table('models')->where('id', $product->id_model)->first()->name;
            } else {$product->model = 'Model n/d'; }
            
            //Mientras
            /*$product->model = 'Ka';
            $product->version = 'XYZ';
            $product->doors = '12';
            $product->year = '2025';*/
            //dd($product->model);
            
            //Product color
            $id_paint = DB::table('budget_details')->where('id_budget', $id)->where('id_product', $id_product)->exists() ?
                        DB::table('budget_details')->where('id_budget', $id)->where('id_product', $id_product)->first()->id_paint : 59;
            $product->color =   DB::table('paints')->where('id', $id_paint)->exists() ?
                                DB::table('paints')->where('id', $id_paint)->first()->name : 'Blanco Oxford';
        
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
        
            
        
            if ( DB::table('engines')->where('id', $product->id_engine)->exists() ) {
                $id_fuel = DB::table('engines')->where('id', $product->id_engine)->first()->id_fuel;
                $product->fuel = DB::table('fuels')->where('id', $id_fuel)->first()->fuel;
                
                $id_cylinder = DB::table('engines')->where('id', $product->id_engine)->first()->id_cylinder;
                //dd( $id_cylinder);
                $product->cylinder = DB::table('cylinders')->where('id', $id_cylinder)->exists() ?
                                    DB::table('cylinders')->where('id', $id_cylinder)->first()->name : '1.7';
                $product->motor = DB::table('engines')->where('id', $product->id_engine)->first()->name;
            } else { 
                $product->fuel = 'Fuel n/d';
                $product->cylinder = 'Cylinder n/d';
                $product->motor = 'Motor n/d';
            }
        
          
            if ( DB::table('tractions')->where('id', $product->id_traction)->exists() ) {
                $product->traction = DB::table('tractions')->where('id', $product->id_traction)->first()->name;
            } else { $product->traction = 'Traction n/d'; }
            //dd($product->traction);
   
            //Details
            $id_detail = DB::table('budget_details')->where('id_budget', $id)->where('id_product', $id_product)->exists() ?
                         DB::table('budget_details')->where('id_budget', $id)->where('id_product', $id_product)->first()->id : null;    
            if ( $id_detail != null ){
                $detail    = Budget_detail::findorfail($id_detail);
                $product->quantity  = $detail->quantity;
                $product->price     = $detail->price;
                $product->subtotal  = $detail->subtotal;
                $product->discount  = $detail->discount_price;
                
            } else {
                $product->quantity = 1;
                $product->price = 25;
                $product->subtotal = 25;
                $product->discount = 5;
            }

        
            $product->discounted_price = ( $product->price - $product->discount ) * $product->quantity;
            
            if ( $id_detail != null ){
                //Expenses:
                if ( $id_type_sale == 1 or $id_type_sale == 3){ //Conventional or Corporative
                    $product->freight_forms = DB::table('budget_expenses')->where('id_budget_detail', $id_detail)->exists()?
                                                DB::table('budget_expenses')->where('id_budget_detail', $id_detail)->first()->freight : 0;
                    $product->patenting = DB::table('budget_expenses')->where('id_budget_detail', $id_detail)->exists() ?
                                            DB::table('budget_expenses')->where('id_budget_detail', $id_detail)->first()->patent : 0;
                    if( $id_type_payment == 2 or $id_type_payment == 4 or $id_type_payment == 5 ) {
                        $product->credit_exp = DB::table('budget_expenses')->where('id_budget_detail', $id_detail)->exists() ?
                                                DB::table('budget_expenses')->where('id_budget_detail', $id_detail)->first()->credit : 0;
                    } else { $product->credit_exp = 0; }
                    
                    $product->inscription = DB::table('budget_expenses')->where('id_budget_detail', $id_detail)->exists() ?
                                            DB::table('budget_expenses')->where('id_budget_detail', $id_detail)->first()->inscription : 0;
                                            
                    $product->other = DB::table('budget_expenses')->where('id_budget_detail', $id_detail)->exists() ?
                                        DB::table('budget_expenses')->where('id_budget_detail', $id_detail)->first()->other : 0;
                    
                }
                else {//Plans (or used)
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
                
            }
            else {//No detail
            
                //Expenses:
                $product->freight_forms = 1;
                $product->patenting = 1;
            
                if( $id_type_payment == 2 or $id_type_payment == 4 or $id_type_payment == 5 ) {
                    $product->credit_exp = 1;
                } else { $product->credit_exp = 0;}
            
                $product->inscription = 0;
                //(Mientras, prueba)
                $product->other = 2;
            
                //Payments:
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
                //if( DB::table('budget_credit')->where('id_budget', $id)->where('id_product', $id_product)->exists()){}
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
            
        //Accesories. Initially, all accesories will be asociated to first product until they are discriminated in the session structure 
        $product->accesories_discount   = 0;
        $product->accesories_total      = 0;
        $product->accesories            = [];
        if ( $index == 0 ) {
            if( DB::table('budget_accessories')->where('id_budget', $id)->where('id_product', $product->id_product)->exists() ){
            
                $accesories_ids = DB::table('budget_accessories')->where('id_budget', $id)->where('id_product', $product->id_product)->pluck('id');
                foreach($accesories_ids as $key=>$accesory_id){
                    $accesory = Budget_accessory::findorfail($accesory_id);
                    $accesories_discount += $accesory->discount * $accesory->quantity;
                    $accesories_total    += ( $accesory->price - $accesory->discount) * $accesory->quantity;
                    $product->accesories[$key]["name"]          = $accesory->name;
                    $product->accesories[$key]["subtotal"]      = $accesory->price * $accesory->quantity;
                    array_push( $product->accesories, $product->accesories[$key] );
                }
            }
        }
        
        
        $prod_exp = $product->freight_forms + $product->patenting + $product->credit_exp + $product->inscription + $product->other + $product->accesories_total;
        
        $prod_pay = $product->efectivo + $product->used + $product->credit_pay + $product->check_pay + $product->documents_pay;
        
        $product->discounted_exp_price = $product->discounted_price + $product->quantity * $prod_exp;
        
        $product->topay = $product->discounted_exp_price - $prod_pay;
        
        //Features
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
        
        if(DB::table('prices')->where('id_product', $id_product)->exists()){
            $Vto_date = DB::table('prices')->where('id_product', $id_product)->first()->Vto_date;
            $product->price_expiration_date = (date_create_from_format('Y-m-d', $Vto_date))->format('d/m/Y');
        } else {
            $product->price_expiration_date = 'N/D';
        }
        
        $product->id_type_sale =$id_type_sale;
        
        array_push($productArray, $product);
        $index ++;
        } //end of foreach product id
        
        
        $pdf = PDF::loadView('admin.printables.Budgets.pdfbudget', compact('id', 'date', 'seller', 'client', 'productArray', 'empresa', 'includeExpenses'));
        
        //Put in server:
        $pdf->save(storage_path('budgets/budget'.$id.'.pdf'));
        
        //Download to user local workstation:
        return $pdf->stream('budget'.$id.'.pdf');
        

          
    }
    
    /**
     * @description Send a budget to the client
     * @author Héctor Agüero
     * @param  int  $id
     * @return \Illuminate\Http\Response
     **/
    public function send_budget($id)
    {
        $budget         = Budget::findorfail($id);
        $budget_path    = storage_path('budgets/budget'.$id.'.pdf');

        if ( ! file_exists( $budget_path ) ) {
            $no_send_reason = ' El archivo pdf no ha sido generado. ';
            
             return response()->json([
                    'status'            => true,
                    'controller'        => 'PrintBudgetsController',
                    'title'             => 'Presupuesto NO enviado.',
                    'no_send_reason'    => $no_send_reason,
                    'type'              => 'success'
                ],200);
        }
        
        $client         = Client::findorfail( $budget->id_client );
        $client->mail   = DB::table( 'client_mails' )->where( 'client_id', $client->id)->where( 'principal',1 )->first()->mail;
        
        if ( is_null ( $client->mail ) ) {
            $no_send_reason = ' El cliente no tiene dirección de correo electrónico registrado. ';
            
             return response()->json([
                    'status'            => true,
                    'controller'        => 'PrintBudgetsController',
                    'title'             => 'Presupuesto NO enviado.',
                    'no_send_reason'    => $no_send_reason,
                    'type'              => 'success'
                ],200);
        }
        
        //Pdf already set - Client has registered email address
        
        $data                   = $client->toArray();
        $data['budget_path']    = $budget_path;
        
        $empresa            = Empresa::findorfail(Auth::user()->id_empresa);
        $data['sender_mail']    = $empresa->email;
        $data['company_name']   = $empresa->nombre;
        
        // Send the email
        Mail::send('emails.budget_mail', $data, function($message) use ($data) {
                
                $message->to($data['mail'])
                        ->from(env('MAIL_USERNAME'), $data['company_name'])
                        ->bcc('heagueron@gmail.com')
                        ->subject( 'Su presupuesto' )
                        ->attach($data['budget_path']);

        });
        
        return response()->json([
            'status'            => true,
            'controller'        => 'PrintBudgetsController',
            'title'             => 'Presupuesto enviado satisfactoriamente.',
            'no_send_reason'    => '',
            'type'              => 'success'
        ],200);
        
    }
    
    /**
     * @description Prints the budget.
     * @author Héctor Agüero
     * @param  int  $id
     * @return \Illuminate\Http\Response
     **/
    public function print_budget()
    {
        $id                 = $_POST["id"]; //target budget
        $includeExpenses    = $_POST["includeExpenses"]; 
        
        //dd( $id, $includeExpenses );
        //WHILE THERE ARE NO BUDGET INFORMATION, SOME REGISTERS ARE MOCK DATA
        
        //$registro = Budget::findorfail($id);
        
        //General data:
        if(DB::table('budgets')->where('id', $id)->exists()){
            $registro = Budget::findorfail($id);
            $id_type_payment = $registro->id_type_payment;
            $id_type_sale = $registro->id_type_sale;
            $id_seller = $registro->id_user;
            $id_client = $registro->id_client;
            $date = (date_create_from_format('Y-m-d H:i:s', $registro->date))->format('d/m/Y');
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
            $product_ids = [1011]; //Check if these products exist in table 'products'
        };
        
        $seller = User::findorfail($id_seller);
        
        //Seller image:
        $pathSellerImg = 'img/users/'.$seller->dni.'.png';
        if(file_exists($pathSellerImg)){
            $seller->image = $pathSellerImg;
        } else {
            $seller->image = 'img/users/grey_camera.png';
        }
        
        $empresa = Empresa::findorfail($id_company);
        $pathCompanyLogo = 'img/empresas/'.strtolower($empresa->nombre).'_logo.png';
        if(file_exists($pathCompanyLogo)){
            $empresa->logo = $pathCompanyLogo;
        } else {
            $empresa->logo = '';
        }
        
        //CLIENT 
        $client = Client::findorfail($id_client);
        $client->cel_phone = $client->client_contact()->where('id_type_phone','=',1)->first()['phone'];
        $client->home_phone = $client->client_contact()->where('id_type_phone','=',2)->first()['phone'];
        $client->mail = $client->client_mails()->where('principal','=','si')->first()['mail'];
        //dd($client->mail);
        
        //Products
        /* As there can be distint products in the same budget, an array must be set up
         * with data for each one of them.
         ******************************************************************************/
        $productArray = [];

        $index = 0;
        foreach ($product_ids as $id_product) {
        
            //$product = new Product();
            $product = Product::findorfail($id_product);
            
            if( DB::table('brands')->where('id', $product->id_brand )->exists()){
                $product->brand = DB::table('brands')->where('id', $product->id_brand)->first()->name;
            } else { $product->brand = 'Brand n/d'; }
            
        
            //$brand = Brand::findorfail($id_brand);
            $pathBrandLogo = 'img/brands/'.strtolower($product->brand).'/'.strtolower($product->brand).'_logo.png';
            if(file_exists($pathBrandLogo)){
                //$brand->logo = $pathBrandLogo;
                $product->brandlogo = $pathBrandLogo;
            } else {
                //$brand->logo = '';
                $product->brandlogo = '';
            }
            //dd($product->brandlogo);
        
            if( DB::table('models')->where('id', $product->id_model )->exists()){
                $product->model = DB::table('models')->where('id', $product->id_model)->first()->name;
            } else {$product->model = 'Model n/d'; }
            
   
            
            //Product color
            $id_paint = DB::table('budget_details')->where('id_budget', $id)->where('id_product', $id_product)->exists() ?
                        DB::table('budget_details')->where('id_budget', $id)->where('id_product', $id_product)->first()->id_paint : 59;
            $product->color =   DB::table('paints')->where('id', $id_paint)->exists() ?
                                DB::table('paints')->where('id', $id_paint)->first()->name : 'Blanco Oxford';
        
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

        
            if ( DB::table('engines')->where('id', $product->id_engine)->exists() ) {
                $id_fuel = DB::table('engines')->where('id', $product->id_engine)->first()->id_fuel;
                $product->fuel = DB::table('fuels')->where('id', $id_fuel)->first()->fuel;
                
                $id_cylinder = DB::table('engines')->where('id', $product->id_engine)->first()->id_cylinder;
                //dd( $id_cylinder);
                $product->cylinder = DB::table('cylinders')->where('id', $id_cylinder)->exists() ?
                                    DB::table('cylinders')->where('id', $id_cylinder)->first()->name : '1.7';
                $product->motor = DB::table('engines')->where('id', $product->id_engine)->first()->name;
            } else { 
                $product->fuel = 'Fuel n/d';
                $product->cylinder = 'Cylinder n/d';
                $product->motor = 'Motor n/d';
            }
 
            if ( DB::table('tractions')->where('id', $product->id_traction)->exists() ) {
                $product->traction = DB::table('tractions')->where('id', $product->id_traction)->first()->name;
            } else { $product->traction = 'Traction n/d'; }
            //dd($product->traction);
   
            //Details
            $id_detail = DB::table('budget_details')->where('id_budget', $id)->where('id_product', $id_product)->exists() ?
                         DB::table('budget_details')->where('id_budget', $id)->where('id_product', $id_product)->first()->id : null;    
            if ( $id_detail != null ){
                $detail    = Budget_detail::findorfail($id_detail);
                $product->quantity  = $detail->quantity;
                $product->price     = $detail->price;
                $product->subtotal  = $detail->subtotal;
                $product->discount  = $detail->discount_price;
                
            } else {
                $product->quantity = 1;
                $product->price = 25;
                $product->subtotal = 25;
                $product->discount = 5;
            }

        
            $product->discounted_price = ( $product->price - $product->discount ) * $product->quantity;
            
            if ( $id_detail != null ){
                //Expenses:
                if ( $id_type_sale == 1 or $id_type_sale == 3){ //Conventional or Corporative
                    $product->freight_forms = DB::table('budget_expenses')->where('id_budget_detail', $id_detail)->exists()?
                                                DB::table('budget_expenses')->where('id_budget_detail', $id_detail)->first()->freight : 0;
                    $product->patenting = DB::table('budget_expenses')->where('id_budget_detail', $id_detail)->exists() ?
                                            DB::table('budget_expenses')->where('id_budget_detail', $id_detail)->first()->patent : 0;
                    if( $id_type_payment == 2 or $id_type_payment == 4 or $id_type_payment == 5 ) {
                        $product->credit_exp = DB::table('budget_expenses')->where('id_budget_detail', $id_detail)->exists() ?
                                                DB::table('budget_expenses')->where('id_budget_detail', $id_detail)->first()->credit : 0;
                    } else { $product->credit_exp = 0; }
                    
                    $product->inscription = DB::table('budget_expenses')->where('id_budget_detail', $id_detail)->exists() ?
                                            DB::table('budget_expenses')->where('id_budget_detail', $id_detail)->first()->inscription : 0;
                                            
                    $product->other = DB::table('budget_expenses')->where('id_budget_detail', $id_detail)->exists() ?
                                        DB::table('budget_expenses')->where('id_budget_detail', $id_detail)->first()->other : 0;
                    
                }
                else {//Plans (or used)
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
                
            }
            else {//No detail
            
                //Expenses:
                $product->freight_forms = 1;
                $product->patenting = 1;
            
                if( $id_type_payment == 2 or $id_type_payment == 4 or $id_type_payment == 5 ) {
                    $product->credit_exp = 1;
                } else { $product->credit_exp = 0;}
            
                $product->inscription = 0;
                //(Mientras, prueba)
                $product->other = 2;
            
                //Payments:
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
                //if( DB::table('budget_credit')->where('id_budget', $id)->where('id_product', $id_product)->exists()){}
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
            
        //Accesories. Initially, all accesories will be asociated to first product until they are discriminated in the session structure 
        $product->accesories_discount   = 0;
        $product->accesories_total      = 0;
        $product->accesories            = [];
        if ( $index == 0 ) {
            if( DB::table('budget_accessories')->where('id_budget', $id)->where('id_product', $product->id_product)->exists() ){
            
                $accesories_ids = DB::table('budget_accessories')->where('id_budget', $id)->where('id_product', $product->id_product)->pluck('id');
                foreach($accesories_ids as $key=>$accesory_id){
                    $accesory = Budget_accessory::findorfail($accesory_id);
                    $accesories_discount += $accesory->discount * $accesory->quantity;
                    $accesories_total    += ( $accesory->price - $accesory->discount) * $accesory->quantity;
                    $product->accesories[$key]["name"]          = $accesory->name;
                    $product->accesories[$key]["subtotal"]      = $accesory->price * $accesory->quantity;
                    array_push( $product->accesories, $product->accesories[$key] );
                }
            }
        }
        
        
        $prod_exp = $product->freight_forms + $product->patenting + $product->credit_exp + $product->inscription + $product->other + $product->accesories_total;
        
        $prod_pay = $product->efectivo + $product->used + $product->credit_pay + $product->check_pay + $product->documents_pay;
        
        $product->discounted_exp_price = $product->discounted_price + $product->quantity * $prod_exp;
        
        $product->topay = $product->discounted_exp_price - $prod_pay;
        
        //Features
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
        
        if(DB::table('prices')->where('id_product', $id_product)->exists()){
            $Vto_date = DB::table('prices')->where('id_product', $id_product)->first()->Vto_date;
            $product->price_expiration_date = (date_create_from_format('Y-m-d', $Vto_date))->format('d/m/Y');
        } else {
            $product->price_expiration_date = 'N/D';
        }
        
        $product->id_type_sale =$id_type_sale;
        
        array_push($productArray, $product);
        $index ++;
        } //end of foreach product id
        
        
        $pdf = PDF::loadView('admin.printables.Budgets.pdfbudget', compact('id', 'date', 'seller', 'client', 'productArray', 'empresa', 'includeExpenses'));
        
        //Put in server:
        $pdf->save(storage_path('budgets/budget'.$id.'.pdf'));
        
        return $pdf->stream();
    }
    
}
