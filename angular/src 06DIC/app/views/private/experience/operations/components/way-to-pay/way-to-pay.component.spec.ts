import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { WayToPayComponent } from './way-to-pay.component';

describe('WayToPayComponent', () => {
  let component: WayToPayComponent;
  let fixture: ComponentFixture<WayToPayComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ WayToPayComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(WayToPayComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
