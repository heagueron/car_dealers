//ANGULAR
  import { Component, OnInit } from '@angular/core';
  import { Store, select } from '@ngrx/store';
  import { ActivatedRoute } from '@angular/router';
  import { Observable } from 'rxjs';

//REDUCERS
  import { AppState } from '../../../../../../store/app.reducers';
  import * as FromUIStore from '../../../../../../store/ui/index';
  import * as FromSalesStore from '../../../../../../store/sales/index';

//MODELS
  import { SaleModel } from 'src/app/core/models/sale.model';
  import { ClientModel } from 'src/app/core/models/client.model';

@Component({
  selector: 'app-sidebar-right-client',
  templateUrl: './sidebar-right-client.component.html',
  styleUrls: ['./sidebar-right-client.component.css']
})
export class SidebarRightClientComponent implements OnInit {
  sale$: Observable<SaleModel>;
  client: ClientModel;

  constructor(private store: Store<AppState>, private activeRoute: ActivatedRoute) {
    this.activeRoute.params
      .subscribe(
        params => {
          this.store.pipe(select(FromSalesStore.getSale(+params.id))).subscribe(res => {
            this.client = res.client;
          });
        }
      );
  }

  ngOnInit() {
    //
  }

  closeSide() {
    this.store.dispatch(new FromUIStore.actions.DisableSidebarRight());
  }
}
