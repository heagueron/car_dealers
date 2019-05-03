import { UserModel } from './user.model';

export interface AuthModel {
    loggedIn: boolean;
    token: string;
    user: UserModel | null;
}