import { BaseModel } from './base.model';

export interface LeadModel extends BaseModel {
    id:number;
    address: string;
    birthday: string;
    comments: string;
    document_nro: string;
    email: string;
    cellphone: string;
    lead_id: string;
    lead_url_origin: string;
    first_name: string;
    name: string;
    phone: string;
    point_of_sale: number;
    last_name: string;
    pre_phone: string;
    vehicle_code: string;
    website_id: string;
    website_lead_id: string;
    seleccionado: boolean;
    create_at: string;  
}

export interface LeadsResponseModel {
  current_page: number;
  data: LeadModel[];
  from: number;
  last_page: number;
  next_page_url: string;
  path: string;
  per_page: number;
  prev_page_url: string;
  to: number;
  total: number;
}

export interface LeadsList{
    data: LeadModel[];
    seleccionado: boolean;
}

export interface LeadPayloadModel {
    leads: LeadsResponseModel;
}

export interface LeadObjectModel {
    id: string;
    value: string;
}