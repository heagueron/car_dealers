import { Injectable } from '@angular/core';
import { Actions, Effect } from '@ngrx/effects';

import * as contactsActions from '../actions/contacts.action';

import { of } from 'rxjs';
import { map, switchMap, catchError } from 'rxjs/operators';

import { ReceptionService } from '../../../core/services/reception/reception.service';

@Injectable()
export class ContactsEffects {

    constructor(
        private actions$: Actions,
        public receptionService: ReceptionService
    ) {}

    @Effect()
    cargarReceptions$ = this.actions$.ofType( contactsActions.ContactsActionTypes.LOAD_CONTACTS )
        .pipe(
            switchMap( () => {

                console.log(' **** RECEPTION EFFECTS DETECTS LOAD ACTION ****');
                
                return this.receptionService.get_receptions({page: '1', filter: '', sort: 'desc'})
                    .pipe(
                        map( receptions => new contactsActions.LoadContactsSuccess( receptions ) ),
                        catchError( error => of(new contactsActions.LoadContactsFail( error ))  )
                    );
            })
        );
            
    @Effect()
    createReception$ = this.actions$.ofType( contactsActions.ContactsActionTypes.SAVE_RECEPTION )
            .pipe(
                switchMap( action => {
                    
                    console.log(' **** RECEPTION EFFECTS DETECTS CREATE ACTION ****');

                    const reception = action['payload'];
                return this.receptionService.save_reception(reception)
                    .pipe(
                        map( savedReception => new contactsActions.SaveReceptionSuccessAction( savedReception ) ),
                        catchError( error => of(new contactsActions.SaveReceptionFailAction( error ))  )
                    );    



                })

            )

    // UPDATE
    @Effect()
    updateReception$ = this.actions$.ofType( contactsActions.ContactsActionTypes.UPDATE_RECEPTION )
            .pipe(
                switchMap( action => {
                    
                    console.log(' **** RECEPTION EFFECTS DETECTS UPDATE ACTION ****');

                    const reception = action['payload'];
                return this.receptionService.save_reception(reception)
                    .pipe(
                        map( savedReception => new contactsActions.UpdateReceptionSuccessAction( savedReception ) ),
                        catchError( error => of(new contactsActions.UpdateReceptionFailAction( error ))  )
                    );    



                })

            )


    // UPDATE End

    // GET RECEPTION SELLERS
    @Effect()
    loadReceptionSellers$ = this.actions$.ofType( contactsActions.ContactsActionTypes.LOAD_RECEPTION_SELLERS )
            .pipe(
                switchMap( action => {
                    
                    console.log(' **** RECEPTION EFFECTS DETECTS LOAD SELLERS ACTION ****');

                    //const reception = action['payload'];

                return this.receptionService.load_reception_sellers()
                    .pipe(
                        map( receptionSellers => new contactsActions.LoadReceptionSellersSuccessAction ( receptionSellers ) ),
                        catchError( error => of(new contactsActions.LoadReceptionSellersFailAction( error ))  )
                    );    



                })

            )


    // GET RECEPTION SELLERS End



}

/*
this.isLoading = true;
    this.contactService.get_contacts({page: '1', filter: '', sort: 'desc'})
                       .subscribe( response => {
                         this.contactsArray = response;
                         this.isLoading = false;
                         

                        } );
                        */