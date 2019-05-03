import { BaseModel } from './base.model';

export interface TaskModel extends BaseModel {
    id_user         : number;
    id_employee     : string;
    id_empresa      : number;
    date            : string;
    id_client       : number;
    id_product      : number;
    id_contact      : number;
    is_closed       : number;
    id_budget       : number;
    id_event        : number;
    id_task_reason  : number;
    id_process      : number;
    manual_entry    : number;
    description     : string;
    id_task_result  : number;
    lead_url_origin : string; 
    title          ?: string;
}

export interface TasksResponseModel {
  current_page: number;
  data: TaskModel[];
  from: number;
  last_page: number;
  next_page_url: string;
  path: string;
  per_page: number;
  prev_page_url: string;
  to: number;
  total: number;
}

export interface TasksList{
    data: TaskModel[];
    seleccionado: boolean;
}

export interface TaskPayloadModel {
    leads: TasksResponseModel;
}

export interface TaskObjectModel {
    id: string;
    value: string;
}