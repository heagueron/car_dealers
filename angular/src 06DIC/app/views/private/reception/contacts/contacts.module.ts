//ANGULAR
import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule, ReactiveFormsModule } from "@angular/forms";

//CORE
import { HttpInterceptorProviders } from '../../../../core/interceptors/index';

//ROUTING
import { ContactsRoutingModule } from './contacts-routing.module';

//MODULES
import { SharedModule } from '../../../../shared/shared.module';
import { NgSelectModule } from '@ng-select/ng-select';

//COMPONENTS
import { ContactsComponent } from './contacts.component';
import { ContactListComponent } from './components/contact-list/contact-list.component';
import { ContactSearchComponent } from './components/contact-search/contact-search.component';
import { ContactStatsComponent } from './components/contact-stats/contact-stats.component';
import { ContactItemComponent } from './components/contact-item/contact-item.component';
import { RegisterReceptionComponent } from './receptionModals/register-reception/register-reception.component';
import { ModalReceptionComponent } from './receptionModals/modal-reception/modal-reception.component';



@NgModule({
imports: [
  CommonModule,
  FormsModule,
  ReactiveFormsModule,
  ContactsRoutingModule,
  NgSelectModule
],
declarations: [
  ContactsComponent,
  ContactListComponent,
  ContactSearchComponent,
  ContactStatsComponent,
  ContactItemComponent,
  RegisterReceptionComponent,
  ModalReceptionComponent 
],
entryComponents: [
    ContactsComponent,
    RegisterReceptionComponent,
    ModalReceptionComponent
],
providers: [HttpInterceptorProviders]
})
export class ContactsModule { }
