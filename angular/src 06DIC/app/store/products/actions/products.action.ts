//ANGULAR
  import { Action } from '@ngrx/store';

//MODELS
  import { ProductMetaModel, ProductModel } from '../../../core/models/product.model';

export enum ProductsActionTypes {
  Load = '[Products] Load',
  LoadPageSuccess = '[Products] Load Success',
  LoadProductsSuccess = '[Products] Load Product Success',
  LoadFail = '[Products] Load Fail',
  LoadMeta = '[Products] Load Meta',
  LoadMetaSuccess = '[Products] Load Meta Success',
  LoadMetaFail = '[Products] Load Meta Fail'
}

export class Load implements Action {
  readonly type = ProductsActionTypes.Load;

  constructor(public payload: any) {
  }
}

export class LoadPageSuccess implements Action {
  readonly type = ProductsActionTypes.LoadPageSuccess;

  constructor(public payload: any) {
  }
}

export class LoadProductsSuccess implements Action {
  readonly type = ProductsActionTypes.LoadProductsSuccess;

  constructor(public payload: ProductModel[]) {
  }
}

export class LoadFail implements Action {
  readonly type = ProductsActionTypes.LoadFail;

  constructor(public payload: any) {
  }
}

export class LoadMeta implements Action {
  readonly type = ProductsActionTypes.LoadMeta;
}

export class LoadMetaSuccess implements Action {
  readonly type = ProductsActionTypes.LoadMetaSuccess;

  constructor(public payload: ProductMetaModel) {
  }
}

export class LoadMetaFail implements Action {
  readonly type = ProductsActionTypes.LoadMetaFail;

  constructor(public payload: any) {
  }
}

export type ProductsActionsUnion =
  | Load
  | LoadPageSuccess
  | LoadProductsSuccess
  | LoadFail
  | LoadMeta
  | LoadMetaSuccess
  | LoadMetaFail;
