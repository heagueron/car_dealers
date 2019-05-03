//ANGULAR
  import { Injectable } from '@angular/core';
  import { HttpClient } from '@angular/common/http';
  import { Observable } from 'rxjs/internal/Observable';

//ENVIRONMENT
  import { environment } from '../../../../environments/environment';

//MODELS
  import { AuthenticateModel } from '../../models/authenticate.model';
  import { LoginModel } from '../../models/login.model';
  
  const LOGIN_ENDPOINT = 'login';

@Injectable({
  providedIn: 'root'
})
export class AuthService {

  constructor(private http: HttpClient) {
    //
  }

  login({email, password}: AuthenticateModel): Observable<LoginModel> {
    return this.http.post<LoginModel>(environment.apiUrl + LOGIN_ENDPOINT, {email, password});
  }
}