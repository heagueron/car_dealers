//ANGULAR
  import { Component, Input, OnInit } from '@angular/core';
  import { Store, select } from '@ngrx/store';

//REDUCERS
  import { AppState } from '../../../../../../store/app.reducers';
  import * as FromUIStore from '../../../../../../store/ui/index';

//MODELS
  import { SellerModel } from 'src/app/core/models/seller.model';

@Component({
  selector: 'app-stage-seller',
  templateUrl: './stage-seller.component.html',
  styleUrls: ['./stage-seller.component.css']
})
export class StageSellerComponent implements OnInit {
  @Input() id: number;
  @Input() seller: SellerModel;
  @Input() dateSale: any;
  @Input() Datepromised: any;

  constructor(private store: Store<AppState>) {
    //
  }

  ngOnInit() {
    //
  }

  openSidebar() {
    this.store.dispatch(new FromUIStore.actions.EnableSidebarRight());
  }
}
