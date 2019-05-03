//ANGULAR
    import { createSelector, createFeatureSelector } from '@ngrx/store';

//REDUCER
    import * as FromAuthReducer from './reducers/auth.reducer';

//ACTIONS
    import * as FromAuthActions from './actions/auth.action';

//CONST
    export const actions = FromAuthActions;
    export const reducer = FromAuthReducer;

export interface AppState {
    auth: FromAuthReducer.State;
}

export const selectAuthState = createFeatureSelector<AppState, FromAuthReducer.State>('auth');

//SELECTORS
    export const getLoggedIn = createSelector(selectAuthState, state => state.loggedIn);

    export const getToken = createSelector(selectAuthState, state => state.token);
    
    export const getUser = createSelector(selectAuthState, state => state.user);