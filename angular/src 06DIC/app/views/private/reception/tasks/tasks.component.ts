import { Component, OnInit, ViewChild, ViewChildren, QueryList } from '@angular/core';


//FullCalendar
import { CalendarComponent } from 'ng-fullcalendar';
import { Options } from 'fullcalendar';
import { TaskService } from 'src/app/core/services/tasks/task.service';
import { TaskModel } from 'src/app/core/models/task.model';
import { Observable } from 'rxjs/internal/Observable';

import { range } from 'rxjs';
import { map, filter, tap } from 'rxjs/operators';
import { ConsoleService } from '@ng-select/ng-select/ng-select/console.service';
import { TargetLocator } from 'selenium-webdriver';

declare const $: any;

@Component({
  selector: 'app-tasks',
  templateUrl: './tasks.component.html',
  styleUrls: ['./tasks.component.css']
})
export class TasksComponent implements OnInit {
  
  displayEvent: any;

  title: string =''; 

  defaultTaskFlag: boolean = false;

  calendarOptions: Options;
  tasks$: Observable<TaskModel[]>;

  @ViewChild(CalendarComponent) ucCalendar: CalendarComponent;
  
  constructor( public taskService: TaskService) {}

  ngOnInit() {
    this.taskService.get_tasks({page: '1', filter: '', sort: 'desc'})
    .subscribe( response => {
      const key = 'task';
      if( response.hasOwnProperty(key) ){
        const newArray = response[key].map( tarea => {
          
          return {
            ... tarea,
            start : tarea.date,
            //backgroundColor : (time() > strtotime($date))?'#dd4b39':'#00a65a'; //Red or Green
            backgroundColor : ( new Date() > new Date(tarea.date) ) 
                              ? '#dd4b39' : '#00a65a',
            title: ( tarea.manual_entry == 1 )
                  ? tarea.description : `\n\r${tarea.client_name} ${tarea.client_last_name} \n\r ${tarea.product}`
          }
        })

        this.calendarOptions = {
          editable: true,
          eventLimit: false,
          header: { left: 'prev,next today', center: 'title', right: 'month,agendaWeek,agendaDay,listMonth'},
          buttonText: { today: 'Hoy', month: 'Mes', week: 'Semana', day: 'Día' },
          monthNames: ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'],
          monthNamesShort: ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'],
          
          dayNames: ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'],
          dayNamesShort: ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'],
          firstDay: 1,
          events: newArray

        };

      }

    })



  } //ends ngOnInit

  // Event Handlers:

  clickButton(model: any) {
    this.displayEvent = model;
    console.log('CLICK!');
  }

  showDefaultTask( model: any ){
    this.defaultTaskFlag = true;
    $('.defaultModal').modal('show');
  }



  eventClick(model: any) {

    this.defaultTaskFlag = true;
    this.title = model.event.title;
    console.log(`TASK TITLE: ${this.title}`);
    $('#openDefaultModal').click();
    
    //$('.defaultModal').modal('show');
    /*
    model = {
      event: {
        id: model.event.id,
        start: model.event.start,
        end: model.event.end,
        title: model.event.title,
        allDay: model.event.allDay
        // other params
      },
      duration: {}
    }
    this.displayEvent = model;
    */
  }

  updateEvent(model: any) {
    model = {
      event: {
        id: model.event.id,
        start: model.event.start,
        end: model.event.end,
        title: model.event.title
        // other params
      },
      duration: {
        _data: model.duration._data
      }
    }
    this.displayEvent = model;
  }

  dayClick(model: any) {
    //this.displayEvent = model;
    console.log('CLICK!');
  }

}





