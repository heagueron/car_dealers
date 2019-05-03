//ANGULAR
  import { Component, Input, OnInit } from '@angular/core';
  import { Store } from '@ngrx/store';

//EXTERNAL
  import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';

//REDUCERS
  import { AppState } from '../../../../../../store/app.reducers';

@Component({
  selector: 'app-modal-load-id-dms',
  templateUrl: './modal-load-id-dms.component.html',
  styleUrls: ['./modal-load-id-dms.component.css']
})
export class ModalLoadIdDMSComponent implements OnInit {
  @Input() operationId: number;

  constructor(
    private store: Store<AppState>,
    public activeModal: NgbActiveModal
  ) {
    //
  }

  ngOnInit() {
    //
  }
}
