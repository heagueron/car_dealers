import { SellersActionsUnion, SellersActionTypes } from '../actions/sellers.action';
import { SellerModel } from '../../../core/models/seller.model';
import { createEntityAdapter, EntityAdapter, EntityState } from '@ngrx/entity';

export const adapter: EntityAdapter<SellerModel> = createEntityAdapter<SellerModel>({
  selectId: (seller: SellerModel) => seller.id
});

export interface State extends EntityState<SellerModel> {
  selectedSellerId: string | null;
  data: SellerModel[];
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
}

export const initialState: State = adapter.getInitialState({
  selectedSellerId: null,
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
  }
});

export function reducer(state = initialState, action: SellersActionsUnion): State {
  switch (action.type) {
    case SellersActionTypes.LoadSellersSuccess: {
      return adapter.addMany(action.payload, state);
    }
    case SellersActionTypes.Select: {
      return {
        ...state,
        selectedSellerId: action.payload
      };
    }
    case SellersActionTypes.ResetSellerState: {
      return initialState;
    }
    default: {
      return state;
    }
  }
}

export const getSelectedId = (state: State) => state.selectedSellerId;