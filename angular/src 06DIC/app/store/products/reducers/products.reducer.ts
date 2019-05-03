//ACTIONS
  import { ProductsActionsUnion, ProductsActionTypes } from '../actions/products.action';

//MODELS
  import { ProductModel } from '../../../core/models/product.model';
  import { ObjectModel } from '../../../core/models/meta.model';

export interface State {
  data: ProductModel[];
  pagination: {
    current_page: number;
    from: number;
    last_page: number;
    next_page_url: any;
    path: string;
    per_page: number;
    prev_page_url: any;
    to: number;
    total: number;
  };
  meta: {
    axis: ObjectModel[];
    brands: ObjectModel[];
    cabines: ObjectModel[];
    clutches: ObjectModel[];
    directions: ObjectModel[];
    engines: ObjectModel[];
    tires: ObjectModel[];
    tractions: ObjectModel[];
    transmissions: ObjectModel[];
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
    axis: [],
    brands: [],
    cabines: [],
    clutches: [],
    directions: [],
    engines: [],
    tires: [],
    tractions: [],
    transmissions: [],
  }
};

export function ProductsReducer(state = initialState, action: ProductsActionsUnion) {
  switch (action.type) {
    case ProductsActionTypes.LoadProductsSuccess: {
      return {
        ...state,
        data: [
          ...action.payload.map( product => {
              return {
                  ...product
              };
          })
        ]
      };
    }

    case ProductsActionTypes.LoadPageSuccess: {
      return {
        ...state,
        pagination: {
          current_page: action.payload.products.current_page,
          from: action.payload.products.from,
          last_page: action.payload.products.last_page,
          next_page_url: action.payload.products.next_page_url,
          path: action.payload.products.path,
          per_page: action.payload.products.per_page,
          prev_page_url:action.payload.products.prev_page_url,
          to: action.payload.products.to,
          total: action.payload.products.total
        }
      };
    }

    case ProductsActionTypes.LoadMetaSuccess: {
      return {
        ...state,
        meta: {
          axis: action.payload.axis,
          brands: action.payload.brands,
          cabines: action.payload.cabines,
          clutches: action.payload.clutches,
          directions: action.payload.directions,
          engines: action.payload.engines,
          tires: action.payload.tires,
          tractions: action.payload.tractions,
          transmissions: action.payload.transmissions,
        }
      };
    }

    default: {
      return state;
    }
  }
}
