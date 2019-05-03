//ANGULAR
  import { Component, OnInit } from '@angular/core';
  import { Observable } from 'rxjs/internal/Observable';
  import { Store, select } from '@ngrx/store';

//REDUCERS
  import { AppState } from '../../../store/app.reducers';
  import * as FromAuthStore from '../../../store/auth';
  import * as FromLeadsStore  from '../../../store/leads';
  import * as FromProductsStore from '../../../store/products';
  import * as FromSalesStore from '../../../store/sales';
  import * as FromSellersStore from '../../../store/sellers';
  import * as FromUIStore from '../../../store/ui';

@Component({
  selector: 'app-navbar-header',
  templateUrl: './navbar-header.component.html',
  styleUrls: ['./navbar-header.component.css']
})
export class NavbarHeaderComponent implements OnInit {
  loggedIn$: Observable<boolean>;

  constructor(private store: Store<AppState>) {
    this.loggedIn$ = this.store.pipe(select(FromAuthStore.getLoggedIn));
  }

  ngOnInit() {
    //
  }

  logout() {
    this.store.dispatch(new FromAuthStore.actions.Logout());
    this.store.dispatch(new FromSalesStore.actions.ResetState());
    this.store.dispatch(new FromUIStore.actions.ResetState());
  }
}
