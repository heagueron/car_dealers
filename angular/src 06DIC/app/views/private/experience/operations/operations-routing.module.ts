//ANGULAR
  import { NgModule } from '@angular/core';
  import { Routes, RouterModule } from '@angular/router';

//COMPONENTS
  import { OperationsComponent } from './operations.component';
  import { SidebarRightSellerComponent } from './components/sidebar-right-seller/sidebar-right-seller.component';
  import { SidebarRightClientComponent } from './components/sidebar-right-client/sidebar-right-client.component';

//GUARDS
  import { AuthGuard } from '../../../../core/guards/auth/auth.guard';

const routes: Routes = [
  {
    path: '',
    component: OperationsComponent,
    canActivate: [AuthGuard]
  },
  {
    path: 'seller-operation/:id',
    component: SidebarRightSellerComponent,
    outlet: 'rightSidenav'
  },
  {
    path: 'client-operation/:id',
    component: SidebarRightClientComponent,
    outlet: 'rightSidenav'
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class OperationsRoutingModule { }
