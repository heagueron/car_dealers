// ANGULAR
    import { Injectable } from '@angular/core';
    import { Router } from '@angular/router';
    import { of } from 'rxjs';
    import { catchError, map, mergeMap, switchMap } from 'rxjs/operators';

// NGRX
    import { Actions, Effect, ofType } from '@ngrx/effects';
    import { LeadsActionTypes, 
             Load, 
             LoadLeadsSuccess, 
             LoadFail, 
             LoadPageSuccess } from '../actions/leads.action';

    import { LeadsService } from '../../../core/services/leads/leads.service';
    import { LeadPayloadModel } from '../../../core/models/lead.model';


@Injectable()
export class LeadsEffects {
  @Effect()
  loadLeads$ = this.actions$.pipe(
    ofType<Load>(LeadsActionTypes.Load),
    map(action => action.payload),
    switchMap((params: any) =>
      this.leadService
        .available_leads(params)
        .pipe(
          map( (res: LeadPayloadModel) => {
            console.log('DENTRO DE EFFECT DE LEADS', res);
            return res;
          }),
          mergeMap((res: LeadPayloadModel) => [
            new LoadPageSuccess(res),
            new LoadLeadsSuccess(res.leads.data)
          ]),
          catchError(error => of(new LoadFail(error)))
        )
    )
  );

  constructor(
    private actions$: Actions,
    private leadService: LeadsService,
    private router: Router
  ) {
  }
}
