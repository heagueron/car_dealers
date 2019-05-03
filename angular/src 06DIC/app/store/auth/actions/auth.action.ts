//ANGULAR
  import { Action } from '@ngrx/store';

//MODELS
  import { AuthenticateModel } from '../../../core/models/authenticate.model';
  import { UserModel } from '../../../core/models/user.model';

export interface User {
  name: string;
}

export enum AuthActionTypes {
  Login = '[Auth] Login',
  Logout = '[Auth] Logout',
  LoginSuccess = '[Auth] Login Success',
  LoginFailure = '[Auth] Login Failure',
  LoginRedirect = '[Auth] Login Redirect',
  SetBearerToken = '[Auth] Set Bearer Token'
}

export class Login implements Action {
  readonly type = AuthActionTypes.Login;

  constructor(public payload: AuthenticateModel) {
  }
}

export class LoginSuccess implements Action {
  readonly type = AuthActionTypes.LoginSuccess;

  constructor(public payload: { user: UserModel }) {
  }
}

export class LoginFailure implements Action {
  readonly type = AuthActionTypes.LoginFailure;

  constructor(public payload: any) {
  }
}

export class LoginRedirect implements Action {
  readonly type = AuthActionTypes.LoginRedirect;
}

export class Logout implements Action {
  readonly type = AuthActionTypes.Logout;
}

export class SetBearerToken implements Action {
  readonly type = AuthActionTypes.SetBearerToken;

  constructor(public payload: string) {
  }
}

export type AuthActionsUnion =
  | Login
  | LoginSuccess
  | LoginFailure
  | LoginRedirect
  | Logout
  | SetBearerToken;
