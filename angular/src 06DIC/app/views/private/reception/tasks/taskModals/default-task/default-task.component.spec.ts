import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { DefaultTaskComponent } from './default-task.component';

describe('DefaultTaskComponent', () => {
  let component: DefaultTaskComponent;
  let fixture: ComponentFixture<DefaultTaskComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ DefaultTaskComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(DefaultTaskComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
