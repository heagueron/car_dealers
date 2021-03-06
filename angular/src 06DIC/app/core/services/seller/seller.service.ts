import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { environment } from '../../../../environments/environment';
import { Observable } from 'rxjs/internal/Observable';
import { SellersResponseModel, SellerPayloadModel } from '../../models/seller.model';

const SELLER_SEARCH = 'sellers';
const GET_LEADS = 'leads';

@Injectable({
  providedIn: 'root'
})
export class SellerService {

  constructor(private http: HttpClient) {
  }

  getSellers(params: any): Observable<SellerPayloadModel> {
    const _params = new HttpParams()
      .set('page', params.page)
      .set('filter', params.filter)
      .set('sort', 'desc');
    return this.http.get<SellerPayloadModel>(environment.apiUrl + SELLER_SEARCH, {params: _params});
  }
  /*
    searchSeller(query: string) {
      const _params = new HttpParams()
        .set('q', query)
        .set('page', '1');
      return this.http.get(environment.apiUrl + CLIENT_SEARCH, {params: _params});
    }*/

  // getSellers_test() {
  //   return this.http.get(environment.apiUrl + SELLER_SEARCH);
  // }

  // getLeads_test() {
  //   return this.http.get(environment.apiUrl + GET_LEADS);
  // }
}
