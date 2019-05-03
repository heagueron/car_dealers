//ANGULAR
  import { NgModule } from '@angular/core';
  import { Routes, RouterModule } from '@angular/router';

//GUARDS
  import { AuthGuard } from '../../../core/guards/auth/auth.guard';

const routes: Routes = [
  {
    path: 'operations',
    loadChildren: './operations/operations.module#OperationsModule',
    canActivate: [AuthGuard]
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class ExperienceRoutingModule { }
