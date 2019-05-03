//ANGULAR
  import { Component, Input, OnInit } from '@angular/core';
  import { Store } from '@ngrx/store';

//EXTERNAL
  import { NgbModal } from '@ng-bootstrap/ng-bootstrap';

//REDUCERS
  import { AppState } from '../../../../../../store/app.reducers';

//COMPONENTS
  import { ModalTaskComponent } from '../modal-task/modal-task.component';

@Component({
  selector: 'app-task',
  templateUrl: './task.component.html',
  styleUrls: ['./task.component.css']
})
export class TaskComponent implements OnInit {
  @Input() id: number;

  constructor(
    private store: Store<AppState>,
    private modalService: NgbModal
  ) {
    //
  }

  ngOnInit() {
    //
  }

  openModal(id) {
    const modalRef = this.modalService.open(ModalTaskComponent, { size: 'lg' });
    modalRef.componentInstance.operationId = id;
  }
}
