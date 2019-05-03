import { BaseModel } from './base.model';

// This client-side model 'ContactModel' will be linked to 
// server-side model 'Lead' using only the data subset needed in Reception.

export interface ContactModel extends BaseModel {
    date            : string;
    derivation_date ?: string;
    company_id      : number;
    user_id         : number;
    client_id       ?: number;
    name            : string;
    last_name       : string;
    pre_phone       ?: string;
    phone           ?: string;
    pre_mobile      ?: string;
    mobile          ?: string;
    mail            ?: string;
    reason_id       : number;
    reason          : string;
    product_id      ?: number;
    product         ?: string;
    seller_id       : number;
    seller_name     : string;
    seller_lastname : string;
    channel_id      : number;
    channel         : string;
    comment_id      ?: number;
    commment        ?: string;
    empathy_user_client_id  ?: number;
    empathy_user_client     ?: string;
    empathy_client_user_id  ?: number;
    empathy_client_user     ?: string; 
}

export interface ContactsResponseModel {
  current_page: number;
  data          : ContactModel[];
  from          : number;
  last_page     : number;
  next_page_url : string;
  path          : string;
  per_page      : number;
  prev_page_url : string;
  to            : number;
  total         : number;
}

export interface ContactsList{
    data            : ContactModel[];
    seleccionado    : boolean;
}

export interface ContactPayloadModel {
    contacts: ContactsResponseModel;
}

export interface ContactPayloadModel2 {
    name            : string;
    last_name       : string;
}


export interface ContactObjectModel {
    id      : string;
    value   : string;
}

export interface ReceptionSellerModel {
    id              : number;
    fullname        : string;
    queue_position  : number;
}