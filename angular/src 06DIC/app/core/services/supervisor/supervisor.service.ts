import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { environment } from '../../../../environments/environment';
import { Observable } from 'rxjs/internal/Observable';
import { SupervisoresResponseModel, SupervisorPayloadModel } from "../../models/supervisor.model";

const GET_SUPERVISORES = 'supervisores'

@Injectable({
  providedIn: 'root'
})

export class SupervisorService {

  constructor( private http: HttpClient ) { }

  getSupervisores(params: any): Observable<SupervisorPayloadModel> {
    const _params = new HttpParams()
      .set('page', params.page)
      .set('filter', params.filter)
      .set('sort', 'desc');
      
    return this.http.get<SupervisorPayloadModel>(environment.apiUrl + GET_SUPERVISORES, {params: _params});
  }
}
