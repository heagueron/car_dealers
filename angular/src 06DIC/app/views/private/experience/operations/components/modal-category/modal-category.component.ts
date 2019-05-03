//ANGULAR
  import { Component, Input, OnInit } from '@angular/core';
  import { Store, select } from '@ngrx/store';
  import { Observable } from 'rxjs';

//EXTERNAL
  import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';

//REDUCERS
  import { AppState } from '../../../../../../store/app.reducers';
  import * as FromSalesStore from '../../../../../../store/sales/index';
  import { CategoryConditionModel } from 'src/app/core/models/client.model';
  import { ObjectModel } from 'src/app/core/models/meta.model';

@Component({
  selector: 'app-modal-category',
  templateUrl: './modal-category.component.html',
  styleUrls: ['./modal-category.component.css']
})
export class ModalCategoryComponent implements OnInit {
  @Input() operationId: number;
  categories$: Observable<ObjectModel[]>;
  category_condition: CategoryConditionModel;

  constructor(
    private store: Store<AppState>,
    public activeModal: NgbActiveModal
  ) {
    this.categories$ = this.store.pipe(select(FromSalesStore.getMetaCategoriesConditions));
  }

  ngOnInit() {
    this.store.pipe(select(FromSalesStore.getSale(+this.operationId))).subscribe(res => {
      this.category_condition = res.client.category_condition;
    });
  }
}
