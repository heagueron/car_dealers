//ACTIONS
  import { SalesActionsUnion, SalesActionTypes } from '../actions/sales.action';

//MODELS
  import { SaleModel } from '../../../core/models/sale.model';
  import { ObjectModel } from '../../../core/models/meta.model';
  import { PaginationModel } from '../../../core/models/pagination.model';

export interface State {
  data: SaleModel[];
  pagination: PaginationModel;
  meta: {
    client_category_conditions: ObjectModel[];
    companies: ObjectModel[];
    insurances: ObjectModel[];
    iva_conditions: ObjectModel[];
    non_purchase_reasons: ObjectModel[];
    sale_status: ObjectModel[];
    stages: ObjectModel[];
    substages: ObjectModel[];
    task_reasons: ObjectModel[];
    task_results: ObjectModel[];
    type_deliveries: ObjectModel[];
    type_invoices: ObjectModel[];
    type_payments: ObjectModel[];
    type_plans: ObjectModel[];
    type_sales: ObjectModel[];
  };
}

const initialState: State = {
  data: [],
  pagination: {
    current_page: 1,
    from: 1,
    last_page: 1,
    next_page_url: null,
    path: null,
    per_page: 0,
    prev_page_url: null,
    to: 1,
    total: 0
  },
  meta: {
    client_category_conditions: [],
    companies: [],
    insurances: [],
    iva_conditions: [],
    non_purchase_reasons: [],
    sale_status: [],
    stages: [],
    substages: [],
    task_reasons: [],
    task_results: [],
    type_deliveries: [],
    type_invoices: [],
    type_payments: [],
    type_plans: [],
    type_sales: [],
  }
};

export function SalesReducer(state = initialState, action: SalesActionsUnion) {
  switch (action.type) {
    case SalesActionTypes.LoadSalesSuccess: {
      return {
        ...state,
        data: [
          ...action.payload.map( sale => {
              return {
                  ...sale
              };
          })
        ]
      };
    }

    case SalesActionTypes.LoadPaginationSuccess: {
      return {
        ...state,
        pagination: {
          current_page: action.payload.sales.current_page,
          from: action.payload.sales.from,
          last_page: action.payload.sales.last_page,
          next_page_url: action.payload.sales.next_page_url,
          path: action.payload.sales.path,
          per_page: action.payload.sales.per_page,
          prev_page_url:action.payload.sales.prev_page_url,
          to: action.payload.sales.to,
          total: action.payload.sales.total
        }
      };
    }

    case SalesActionTypes.LoadMetaSuccess: {
      return {
        ...state,
        meta: {
          client_category_conditions: action.payload.client_category_conditions,
          companies: action.payload.companies,
          insurances: action.payload.insurances,
          iva_conditions: action.payload.iva_conditions,
          non_purchase_reasons: action.payload.non_purchase_reasons,
          sale_status: action.payload.sale_status,
          stages: action.payload.stages,
          substages: action.payload.substages,
          task_reasons: action.payload.task_reasons,
          task_results: action.payload.task_results,
          type_deliveries: action.payload.type_deliveries,
          type_invoices: action.payload.type_invoices,
          type_payments: action.payload.type_payments,
          type_plans: action.payload.type_plans,
          type_sales: action.payload.type_sales
        }
      };
    }

    case SalesActionTypes.ResetState: {
      return initialState;
    }

    default: {
      return state;
    }
  }
}

export const getSales = (state: State) => state.data;