//ANGULAR
  import { Injectable } from '@angular/core';
  import { HttpErrorResponse, HttpEvent, HttpHandler, HttpInterceptor, HttpRequest, HttpResponse } from '@angular/common/http';
  import { Observable } from 'rxjs/internal/Observable';
  import { Store } from '@ngrx/store';
  import { catchError, map, tap } from 'rxjs/operators';
  import { of } from 'rxjs/internal/observable/of';

//REDUCERS
  import { AppState } from '../../store/app.reducers';
  import * as FromAuthStore from '../../store/auth/index';
  import * as FromUIStore from '../../store/ui/index';

//SERVICES
  import { AlertService } from '../services/alert/alert.service';

declare const $: any;

@Injectable()
export class JwtInterceptor implements HttpInterceptor {

  constructor(
    private store: Store<AppState>,
    private alertService: AlertService) {
    //
  }

  intercept(req: HttpRequest<any>, next: HttpHandler): Observable<HttpEvent<any>> {
    return next.handle(req).pipe(
      tap((event: HttpEvent<any>) => {
        if (event instanceof HttpResponse) {
          //
        }
      }),
      catchError((err: any) => {
        if(err instanceof HttpErrorResponse) {
          switch(err.status) {
            case 401: 
              this.store.dispatch(new FromAuthStore.actions.Logout());
              this.store.dispatch(new FromUIStore.actions.DisableLoading());
              if(err.error.error === "invalid_credentials"){
                this.alertService.error("Credenciales no válidas");
              }
              else {
                this.alertService.error(err.error.error);
              }
              break
          }
        }
        return of(err);
      })
    );
  }
}
