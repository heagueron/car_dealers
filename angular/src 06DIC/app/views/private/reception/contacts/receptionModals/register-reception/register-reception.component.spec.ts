import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { RegisterReceptionComponent } from './register-reception.component';

describe('RegisterReceptionComponent', () => {
  let component: RegisterReceptionComponent;
  let fixture: ComponentFixture<RegisterReceptionComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ RegisterReceptionComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(RegisterReceptionComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
