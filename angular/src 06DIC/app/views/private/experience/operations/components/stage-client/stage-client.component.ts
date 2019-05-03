//ANGULAR
  import { Component, Input, OnInit } from '@angular/core';
  import { Store } from '@ngrx/store';

//EXTERNAL
  import { NgbModal } from '@ng-bootstrap/ng-bootstrap';

//REDUCERS
  import { AppState } from '../../../../../../store/app.reducers';
  import * as FromUIStore from '../../../../../../store/ui/index';

//COMPONENTS
  import { ModalCategoryComponent } from '../modal-category/modal-category.component';
  import { ModalPurchaseRequestComponent } from '../modal-purchase-request/modal-purchase-request.component';
  import { ModalLoadIdDMSComponent } from '../modal-load-id-dms/modal-load-id-dms.component';

//MODELS
  import { ClientModel } from '../../../../../../core/models/client.model';

declare const $: any;

@Component({
  selector: 'app-stage-client',
  templateUrl: './stage-client.component.html',
  styleUrls: ['./stage-client.component.css']
})
export class StageClientComponent implements OnInit {
  @Input() id: number;
  @Input() client: ClientModel;

  constructor(
    private store: Store<AppState>, 
    private modalService: NgbModal
  ) {
    //
  }

  openModalCategory(id) {
    const modalRef = this.modalService.open(ModalCategoryComponent, { size: 'lg' });
    modalRef.componentInstance.operationId = id;
  }

  openModalPurchaseRequest(id) {
    const modalRef = this.modalService.open(ModalPurchaseRequestComponent, { size: 'lg' });
    modalRef.componentInstance.operationId = id;
  }

  openModalLoadIdDms(id) {
    const modalRef = this.modalService.open(ModalLoadIdDMSComponent);
    modalRef.componentInstance.operationId = id;
  }

  ngOnInit() {
    //
  }

  openSidebar() {
    this.store.dispatch(new FromUIStore.actions.EnableSidebarRight());
  }
}
