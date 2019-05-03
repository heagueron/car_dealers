import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { StageClientComponent } from './stage-client.component';

describe('StageClientComponent', () => {
  let component: StageClientComponent;
  let fixture: ComponentFixture<StageClientComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ StageClientComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(StageClientComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
