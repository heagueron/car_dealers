//ANGULAR
  import { Component, Input, OnInit } from '@angular/core';
  import { Store } from '@ngrx/store';

//EXTERNAL
  import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
  import { NgSelectConfig } from '@ng-select/ng-select';

//REDUCERS
  import { AppState } from '../../../../../../store/app.reducers';
  
@Component({
  selector: 'app-modal-task',
  templateUrl: './modal-task.component.html',
  styleUrls: ['./modal-task.component.css']
})
export class ModalTaskComponent implements OnInit {
  @Input() operationId: number;
  selectOptionsTask: Array<any>;
  selectOptionsReason: Array<any>;
  selectOptionsResult: Array<any>;

  constructor(
    private store: Store<AppState>,
    public activeModal: NgbActiveModal,
    private ngSelectConfig: NgSelectConfig
  ) {
    this.ngSelectConfig.notFoundText = 'Custom not found';
    
    this.selectOptionsTask = [];
    this.selectOptionsReason = [];
    this.selectOptionsResult = [];
  }

  ngOnInit() {
    this.selectOptionsTask.push(
      {id: 1, name: 'Llamada Telefonica'}, 
      {id: 2, name: 'Mensaje de Texto'},
      {id: 3, name: 'Mensaje de Whatsapp'},
      {id: 4, name: 'Correo electronico'},
      {id: 5, name: 'Vendedor visita al cliente'},
      {id: 6, name: 'Teleconferencia'}
    );

    this.selectOptionsReason.push(
      {id: 1, name: 'Presentar la empresa'}, 
      {id: 2, name: 'Seguimiento'},
      {id: 3, name: 'Campaña'},
      {id: 4, name: 'Firmar boleta'},
      {id: 5, name: 'Negociar cierre'}
    );

    this.selectOptionsResult.push(
      {id: 1, name: 'Ocupado'}, 
      {id: 2, name: 'Telefono erroneo'},
      {id: 3, name: 'Deje mens. de voz'},
      {id: 4, name: 'Deje mens. Whatsapp'},
      {id: 5, name: 'Deje SMS'},
      {id: 6, name: 'Envie Email'}
    );
  }
}
