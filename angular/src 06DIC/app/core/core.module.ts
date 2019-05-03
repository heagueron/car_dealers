//ANGULAR
  import { ModuleWithProviders, NgModule, Optional, SkipSelf } from '@angular/core';

//GUARDS
  import { AuthGuard } from './guards/auth/auth.guard';

//INTERCEPTORS
  import { HttpInterceptorProviders } from './interceptors/index';

//SERVICES
  import { AlertService } from './services/alert/alert.service';
  import { AuthService } from './services/auth/auth.service';
  import { LeadsService } from "./services/leads/leads.service";
  import { PaginationService } from './services/pagination/pagination.service';
  import { ProductService } from './services/product/product.service';
  import { SaleService } from './services/sale/sale.service';
  import { TaskService } from './services/tasks/task.service';
  import { ContactsService } from './services/contacts/contacts.service';

@NgModule()
export class CoreModule {
  constructor (@Optional() @SkipSelf() parentModule: CoreModule) {
    if (parentModule) {
      throw new Error(
        'CoreModule is already loaded. Import it in the AppModule only');
    }
  }

  static forRoot(): ModuleWithProviders {
    return {
      ngModule: CoreModule,
      providers: [
        AuthGuard,
        AlertService,
        AuthService,
        HttpInterceptorProviders,
        LeadsService,
        PaginationService,
        ProductService,
        SaleService,
        TaskService,
        ContactsService
      ]
    };
  }
}
