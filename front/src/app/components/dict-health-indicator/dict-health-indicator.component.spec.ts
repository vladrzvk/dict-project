import { ComponentFixture, TestBed } from '@angular/core/testing';

import { DictHealthIndicatorComponent } from './dict-health-indicator.component';

describe('DictHealthIndicatorComponent', () => {
  let component: DictHealthIndicatorComponent;
  let fixture: ComponentFixture<DictHealthIndicatorComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [DictHealthIndicatorComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(DictHealthIndicatorComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
