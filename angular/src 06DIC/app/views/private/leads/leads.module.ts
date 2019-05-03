// ANGULAR
  import { NgModule } from '@angular/core';
  import { Routes, RouterModule } from "@angular/router";
  import { CommonModule } from '@angular/common';
  import { FormsModule } from "@angular/forms";

//MODULES
  import { SharedModule } from '../../../shared/shared.module';
  import { LeadsRoutingModule } from './leads-routing.module';
  //import { NgSelectModule } from "@ng-select/ng-select";

// COMPONENTS
  import { LeadsComponent } from "./leads.component";
  import { DerivacionComponent } from "./components/derivacion/derivacion.component";

// NGRX
  import { StoreModule } from '@ngrx/store';
  import { EffectsModule } from '@ngrx/effects';
  import * as FromLeadsReducer from '../../../store/leads/reducers/leads.reducer';
  import { LeadsEffects } from "../../../store/leads/effects/leads.effects";
  import { DerivationSelectionComponent } from './components/modals/derivation-selection.component';
import { SelectionUsersComponent } from './components/modals/modal_for_derivation/selection-users.component';
import { LeadsGerentesComponent } from './components/gerentes/leads-gerentes.component';
import { LeadsSupervisoresComponent } from './components/supervisores/leads-supervisores.component';
import { LeadsVendedoresComponent } from './components/vendedores/leads-vendedores.component';

@NgModule({
  imports: [
    CommonModule,
    LeadsRoutingModule,
    //NgSelectModule,
    FormsModule,
    SharedModule,
    StoreModule.forFeature('leads', FromLeadsReducer.reducer ),
    EffectsModule.forFeature([LeadsEffects])
  ],
  declarations: [
    LeadsComponent,
    DerivationSelectionComponent,
    DerivacionComponent,
    SelectionUsersComponent,
    LeadsGerentesComponent,
    LeadsSupervisoresComponent,
    LeadsVendedoresComponent
  ]
})
export class LeadsModule { }
