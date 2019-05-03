//ANGULAR
  import { Component, OnInit } from '@angular/core';
  import { Observable } from 'rxjs/internal/Observable';
  import { Store, select } from '@ngrx/store';

//REDUCERS
  import { AppState } from '../../../store/app.reducers';
  import * as FromAuthStore from '../../../store/auth/index';

//MODELS
  import { UserModel } from '../../../core/models/user.model';

declare const $: any;

@Component({
  selector: 'app-sidebar-left',
  templateUrl: './sidebar-left.component.html',
  styleUrls: ['./sidebar-left.component.css']
})
export class SidebarLeftComponent implements OnInit {
  loggedIn$: Observable<boolean>;
  user$: Observable<UserModel>;

  constructor(private store: Store<AppState>) {
    this.user$ = this.store.pipe(select(FromAuthStore.getUser));
    this.loggedIn$ = this.store.pipe(select(FromAuthStore.getLoggedIn));

    $(function() {            
      menuToggle();   
    });

    function menuToggle() {
      $('.menu-toggle').on('click', function(e) {
        const $this = $(this);
        const $content = $this.next();
        
        if ($($this.parents('ul')[0]).hasClass('list')) {
          const $not = $(e.target).hasClass('menu-toggle') ? e.target : $(e.target).parents('.menu-toggle');
          $.each($('.menu-toggle.toggled').not($not).next(), function(i, val) {
            if ($(val).is(':visible')) {
                $(val).prev().toggleClass('toggled');
                $(val).slideUp();
            }
          });
        }
        
        $this.toggleClass('toggled');
        $content.slideToggle(320);
      });
    }
  }

  ngOnInit() {
    //
  }
}
