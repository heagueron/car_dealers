import { BaseModel } from './base.model';

export interface GerenteModel extends BaseModel {
  birthday: string;
  cellphone: string;
  email: string;
  first_name: string;
  last_name: string;
  gender: string;
  id_company: number;
  phone: string;
}

/*export interface CategoryModel extends BaseModel {
  category: string;
  condition: string;
  icon: string;
}

export interface ChannelModel extends BaseModel {
  channel: string;
  details: string;
  icon: string;
  order: string;
}

export interface GerenteEmailModel extends BaseModel {
  Gerente_id: number;
  id: number;
  id_type_mail: number;
  mail: string;
  principal: number;
}

export interface GerenteContactModel extends BaseModel {
  area_code: number;
  cliend_id: number;
  ext_phone: string;
  id: number;
  id_paises: number;
  id_type_phone: number;
  order: string;
  phone: string;
  wsp: string;
}

export interface OriginModel extends BaseModel {
  id_brand_origin: number;
  id_parent: number;
  order: string;
  origin: string;
}

export interface SocialNetworkModel extends BaseModel {
  facebook: string;
  google: string;
  instagram: string;
  linkedin: string;
  twitter: string;
  cellphone: string;
  email: string;
  first_name: string;
  last_name: string;
  gender: string;
  id_company: number;
  phone: string;
}
*/
export interface GerentesResponseModel {
  current_page: number;
  data: GerenteModel[];
  from: number;
  last_page: number;
  next_page_url: string;
  path: string;
  per_page: number;
  prev_page_url: string;
  to: number;
  total: number;
}

export interface Company extends BaseModel {
  cuit: string;
  database_name: string;
  database_pass: string;
  database_user: string;
  direccion: string;
}

export interface GerenteObjectModel {
  id: string;
  value: string;
}

export interface GerentePayloadModel {
  gerentes: GerentesResponseModel;
}
