//ANGULAR
  import { Injectable } from '@angular/core';
  import { HttpClient, HttpParams } from '@angular/common/http';
  import { environment } from '../../../../environments/environment';
  import { Observable } from 'rxjs/internal/Observable';
  import { Store, select } from '@ngrx/store';

//STORE
  import { AppState } from '../../../store/app.reducers';
  import * as FromUIStore from '../../../store/ui/index';

const SALES = 'sales';
const METADATA = 'salesmetadata';
const SUBSTAGE = 'sub_stage';

@Injectable({
  providedIn: 'root'
})
export class SaleService {

  constructor(
    private http: HttpClient,
    private store: Store<AppState>
  ) {
    //
  }

  getSales(params: {page: '1', filter: '', sort: 'desc'}): Observable<any> {
    this.store.dispatch(new FromUIStore.actions.EnableLoading());

    const _params = new HttpParams()
      .set('page', params.page)
      .set('filter', params.filter)
      .set('sort', params.sort);
    let data = this.http.get<any>(environment.apiUrl + SALES, {params: _params});
    this.store.dispatch(new FromUIStore.actions.DisableLoading());

    return data;
  }

  searchSale(query: string) {
    this.store.dispatch(new FromUIStore.actions.EnableLoading());

    const _params = new HttpParams()
      .set('q', query)
      .set('page', '1');
    let data = this.http.get(environment.apiUrl + SALES, {params: _params});
    this.store.dispatch(new FromUIStore.actions.DisableLoading());

    return data;
  }

  getSalesMeta() {
    this.store.dispatch(new FromUIStore.actions.EnableLoading());

    let data = this.http.get(environment.apiUrl + METADATA);
    this.store.dispatch(new FromUIStore.actions.DisableLoading());

    return data;
  }

  getStage(id: number) {
    this.store.dispatch(new FromUIStore.actions.EnableLoading());

    const _params = new HttpParams()
      .set('id_stage', String(id));
    let data = this.http.get(environment.apiUrl + SUBSTAGE, {params: _params});
    this.store.dispatch(new FromUIStore.actions.DisableLoading());
    
    return data;
  }
}
