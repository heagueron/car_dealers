import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { StageSellerComponent } from './stage-seller.component';

describe('StageSellerComponent', () => {
  let component: StageSellerComponent;
  let fixture: ComponentFixture<StageSellerComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ StageSellerComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(StageSellerComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
