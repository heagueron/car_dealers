//ANGULAR
    import { createSelector, createFeatureSelector } from '@ngrx/store';

//REDUCERS
    import * as FromSalesReducer from './reducers/sales.reducer';

//ACTIONS
    import * as FromSalesActions from './actions/sales.action';

//CONST
    export const actions = FromSalesActions;
    export const reducer = FromSalesReducer;

export interface AppState {
    sales: FromSalesReducer.State,
}

export const selectSalesState = createFeatureSelector<AppState, FromSalesReducer.State>('sales');

//SELECTORS
    export const getSales = createSelector(selectSalesState, state => state.data);
    export const getSale = (id: number) => createSelector(getSales, value => value.filter(sale => sale.id === id)[0]);

    export const getPagination = createSelector(selectSalesState, state => state.pagination);

    export const getMeta = createSelector(selectSalesState, state => state.meta);
    export const getMetaCategoriesConditions = createSelector(getMeta, value => value.client_category_conditions);