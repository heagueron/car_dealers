import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { LeadsVendedoresComponent } from './leads-vendedores.component';

describe('LeadsVendedoresComponent', () => {
  let component: LeadsVendedoresComponent;
  let fixture: ComponentFixture<LeadsVendedoresComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ LeadsVendedoresComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(LeadsVendedoresComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
