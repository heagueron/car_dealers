import { Component, OnInit, Input } from '@angular/core';

@Component({
  selector: 'app-default-task',
  templateUrl: './default-task.component.html',
  styleUrls: ['./default-task.component.css']
})
export class DefaultTaskComponent implements OnInit {

  @Input() title: string;
  constructor() { }

  ngOnInit() {
  }

}
