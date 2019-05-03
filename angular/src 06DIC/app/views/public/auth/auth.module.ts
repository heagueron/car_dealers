//ANGULAR
  import { NgModule } from '@angular/core';
  import { CommonModule } from '@angular/common';

//ROUTING
  import { AuthRoutingModule } from './auth-routing.module';

//MODULES
  import { SharedModule } from '../../../shared/shared.module';

//COMPONENTS
  import { AuthComponent } from './auth.component';
  import { LoginComponent } from './login/login.component';

@NgModule({
  imports: [
    AuthRoutingModule,
    CommonModule,
    SharedModule
  ],
  declarations: [
    AuthComponent, 
    LoginComponent
  ]
})
export class AuthModule { }
