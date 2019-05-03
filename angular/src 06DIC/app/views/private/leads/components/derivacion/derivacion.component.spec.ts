import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { DerivacionComponent } from './derivacion.component';

describe('DerivacionComponent', () => {
  let component: DerivacionComponent;
  let fixture: ComponentFixture<DerivacionComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ DerivacionComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(DerivacionComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
