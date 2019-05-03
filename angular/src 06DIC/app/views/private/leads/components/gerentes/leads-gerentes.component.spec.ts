import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { LeadsGerentesComponent } from './leads-gerentes.component';

describe('LeadsGerentesComponent', () => {
  let component: LeadsGerentesComponent;
  let fixture: ComponentFixture<LeadsGerentesComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ LeadsGerentesComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(LeadsGerentesComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
