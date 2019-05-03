//ANGULAR
    import { ActionReducer, ActionReducerMap, MetaReducer } from '@ngrx/store';
    import { localStorageSync } from 'ngrx-store-localstorage';
    import { storeFreeze } from 'ngrx-store-freeze';

//REDUCERS
    import * as authReducer from './auth/reducers/auth.reducer';
    import * as productsReducer from './products/reducers/products.reducer';
    import * as salesReducer from './sales/reducers/sales.reducer';
    import * as uiReducer from './ui/reducers/ui.reducer';
    import * as contactsReducer from './contacts/reducers/contacts.reducer';

//ENVIRONMENT
    import { environment } from '../../environments/environment';

export interface AppState {
    auth: authReducer.State,
    products: productsReducer.State,
    sales: salesReducer.State,
    ui      : uiReducer.State,
    contacts: contactsReducer.ContactsState
}

export const AppReducers: ActionReducerMap<AppState> = {
    auth: authReducer.AuthReducer,
    products: productsReducer.ProductsReducer,
    sales: salesReducer.SalesReducer,
    ui      : uiReducer.UIReducer,
    contacts: contactsReducer.ContactsReducer
}

/**
 * By default, @ngrx/store only writes to memory. We use ngrx-store-localstorage
 * to persist the states to the browser's storage to avoid losing them on page reload
 */
export function localStorageSyncReducer(reducer: ActionReducer<any>): ActionReducer<any> {
  return localStorageSync({
      keys: ['auth', 'products', 'sales', 'ui', 'contacts'],
      rehydrate: true
    })
    (reducer);
}

// console.log all actions
export function logger(reducer: ActionReducer<AppState>): ActionReducer<AppState> {
    return function(state: AppState, action: any): AppState {
      console.log('state', state);
      console.log('action', action);
  
      return reducer(state, action);
    };
}
/**
 * By default, @ngrx/store uses combineReducers with the reducer map to compose
 * the root meta-reducer. To add more meta-reducers, provide an array of meta-reducers
 * that will be composed to form the root meta-reducer.
 */
export const metaReducers: MetaReducer<AppState>[] = !environment.production? [logger, storeFreeze, localStorageSyncReducer]: [localStorageSyncReducer];