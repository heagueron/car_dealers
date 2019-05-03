import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { SellersRoutingModule } from './sellers-routing.module';
import { SellersComponent } from './sellers.component';
import { SharedModule } from 'src/app/shared/shared.module';


  import { StoreModule } from '@ngrx/store';
  import { EffectsModule } from '@ngrx/effects';
  import * as FromSellersReducer from '../../../store/sellers/reducers/sellers.reducer';
  import { SellersEffects } from '../../../store/sellers/effects/sellers.effects';

@NgModule({
  imports: [
    CommonModule,
    SellersRoutingModule,
    SharedModule,
    StoreModule.forFeature('sellers', FromSellersReducer.reducer ),
    EffectsModule.forFeature([SellersEffects])
  ],
  declarations: [
    SellersComponent
  ]
})
export class SellersModule { }
