import { TestBed } from '@angular/core/testing';

import { DictHttpInterceptor } from './dict-http.service';

describe('DictHttpService', () => {
  let service: DictHttpInterceptor;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(DictHttpInterceptor);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
