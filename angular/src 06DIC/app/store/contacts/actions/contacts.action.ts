// NGRX
import { Action } from '@ngrx/store';

// MODELS
import { ContactModel, ContactPayloadModel, ReceptionSellerModel } from '../../../core/models/contact.model';


export enum ContactsActionTypes { //variable de enumerables

  LOAD_CONTACTS              = '[CONTACTS] Load Contacts',
  LOAD_CONTACTS_PAGE_SUCCESS = '[CONTACTS] Load Contacts Page Success',
  LOAD_CONTACTS_SUCCESS      = '[CONTACTS] Load Contacts Success',
  LOAD_CONTACTS_FAIL         = '[CONTACTS] Load Contacts Fail',
  
  SET_SELECTED_RECEPTION     = '[CONTACTS] Set Selected Reception',
  UNSET_SELECTED_RECEPTION   = '[CONTACTS] Unset Selected Reception',
  
  SAVE_RECEPTION             = '[CONTACTS] SAVE Reception',
  SAVE_RECEPTION_SUCCESS     = '[CONTACTS] SAVE Success',
  SAVE_RECEPTION_FAIL        = '[CONTACTS] SAVE Fail',
  
  UPDATE_RECEPTION           = '[CONTACTS] UPDATE Reception',
  UPDATE_RECEPTION_SUCCESS   = '[CONTACTS] UPDATE Success',
  UPDATE_RECEPTION_FAIL      = '[CONTACTS] UPDATE Fail',

  LOAD_RECEPTION_SELLERS             = '[CONTACTS] Load Reception Sellers',
  LOAD_RECEPTION_SELLERS_SUCCESS     = '[CONTACTS] Load Reception Sellers Success',
  LOAD_RECEPTION_SELLERS_FAIL        = '[CONTACTS] Load Reception Sellers Fail',

}



export class LoadContactsAction implements Action {
  readonly type = ContactsActionTypes.LOAD_CONTACTS;
  constructor() {
  }
}

export class LoadContactsPageSuccess implements Action {
  readonly type = ContactsActionTypes.LOAD_CONTACTS_PAGE_SUCCESS;
  constructor(public payload: ContactPayloadModel) {
  }
}

export class LoadContactsSuccess implements Action {
  readonly type = ContactsActionTypes.LOAD_CONTACTS_SUCCESS;
  constructor(public payload: ContactModel[]) {}
  
}

export class LoadContactsFail implements Action {
  readonly type = ContactsActionTypes.LOAD_CONTACTS_FAIL;

  constructor(public payload: any) {
  }
}

export class SetSelectedReceptionAction implements Action {
  readonly type = ContactsActionTypes.SET_SELECTED_RECEPTION;
  constructor(public payload: ContactModel ) {}  
}

export class UnsetSelectedReceptionAction implements Action {
  readonly type = ContactsActionTypes.UNSET_SELECTED_RECEPTION;
  constructor() {}  
}

export class SaveReceptionAction implements Action {
  readonly type = ContactsActionTypes.SAVE_RECEPTION;
  constructor(public payload: ContactModel ) {}  
}

export class SaveReceptionSuccessAction implements Action {
  readonly type = ContactsActionTypes.SAVE_RECEPTION_SUCCESS;
  constructor(public payload: ContactModel) {
  }
}

export class SaveReceptionFailAction implements Action {
  readonly type = ContactsActionTypes.SAVE_RECEPTION_FAIL;
  constructor(public payload: any) {
  }
}

export class UpdateReceptionAction implements Action {
  readonly type = ContactsActionTypes.UPDATE_RECEPTION;
  constructor(public payload: ContactModel ) {}  
}

export class UpdateReceptionSuccessAction implements Action {
  readonly type = ContactsActionTypes.UPDATE_RECEPTION_SUCCESS;
  constructor(public payload: ContactModel) {
  }
}

export class UpdateReceptionFailAction implements Action {
  readonly type = ContactsActionTypes.UPDATE_RECEPTION_FAIL;
  constructor(public payload: any) {
  }
}

export class LoadReceptionSellersAction implements Action {
  readonly type = ContactsActionTypes.LOAD_RECEPTION_SELLERS;
  constructor( ) {}  
}

export class LoadReceptionSellersSuccessAction implements Action {
  
  readonly type = ContactsActionTypes.LOAD_RECEPTION_SELLERS_SUCCESS;
  
  constructor(public payload: ReceptionSellerModel[]) {
  }

}

export class LoadReceptionSellersFailAction implements Action {
  readonly type = ContactsActionTypes.LOAD_RECEPTION_SELLERS_FAIL;
  constructor(public payload: any) {
  }
}

export type ContactsActionsUnion =
  | LoadContactsAction
  | LoadContactsPageSuccess
  | LoadContactsSuccess
  | LoadContactsFail
  | SetSelectedReceptionAction
  | UnsetSelectedReceptionAction
  | SaveReceptionAction
  | SaveReceptionSuccessAction
  | SaveReceptionFailAction
  | UpdateReceptionAction
  | UpdateReceptionSuccessAction
  | UpdateReceptionFailAction
  | LoadReceptionSellersAction
  | LoadReceptionSellersSuccessAction
  | LoadReceptionSellersFailAction;
