
@section('sale_printable_partial')

    @section('css')
    <style type="text/css">
        table {
            border-spacing: 15px;
        }
        @media print {
            footer {page-break-after: always;}
        }
        .page-break {
            page-break-after: always;
        }
        
        /*Printables*/
        .test{
            color: orangered;
        }

        .oc-block {
            border-top: 3px solid #244BA6;
            padding: 10px;
            padding-top: 1px;
            padding-bottom: 1px;
            border-top-left-radius: 0.4em; 
            border-top-right-radius: 0.4em;
            margin-bottom: 0.5em;
        }

        .oc-block p{
            margin-bottom: 1px;
            font-family: Helvetica;
            font-size: 12px;
        }
        
        .lb-border{
            border-left: 1px solid lightgrey;
            border-right: 1px solid lightgrey;
            border-bottom: 1px solid lightgrey;
        }
        
        @media print {
            footer {page-break-after: always;}
        }


        .bpfooter {
            position: fixed;
            left: 0;
            bottom: 0;
            width: 100%;
            background-color: blue;
            color: white;
            text-align: center;
        }
        
        
    </style>

    @endsection

  

        @php (@$len = count($productArray))

        @if($len == 0)
            <h3> La orden <span style='color:red;'>{{ $id }}</span> no ha sido completada </h3>
        @endif
        
        
        @foreach ( $productArray as $i=>$product )

            <!--div class="container" style="background-color:white;"-->

