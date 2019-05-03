import { Component, OnInit, OnDestroy, Input, ViewEncapsulation } from '@angular/core';
import { FormGroup, FormControl, Validators } from '@angular/forms';

import { Store } from '@ngrx/store';
import { AppState } from 'src/app/store/sellers';

import { Subscription } from 'rxjs';
import { ContactModel } from 'src/app/core/models/contact.model';

import * as contactsActions from '../../../../../../store/contacts/index';

//EXTERNAL
//import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import { NgSelectConfig } from '@ng-select/ng-select';
import { NgOption } from '@ng-select/ng-select';

declare const $: any;
declare const moment: any;

@Component({
  selector: 'app-register-reception',
  templateUrl: './register-reception.component.html',
  styleUrls: ['./register-reception.component.css'],
  encapsulation: ViewEncapsulation.None
})
export class RegisterReceptionComponent implements OnInit, OnDestroy{

 

  receptionId: number;
  name: string;

  receptionForm: FormGroup;

  selectReasonOptions: Array<any>;
  selectProductOptions: Array<any>;
  selectChannelOptions: Array<any>;
  selectSellerOptions: Array<any>;
  selectEmpathyOptions: Array<any>;

  subscription: Subscription = new Subscription();
  constructor( 
    private store           : Store<AppState>,
    //public activeModal      : NgbActiveModal,
    private ngSelectConfig  : NgSelectConfig)

    {
    this.ngSelectConfig.notFoundText = 'Custom not found';
    
    this.selectReasonOptions = [];
    this.selectProductOptions = [];
    this.selectChannelOptions = [];
    this.selectSellerOptions = [];
    this.selectEmpathyOptions = [];
    }

  ngOnInit() {

    // Main form:
    this.receptionForm = new FormGroup({
      'name'         : new FormControl( '', Validators.required ),
      'last_name'  : new FormControl( '', Validators.required ),
      'phone'      : new FormControl( '' ),
      'mobile'     : new FormControl( '' ),
      'mail'       : new FormControl( '' ),
      'reason'     :  new FormControl( '' ),
      'reason_id'  : new FormControl( 1, Validators.min(1) ),
      'seller'     : new FormControl( '' ),
      'seller_id'  : new FormControl( 1  ),
      'product'    : new FormControl( '' ),
      'product_id' : new FormControl( 1, Validators.min(1) ),
      'channel'    : new FormControl( '' ),
      'channel_id' : new FormControl( 1, Validators.min(1) ),
      'comment'    : new FormControl( '' ),
      'empathy_user_client'     : new FormControl( '' ),
      'empathy_client_user'     : new FormControl( '' ),
      'empathy_user_client_id'  : new FormControl( 1  ),
      'empathy_client_user_id'  : new FormControl( 1  )
    });


    // Listen to contacts and request to fill the form with selected reception
    this.store.select('contacts')
      .subscribe(  contacts => {
        if( contacts.selectedReception != null){
          console.log(`SELECTEDT RECEPTION ID: ${contacts.selectedReception.id} `);
          this.setInitialFormValues( contacts.selectedReception );
          this.store.dispatch( new contactsActions.actions.UnsetSelectedReceptionAction );
        } 
    }); 

    
    console.warn(`OnInit warn the FORM: ${this.receptionForm.value}`);
  
    // Selectables options
    this.setSelectablesOptions();    


  } // ngOnInit end

  ngOnDestroy() {
    this.subscription.unsubscribe();
  }

  setInitialFormValues( reception: ContactModel) {
    console.log("INSIDE setInitialFormValues")
    console.warn(`warn the FORM: ${this.receptionForm.value}`);
    this.receptionForm.setValue({
      name       :  reception.name,
      last_name  :  reception.last_name,
      phone      :  reception.phone,
      mobile     :  reception.mobile,
      mail       :  reception.mail,
      reason     :  reception.reason,
      reason_id  :  reception.reason_id,
      seller     :  `${reception.seller_name} ${reception.seller_lastname}`,
      seller_id  :  reception.seller_id,
      product    :  reception.product,
      product_id :  reception.product_id,
      channel    :  reception.channel,
      channel_id :  reception.channel_id,
      comment    :  reception.commment == null? '' : reception.commment,
      empathy_user_client     :  reception.empathy_user_client == null? '' : reception.empathy_user_client,
      empathy_user_client_id  :  reception.empathy_user_client_id == null? '' : reception.empathy_user_client_id,
      empathy_client_user     :  reception.empathy_client_user == null? '' : reception.empathy_client_user,
      empathy_client_user_id  :  reception.empathy_client_user_id == null? '' : reception.empathy_client_user_id 
    });
  }

  setSelectablesOptions(){
    
    // Reasons
    this.selectReasonOptions.push(
      {id: 1, name: 'Venta de planes'}, 
      {id: 2, name: 'Venta convencional'},
      {id: 3, name: 'Venta Usados'},
      {id: 4, name: 'Ventas'},
      {id: 5, name: 'Adm Plan Ahorro'},
      {id: 6, name: 'Cupón Plan'},
      {id: 7, name: 'Adm convencional'},
      {id: 8, name: 'Administración'},
      {id: 9, name: 'Servicio'},
      {id: 10, name: 'Repuestos'},
      {id: 11, name: 'Entrega'}
    );
    
    // Channels
    this.selectChannelOptions.push(
      {id: 3, name: 'Telefónico'},
      {id: 4, name: 'Personal'},
      {id: 5, name: 'Email'},
      {id: 6, name: 'Web Lead'},
      {id: 8, name: 'Whatsapp'},
      {id: 9, name: 'Teleconferencia'}
    );

    // Products
    this.selectProductOptions.push(
      {id: 1090, name: 'New Mondeo'},
      {id: 1190, name: 'New Ranger'},
      {id: 5, name: 'Mustang'}
    );

    // Sellers
    this.selectSellerOptions.push(
      {id: 1, name: 'Cesar Gonzalez'},
      {id: 1190, name: 'Juan Ranger'},
      {id: 5, name: 'Luis Mustang'}
    );

    // Empathies
    this.selectEmpathyOptions.push(
      {id: 1, name: 'E1', icon: 'fa fa-user'}, 
      {id: 2, name: 'E2', icon: 'fa fa-user'}, 
      {id: 3, name: 'E3', icon: 'fa fa-user'}, 
      {id: 4, name: 'E4', icon: 'fa fa-user'},
      {id: 5, name: 'E5', icon: 'fa fa-user'}
    );

  }



}
