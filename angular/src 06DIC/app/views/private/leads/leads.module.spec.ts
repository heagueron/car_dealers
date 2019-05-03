import { LeadsModule } from './leads.module';

describe('LeadsModule', () => {
  let leadsModule: LeadsModule;

  beforeEach(() => {
    leadsModule = new LeadsModule();
  });

  it('should create an instance', () => {
    expect(leadsModule).toBeTruthy();
  });
});