<!--main table-->
<table style="padding-left: 10px;">

    <!--main table row 1-->
    <tr>
        <td style="width:40vw;">
            @if($empresa->logo =='')
                <p style="color:#244BA6; font-size: 50px; text-transform: uppercase; font-weight: bold;">{{$empresa->nombre}}</p>
            @else 
                <img src="{{ asset($empresa->logo) }}" class="pull-left" alt="Concesionario" style="width:90%">
            @endif
        </td>
        
        <td style="width:20vw">
            @if($product->brandlogo =='')
                <p style="color:#244BA6; font-size: 50px; text-transform: uppercase; font-weight: bold;">{{$product->brand}}</p>
            @else 
                <img src="{{ asset($product->brandlogo) }}" class="img-responsive" alt="Marca" style="width:60%; padding-left:15px;">
            @endif
        </td>
        
        
    </tr>
    
    <!--main table row 2-->
    <tr>
        <td valign="top" style="width:40vw;">
            <div class="oc-block">
                
                <div class = 'row'>
                    
                    <div class="col-md-9">
                        <img src="{{ asset($product->image) }}" alt="Imagen producto no encontrada" style="width:100%; display: block; margin-left: auto; margin-right: auto;" >
                    </div>
                    
                    <div class="col-md-3">
                        <img src="{{ asset($client->image) }}" alt="Cliente" style="width:65%; border-radius: 50%; display: block; padding-top: 10px;" >
                    </div>
                </div>
                
                
                <p style="font-size:1em; color:lightgrey; text-align:center;">
                    
                    <span>{{$product->brand}}</span>
                    <span>{{$product->model}}</span>
                    <span>{{$product->version}}</span>
                    <span>{{$product->doors}}</span><span>P</span>
                    
                    <span>{{$product->cylinder}}</span><span>Cil</span>
                    <span>{{$product->motor}}</span><span>L</span>
                    <span>{{$product->fuel}}</span>
                    
                    <span>{{$product->traction}}</span>
                    <span>{{$product->color}}</span>
                    <span>{{$product->year}}</span>
                    
                </p>
                <p style="font-size:0.75em; text-align:center;">
                    Imagen no contractual.
                </p>
                
                <p style="font-size:0.75em; text-align:center;"> 
                    <span>Catálogo:</span>&nbsp;<span>{{$product->tmaseq}}</span>&nbsp;
                    <span>Pedido:</span>&nbsp;<span>{{$product->order_number}}</span>&nbsp;
                    <span>Serie:</span>&nbsp;<span>{{$product->serie}}</span>&nbsp;
                    <span>VIN:</span>&nbsp;<span>{{$product->VIN}}</span>&nbsp;
                    <span>Stock:</span>&nbsp;<span>{{$product->stock_number}}</span>
                </p>
    
            </div>
        </td>
        
        <td valign="top" style="width:20vw">
            <div class="oc-block pull-right" style="width:25vw">
                
            	<p>Solicitud Compra: <span>{{ $sale->status==4? "SRU" : $sale->sale_number }}</span>&nbsp;&nbsp;&nbsp;&nbsp;<span>Página {{$i+1}}/{{$len}}</span></p>
                <p>Fecha: <span>{{$date}}</span></p>
                <p>Apellido: <span>{{$client->last_name}}</span></p>
                <p>Nombre: <span>{{$client->name}}</span></p>
                <p>Condominio:</p>
                <p>Domicilio: 
                    <span>Calle</span>&nbsp;<span>{{$client->street}}</span>
                    <span>N Dpto:</span>&nbsp;<span>{{$client->number_dpto}}</span>
                    <span>Piso:</span>&nbsp;<span>{{$client->floor}}</span>
                </p>
                <p> 
                    <span>Localidad:</span>&nbsp;<span>{{$client->locality}}</span>&nbsp;&nbsp;
                    <!--span>Ciudad:</span>&nbsp;<span>{{$client->locality}}</span-->&nbsp;&nbsp;
                    <span>Provincia:</span>&nbsp;<span>{{$client->state}}</span>
                </p>
                <p>Teléfono: <span>{{$client->home_phone}}</span></p>
                <p>Móvil: <span>{{$client->cel_phone}}</span></p>
                <p>E-mail: <span>{{$client->mail}}</span></p>
                <p>Fecha Nacimiento: <span>{{$client->birthday}}</span></p>
                <p>DNI: <span>{{$client->dni}}</span></p>
                <p>CUIT/CUIL: <span>{{$client->cuitcuil}}</span></p>
                <p>Condic. IVA: <span>{{$client->iva_condition}}</span></p>
                <p>Estado Civil: <span>{{$client->marital_status}}</span></p>
                <p>Nacionalidad: <span>{{$client->nacionality}}</span></p>
                <p>Ocupación: <span>{{$client->occupation}}</span></p>
                
            </div>
        </td>
    </tr>

    <!--main table row 3 Conyugue row-->
    <tr>
        <td style="width:40vw;" valign="top">
            
            @if( ! is_null( $conyugue ) )
            <div class="lb-border oc-block">
                
                <label>Cónyugue:</label>    
                <p> 
                    <span>Apellido: {{ $conyugue->lastname }}</span>&nbsp;&nbsp;
                    <span>Nombre: {{ $conyugue->name }}</span>&nbsp;&nbsp;
                    <span>Fecha Nac: {{ $conyugue->birthday }}</span>
                </p>
                <p> 
                    <span>{{ $conyugue->document }}: {{ $conyugue->document_nro }}</span>&nbsp;&nbsp;
                    <span>CUIT/CUIL: {{ $conyugue->cuitcuil }}</span>&nbsp;&nbsp;
                    <span>Nacionalidad: {{ $conyugue->nacionality }}</span>
                </p>
                <p> 
                    <span>Estado Civil: {{ $conyugue->conyugue_marital_status }}</span>&nbsp;&nbsp;
                    <span>Ocupación: {{ $conyugue->occupation }}</span>&nbsp;&nbsp;
                    <span>Condic. IVA: {{ $conyugue->iva_condition }}</span>
                </p>
            </div>
            @endif
            
        </td>
        
        <td valign="top" style="width:20vw" rowspan=3>
            <div  style="font-size: 75%; width:25vw;" class="lb-border oc-block pull-right" >
                <table>
                    <tr>
                        <th>Producto</th>
                        <th class="text-center" style="padding-left: 2rem">Cantidad</th> 
                        <th class="text-right" style="padding-left: 2rem">Precio</th>
                    </tr>
                    <tr>
                        
                        <td>
                            <span>{{ $product->brand }}</span>
                            <span>{{ $product->model }}</span>
                            <span>{{ $product->version }}</span>
                            <span>{{$product->doors}}</span><span>P</span>
                            
                            <span>{{ $product->cylinder }}</span><span>Cil</span>
                            
                            <span>{{$product->motor}}</span><span>L</span>
                            <span>{{ $product->fuel }}</span>
                            <span>{{$product->traction}}</span>
                            <span>{{$product->color}}</span>
                            <span>{{$product->year}}</span>
                        </td>
                        
                        <td class="text-center" style="padding-left: 2rem" id="bpProdQuantity">{{ $product->quantity }}</td> 
                        
                        <td class="text-right" style="padding-left: 2rem" id="bpProdPrice">{{ $product->price }}</td>
                        
                    </tr>
                    <tr>
                        <td>Precio Total</td>
                        <td class="text-center" style="padding-left: 2rem"></td> 
                        <td class="text-right" style="padding-left: 2rem" id="bpProdSubTotal">{{ $product->subtotal }}</td>
                    </tr>
                    
                @if( $product->id_type_sale != 2 )
                    
                    @if($product->discount > 0)
                    
                        <tr>
                            <td colspan="2">-Bonificación</td>
                            <td class="text-right" style="padding-left: 2rem">{{$product->discount}}</td>
                        </tr>
                 
                        <tr>
                            <td colspan="2" style="text-align:left; font-weight: bold; font-size:130%;">Total Producto</td>
                            <td class="text-right" style="padding-left: 2rem; font-weight: bold; font-size:130%">{{$product->discounted_price}}</td>
                        </tr>
                        
                    @endif
                    
                    <tr><td colspan="3" style="font-weight:bold;" id="expTitle{{$i}}" type="hidden"> Gastos a cargo del cliente</td></tr>
                    <tr>
                        <td colspan="2" style="text-align:right">Fletes y formularios</td>
                        <td class="text-right" style="padding-left: 2rem">{{$product->freight_forms}}</td>
                    </tr>
                    
                     @if( $product->patenting == 2 )
                        <tr>
                            <td colspan="2" style="text-align:right">Transferencia/Patentamiento</td>
                            <td class="text-right" style="padding-left: 2rem">{{$product->patenting}}</td>
                        </tr>
                    @endif
         
                    @if($product->credit_exp > 0)
                        <tr>
                            <td colspan="2" style="text-align:right">Crediticios</td>
                            <td class="text-right" style="padding-left: 2rem">{{$product->credit_exp}}</td>
                        </tr>
                    @endif
                    
                    @if($product->inscription > 0)
                        <tr>
                            <td colspan="2" style="text-align:right">Inscripción</td>
                            <td class="text-right" style="padding-left: 2rem">{{$product->inscription}}</td>
                        </tr>
                    @endif
                    
                    @if($product->other > 0)
                        <tr>
                            <td colspan="2" style="text-align:right">Otros</td>
                            <td class="text-right" style="padding-left: 2rem">{{$product->other}}</td>
                        </tr>
                    @endif
                    
                    @if( !is_null( $product->accesories ) )
                        <tr>
                            <td colspan="3" style="font-weight:bold;">Accesorios</td>
                        </tr>
                        @foreach( $product->accesories as $accesory )
                        
                            <tr>
                                <td colspan="2" style="text-align:right">{{$accesory->name}}</td>
                                <td class="text-right" style="padding-left: 2rem">{{$accesory->quantity * $accesory->price}}</td>
                            </tr>
                            
                        @endforeach
                        <tr>
                            <td colspan="2" style="text-align:right">Descuento Accs.</td>
                            <td class="text-right" style="padding-left: 2rem">{{$product->accesories_discount}}</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="text-align:right">Total Accs.</td>
                            <td class="text-right" style="padding-left: 2rem">{{$product->accesories_total}}</td>
                        </tr>
                        
                    @endif
                    
                    

                    <tr id="exp3">
                        
                        <td colspan="2" style="text-align:left; font-weight:bold; font-size:130%;">Total General</td>
                        <td class="text-right" style="padding-left: 2rem; font-weight:bold; font-size:130%;">{{ $product->discounted_exp_price }}</td>
                        
                    </tr> 
                    
                    <!-- Payments -->
                    <tr>
                        <td colspan="3" style="font-weight:bold;">Forma de Pago</td>
                    </tr>
                    
                    @if( $product->efectivo["sign"] > 0 )
                        <tr>
                            <td colspan="2" style="text-align:right">Depósito en Garantía</td>
                            <td class="text-right" style="padding-left: 2rem">{{$product->efectivo["sign"]}}</td>
                        </tr>
                    @endif
                    
                    @if( $product->efectivo["cash"] > 0 )
                        <tr>
                            <td colspan="2" style="text-align:right">Contado Efectivo</td>
                            <td class="text-right" style="padding-left: 2rem">{{$product->efectivo["cash"]}}</td>
                        </tr>
                    @endif
                    
                    @if( $product->pay_used > 0 )
                        <tr>
                            <td colspan="2" style="text-align:right">Usado (Valor de Toma)</td>
                            <td class="text-right" style="padding-left: 2rem">{{$product->pay_used}}</td>
                        </tr>
                    @endif
                    
                    <!--VERIFY IS CREDIT STATUS IS 'APROVED'-->
                    @if( $product->credit_pay > 0 )
                        <tr>
                            <td colspan="2" style="text-align:right">Crédito</td>
                            <td class="text-right" style="padding-left: 2rem">{{$product->pay_credit}}</td>
                        </tr>
                    @endif
                    
                    @if($product->check_pay > 0)
                        <tr>
                            <td colspan="2" style="text-align:right">Cheque</td>
                            <td class="text-right" style="padding-left: 2rem">{{$product->pay_check}}</td>
                        </tr>
                    @endif
                    
                    @if($product->documents_pay > 0)
                        <tr>
                            <td colspan="2" style="text-align:right">Documentos</td>
                            <td class="text-right" style="padding-left: 2rem">{{$product->pay_documents}}</td>
                        </tr>
                    @endif
                    
                    <tr>
                        <td colspan="2" style="text-align:left; font-weight: bold; font-size:130%;">Total a Pagar</td>
                        <td class="text-right" style="padding-left: 2rem; font-weight: bold; font-size:130%;">{{$product->topay}}</td>
                    </tr>
                @else
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
                    
                @endif

                </table>
                
                @if( $product->id_type_sale == 2 )
                    <p> * Valor promedio de las cuotas del rango detallado.</p>
                @endif
                
            </div>

            
	    </td>
	    
    </tr> <!-- End of Conyugue row -->
    
    <!-- main table row 4 Observations/Used/Blue Cedules/Credit/Fecha Entrega/Accesories row -->
    <tr>
        <td style="width:40vw;" valign="top">
            <div class="lb-border oc-block">
                <label>Observaciones:</label>
                @if( $product->used["used_valortoma"] > 0 )
                
                    <p> 
                        <span>Usado que entrega:</span>&nbsp;&nbsp;
                        <span>{{ $product->used["used_brand"] }}</span>&nbsp;&nbsp;
                        <span>{{ $product->used["used_model"] }}</span>&nbsp;&nbsp;
                        <span>{{ $product->used["used_version" ]}}</span>&nbsp;&nbsp;
                        <span>{{ $product->used["used_doors"] }}</span><span>P</span>&nbsp;&nbsp;
                        <span>{{ $product->used["used_motor"] }}</span>&nbsp;&nbsp;
                        <span>{{ $product->used["fuel_name"] }}</span>&nbsp;&nbsp;
                        <span>{{ $product->used["used_year"] }}</span>&nbsp;&nbsp;
                        <span>{{ $product->used["color_name"] }}</span>&nbsp;&nbsp;
                        <span>KM:</span>&nbsp;<span>{{ $product->used["used_kilometers"] }}</span>&nbsp;&nbsp;
                        <span>Estado:</span>&nbsp;<span>{{ $product->used["status_name"] }}</span>&nbsp;&nbsp;
                    </p>
                    
                @endif 
                
                @if( count( $product->blue_cedules ) > 0)
                <div> 
                    <p>Cédulas azules para:</p>&nbsp;&nbsp;
                    @foreach( $product->blue_cedules as $blue_cedule)
                    
                        <p>
                            <span>{{ $blue_cedule->fullname }}&nbsp;&nbsp;{{ $blue_cedule->dni }}</span>
                        
                        </p>
                        
                    @endforeach    
                </div>
                @endif
                
                @if($product->credit_pay > 0)
                
                    <p>Crédito:</p>
                    <p> 
                    
                        <span>Estado:</span>&nbsp;<span>{{ $product->credit["credit_status_name"] }}</span>&nbsp;&nbsp;
                        <span>Nombre:</span>&nbsp;<span>{{ $product->credit["credit_name"] }}</span>&nbsp;&nbsp;
                        <span>Banco:</span>&nbsp;<span>{{ $product->credit["credit_bank_name"] }}</span>&nbsp;&nbsp;
                        <span>Tasa:</span>&nbsp;<span>{{ $product->credit["interest"] }}</span>&nbsp;&nbsp;
                        <span>Cuota Variable/Fija:</span>&nbsp;<span>{{ $product->credit["credit_cuota_type_name"] }}</span>&nbsp;&nbsp;
                        
                    </p>
                    
                @endif 
                <p id="fae{{$i}}" style='display:none;'>
                    <span>Fecha Aprox. Entrega: </span>&nbsp;&nbsp;<span>{{ $sale->date_delivery }}</span>   
                </p>
                
                <!--If there are accesories -->
                @if( count( $product->accesories ) > 0)
                <div>
                    <p>Accesorios:</p>&nbsp;&nbsp;
                    @foreach( $product->accesories as $accesory)
                    
                        <p>
                            <span>{{ $accesory->name }}&nbsp;&nbsp;{{ $accesory->quantity * $accesory->price }}</span>
                        </p>
                        
                    @endforeach 
                    <p>
                        <span>Descuento&nbsp;&nbsp;{{ $product->accesories_discount }}</span>
                    </p>
                    <p>
                        <span>Total Acc&nbsp;&nbsp;{{ $product->accesories_total }}</span>
                    </p>
                </div>
                @endif
                
            </div>
        </td>
 
    </tr> <!--End of Observations row -->
    
    <!-- main table row 5 Comments row -->
    <tr >
        <td style="width:40vw; display:none;" valign="top" id="comments{{$i}}" >
            <div class="lb-border oc-block">
                <label>Comentarios:</label>
                <!-- Lista de comentarios -->
                @foreach ($comments as $comment)
                    <p>{{$comment->comment}}</p>
                @endforeach
                 
            </div>
        </td>
 
    </tr> <!--End of Comments row -->
    
    @if( $product->id_type_sale == 2 )
    <!-- main table row 6 Empezas a pagar row -->
    <tr>
        <td colspan="2" style="text-align:center"><h3>Empezas a pagar tu 0km con el pago de la primera cuota: 5</h3></td>
    </tr>  
    @endif
    
    <!-- main table row 7 Todos los precios -->
    <tr>
        <td style="width:40vw;">
            <h4 class="text-center">Todos los precios son finales iva incluído</h4>
        </td>
        
        <td id="checkContainer{{$i}}" style="width:20vw;" >

            <form action="{{ route('print.salepdf', ['id' => $id]) }}" method="post">
  				{!! csrf_field() !!}
  				
            <!--    <label class="checkbox-inline"><input type="checkbox" name="includeExpenses" id="showExpenses{{$i}}"> Gastos</label>    -->
                <label class="checkbox-inline"><input type="checkbox" name="includeFechaEntrega" id="showFechaEntrega{{$i}}"> Fecha Entrega</label>
                <label class="checkbox-inline"><input type="checkbox" name="includeComentarios" id="showComentarios{{$i}}"> Comentarios</label>
                
                <!--button type="submit" class="btn btn-sm btn-primary">Descargar Orden de Venta en PDF</button-->
                
                @if( $sale->can_pdf === true )
                <button type="button" class="btn btn-sm btn-primary" id="build_sale_pdf'.$i.'" onclick="generate_sale_pdf( {{$sale->id}} )">
                        Guardar Orden de Compra en PDF
                </button>
                @endif
                
  		    </form>
   
        </td>
    </tr>
    
