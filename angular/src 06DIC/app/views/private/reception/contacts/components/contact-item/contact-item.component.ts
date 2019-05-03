// ANGULAR
import { Component, OnInit, Input } from '@angular/core';
import { FormControl } from '@angular/forms';
import { Validators } from '@angular/forms';
import { ViewChild } from '@angular/core';
import { ElementRef } from '@angular/core';

// NGRX
import { Store } from '@ngrx/store';
import { ContactModel } from 'src/app/core/models/contact.model';

@Component({
  selector: 'app-contact-item',
  templateUrl: './contact-item.component.html',
  styleUrls: ['./contact-item.component.css']
})
export class ContactItemComponent implements OnInit {

  @Input() contact: ContactModel;

  constructor() { }

  ngOnInit() {
  }

}
