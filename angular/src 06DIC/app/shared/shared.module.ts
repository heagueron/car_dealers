//ANGULAR
  import { NgModule } from '@angular/core';
  import { CommonModule } from '@angular/common';
  import { FormsModule, ReactiveFormsModule } from '@angular/forms';
  import { RouterModule } from '@angular/router';

//EXTERNAL
  import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
  import { SweetAlert2Module } from '@toverux/ngx-sweetalert2';
  import { NgSelectModule } from '@ng-select/ng-select';
  import { FullCalendarModule } from 'ng-fullcalendar';

//COMPONENTS
  import { LoaderComponent } from './components/loader/loader.component';
  import { NavbarHeaderComponent } from './components/navbar-header/navbar-header.component';
  import { OverlayComponent } from './components/overlay/overlay.component';
  import { SidebarLeftComponent } from './components/sidebar-left/sidebar-left.component';
  import { SidebarRightComponent } from './components/sidebar-right/sidebar-right.component';
  import { ChatComponent } from './components/chat/chat.component';

@NgModule({
  imports: [
    CommonModule,
    FormsModule,
    ReactiveFormsModule,
    SweetAlert2Module,
    RouterModule,
    NgbModule,
    NgSelectModule,
    FullCalendarModule
  ],
  exports: [
    ChatComponent,
    FormsModule,
    NavbarHeaderComponent,
    LoaderComponent,
    OverlayComponent,
    ReactiveFormsModule,
    SweetAlert2Module,
    SidebarLeftComponent,
    SidebarRightComponent,
    NgbModule,
    NgSelectModule,
    FullCalendarModule
  ],
  declarations: [
    ChatComponent,
    NavbarHeaderComponent,
    LoaderComponent,
    OverlayComponent,
    SidebarLeftComponent,
    SidebarRightComponent
  ]
})
export class SharedModule { }
