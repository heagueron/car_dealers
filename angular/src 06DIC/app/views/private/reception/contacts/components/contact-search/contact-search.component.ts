import { Component, OnInit, ViewEncapsulation } from '@angular/core';
import { FormControl, FormGroup, Validators } from '@angular/forms';

import { select, Store } from '@ngrx/store';
import * as contactsActions from '../../../../../../store/contacts/index';

//EXTERNAL
//import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { NgOption } from '@ng-select/ng-select';
import Swal from 'sweetalert2';

import { ModalReceptionComponent } from '../../receptionModals/modal-reception/modal-reception.component'

import { ClientService } from './../../../../../../core/services/clients/client.service';
import { Subject } from 'rxjs';
import { map, switchMap, catchError, debounceTime, distinctUntilChanged, filter, tap } from 'rxjs/operators';
import { ContactModel } from 'src/app/core/models/contact.model';
import { AppState } from 'src/app/store/sellers';

declare const $: any;
declare const moment: any;

@Component({
  selector: 'app-contact-search',
  templateUrl: './contact-search.component.html',
  styleUrls: ['./contact-search.component.css'],
  encapsulation: ViewEncapsulation.None
})
export class ContactSearchComponent implements OnInit {
  
  results: any[] = [];
  queryField: FormControl = new FormControl();

  reception: ContactModel;

  //selClient: FormGroup;
  
  constructor(
    private clientService: ClientService,
    //private modalService: NgbModal,
    public store : Store<AppState>
    ) {}

  ngOnInit() {
  
    this.queryField.valueChanges
      .pipe(
        debounceTime(700),
        distinctUntilChanged(),
        filter( entry => entry.length > 3),
        switchMap( queryField =>this.clientService.search( {page: '10', q: queryField, sort: 'desc'} ) )
        )
      .subscribe( 
        response => {

          if( response['data'].length === 0 ){ // No results matching the search term 

            console.log('THERE ARE NO CLIENTS MATCHING THAT SEARCH TERM');

            Swal({
              title: 'No encontrado',
              text: 'Desea ingresar nuevo registro ?',
              type: 'warning',
              showCancelButton: true,
              confirmButtonText: 'Si, registrar nuevo!',
              cancelButtonText: 'Cancelar'
            }).then((result) => {
              if (result.value) {
                this.selectedClient( '', this.queryField.value );
                //$('#receptionModal').modal('show');
              } else if (result.dismiss === Swal.DismissReason.cancel) {
              }
            })

          } else { // Some results matching the search term ...

            // Open and Fill the search result list
            
            this.results = response['data'];
            setTimeout( function() { $('.ng-input').click(); }, 1000);
            
          }

        }  
      );
      
      /*
      this.selClient = new FormGroup({
        'id'         : new FormControl( '0'),
        'name'       : new FormControl( '' ),
        'last_name'  : new FormControl( '' ),
        'phone'      : new FormControl( '' ),
        'mobile'     : new FormControl( '' ),
        'mail'       : new FormControl( '' ),
        'channel_id' : new FormControl( '0')
      })*/

      this.setReception();

  }

  
  selectedClient( selectedClient:any, newClient:string='' ){
    
    

    console.log(`SELECTED CLIENT NAME: ${selectedClient.name}`);
    console.log(`SELECTED CLIENT ID: ${selectedClient.id}`);

    const client = this.results.filter( item => item.id == selectedClient.id)[0];
    
    console.log(`AFTER FILTER CLIENT: ${client}`);
    
    console.log(`AFTER FILTER CLIENT ID: ${client.id}`);

    this.results = [];
    this.queryField.setValue('');


    if( newClient === '' ){ // Client selected on search results

      this.reception.client_id  = client.id;
      this.reception.name       = client.name;
      this.reception.last_name  = client.last_name;
      this.reception.phone      = client.phone;
      this.reception.mobile     = client.mobile;
      this.reception.mail       = client.mail;
      this.reception.channel_id = client.id_channel;

    } else { // New client name or term 

      this.reception.name       = newClient;
      console.log(`NEW TERM: ${newClient}`);

    }

    // Load the reception sellers queue
    this.store.dispatch( new contactsActions.actions.LoadReceptionSellersAction );
    
    // Send the client data to store
    this.store.dispatch( new contactsActions.actions.SetSelectedReceptionAction( {... this.reception} ) );
    
    // Open the form modal 
    setTimeout( function() { $('#receptionModal').modal('show'); }, 1000);

  }
/*
  selectedClient( selectedClient:any, newClient:string='' ){
    
    this.results = [];
    this.queryField.setValue('');

    if( newClient === '' ){ // Client selected on search results

      for(var key in selectedClient){
        if(selectedClient.hasOwnProperty(key)){
          console.log(key + '->' + selectedClient[key]);
        }
      }
      

      this.reception.name       = selectedClient.name;
      //this.form.controls['your form control name'].value
      this.reception.last_name       = this.selClient.controls['last_name'].value;
      this.reception.phone       = this.selClient.controls['phone'].value;
      this.reception.mobile       = this.selClient.controls['mobile'].value;
      this.reception.mail      = this.selClient.controls['mail'].value;
      this.reception.channel_id      = this.selClient.controls['channel_id'].value;
      //this.reception.client_id       = this.selClient.controls['client_id'].value;



    } else { // New client name or term 
      this.reception.name       = newClient;
    }
    

    this.store.dispatch( new contactsActions.actions.SetSelectedReceptionAction( {... this.reception} ) );
    

    //setTimeout(function(){ $('#launchReceptionModal').click(); }, 1000); 
    

    //const modalRef = this.modalService.open( ModalReceptionComponent, { size: 'lg' });
    //modalRef.componentInstance.selectedName = selectedName;
  }
*/

  setReception(){
    this.reception = {
        id              : 0,
        date            : '',
        derivation_date : '',
        company_id      : 0,
        user_id         : 0,
        client_id       : 0,
        name            : '',
        last_name       : '',
        pre_phone       : '',
        phone           : '',
        pre_mobile      : '',
        mobile          : '',
        mail            : '',
        reason_id       : 0,
        reason          : '',
        product_id      : 0,
        product         : '',
        seller_id       : 0,
        seller_name     : '',
        seller_lastname : '',
        channel_id      : 0,
        channel         : '',
        comment_id      : 0,
        commment        : '',
        empathy_user_client_id  : 0,
        empathy_user_client     : '',
        empathy_client_user_id  : 0,
        empathy_client_user     : '',
        created_at      : '',
        deleted_at      : '',
        updated_at      : ''
    
    }
  }

}