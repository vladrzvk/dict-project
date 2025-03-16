import { TestBed } from '@angular/core/testing';

import { DictMonitoringService } from './dict-monitoring.service';

describe('DictMonitoringService', () => {
  let service: DictMonitoringService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(DictMonitoringService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
