// ANGULAR
import { Component, OnInit, Input, ViewChild } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { select, Store } from '@ngrx/store';
import { Observable } from 'rxjs/internal/Observable';

//REDUCERS
import { AppState } from 'src/app/store/app.reducers';
import * as fromContactsStore from '../../../../../../store/contacts/index';

// MODELS
import { ContactModel, ContactPayloadModel } from 'src/app/core/models/contact.model';
import { PaginationModel } from '../../../../../../core/models/pagination.model';


// SERVICES
import { PaginationService } from 'src/app/core/services/pagination/pagination.service';
import { ContactsState } from 'src/app/store/contacts/reducers/contacts.reducer';

import * as contactsActions from '../../../../../../store/contacts/index';



declare const $: any;

@Component({
  selector: 'app-contact-list',
  templateUrl: './contact-list.component.html',
  styleUrls: ['./contact-list.component.css']
})
export class ContactListComponent implements OnInit {

  contacts$    : Observable<ContactModel[]>;
  pagination$  : Observable<PaginationModel>
  paginate     : any = [];

  contactsArray   : ContactModel[];
  selectedContact : ContactModel;
  dateNow         : Date;
  isLoading       : boolean = false;
  loadError       : any;

  registerModalFlag : boolean = false;
  

  constructor(
    public paginationService  : PaginationService,
    public store : Store<AppState>,
  ) {
    this.contacts$ = this.store.pipe(select(fromContactsStore.getContacts));
  }

  ngOnInit() {

    this.store.select('contacts').subscribe( contactos => {
          this.contactsArray = contactos.data;
          this.isLoading = contactos.loading;
          this.loadError = contactos.load_error;

          console.log(`PAGINATION total: ${contactos.pagination.total}`);

          //test:
          //this.showRegisterModal();
    });

    this.store.dispatch( new contactsActions.actions.LoadContactsAction() );
    
    

    /*
    this.pagination$.subscribe(pagination => {
      this.paginate = this.paginationService
      .getPagination(pagination.current_page, pagination.last_page, pagination.to);
    });
    */
  }

  showRegisterModal( reception: ContactModel ){

    this.registerModalFlag = true;
    
    // Load the reception sellers queue
    this.store.dispatch( new contactsActions.actions.LoadReceptionSellersAction );

    // Send the client data to store
    this.store.dispatch( new contactsActions.actions.SetSelectedReceptionAction( reception ) );

    // Open the form modal 
    setTimeout( function() { $('#receptionModal').modal('show'); }, 1000);

  }

  editContact( contact: ContactModel){
    console.log(`Let's edit contact: ${contact.id}`);
    this.selectedContact = contact;
    this.dateNow = new Date();
  }

  saveContact( contact: ContactModel){
    console.log(`Let's save contact: ${contact.id}`);
    this.selectedContact = null;
  }

  closeContact( contact: ContactModel){
    console.log(`Let's close contact: ${contact.id}`);
    this.selectedContact = null;
  }

  



}
