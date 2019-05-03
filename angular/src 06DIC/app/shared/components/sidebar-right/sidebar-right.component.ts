//ANGULAR
  import { Component, OnInit } from '@angular/core';
  import { Observable } from 'rxjs/internal/Observable';
  import { Store, select } from '@ngrx/store';

//REDUCERS
  import { AppState } from '../../../store/app.reducers';
  import * as FromAuthStore from '../../../store/auth/index';
  import * as FromUIStore from '../../../store/ui/index';

declare const $: any;

@Component({
  selector: 'app-sidebar-right',
  templateUrl: './sidebar-right.component.html',
  styleUrls: ['./sidebar-right.component.css']
})
export class SidebarRightComponent implements OnInit {
  loggedIn$: Observable<boolean>;
  openSidebar$: Observable<boolean>;

  constructor(private store: Store<AppState>) {
    this.loggedIn$ = this.store.pipe(select(FromAuthStore.getLoggedIn));
    this.openSidebar$ = this.store.pipe(select(FromUIStore.getSidebarRight));
  }  

  ngOnInit() {
    //
  }
}
