//ANGULAR
import { createSelector, createFeatureSelector } from '@ngrx/store';

//REDUCERs
import * as fromContactsReducer from './reducers/contacts.reducer';

//ACTIONS
import * as fromContactsActions from './actions/contacts.action';

//EFFECTS
import * as fromContactsEffects from './effects/contacts.effects';

//CONST
export const actions = fromContactsActions;
export const reducer = fromContactsReducer;
export const effect  = fromContactsEffects;

export interface State {
    contacts: fromContactsReducer.ContactsState,
}

export const selectContactsState = createFeatureSelector
    <State, fromContactsReducer.ContactsState>('contacts');

//SELECTORS

export const getContacts = createSelector( selectContactsState, state => state.data );

export const getContact  = (id: number) => createSelector(
    getContacts, value => value.filter(contact => contact.id === id));

export const getPagination = createSelector(
    selectContactsState, state => state.pagination);
/*
export const getMeta = createSelector(
    selectSalesState, state => state.meta);
*/    