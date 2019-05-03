//ANGULAR
import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

//COMPONENTS
import { TasksComponent } from './tasks.component';

//GUARDS
import { AuthGuard } from '../../../../core/guards/auth/auth.guard';



const routes: Routes = [

{
  path: '',
  component: TasksComponent,
  canActivate: [AuthGuard]
}

];

@NgModule({
imports: [RouterModule.forChild(routes)],
exports: [RouterModule]
})
export class TasksRoutingModule { }