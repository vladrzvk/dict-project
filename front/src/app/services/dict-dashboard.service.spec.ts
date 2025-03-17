import { TestBed } from '@angular/core/testing';

import { DictDashboardService } from './dict-dashboard.service';

describe('DictDashboardService', () => {
  let service: DictDashboardService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(DictDashboardService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
