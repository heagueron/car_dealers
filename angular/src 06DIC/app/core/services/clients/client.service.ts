// ANGULAR
import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';

// RXJS
import { Observable } from 'rxjs/internal/Observable';
import {debounceTime, distinctUntilChanged, map} from 'rxjs/operators';

// ENVIRONMENT
import { environment } from '../../../../environments/environment';

// MODELS
import { ClientModel } from '../../models/client.model';

const CLIENT_SEARCH = 'client_search';

@Injectable({
  providedIn: 'root'
})
export class ClientService {

  queryUrl: string = '?search=';

  constructor(private http: HttpClient) { }

  

  search(params: any): Observable<ClientModel[]> {

    const _params = new HttpParams()
      .set('q', params.q)
      .set('page', params.page)
      .set('sort', 'desc'); 
    
    return this.http.get<ClientModel[]>(environment.apiUrl + CLIENT_SEARCH,{params: _params});
  }

}

