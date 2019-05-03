// ANGULAR
  import { NgModule } from '@angular/core';
  import { Routes, RouterModule } from '@angular/router';
  import { AuthGuard } from 'src/app/core/guards/auth/auth.guard';
// COMPONENTS
  import { SellersComponent } from './sellers.component';

const routes: Routes = [
  {
    path: '',
    component: SellersComponent,
    canActivate: [AuthGuard]
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class SellersRoutingModule { }