</table>

<!--p>i: {{ $i }}</p-->

                <footer>
                    <p class="text-center" style="background-color: #244BA6; color:white">Direccion:&nbsp;&nbsp; {{ $empresa->direccion }}</p>
                </footer> 

            <!--/div-->

            @if($i < $len-1)
                <div class="page-break"></div>
            @endif

        @endforeach  
    								 

    
    <div class="page-break"></div>
        
    <!-- Legal text in case of Reservation -->
<div class="container-fluid">
    <table>
        <tr>
            <td>
                @if($product->brandlogo =='')
                <p style="color:#244BA6; font-size: 50px; text-transform: uppercase; font-weight: bold;">{{$product->brand}}</p>
            @else 
                <img src="{{ asset($product->brandlogo) }}" class="img-responsive" alt="Marca" style="width:60%; padding-left:15px;">
            @endif
            </td>
            <td>
                @if($empresa->logo =='')
                <p style="color:#244BA6; font-size: 50px; text-transform: uppercase; font-weight: bold;">{{$empresa->nombre}}</p>
            @else 
                <img src="{{ asset($empresa->logo) }}" class="pull-left" alt="Concesionario" style="width:90%">
            @endif
            </td>
        </tr>
        
    </table>
    <p class="text-center" style="font-weight: bold; font-size: 1.5rem">Solicitud de Reserva de Unidad</p>
    
    <p>Sres.</p>
    <p>{{ $empresa->nombre }}</p>
    <p>De mi mayor consideración.</p>
    
    <p>&nbsp;&nbsp;&nbsp;&nbsp;<span style="font-weight: bold;">{{strtoupper( $client->last_name )}},&nbsp;{{strtoupper( $client->name )}}</span> me dirijo a Uds. a fin de efectuarles una proposición
        de adquisición de unidad automotor 0 Km. en base a las siguientes estipulaciones: </p>
    </p>
    <br>
    <p>
        Tengo pleno conocimiento de que para adquirir unidades automotrices procedentes de fabricación y/o ensamblados en países extranjeros, se debe aguardar como plazos de 60 o 90 días como mínimo, 
        dependiendo del país de origen en la importación, que ante ello, es voluntad del suscripto, formular una reserva de unidad de conformidad a las pautas que propongo y que paso a detallar: 
    </p>
    <br>
    <p>
        <span style="font-weight:bold; text-decoration:underline;">Primera:</span>&nbsp;Objeto. Manifiesto que deseo formular una reserva de adquisición a futuro de una unidad 0 Km. marca {{strtoupper( $product->brand )}}
        modelo <span style="font-weight:bold">{{strtoupper( $product->brand )}}&nbsp;{{strtoupper( $product->model )}}&nbsp;{{strtoupper( $product->version )}}&nbsp;</span>catálogo 
        <span style="font-weight:bold">{{strtoupper( $product->tmaseq )}}&nbsp;</span>color <span style="font-weight:bold">{{strtoupper( $product->color )}}&nbsp;</span>. Declaro con carácter de declaración jurada, 
        que estoy en perfecto conocimiento de que se trata de una oferta aleatoria de conformidad a los artículos 1173, 1332, y 1404 a 1407 del Código Civil y que propongo la presente oferta unilateral sin 
        haber recibido promesas futuras u ofrecimientos de cualquier tipo, descartando que pueda invocar publicidad u ofertas comerciales engañosas a tenor de las normas de la ley n. 22.240. 
    </p>
    <br>
    <p>
        <span style="font-weight:bold; text-decoration:underline;">Segunda:</span>&nbsp; Aporte compromisorio. A los fines indicados, haré efectiva entrega en el acto de aceptación de la suma de 
        <span style="font-weight:bold;">(AR$ {{$product->efectivo["sign"]}} )&nbsp;</span> en concepto de reserva de la unidad de compra a futuro a condición de que se produzca realmente su ingreso al país 
        antes del día <label style="color:red">(X TOP DATE X)</label>.
    </p>
    <br>
    <p>
       <span style="font-weight:bold; text-decoration:underline;">Tercera:</span>&nbsp; Condiciones Generales. El precio de la unidad y las condiciones de pago, se determinarán al momento de efectuarse
       la facturación de la unidad; como color alternativo, en caso de no disponerse del seleccionado, opto por el <label style="color:red">(X ALTERNATIVE COLOR X)</label>. propongo, que en caso de fracasar 
       la operación sin culpa de ambas partes, se me devuelva el importe sin intereses y/o compensaciones y/o indemnizaciones; en cambio, en caso de que la unidad automotor se encontrase a mi disposición
       y la futura operación de compra fracasare por culpa del suscripto o me negase sin fundamentos a celebrar el negocio, acepto, reconocer y entregar en favor de la concesionaria {{ $empresa->nombre }} 
       una suma equivalente a <label style="color:red">( X una quita del 0% X )</label> de la suma entregada en depósito en concepto de compensación de gastos y cláusula penal por la frustación del contrato.
    </p>
    <br>
    <p>
       <span style="font-weight:bold; text-decoration:underline;">Cuarta:</span>&nbsp; La consecionaria {{ $empresa->nombre }} manifiesta que transcurridos diez días de recepcionada la presente, sin que la 
       presente haya sido formalmente rechazada mediante carta documentada, se considerará aceptada integramente la oferta, recibiendo el moento que tendrá el carácter de reserva y el que será imputado al 
       precio final de la futura venta que se celebre, en las condiciones antes propuestas.    
    </p>
    <br>
    <p>
        <span style="font-weight:bold; text-decoration:underline;">Quinta:</span>&nbsp; Las partes establecen domicilios especiales a todos los efectos en los consignados ut supra, fijándolos como válidos 
        y especiales a todos los efectos del contrato, sometiéndose a la competencia de los tribunales provinciales ordinarios de la ciudad de <span>{{ $empresa->locality_name }}</span>.
    </p>
    <br>
    <table style="width:50%;" border="1">
        <tr>
            
            <td>
                &nbsp;&nbsp;<span>{{strtoupper( $client->last_name )}},&nbsp;{{strtoupper( $client->name )}}</span>&nbsp;Tlf./Cel.:&nbsp;
                    {{ ( $client->cel_phone != null and $client->cel_phone != '' ) ? $client->cel_phone : $client->home_phone }}
            </td>
            
        </tr>
            <td>
                &nbsp;&nbsp;<span>DNI:</span>&nbsp;<span>{{$client->dni}}</span>&nbsp;<span>E-mail:</span>&nbsp;<span>{{$client->mail}}</span>
            </td>
        <tr>
            
        </tr>
    </table>
</div>


    
@endsection


