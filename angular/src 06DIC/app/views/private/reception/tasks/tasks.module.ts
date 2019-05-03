//ANGULAR
import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

//FullCalendar
import { FullCalendarModule } from 'ng-fullcalendar';

//CORE
import { HttpInterceptorProviders } from '../../../../core/interceptors/index';

//ROUTING
import { TasksRoutingModule } from './tasks-routing.module';

//MODULES
import { SharedModule } from '../../../../shared/shared.module';


//COMPONENTS
import { TasksComponent } from './tasks.component';
import { DefaultTaskComponent } from './taskModals/default-task/default-task.component';


@NgModule({
imports: [
  CommonModule,
  FullCalendarModule,
  TasksRoutingModule
],
declarations: [
    TasksComponent,
    DefaultTaskComponent  
],
entryComponents: [
    TasksComponent
],
providers: [HttpInterceptorProviders]
})
export class TasksModule { }