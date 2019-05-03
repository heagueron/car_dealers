//ANGULAR
    import { createSelector, createFeatureSelector } from '@ngrx/store';

//REDUCER
    import * as FromUIReducer from './reducers/ui.reducer';

//ACTIONS
    import * as FromUIActions from './actions/ui.action';

//CONST
    export const actions = FromUIActions;
    export const reducer = FromUIReducer;

export interface AppState {
    ui: FromUIReducer.State;
}

export const selectUIState = createFeatureSelector<AppState, FromUIReducer.State>('ui');

//SELECTORS
    export const getIsLoading = createSelector(selectUIState, state => state.isLoading);
    
    export const getSidebarRight = createSelector(selectUIState, state => state.sidebar_right);