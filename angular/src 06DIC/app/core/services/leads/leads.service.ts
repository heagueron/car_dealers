//ANGULAR
  import { Injectable } from '@angular/core';
  import { HttpClient, HttpParams } from '@angular/common/http';
  import { Observable } from 'rxjs/internal/Observable';

//ENVIRONMENT
  import { environment } from '../../../../environments/environment';

// MODELS
  import { LeadsResponseModel, LeadPayloadModel } from '../../models/lead.model';
import { SellerPayloadModel } from '../../models/seller.model';

const GET_LEADS = 'leads';
const AVAILABLES_LEADS = 'availables_leads';
const OPTIONS_CRITERIOS = 'options_criterios';
const ASIGNAR_LEADS_CONFIGURACION = 'asignar_leads_configuracion';

@Injectable({
  providedIn: 'root'
})
export class LeadsService {

  constructor(private http: HttpClient) {}

  available_leads(params: any): Observable<LeadPayloadModel> {
    const _params = new HttpParams()
      .set('page', params.page)
      .set('filter', params.filter)
      .set('sort', 'desc');    
    return this.http.get<LeadPayloadModel>(environment.apiUrl + AVAILABLES_LEADS,{params: _params});
  }

  options_criterios(){
    
    return this.http.get<any>(environment.apiUrl + OPTIONS_CRITERIOS);
  }

  asign_leads_seller(params: any):Observable<any>{
    console.log('PARAMETROS: ', params);
    const _params = new HttpParams()
      .set('criterios', params.criterios)
      .set('opciones_criterios', params.opciones_criterios)
      .set('derivacion', params.derivaciones)
      .set('gerentes', params.gerentes)
      .set('supervisores', params.supervisores)
      .set('vendedores', params.vendedores);
    console.log('PARAMETROS ASIGN LEADS = ',_params);
    return this.http.get<any>(environment.apiUrl + ASIGNAR_LEADS_CONFIGURACION,{ params: _params });

  }
}
