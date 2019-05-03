//ANGULAR
  import { NgModule } from '@angular/core';
  import { CommonModule } from '@angular/common';

//CORE
  import { HttpInterceptorProviders } from '../../../../core/interceptors/index';

//ROUTING
  import { OperationsRoutingModule } from './operations-routing.module';

//MODULES
  import { SharedModule } from '../../../../shared/shared.module';

//PIPES
  import { IdCategoryConditionsPipe } from '../../../../core/pipes/id-category-conditions/id-category-conditions.pipe';

//COMPONENTS
  import { OperationsComponent } from './operations.component';
  import { StageSellerComponent } from './components/stage-seller/stage-seller.component';
  import { StageClientComponent } from './components/stage-client/stage-client.component';
  import { ProductComponent } from './components/product/product.component';
  import { WayToPayComponent } from './components/way-to-pay/way-to-pay.component';
  import { TaskComponent } from './components/task/task.component';
  import { AvanceComponent } from './components/avance/avance.component';
  import { SidebarRightSellerComponent } from './components/sidebar-right-seller/sidebar-right-seller.component';
  import { SidebarRightClientComponent } from './components/sidebar-right-client/sidebar-right-client.component';
  import { ModalAvanceComponent } from './components/modal-avance/modal-avance.component';
  import { ModalCategoryComponent } from './components/modal-category/modal-category.component';
  import { ModalTaskComponent } from './components/modal-task/modal-task.component';
  import { ModalLoadIdDMSComponent } from './components/modal-load-id-dms/modal-load-id-dms.component';
  import { ModalPurchaseRequestComponent } from './components/modal-purchase-request/modal-purchase-request.component';
  import { StatusComponent } from './components/status/status.component';

@NgModule({
  imports: [
    CommonModule,
    OperationsRoutingModule,
    SharedModule
  ],
  declarations: [
    IdCategoryConditionsPipe,
    OperationsComponent,
    StageSellerComponent,
    StageClientComponent,
    ProductComponent,
    SidebarRightSellerComponent,
    SidebarRightClientComponent,
    WayToPayComponent,
    TaskComponent,
    AvanceComponent,
    ModalAvanceComponent,
    ModalCategoryComponent,
    ModalTaskComponent,
    ModalLoadIdDMSComponent,
    ModalPurchaseRequestComponent,
    StatusComponent
  ],
  entryComponents: [
    ModalAvanceComponent,
    ModalCategoryComponent,
    ModalTaskComponent,
    ModalLoadIdDMSComponent,
    ModalPurchaseRequestComponent
  ],
  providers: [HttpInterceptorProviders]
})
export class OperationsModule { }
