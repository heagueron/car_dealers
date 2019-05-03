//ANGULAR
import { Injectable } from '@angular/core';
import { HttpClient, HttpParams, HttpHeaders } from '@angular/common/http';
import { Observable } from 'rxjs/internal/Observable';

//ENVIRONMENT
import { environment } from '../../../../environments/environment';

// MODELS
import { ContactsResponseModel, ContactPayloadModel, ContactPayloadModel2, ContactModel } from '../../models/contact.model';
import { Store } from '@ngrx/store';
import { AppState } from 'src/app/store/app.reducers';

const GET_CONTACTS = 'contacts';
const SAVE_RECEPTION = 'save_reception';

const LOAD_RECEPTION_SELLERS = 'get_queue';

//const AVAILABLES_CONTACTS = 'availables_contacts';


@Injectable({
  providedIn: 'root'
})
export class ReceptionService {

  httpOptions : any;

  constructor( private http: HttpClient, public store: Store<AppState> ) {

    this.store.select('auth').subscribe( authData => {

      this.httpOptions = {
        headers: new HttpHeaders({
          'Content-Type' : 'application/json',
          'Authorization': authData.token
        })
      };

    });

  }

  get_receptions(params: any): Observable<ContactModel[]> {
    const _params = new HttpParams()
      .set('page', params.page)
      .set('filter', params.filter)
      .set('sort', 'desc');    
    return this.http.get<ContactModel[]>(environment.apiUrl + GET_CONTACTS,{params: _params});
  }

  save_reception(reception: any): Observable<any> {

    console.log(`RECEPTION RECIBIDA EN SERVICIO CREAR: ${reception.name}`);

    const _params = new HttpParams()
      .set( 'reception', reception ); 
    
    //return this.http.post<any>(environment.apiUrl + SAVE_RECEPTION,{params: _params});

    return this.http.post<any>(environment.apiUrl + SAVE_RECEPTION, reception, this.httpOptions);
 
  }

  load_reception_sellers():Observable<any> {
    return this.http.get<any>(environment.apiUrl + LOAD_RECEPTION_SELLERS );
  }

}
