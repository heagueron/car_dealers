import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { DerivationSelectionComponent } from './derivation-selection.component';

describe('DerivationSelectionComponent', () => {
  let component: DerivationSelectionComponent;
  let fixture: ComponentFixture<DerivationSelectionComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ DerivationSelectionComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(DerivationSelectionComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
