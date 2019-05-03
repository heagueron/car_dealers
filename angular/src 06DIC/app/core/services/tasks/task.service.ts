//ANGULAR
import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs/internal/Observable';

//ENVIRONMENT
import { environment } from '../../../../environments/environment';

// MODELS
import { TaskModel } from '../../models/task.model';

const GET_TASKS = 'tasks';

@Injectable({
  providedIn: 'root'
})
export class TaskService {

  constructor(private http: HttpClient) {}

  get_tasks(params: any): Observable<TaskModel[]> {
    const _params = new HttpParams()
      .set('page', params.page)
      .set('filter', params.filter)
      .set('sort', 'desc');    
    return this.http.get<TaskModel[]>(environment.apiUrl + GET_TASKS,{params: _params});
  } 

}
