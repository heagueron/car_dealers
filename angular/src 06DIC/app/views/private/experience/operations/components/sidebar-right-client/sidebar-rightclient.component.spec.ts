import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { SidebarRightClientComponent } from './sidebar-right-client.component';

describe('SidebarRightClientComponent', () => {
  let component: SidebarRightClientComponent;
  let fixture: ComponentFixture<SidebarRightClientComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ SidebarRightClientComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(SidebarRightClientComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
