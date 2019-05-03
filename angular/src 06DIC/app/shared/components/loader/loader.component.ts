//ANGULAR
  import { Component, OnInit } from '@angular/core';
  import { Observable } from 'rxjs/internal/Observable';
  import { Store, select } from '@ngrx/store';

//REDUCERS
  import { AppState } from '../../../store/app.reducers';
  import * as FromUIStore from '../../../store/ui/index';

declare const $: any;
declare const jquery: any;

@Component({
  selector: 'app-loader',
  templateUrl: './loader.component.html',
  styleUrls: ['./loader.component.css']
})
export class LoaderComponent implements OnInit {
  IsLoading$: Observable<boolean>;

  constructor(private store: Store<AppState>) {
    this.IsLoading$ = this.store.pipe(select(FromUIStore.getIsLoading));
  }

  ngOnInit() {
    //setTimeout(function() {
      //$('.page-loader-wrapper').show();
    //}, 1000);
  }

}
