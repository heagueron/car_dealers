//ANGULAR
  import { Component, OnInit } from '@angular/core';
  import { ActivatedRoute } from '@angular/router';
  import { Store, select } from '@ngrx/store';
  import { Observable } from 'rxjs';

//REDUCERS
  import { AppState } from '../../../../../../store/app.reducers';
  import * as FromUIStore from '../../../../../../store/ui/index';
  import * as FromSalesStore from '../../../../../../store/sales/index';

//MODELS
  import { SaleModel } from 'src/app/core/models/sale.model';
import { SellerModel } from 'src/app/core/models/seller.model';

@Component({
  selector: 'app-sidebar-right-seller',
  templateUrl: './sidebar-right-seller.component.html',
  styleUrls: ['./sidebar-right-seller.component.css']
})
export class SidebarRightSellerComponent implements OnInit {
  sale$: Observable<SaleModel>;
  seller: SellerModel;

  constructor(private store: Store<AppState>, private activeRoute: ActivatedRoute) {
    this.activeRoute.params
      .subscribe(
        params => {
          this.store.pipe(select(FromSalesStore.getSale(+params.id))).subscribe(res => {
            this.seller = res.seller
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
