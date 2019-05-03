//ANGULAR
  import { Component, OnInit } from '@angular/core';
  import { Router } from '@angular/router';
  import { FormBuilder, FormGroup, Validators } from '@angular/forms';
  import { Store, select } from '@ngrx/store';
  import { Observable } from 'rxjs';

//REDUCERS
  import { AppState } from '../../../../store/app.reducers';
  import * as FromAuthStore from '../../../../store/auth/index';
  import * as FromUIStore from '../../../../store/ui/index';

declare const $: any;

@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.css']
})
export class LoginComponent implements OnInit {
  loggedIn$: Observable<boolean>;
  loginForm: FormGroup;
  wallpaperUrl: string;
  
  constructor(private formBuilder: FormBuilder, private store: Store<AppState>, private router: Router) {
    this.loggedIn$ = this.store.pipe(select(FromAuthStore.getLoggedIn));
  }

  ngOnInit() {
    this.loggedIn$.subscribe(res => {
      if(res){
        this.router.navigate(['/']);
      }
    });

    $('body').addClass('authentication sidebar-collapse');
    
    $(".navbar-toggler").on('click',function() {
        $("html").toggleClass("nav-open");
    });

    $('.form-control').on("focus", function() {
        $(this).parent('.input-group').addClass("input-group-focus");

    }).on("blur", function() {
        $(this).parent(".input-group").removeClass("input-group-focus");
    });

    this.wallpaperUrl = 'http://vunature.com/wp-content/uploads/2016/11/lakes-nevada-big-spain-sierra-trees-canyon-lake-snow-mountains-pine-photo-nature-hd-1920x1080.jpg';
    
    this.loginForm = this.formBuilder.group({
      email: [
        '',
        Validators.required
      ],
      password: [
        '',
        [
          Validators.required,
          Validators.minLength(4)
        ]
      ]
    });

    this.store.dispatch(new FromUIStore.actions.DisableLoading());
  }

  login() {
    if(this.loginForm.valid) {
      this.store.dispatch(new FromUIStore.actions.EnableLoading());
      this.store.dispatch(new FromAuthStore.actions.Login(this.loginForm.value));
    }
  }

  get email() {
    return this.loginForm.get('email');
  }

  get password() {
    return this.loginForm.get('password');
  }
}
