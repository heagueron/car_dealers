import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { SidebarRightSellerComponent } from './sidebar-right-seller.component';

describe('SidebarRightSellerComponent', () => {
  let component: SidebarRightSellerComponent;
  let fixture: ComponentFixture<SidebarRightSellerComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ SidebarRightSellerComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(SidebarRightSellerComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
