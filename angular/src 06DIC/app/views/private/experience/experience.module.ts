//ANGULAR
  import { NgModule } from '@angular/core';
  import { CommonModule } from '@angular/common';

//ROUTING
  import { ExperienceRoutingModule } from './experience-routing.module';

//COMPONENTS
  import { ExperienceComponent } from './experience.component';

@NgModule({
  imports: [
    CommonModule,
    ExperienceRoutingModule
  ],
  declarations: [
    ExperienceComponent
  ]
})
export class ExperienceModule { }
