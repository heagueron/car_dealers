//ANGULAR
  import { Component, Input, OnInit } from '@angular/core';

@Component({
  selector: 'app-way-to-pay',
  templateUrl: './way-to-pay.component.html',
  styleUrls: ['./way-to-pay.component.css']
})
export class WayToPayComponent implements OnInit {
  @Input() id: number;

  constructor() { 
    //
  }

  ngOnInit() {
    //
  }
}
