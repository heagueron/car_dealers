//ANGULAR
  import { Component, Input, OnInit } from '@angular/core';
  import { Store } from '@ngrx/store';

//EXTERNAL
  import { NgbModal } from '@ng-bootstrap/ng-bootstrap';

//REDUCERS
  import { AppState } from '../../../../../../store/app.reducers';

//COMPONENTS
  import { ModalAvanceComponent } from '../modal-avance/modal-avance.component';

@Component({
  selector: 'app-avance',
  templateUrl: './avance.component.html',
  styleUrls: ['./avance.component.css']
})
export class AvanceComponent implements OnInit {
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
    const modalRef = this.modalService.open(ModalAvanceComponent, { size: 'lg' });
    modalRef.componentInstance.operationId = id;
  }
}
