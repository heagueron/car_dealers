import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { environment } from '../../../../environments/environment';
import { Observable } from 'rxjs/internal/Observable';
import { GerentesResponseModel, GerentePayloadModel } from '../../models/gerente.model';

const GET_GERENTES = 'gerentes'

@Injectable({
  providedIn: 'root'
})
export class GerenteService {

  constructor( private http: HttpClient ) { }

  getGerentes(params: any): Observable<GerentePayloadModel> {
    const _params = new HttpParams()
      .set('page', params.page)
      .set('filter', params.filter)
      .set('sort', 'desc');
      
    return this.http.get<GerentePayloadModel>(environment.apiUrl + GET_GERENTES, {params: _params});
  }
}
