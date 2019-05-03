//ACTIONS
    import { AuthActionsUnion, AuthActionTypes } from '../actions/auth.action';

//CORE
    import { UserModel } from '../../../core/models/user.model';

export interface State {
    loggedIn: boolean;
    token: string;
    user: UserModel | null;
}

const initialState: State = {
    loggedIn: false,
    token: '',
    user: null,
};

export function AuthReducer(state = initialState, action: AuthActionsUnion){
  switch (action.type) {
    case AuthActionTypes.Login: {
      return {
        ...state,
        error: null,
        pending: true,
      };
    }

    case AuthActionTypes.LoginSuccess: {
      return {
        ...state,
        loggedIn: true,
        user: action.payload.user,
      };
    }

    case AuthActionTypes.LoginFailure: {
      return {
        ...state,
        error: action.payload,
        pending: false,
      };
    }

    case AuthActionTypes.Logout: {
      return initialState;
    }

    case AuthActionTypes.SetBearerToken: {
      return {
        ...state,
        token: action.payload
      };
    }

    default: {
      return state;
    }
  }
}
