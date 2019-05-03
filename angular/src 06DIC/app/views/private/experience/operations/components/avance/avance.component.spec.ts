import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AvanceComponent } from './avance.component';

describe('AvanceComponent', () => {
  let component: AvanceComponent;
  let fixture: ComponentFixture<AvanceComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AvanceComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AvanceComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
