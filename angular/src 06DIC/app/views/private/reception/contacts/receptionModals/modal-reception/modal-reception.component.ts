import { Component, OnInit, OnDestroy, Input, ViewEncapsulation } from '@angular/core';
import { FormGroup, FormControl, Validators } from '@angular/forms';

import { Store } from '@ngrx/store';
import { AppState } from 'src/app/store/sellers';



import { Subscription } from 'rxjs';
import { ContactModel, ReceptionSellerModel } from 'src/app/core/models/contact.model';

import * as contactsActions from '../../../../../../store/contacts/index';

//EXTERNAL
//import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import { NgSelectConfig } from '@ng-select/ng-select';
import { NgOption } from '@ng-select/ng-select';
import { isNullOrUndefined } from 'util';
import Swal from 'sweetalert2';

declare const $: any;
declare const moment: any;

@Component({
  selector: 'app-modal-reception',
  templateUrl: './modal-reception.component.html',
  styleUrls: ['./modal-reception.component.css'],
  encapsulation: ViewEncapsulation.None
})
export class ModalReceptionComponent implements OnInit, OnDestroy{

 // @Input() selectedName: string;

  receptionId: number;
  name: string;

  newReceptionFlag: boolean;
  reception: ContactModel;

  saving:boolean = false;
  processing: true;

  receptionForm: FormGroup;

  selectReasonOptions: Array<any>;
  selectProductOptions: Array<any>;
  selectChannelOptions: Array<any>;
  selectSellerOptions: Array<any>;
  selectEmpathyOptions: Array<any>;

  shownSeller: ReceptionSellerModel;

  derivationType: number = 1;
  nextSeller: ReceptionSellerModel;
  receptionSellers : ReceptionSellerModel[];

  //subscription: Subscription = new Subscription();
  
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
      'id'         : new FormControl( '0' ),
      'name'       : new FormControl( '', Validators.required ),
      'last_name'  : new FormControl( '', Validators.required ),
      'client_id'  : new FormControl( 1  ),
      'phone'      : new FormControl( '' ),
      'mobile'     : new FormControl( '' ),
      'mail'       : new FormControl( '' ),
      'reason'     : new FormControl( '' ),
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

    //this.receptionForm.patchValue( { name       :  this.selectedName } );

    this.setSeller();
    
    // Listen to contacts and request to fill the form with selected reception
    this.store.select('contacts')
      .subscribe(  contacts => {
        console.log(`**** savingReception: ${contacts.savingReception}`);

        if( contacts.selectedReception !== null){
          
          if( isNullOrUndefined( contacts.selectedReception.id) ) { 
            this.newReceptionFlag = true;
          } else { this.newReceptionFlag = false;}

          this.setInitialFormValues( contacts.selectedReception );

          this.shownSeller.id       = this.receptionForm.get('seller_id').value;
          this.shownSeller.fullname = this.receptionForm.get('seller').value;


          this.store.dispatch( new contactsActions.actions.UnsetSelectedReceptionAction );

        }

        this.saving = contacts.savingReception;
        if( contacts.savingReception === false && contacts.savedReception === true ){
          $('#receptionModal').modal('hide');
        }

        // Reception Sellers QUEUE:
        if( !isNullOrUndefined(contacts.receptionSellers) ){
          
          //Set the seller list from the server queue
          this.receptionSellers = contacts.receptionSellers;

          this.nextSeller = this.receptionSellers[0];
          
          if( isNullOrUndefined( this.receptionForm.get('seller_id').value ) ){
            this.shownSeller = this.receptionSellers[0];
          }
          
          /*
          if( isNullOrUndefined( this.receptionForm.get('seller_id').value ) ){
            this.receptionForm.patchValue( { seller_id  :  this.nextSeller.id } )
          }
          */
        }

    }); 
    
    
    console.warn(`OnInit warn the FORM: ${this.receptionForm.value}`);
  
    // Selectables options
    this.setSelectablesOptions();    

    this.saving = false;

    
  } // ngOnInit end

  // Change derivation type on modal
  setDerivationType( selDerivationType:number ){
    console.log(`DERIVATION TYPE: ${selDerivationType}`);
    this.derivationType = selDerivationType;
  }

  setInitialFormValues( reception: ContactModel) {
    console.log("INSIDE setInitialFormValues")
    console.warn(`warn the FORM: ${this.receptionForm.value}`);
    this.receptionForm.setValue({
      id         :  isNullOrUndefined(reception.id) ?  0 : reception.id,
      name       :  reception.name,
      last_name  :  reception.last_name,
      client_id  :  isNullOrUndefined(reception.client_id) ? 0 : reception.client_id,
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
    console.log("SELLER ID SET INITIAL: "+this.receptionForm.get('seller_id').value );
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
      {id: 1, name: 'Leonardo Da Vinci'},
      {id: 1190, name: 'Juan Ranger'},
      {id: 5, name: 'Luis Mustang'}
    );

    // Empathies
    this.selectEmpathyOptions.push(
      {id: 1, name: 'Muy mal', icon: 'fa fa-frown-o'}, 
      {id: 2, name: 'Mal', icon: 'fa fa-frown-o'}, 
      {id: 3, name: 'Más o menos', icon: 'fa fa-meh-o'}, 
      {id: 4, name: 'Bien', icon: 'fa fa-smile-o'},
      {id: 5, name: 'Muy bien', icon: 'fa fa-smile-o'}
    );

  }

  setSeller(){
    this.shownSeller = {
      id              : 0,
      fullname        : '',
      queue_position  : 1000
    }
  }

  saveReception(){

    this.reception = this.receptionForm.value;
    if( this.reception.phone=='' && this.reception.mobile=='' && this.reception.mail=='' ){
      Swal('Atención:', 'Debe ingresar teléfono, celular o e-mail', 'warning');
      return;
    }
    
    this.saving = true;
    
    

    if( this.receptionForm.get('id').value > 0 ) {

      console.log('UPDATE RECEPTION !!!');
      this.store.dispatch( new contactsActions.actions.UpdateReceptionAction( this.reception ) );

    } else {

      console.log('CREATE RECEPTION !!!');
      this.store.dispatch( new contactsActions.actions.SaveReceptionAction( this.reception ) );

    }
    

  }


  cancelEditReception(){
    this.saving = false;
  }



  ngOnDestroy() {
    // this.subscription.unsubscribe();
  }

}

