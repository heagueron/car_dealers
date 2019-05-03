//ANGULAR
  import { Component, Input, OnInit } from '@angular/core';
  import { Store } from '@ngrx/store';

//EXTERNAL
  import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
  import { NgSelectConfig } from '@ng-select/ng-select';

//REDUCERS
  import { AppState } from '../../../../../../store/app.reducers';

@Component({
  selector: 'app-modal-purchase-request',
  templateUrl: './modal-purchase-request.component.html',
  styleUrls: ['./modal-purchase-request.component.css']
})
export class ModalPurchaseRequestComponent implements OnInit {
  @Input() operationId: number;
  selectOptionsContact: Array<any>;

  constructor(
    private store: Store<AppState>, 
    public activeModal: NgbActiveModal, 
    private ngSelectConfig: NgSelectConfig
  ) {
    this.ngSelectConfig.notFoundText = 'Custom not found';
    
    this.selectOptionsContact = [];
  }

  ngOnInit() {
    this.selectOptionsContact.push(
      {id: 1, name: 'Llamada Telefonica'}, 
      {id: 2, name: 'Mensaje de Texto'},
      {id: 3, name: 'Mensaje de Whatsapp'},
      {id: 4, name: 'Correo electronico'},
      {id: 5, name: 'Vendedor visita al cliente'},
      {id: 6, name: 'Teleconferencia'}
    );
  }
}
