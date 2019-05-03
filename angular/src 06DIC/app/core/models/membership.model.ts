import { BaseModel } from './base.model';

export interface MembershipModel extends BaseModel {
    color: string,
    detail: string,
    name: string
}