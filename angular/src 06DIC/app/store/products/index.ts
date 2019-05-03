//ANGULAR
    import { createSelector, createFeatureSelector } from '@ngrx/store';

//REDUCERs
    import * as FromProductsReducer from './reducers/products.reducer';

//ACTIONS
    import * as FromProductsActions from './actions/products.action';

//CONST
    export const actions = FromProductsActions;
    export const reducer = FromProductsReducer;

export interface AppState {
    products: FromProductsReducer.State,
}

export const selectProductsState = createFeatureSelector<AppState, FromProductsReducer.State>('products');

//SELECTORS
    export const getProducts = createSelector(selectProductsState, state => state.data);

    export const getPagination = createSelector(selectProductsState, state => state.pagination);
    
    export const getMeta = createSelector(selectProductsState, state => state.meta);