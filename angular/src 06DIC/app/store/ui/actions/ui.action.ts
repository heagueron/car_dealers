//ANGULAR
  import { Action } from '@ngrx/store';

export enum UIActionTypes {
    enable_loading = '[UI Loading] Enable Loading',
    disable_loading = '[UI Loading] Disable Loading',
    enable_sidebar_right = '[UI SidebarRight] Enable Sidebar right',
    disable_sidebar_right = '[UI SidebarRight] Disable Sidebar right',
    ResetState = '[UI] Reset State'
}

export class EnableLoading implements Action {
  readonly type = UIActionTypes.enable_loading;
}

export class DisableLoading implements Action {
  readonly type = UIActionTypes.disable_loading;
}

export class EnableSidebarRight implements Action {
  readonly type = UIActionTypes.enable_sidebar_right;
}

export class DisableSidebarRight implements Action {
  readonly type = UIActionTypes.disable_sidebar_right;
}

export class ResetState implements Action {
  readonly type = UIActionTypes.ResetState;
}

export type UIActionsUnion =
  | EnableLoading
  | DisableLoading
  | EnableSidebarRight
  | DisableSidebarRight
  | ResetState;
