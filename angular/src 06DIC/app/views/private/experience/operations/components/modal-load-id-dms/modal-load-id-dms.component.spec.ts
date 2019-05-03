import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ModalLoadIdDMSComponent } from './modal-load-id-dms.component';

describe('ModalLoadIdDMSComponent', () => {
  let component: ModalLoadIdDMSComponent;
  let fixture: ComponentFixture<ModalLoadIdDMSComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ModalLoadIdDMSComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ModalLoadIdDMSComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
