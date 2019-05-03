

//ANGULAR
import { createSelector, createFeatureSelector} from '@ngrx/store';

//REDUCERs
    import * as FromLeadsReducer from './reducers/leads.reducer';
    // import * as FromSalesMetaReducer from './reducers/sales-meta.reducer';
    import * as FromLeadsPaginationReducer from './reducers/leads-page.reducer';

//ACTIONS
    import * as FromLeadsActions from './actions/leads.action';

//CONST
    export const actions = FromLeadsActions;
    // export const reducerMeta = FromSalesMetaReducer;
    // export const reducerPagination = FromSalesPaginationReducer;

export interface AppState {
    leads: FromLeadsReducer.State,
    // salesMeta: FromSalesMetaReducer.State,
    leadsPagination: FromLeadsPaginationReducer.State
}

export const selectLeadState = createFeatureSelector<AppState>('leads');

export const getLeadsEntitiesState = createSelector(selectLeadState, state => state.leads);
/** Selectors using NGRX Entity **/
export const {
    selectIds: getLeadIds,
    selectEntities: getLeadEntities,
    selectAll: getAllLeads,
    selectTotal: getTotalLeads,
} = FromLeadsReducer.adapter.getSelectors(getLeadsEntitiesState);