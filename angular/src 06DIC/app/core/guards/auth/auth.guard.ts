//ANGULAR
  import { Injectable } from '@angular/core';
  import { CanActivate, ActivatedRouteSnapshot, Router, RouterStateSnapshot } from '@angular/router';
  import { Observable } from 'rxjs';
  import { map, take } from 'rxjs/operators';
  import { Store, select } from '@ngrx/store';

//REDUCERS
  import { AppState } from '../../../store/app.reducers';
  import * as FromAuthStore from '../../../store/auth/index';

@Injectable({
  providedIn: 'root'
})
export class AuthGuard implements CanActivate {
  constructor(private store: Store<AppState>, private route: Router, /*private alertService: AlertService*/) {
    //
  }

  canActivate(): Observable<boolean> {
    return this.store.pipe(select(FromAuthStore.getLoggedIn),
      map(loggedIn => {
        if(!loggedIn) {
          this.store.dispatch(new FromAuthStore.actions.LoginRedirect());
          //this.alertService.error('Por favor inicia sesión');
          return false;
        }
        return true;
      }),
      take(1)
    );
  }
}
