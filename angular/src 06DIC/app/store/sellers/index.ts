//ANGULAR
import { createSelector, createFeatureSelector } from '@ngrx/store';

//REDUCERs
    import * as FromSellerReducer from './reducers/sellers.reducer';

//ACTIONS
    import * as FromSellerActions from './actions/sellers.action';

//CONST
    export const actions = FromSellerActions;
    export const reducer = FromSellerReducer;

export interface AppState {
    sellers: FromSellerReducer.State,
}

export const selectSellerState = createFeatureSelector<AppState, FromSellerReducer.State>('sellers');

//SELECTORS
    export const getSellers = createSelector(selectSellerState, state => state.data);
    export const getPagination = createSelector(selectSellerState, state => state.pagination);