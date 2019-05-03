import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

// COMPONENTS
import { LeadsComponent } from "./leads.component";
import { DerivacionComponent } from './components/derivacion/derivacion.component';
import { LeadsGerentesComponent } from './components/gerentes/leads-gerentes.component';
import { LeadsSupervisoresComponent } from './components/supervisores/leads-supervisores.component';
import { LeadsVendedoresComponent } from './components/vendedores/leads-vendedores.component';

import { AuthGuard } from '../../../core/guards/auth/auth.guard';


const routes: Routes = [
  {
    path: '',
    component: LeadsComponent,
    canActivate: [AuthGuard]
  },
  {
    path: 'derivacion',
    component: DerivacionComponent,
    canActivate: [AuthGuard]
  },
  {
    path: 'gerentes',
    component: LeadsGerentesComponent,
    canActivate: [AuthGuard]
  },
  {
    path: 'supervisores',
    component: LeadsSupervisoresComponent,
    canActivate: [AuthGuard]
  },
  {
    path: 'vendedores',
    component: LeadsVendedoresComponent,
    canActivate: [AuthGuard]
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class LeadsRoutingModule { }
