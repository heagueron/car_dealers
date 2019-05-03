//ANGULAR
  import { Injectable } from '@angular/core';
  import { Router } from '@angular/router';
  import { Actions, Effect, ofType } from '@ngrx/effects';
  import { of } from 'rxjs';
  import { catchError, exhaustMap, map, mergeMap, tap } from 'rxjs/operators';

//ACTIONS
  import * as fromAuth from '../actions/auth.action';
  import * as fromUI from '../../ui/actions/ui.action';

//MODELS
  import { AuthenticateModel } from '../../../core/models/authenticate.model';

//SERVICES 
  import { AuthService } from '../../../core/services/auth/auth.service';

@Injectable()
export class AuthEffects {
  
  constructor(private actions$: Actions, private authService: AuthService, private router: Router) {
    //
  }

  @Effect()
  login$ = this.actions$.ofType<fromAuth.Login>(fromAuth.AuthActionTypes.Login)
    .pipe(
      map(action => action.payload),
      exhaustMap((auth: AuthenticateModel) =>
        this.authService.login(auth)
          .pipe(
            mergeMap(res => [
              new fromAuth.LoginSuccess({ user: res.user }),
              new fromAuth.SetBearerToken(res.token),
              new fromUI.DisableLoading()
            ]),
            catchError(error => of(new fromAuth.LoginFailure(error)))
          )
      )
    );

  @Effect({ dispatch: false })
  loginSuccess$ = this.actions$.ofType<fromAuth.LoginSuccess>(fromAuth.AuthActionTypes.LoginSuccess)
    .pipe(
      tap(() => this.router.navigate(['/experience/operations']))
    );

  @Effect({ dispatch: false })
  loginRedirect$ = this.actions$.ofType(fromAuth.AuthActionTypes.LoginRedirect, fromAuth.AuthActionTypes.Logout)
    .pipe(
      tap(authed => {
        this.router.navigate(['/auth/login']);
      })
    );
}
