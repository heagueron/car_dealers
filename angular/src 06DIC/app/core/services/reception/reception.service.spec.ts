import { TestBed } from '@angular/core/testing';

import { ReceptionService } from './reception.service';

describe('ReceptionService', () => {
  beforeEach(() => TestBed.configureTestingModule({}));

  it('should be created', () => {
    const service: ReceptionService = TestBed.get(ReceptionService);
    expect(service).toBeTruthy();
  });
});
