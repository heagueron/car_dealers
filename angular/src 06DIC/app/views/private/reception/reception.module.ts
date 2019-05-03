
// ANGULAR
import { NgModule } from '@angular/core';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';

//ROUTING
import { ReceptionRoutingModule } from './reception-routing.module';
  
// COMPONENTS
import { ReceptionComponent } from './reception.component';


@NgModule({
imports: [
  CommonModule,
  ReceptionRoutingModule,
  FormsModule,
  ReactiveFormsModule
],
declarations: [
  ReceptionComponent
]
})
export class ReceptionModule { }
