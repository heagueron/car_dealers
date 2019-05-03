import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ModalAvanceComponent } from './modal-avance.component';

describe('ModalAvanceComponent', () => {
  let component: ModalAvanceComponent;
  let fixture: ComponentFixture<ModalAvanceComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ModalAvanceComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ModalAvanceComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
