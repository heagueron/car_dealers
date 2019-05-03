import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { LeadsSupervisoresComponent } from './leads-supervisores.component';

describe('LeadsSupervisoresComponent', () => {
  let component: LeadsSupervisoresComponent;
  let fixture: ComponentFixture<LeadsSupervisoresComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ LeadsSupervisoresComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(LeadsSupervisoresComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
