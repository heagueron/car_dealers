//ANGULAR
  import { Component, Input, OnInit } from '@angular/core';
  import { Store } from '@ngrx/store';

//EXTERNAL
  import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';

//REDUCERS
  import { AppState } from '../../../../../../store/app.reducers';

@Component({
  selector: 'app-modal-avance',
  templateUrl: './modal-avance.component.html',
  styleUrls: ['./modal-avance.component.css']
})
export class ModalAvanceComponent implements OnInit {
  @Input() operationId: number;
  
  constructor(
    private store: Store<AppState>,
    public activeModal: NgbActiveModal,
  ) {
    //
  }

  ngOnInit() {
    //
  }
}
