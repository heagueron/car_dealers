//ANGULAR
  import { Injectable } from '@angular/core';
  import { Location } from '@angular/common';
  import { Actions, Effect, ofType } from '@ngrx/effects';
  import { tap } from 'rxjs/operators';

//ACTIONS
  import * as fromUI from '../actions/ui.action';

declare const $: any;

@Injectable()
export class UIEffects {
  @Effect({ dispatch: false })
    disableSidebarRight$ = this.actions$.ofType(fromUI.UIActionTypes.disable_sidebar_right)
      .pipe(
        tap(() => this.location.back())
      )
  
  constructor(private actions$: Actions, private location: Location) {
    //
  }
}