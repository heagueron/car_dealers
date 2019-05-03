//ANGULAR
import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

//GUARDS
import { AuthGuard } from '../../../core/guards/auth/auth.guard';

const routes: Routes = [

{
  path: 'contacts',
  loadChildren: './contacts/contacts.module#ContactsModule',
  canActivate: [AuthGuard]
},

{
  path: 'tasks',
  loadChildren: './tasks/tasks.module#TasksModule',
  canActivate: [AuthGuard]
}

];

@NgModule({
imports: [RouterModule.forChild(routes)],
exports: [RouterModule]
})
export class ReceptionRoutingModule { }
