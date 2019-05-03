//ANGULAR
  import { Component, OnInit } from '@angular/core';
  import { Store, select } from '@ngrx/store';
  import { Observable } from 'rxjs';

//REDUCERS
  import { AppState } from '../../../../store/app.reducers';
  import * as FromSalesStore from '../../../../store/sales/index';

//SERVICES
  import { PaginationService } from './../../../../core/services/pagination/pagination.service';
  import { SaleService } from '../../../../core/services/sale/sale.service';

//MODELS
  import { SaleModel, SaleMetaResponseModel } from '../../../../core/models/sale.model';
  import { PaginationModel } from '../../../../core/models/pagination.model';

declare const $: any;

@Component({
  selector: 'app-operations',
  templateUrl: './operations.component.html',
  styleUrls: ['./operations.component.css']
})
export class OperationsComponent implements OnInit {
  sales$: Observable<SaleModel[]>;
  //salesMeta$: Observable<SaleMetaResponseModel>;
  pagination$: Observable<PaginationModel>;
  paginate: any = [];

  constructor(
    private saleService: SaleService, 
    private paginationService: PaginationService, 
    private store: Store<AppState>
  ) {
    this.sales$ = this.store.pipe(select(FromSalesStore.getSales));
    //this.salesMeta$ = this.store.pipe(select(FromSalesStore.getMeta));
    this.pagination$ = this.store.pipe(select(FromSalesStore.getPagination));
  }

  ngOnInit() {
    //TEST SERVICE
      this.saleService.getSales({page: '1', filter: '', sort: 'desc'}).subscribe(data => {
        console.log(data);
      });
      this.saleService.getSalesMeta().subscribe(data => {
        console.log(data);
      });
    
    this.store.dispatch(new FromSalesStore.actions.Load({page: '1', filter: ''}));

    this.pagination$.subscribe(pagination => {
      this.paginate = this.paginationService.getPagination(pagination.current_page, pagination.last_page, pagination.to);
    });
  }

  changePage(pageNum:number) {
    this.store.dispatch(new FromSalesStore.actions.Load({page: pageNum, filter: ''}));
  }
}
