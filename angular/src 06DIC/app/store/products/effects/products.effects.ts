//ANGULAR
  import { Injectable } from '@angular/core';
  import { Actions, Effect, ofType } from '@ngrx/effects';
  import { of } from 'rxjs';
  import { catchError, exhaustMap, map, mergeMap, switchMap } from 'rxjs/operators';

//ACTIONS
  import * as fromProducts from '../actions/products.action';

//MODELS
  import { ProductMetaModel, ProductPayloadModel } from '../../../core/models/product.model';

//SERVICES 
  import { ProductService } from '../../../core/services/product/product.service';

@Injectable()
export class ProductsEffects {
  @Effect()
  loadProducts$ = this.actions$.ofType<fromProducts.Load>(fromProducts.ProductsActionTypes.Load)
    .pipe(
      map(action => action.payload),
      exhaustMap((params: any) =>
      this.productService.getProducts(params)
        .pipe(
          mergeMap((res: any) => [
            new fromProducts.LoadPageSuccess(res),
            new fromProducts.LoadProductsSuccess(res.products.data),
            new fromProducts.LoadMeta()
          ]),
          catchError(error => of(new fromProducts.LoadFail(error)))
        )
      )
    );

 @Effect()
  loadProductsMeta$ = this.actions$.ofType<fromProducts.LoadMeta>(fromProducts.ProductsActionTypes.LoadMeta)
    .pipe(
        mergeMap(() => {
          return this.productService.getProductsMeta()
            .pipe(
              map((res: ProductMetaModel) => 
                new fromProducts.LoadMetaSuccess(res)
              ),
              catchError(error => of(new fromProducts.LoadMetaFail(error)))
            );
        })
    );

  constructor(private actions$: Actions, private productService: ProductService) {
    //
  }
}