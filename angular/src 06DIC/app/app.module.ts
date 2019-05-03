//ANGULAR
  import { BrowserModule } from '@angular/platform-browser';
  import { NgModule } from '@angular/core';
  import { HttpClientModule } from '@angular/common/http';
  import { StoreModule } from '@ngrx/store';
  import { EffectsModule } from '@ngrx/effects';

//EXTERNAL
  import { StoreDevtoolsModule } from '@ngrx/store-devtools';

//REDUCERS
  import { AppReducers, metaReducers } from './store/app.reducers';

//EFFECTS
  import { AppEffects } from './store/app.effects';

//ENVIRONMENTS
  import { environment } from '../environments/environment';

//ROUTING
  import { AppRoutingModule } from './app-routing.module';

//MODULES
  import { CoreModule } from './core/core.module';
  import { SharedModule } from './shared/shared.module';
  import { AuthModule } from './views/public/auth/auth.module';

//COMPONENTS
  import { AppComponent } from './app.component';

@NgModule({
  declarations: [
    AppComponent
  ],
  imports: [
    AppRoutingModule,
    AuthModule,
    BrowserModule,
    CoreModule.forRoot(),
    HttpClientModule,
    SharedModule,
    StoreModule.forRoot(AppReducers, {metaReducers}),
    StoreDevtoolsModule.instrument({
      maxAge: 25,
      logOnly: environment.production
    }),
    EffectsModule.forRoot(AppEffects),
  ],
  providers: [],
  bootstrap: [AppComponent]
})
export class AppModule { }
