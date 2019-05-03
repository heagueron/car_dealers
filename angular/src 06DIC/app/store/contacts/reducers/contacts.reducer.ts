//ACTIONS
import { ContactsActionsUnion, ContactsActionTypes } from '../actions/contacts.action';

//MODELS
import { ContactModel, ReceptionSellerModel } from 'src/app/core/models/contact.model';
import { PaginationModel } from 'src/app/core/models/pagination.model';


export interface ContactsState {

  data        : ContactModel[];
  pagination  : PaginationModel;

  loaded      : boolean;
  loading     : boolean;
  load_error  : any;

  savingReception  : boolean;
  savedReception   : boolean; 
  saveReception_error       : any;

  updatedReception: boolean;

  selectedReception : ContactModel;

  receptionSellers    : ReceptionSellerModel[];
  loaded_sellers      : boolean;
  loading_sellers     : boolean;
  load_seller_error   : any;

}

const initialState: ContactsState = {
  data: [],
  pagination: {
    current_page    : 1,
    from            : 1,
    last_page       : 1,
    next_page_url   : null,
    path            : null,
    per_page        : 0,
    prev_page_url   : null,
    to              : 1,
    total           : 0
  },

  loaded      : false,
  loading     : false,
  load_error  : null,

  savingReception : false,
  savedReception  : false,
  saveReception_error      : null,

  updatedReception  : false,

  selectedReception : null,

  receptionSellers  : [],
  loaded_sellers    : false,
  loading_sellers   : false,
  load_seller_error : null

};

export function ContactsReducer(state = initialState, action: ContactsActionsUnion):ContactsState {
  
  switch (action.type) {

    case ContactsActionTypes.LOAD_CONTACTS: {

      return {
        ...state,
        loading : true
      };
    }

    case ContactsActionTypes.LOAD_CONTACTS_SUCCESS: {

      console.log(`EN REDUCER, action.payload: ${action.payload}`);
      return {
        ...state,
        data: action.payload['data'],
        loaded     : true,
        loading    : false,
        load_error : null,
        pagination : {
          current_page    : action.payload['current_page'],
          from            : action.payload['from'],
          last_page       : action.payload['last_page'],
          next_page_url   : action.payload['next_page_url'],
          path            : action.payload['path'],
          per_page        : action.payload['per_page'],
          prev_page_url   : action.payload['prev_page_url'],
          to              : action.payload['to'],
          total           : action.payload['total'],
        }
      };
    }

    case ContactsActionTypes.SET_SELECTED_RECEPTION: {
      return {
        ... state,
        selectedReception: action.payload
      };
    }

    case ContactsActionTypes.UNSET_SELECTED_RECEPTION: {
      return {
        ... state,
        selectedReception: null
      };
    }

    case ContactsActionTypes.SAVE_RECEPTION: {
      console.log(`EN REDUCER, savingReception: ${state.savingReception}`);
      return {
        ...state,
        savingReception : true,
        savedReception  : false
      };
    }

    case ContactsActionTypes.SAVE_RECEPTION_SUCCESS: {
      console.log(`EN REDUCER, savingReceptionSuccess: ${action.payload}`);
      return {
        ...state,
        data            : [ ...state.data, action.payload['lead'] ],
        savingReception : false,
        savedReception  : true
        // data: [...state.data, action.payload] // New lead saved and received back from server.
      };
    }

    case ContactsActionTypes.UPDATE_RECEPTION: {
      console.log(`EN REDUCER, savingReception: ${state.savingReception}`);
      return {
        ...state,
        savingReception : true,
        updatedReception: false
      };
    }

    case ContactsActionTypes.UPDATE_RECEPTION_SUCCESS: {
      
      const newData = state.data.map( reception => {
        if( reception.id === action.payload['lead'].id ){
          return action.payload['lead'];
        } else { return reception  }
      });

      return {
        ... state,
        data: newData,
        savingReception : false,
        updatedReception: true
      }

    }

    case ContactsActionTypes.LOAD_RECEPTION_SELLERS: {

      return {
        ...state,
        loading_sellers : true,
        loaded_sellers  : false
      };
    }
    
    ///////
    case ContactsActionTypes.LOAD_RECEPTION_SELLERS_SUCCESS: {
      console.log(`EN REDUCER, LOAD_RECEPTION_SELLERS_SUCCESS: ${action.payload}`);  
      return {

        ...state,
        receptionSellers : action.payload,
        loaded_sellers   : true,
        loading_sellers  : false,
        load_error       : null,
        
      };
    }
    ////////
    default: {
      return state
    }

  } //switch end

} //Class ContactReducer end
