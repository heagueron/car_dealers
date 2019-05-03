import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ModalPurchaseRequestComponent } from './modal-purchase-request.component';

describe('ModalPurchaseRequestComponent', () => {
  let component: ModalPurchaseRequestComponent;
  let fixture: ComponentFixture<ModalPurchaseRequestComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ModalPurchaseRequestComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ModalPurchaseRequestComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
