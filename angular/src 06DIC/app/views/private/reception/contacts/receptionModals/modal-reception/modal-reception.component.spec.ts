import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ModalReceptionComponent } from './modal-reception.component';

describe('ModalReceptionComponent', () => {
  let component: ModalReceptionComponent;
  let fixture: ComponentFixture<ModalReceptionComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ModalReceptionComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ModalReceptionComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
