//ANGULAR
  import { HTTP_INTERCEPTORS } from '@angular/common/http';

//TOKEN
  import { TokenInterceptor } from './token.interceptor';

//JWT
  import { JwtInterceptor } from './jwt.interceptor';

export const HttpInterceptorProviders = [
  {provide: HTTP_INTERCEPTORS, useClass: JwtInterceptor, multi: true},
  {provide: HTTP_INTERCEPTORS, useClass: TokenInterceptor, multi: true}
]

