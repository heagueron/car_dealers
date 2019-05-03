//ANGULAR
  import { Injectable } from '@angular/core';
  import { Actions, Effect, ofType } from '@ngrx/effects';
  import { of } from 'rxjs';
  import { catchError, exhaustMap, map, mergeMap, switchMap } from 'rxjs/operators';

//ACTIONS
  import * as fromSales from '../actions/sales.action';

//MODELS
  import { SaleMetaResponseModel, SalePayloadModel } from '../../../core/models/sale.model';

//SERVICES 
  import { SaleService } from '../../../core/services/sale/sale.service';

@Injectable()
export class SalesEffects {
  @Effect()
  loadSales$ = this.actions$.ofType<fromSales.Load>(fromSales.SalesActionTypes.Load)
    .pipe(
      map(action => action.payload),
      exhaustMap((params: any) =>
      this.saleService.getSales(params)
        .pipe(
          mergeMap((res: SalePayloadModel) => [
            new fromSales.LoadPaginationSuccess(res),
            new fromSales.LoadSalesSuccess(res.sales.data),
            new fromSales.LoadMeta()
          ]),
          catchError(error => of(new fromSales.LoadFail(error)))
        )
      )
    );

 @Effect()
  loadSalesMeta$ = this.actions$.ofType<fromSales.LoadMeta>(fromSales.SalesActionTypes.LoadMeta)
    .pipe(
        mergeMap(() => {
          return this.saleService.getSalesMeta()
            .pipe(
              map((res: SaleMetaResponseModel) => 
                new fromSales.LoadMetaSuccess(res)
              ),
              catchError(error => of(new fromSales.LoadMetaFail(error)))
            );
        })
    );

  constructor(private actions$: Actions, private saleService: SaleService) {
    //
  }
}