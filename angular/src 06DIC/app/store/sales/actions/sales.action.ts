//ANGULAR
  import { Action } from '@ngrx/store';

//MODELS
  import { SaleMetaResponseModel, SaleModel } from '../../../core/models/sale.model';

export enum SalesActionTypes {
  Load = '[Sales] Load',
  LoadPaginationSuccess = '[Sales] Load Success',
  LoadSalesSuccess = '[Sales] Load Sale Success',
  LoadFail = '[Sales] Load Fail',
  LoadMeta = '[Sales] Load Meta',
  LoadMetaSuccess = '[Sales] Load Meta Success',
  LoadMetaFail = '[Sales] Load Meta Fail',
  ResetState = '[Sales] Reset State'
}

export class Load implements Action {
  readonly type = SalesActionTypes.Load;

  constructor(public payload: any) {
  }
}

export class LoadPaginationSuccess implements Action {
  readonly type = SalesActionTypes.LoadPaginationSuccess;

  constructor(public payload: any) {
  }
}

export class LoadSalesSuccess implements Action {
  readonly type = SalesActionTypes.LoadSalesSuccess;

  constructor(public payload: SaleModel[]) {
  }
}

export class LoadFail implements Action {
  readonly type = SalesActionTypes.LoadFail;

  constructor(public payload: any) {
  }
}

export class LoadMeta implements Action {
  readonly type = SalesActionTypes.LoadMeta;
}

export class LoadMetaSuccess implements Action {
  readonly type = SalesActionTypes.LoadMetaSuccess;

  constructor(public payload: SaleMetaResponseModel) {
  }
}

export class LoadMetaFail implements Action {
  readonly type = SalesActionTypes.LoadMetaFail;

  constructor(public payload: any) {
  }
}

export class ResetState implements Action {
  readonly type = SalesActionTypes.ResetState;
}

export type SalesActionsUnion =
  | Load
  | LoadPaginationSuccess
  | LoadSalesSuccess
  | LoadFail
  | LoadMeta
  | LoadMetaSuccess
  | LoadMetaFail
  | ResetState;