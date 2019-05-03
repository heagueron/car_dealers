<h3 class="head text-center"> GASTOS Y PAGOS </h3>
<hr>





<div id="expense_partial-view">
    @include('admin.budgets.expenses.__partial', ['id_budget'=>'nuevo', 'budget_key'=>'budget_detail'])
    @yield('expense_partial')
</div>





<script>
	@section('js_tab__expense')

    //escribir las funciones aqui



    /**
     * Prices Cuotas  for table: preload_product_manual & preload_product_search
     *
     * @param   void
     * @return  view expense partial
     * @author  Carlos Villarroel  -  cevv07@gmail.com
     * @view    x.budgets.modal.__expense
     * */
    $('#tab_payment').click(function(){

        var id_budget       = $('#budget_id_master').val();
        var id_type_sale    = $('#budget_id_type_sale').val();
        var id_type_payment = $('#budget_id_type_payment').val();

        $.ajax({
            url : '{{url('expense/partial')}}',
            type : 'GET',
            data: { id_budget: id_budget, id_type_sale: id_type_sale, id_type_payment: id_type_payment },
            dataType : 'json',
            beforeSend: function(){
                $('#expense_partial-view').empty().append('<div class="row"><div class="col-xs-12" style="text-align: center;"> <i class="fa fa-spinner fa-pulse fa-lg fa-5x text-yellow" style="vertical-align: middle;"></i> </div> </div>');
            },
            success : function(response) {
                $("#expense_partial-view").html(response.content);
            }
        });
    });







    //Initial setup
    $('#tab_payment_XYZ').click(function(){

        const budget_id_master = $('#budget_id_master').val()  || null;
        //alert('budget_id_master: '+budget_id_master);
        const payment_method = $('#budget_id_type_payment').val();

        //Ajax to bring product expenses and payments from session
        $.ajax({
            url: 'load_expenses_payments',
            type: 'GET',
            cache: false,
            data: {
                budget_id_master: budget_id_master
            },
            dataType: 'json',

            success: function(data) {
                //alert('Respuesta del servidor -> products: '+data.products_ids);
                //alert('Respuesta del servidor -> eps: '+data.products_eps);

                var eps = data.products_eps;

                Object.keys(eps).forEach(key => {
                    alert('key: '+key);
                    //alert(eps[key].expenses.freight_forms);
                    setTabs('5', key);
                    populate_exp_pay(key, eps[key].expenses, eps[key].payments);
                });

            },

            error: function(data) {
                alert('Ajax load_expenses_payments / Error recibido del servidor: '+data.status);
            },

        }); //End of Ajax to bring product expenses and payments from session

        //In the main foreach, this will be assigned to all pay methods:
        //const pm = $('#budget_id_type_payment').text();
        //Mientras tanto,


        //$('#paymethod494').val(4); $('#paymethod495').val(4);
        $("[id^=paymethod]").val(5);
        //setTabs('5', 494);
        //setTabs('5', 495);

        //Here, after the ajax, capture products ids
        //Mientras tanto

    }); //End of Initial setup






    //Function to populate expenses and payments brought from Session
    //Receives the  product id (=>b), the expenses (=>exp) and payments (=>pay)
    function populate_exp_pay(b, exp, pay) {
        //alert('ready to populate prod: '+b);
        $('#gp_id_product'+b).text(b);

        //Expenses
        $('#flete'+b).val(exp.freight_forms);
        $('#patent'+b).val(exp.patent);
        $('#credit'+b).val(exp.credit);
        $('#inscription'+b).val(exp.inscription);
        $('#other'+b).val(exp.other);

        //Payments - efectivo
        $('#sign'+b).val(pay.efectivo.sign);
        $('#cash'+b).val(pay.efectivo.cash);

        //Payments - credito
        if(pay.credito.credit_total=='undefined' || pay.credito.credit_total==null || pay.credito.credit_total=="" || pay.credito.credit_total==0){
            $('#cb_bank'+b).val(0);
            $('#credit_content'+b).hide();
        } else {
            $('#cb_bank'+b).val(1);
            $('#credit_content'+b).show();
            alert('Hay credito banco para producto: '+b);

            $('#credit_bank'+b).val(pay.credito.credit_bank);
            $('#credit_capital'+b).val(pay.credito.credit_capital);
            $('#credit_interest'+b).val(pay.credito.credit_interest);
            $('#credit_cuotas_num'+b).val(pay.credito.credit_cuotas_num);
            $('#credit_cuotas_val'+b).val(pay.credito.credit_cuotas_val);
            $('#credit_total'+b).val(pay.credito.credit_total);
        }

        //Payments - cheques
        if(pay.cheques.check_amount=='undefined' || pay.cheques.check_amount==null || pay.cheques.check_amount=="" || pay.cheques.check_amount==0){
            $('#cb_check'+b).val(0);
            $('#check_content'+b).hide();
        } else {
            $('#cb_check'+b).val(1);
            $('#check_content'+b).show();
            alert('Hay pago con cheque para producto: '+b);

            $('#check_bank'+b).val(pay.cheques.check_bank);
            $('#check_amount'+b).val(pay.cheques.check_amount);
            $('#check_observation'+b).val(pay.cheques.check_observation);
        }

        //Payments - documentos
        if(pay.cheques.check_amount=='undefined' || pay.cheques.check_amount==null || pay.cheques.check_amount=="" || pay.cheques.check_amount==0){
            $('#cb_docs'+b).val(0);
            $('#docs_content'+b).hide();
        } else {
            $('#cb_docs'+b).val(1);
            $('#docs_content'+b).show();
            alert('Hay pago con documentos para producto: '+b);

            $('#docs_quantity'+b).val(pay.documentos.docs_quantity);
            $('#docs_value'+b).val(pay.documentos.docs_value);
            $('#docs_total'+b).val(pay.documentos.docs_total);
        }

        //Payments - usado
        $('#used_brand'+b).val(pay.usado.used_brand);
        $('#used_model'+b).val(pay.usado.used_model);
        $('#used_version'+b).val(pay.usado.used_version);
        $('#used_year'+b).val(pay.usado.used_year);
        $('#used_kilometers'+b).val(pay.usado.used_kilometers);
        $('#used_valortoma'+b).val(pay.usado.used_valortoma);

    }//End of Function to populate expenses and payments brought from Session


    //Popover to select credit options
    //This will also will be assigned to every product (after the ajax)
    //Mientras tanto,
    //const h = 'banco'+0;

    $("[id^=tab_bank]").each(function () {
        let b = this.id.substr(8);
        //var targetId = $(this).attr('href');
        var target = $('#credit_options'+b).detach();

        $(this).popover({
            placement: "bottom",
            trigger: "focus",
            html: true,
            content: function () {
                return target;
            }
        });



    });
    /*
    $("[data-toggle='popover']").on('hidden.bs.popover', function(){
        alert('The popover is now hidden.');
    });*/

    $("[id^=tab_bank]").on('hide.bs.popover', function(){

        let b = this.id.substr(8);

        setCreditOptions(b);

    });


    function setCreditOptions(b) {
        if( $('#bank_option'+b).prop('checked') ) {
            //alert('banco');
            $('#credit_content'+b).show();
        } else {$('#credit_content'+b).hide();}

        if( $('#check_option'+b).prop('checked') ) {
            //alert('cheque');
            $('#check_content'+b).show();
        } else {$('#check_content'+b).hide();}

        if( $('#docs_option'+b).prop('checked') ) {
            //alert('document');
            $('#docs_content'+b).show();
        } else {$('#docs_content'+b).hide();}
    }

    //Handlers for bank popover checkboxes changes
    function bo_change(b) {
        //alert('Cambio en bank option');
        $('#tab_bank'+b).popover("hide");

        if($('#cb_bank'+b).val()==1){
            $('#cb_bank'+b).val(0);
        } else {$('#cb_bank'+b).val(1);}

        setCreditOptions(b);
    }
    function co_change(b) {
        //alert('Cambio en bank option');
        $('#tab_bank'+b).popover("hide");

        if($('#cb_check'+b).val()==1){
            $('#cb_check'+b).val(0);
        } else {$('#cb_check'+b).val(1);}

        setCreditOptions(b);
    }
    function do_change(b) {
        //alert('Cambio en bank option');
        $('#tab_bank'+b).popover("hide");

        if($('#cb_docs'+b).val()==1){
            $('#cb_docs'+b).val(0);
        } else {$('#cb_docs'+b).val(1);}

        setCreditOptions(b);
    }
    //End of Handlers for bank popover checkboxes changes

    //Handler to recolect and send product expenses and payments to session
    $("[id^=send_gp]").on('click', function(e){
        e.preventDefault();

        const budget_id_master = $('#budget_id_master').text()  || null;

        let b = this.id.substr(7);
        //alert('enviar datos producto: '+j);

        var gp_id_product = $('#gp_id_product'+b).text();

        var gp_expenses = {
            'freight_forms': $('#flete'+b).val(),
            'patent': $('#patent'+b).val(),
            'credit': $('#credit'+b).val(),
            'inscription': $('#inscription'+b).val(),
            'other': $('#other'+b).val(),
        };

        var gp_cash = {
            'sign': $('#sign'+b).val(),
            'cash': $('#cash'+b).val()
        };

        var gp_credit = {
            'credit_bank': $('#credit_bank'+b).val(),
            'credit_capital': $('#credit_capital'+b).val(),
            'credit_interest': $('#credit_interest'+b).val(),
            'credit_cuotas_num': $('#credit_cuotas_num'+b).val(),
            'credit_cuotas_val': $('#credit_cuotas_val'+b).val(),
            'credit_total': $('#credit_total'+b).val()
        };

        var gp_check = {
            'check_bank': $('#check_bank'+b).val(),
            'check_amount': $('#check_amount'+b).val(),
            'check_observation': $('#check_observation'+b).val()
        };

        var gp_documents = {
            'docs_quantity' : $('#docs_quantity'+b).val(),
            'docs_value' :    $('#docs_value'+b).val(),
            'docs_total' :    $('#docs_total'+b).val()
        };

        var gp_used = {
            'used_brand' : $('#used_brand'+b).val(),
            'used_model' : $('#used_model'+b).val(),
            'used_version' : $('#used_version'+b).val(),
            'used_year' : $('#used_year'+b).val(),
            'used_kilometers' : $('#used_kilometers'+b).val(),
            'used_valortoma' : $('#used_valortoma'+b).val(),
        };


        //alert('gp_payments.credit.credit_capital: '+credit.credit_capital);

        const token = '{{csrf_token()}}';

        //Ajax to send product expenses and payments to session
        $.ajax({
            url: 'store_product_expenses',
            type: 'POST',
            cache: false,
            data: {
                _token: token,
                budget_id_master: budget_id_master,
                gp_id_product: gp_id_product,
                gp_expenses: gp_expenses,
                gp_cash: gp_cash,
                gp_credit: gp_credit,
                gp_check: gp_check,
                gp_documents: gp_documents,
                gp_used: gp_used
            },
            dataType: 'json',

            success: function(data) {
                alert('Respuesta del servidor -> title : '+data.title);
                //alert('gp_documents: '+data.gp_documents.docs_value);
            },
            error: function(data) {
                alert('Error recibido del servidor: '+data.status);
            },

        }); //End of ajax to send product expenses and payments to session

    }); //End of Handler to recolect and send product expenses and payments to session

    //Handler for credit_cuotas_num changes
    $("[id^=credit_cuotas_num]").change(function(event){
        event.preventDefault();
        let j = this.id.substr(17);
        //alert("Cambio en crd cuotas num de prod: "+j);
        let ct = $('#credit_cuotas_num'+j).val() * $('#credit_cuotas_val'+j).val();
        $('#credit_total'+j).val(ct);
    }); //End of Handle for credit_cuotas_num changes

    //Handler for credit_cuotas_val changes
    $("[id^=credit_cuotas_val]").change(function(event){
        event.preventDefault();
        let j = this.id.substr(17);
        //alert("Cambio en crd cuotas num de prod: "+j);
        let ct = $('#credit_cuotas_num'+j).val() * $('#credit_cuotas_val'+j).val();
        $('#credit_total'+j).val(ct);
    }); //End of Handle for credit_cuotas_val changes

    //Handler for docs_quantity changes
    $("[id^=docs_quantity]").change(function(event){
        event.preventDefault();
        let j = this.id.substr(13);
        let dt = $('#docs_quantity'+j).val() * $('#docs_value'+j).val();
        $('#docs_total'+j).val(dt);
    }); //End of Handle for docs_quantity changes

    //Handler for docs_value changes
    $("[id^=docs_value]").change(function(event){
        event.preventDefault();
        let j = this.id.substr(10);
        let dt = $('#docs_quantity'+j).val() * $('#docs_value'+j).val();
        $('#docs_total'+j).val(dt);
    }); //End of Handle for docs_value changes



    $("[id^=tab_expenses]").on('click', function(){
        let j = this.id.substr(12);
        $('#infPyG' + j).html('<i class="glyphicon glyphicon-usd"></i> Gastos');
    });
    $("[id^=tab_cash]").on('click', function(){
        let j = this.id.substr(8);
        $('#infPyG' +j).html('<i class="fa fa-money"></i> Pago con efectivo');
        //alert('hi');
    });

    /* Tab information is part of credit, handled in tab_bank
    $("[id^=tab_check]").on('click', function(){
        j = this.id.substr(9);
        $('#infPyG' +j).html('<i class="fa fa-edit"></i> Pago con cheques');
        setCreditOptions(j);
    });*/

    $("[id^=tab_bank]").on('click', function(){
        let j = this.id.substr(8);
        $('#infPyG' +j).html('<i class="fa fa-bank"></i> Pago con crédito');

        if($('#cb_bank'+j).val()==1){
            $('#bank_option'+j).prop('checked', true);
        }else{
            $('#bank_option'+j).prop('checked', false);
        }

        if($('#cb_check'+j).val()==1){
            $('#check_option'+j).prop('checked', true);
        }else{
            $('#check_option'+j).prop('checked', false);
        }

        if($('#cb_docs'+j).val()==1){
            $('#docs_option'+j).prop('checked', true);
        }else{
            $('#docs_option'+j).prop('checked', false);
        }

        setCreditOptions(j);

    });
    $("[id^=tab_docs]").on('click', function(){
        let j = this.id.substr(8);
        $('#infPyG' +j).html('<i class="glyphicon glyphicon-list-alt"></i> Pago con documentos');
    });
    $("[id^=tab_used]").on('click', function(){
        let j = this.id.substr(8);
        $('#infPyG' +j).html('<i class="fa fa-car"></i> Pago con usados');
    });

    $("[id^=paymethod]").change(function(event){
        event.preventDefault();
        let paymethod = event.target.value;
        let prodIndex = this.id.substr(9);
        //alert('pm: '+paymethod+' '+'prodIndex: '+prodIndex);

        setTabs(paymethod, prodIndex);
    });

    //Select the tabs to show according to the type of payment pm, for the product index pi
    function setTabs (pm, pi) {
        //alert('setTabs: '+'pm: '+pm+' pi: '+pi);
        switch (pm) {

            case '1':
                $('#tab_cash'+pi).show();
                $('#tab_check'+pi).hide(); $('#tab_bank'+pi).hide(); $('#tab_docs'+pi).hide(); $('#tab_used'+pi).hide();
                $('#pm' + pi).html('Contado');
                break;

            case '2':
                $('#tab_cash'+pi).show();  $('#tab_bank'+pi).show();
                $('#tab_check'+pi).hide(); $('#tab_docs'+pi).hide(); $('#tab_used'+pi).hide();
                $('#pm' + pi).html('Contado + crédito');
                break;

            case '3':
                $('#tab_cash'+pi).show();
                $('#tab_bank'+pi).hide(); $('#tab_check'+pi).hide(); $('#tab_docs'+pi).hide();
                $('#tab_used'+pi).show();
                $('#pm' + pi).html('Contado + usado');
                break;

            case '4':
                $('#tab_cash'+pi).hide(); $('#tab_check'+pi).hide();
                $('#tab_bank'+pi).show(); $('#tab_docs'+pi).hide(); $('#tab_used'+pi).show();
                $('#pm' + pi).html('Crédito + usado');
                break;

            case '5':
                $('#tab_cash'+pi).show(); $('#tab_check'+pi).hide(); $('#tab_bank'+pi).show(); $('#tab_docs'+pi).hide(); $('#tab_used'+pi).show();
                $('#pm' + pi).html('Contado + crédito + usado');
                break;

            default:
                $('#tab_cash'+pi).show(); $('#tab_check'+pi).hide(); $('#tab_bank'+pi).show(); $('#tab_docs'+pi).hide(); $('#tab_used'+pi).show();
                $('#pm' + pi).html('Contado + crédito + usado');
        }
    }


	@endsection


    /**  Funciones para form_ajax_success: solo si se usa validation_beta  **/

	@section('form_ajax_success_tab__expense')

	@endsection
</script>