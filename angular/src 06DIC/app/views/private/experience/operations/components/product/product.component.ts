//ANGULAR
  import { Component, Input, OnInit } from '@angular/core';
  import { Store, select } from '@ngrx/store';

//REDUCERS
  import { AppState } from '../../../../../../store/app.reducers';
  import * as FromProductsStore from '../../../../../../store/products/index';

//MODELS
  import { BudgetModel } from '../../../../../../core/models/budget.model';

@Component({
  selector: 'app-product',
  templateUrl: './product.component.html',
  styleUrls: ['./product.component.css']
})
export class ProductComponent implements OnInit {
  @Input() budget: BudgetModel[];

  constructor(private store: Store<AppState>) {
    //
  }

  ngOnInit() {
    //console.log(this.budget[0]);
  }

}
