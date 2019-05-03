//ANGULAR
  import { NgModule } from '@angular/core';
  import { Routes, RouterModule } from '@angular/router';

//GUARDS
  import { AuthGuard } from './core/guards/auth/auth.guard';
  //import { DerivacionComponent } from './views/private/leads/components/derivacion/derivacion.component';
  //import { PageNotFoundComponent } from './shared/components/page-not-found/page-not-found.component';

const routes: Routes = [
  {
    path: 'experience',
    loadChildren: './views/private/experience/experience.module#ExperienceModule',
    canActivate: [AuthGuard]
  },
  {
    path: 'leads',
    loadChildren: './views/private/leads/leads.module#LeadsModule',
    canActivate: [AuthGuard]
  },
  {
    path: 'sellers',
    loadChildren: './views/private/sellers/sellers.module#SellersModule',
    canActivate: [AuthGuard]
  },

  {
    path: 'reception',
    loadChildren: './views/private/reception/reception.module#ReceptionModule',
    canActivate: [AuthGuard]
  },
  // {
  //   path: 'derivacion',
  //   loadChildren: './views/private/leads/leads.module#LeadsModule',
  //   canActivate: [AuthGuard]
  // },
  {
    path: '',
    redirectTo: 'experience/operations',
    pathMatch: 'full'
  }
  //{ path: '**', component: PageNotFoundComponent },
];

@NgModule({
  imports: [RouterModule.forRoot(routes)],
  exports: [RouterModule]
})
export class AppRoutingModule { }
