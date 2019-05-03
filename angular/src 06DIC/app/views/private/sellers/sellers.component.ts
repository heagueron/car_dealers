  import { Component, OnInit, OnDestroy } from '@angular/core';
  import { Observable, Subject } from 'rxjs';

// SERVICES  
  import { SellerService } from "../../../core/services/seller/seller.service";
// MODELS
  import { SellerModel } from 'src/app/core/models/seller.model';

// NGRX
  import { Store, select } from '@ngrx/store';
  import { tap } from 'rxjs/operators';
  import { AppState } from 'src/app/store/sellers';
  import * as SellerActions from "../../../store/sellers/actions/sellers.action";
  import * as fromSellers from '../../../store/sellers'
  



@Component({
  selector: 'app-sellers',
  templateUrl: './sellers.component.html',
  styleUrls: ['./sellers.component.css']
})
export class SellersComponent implements OnInit {
  
  sellers$: Observable<SellerModel[]>;
  sellersCopy$: SellerModel[];
  resultsLength$: Observable<number>;

  
  
  constructor( private sellerService: SellerService,
               private store: Store<AppState> ) { }

  ngOnInit() {
  
    const accionLoad = new SellerActions.Load({page: 1, filter: ''});
    this.store.dispatch( accionLoad );
    
    this.sellerService.getSellers({page: 1, filter: ''}).subscribe(
      data =>{
        console.log('DATOS DEL SERVICIO SELLER (FUNCIONANDO): ',data.sellers.data);
        this.sellersCopy$ = data.sellers.data;
      }
    );
    
    this.store.pipe(
      select(fromSellers.getSellers),
      tap(sellers => {
        console.log( 'DENTRO DEL PIPE DE SELLERS: ' , sellers );
        // this.setLeadsCopy(leads);
      })
    );
  }
}
