import { ComponentFixture, TestBed } from '@angular/core/testing';

import { DictDashboardComponent } from './dict-dashboard.component';

describe('DictDashboardComponent', () => {
  let component: DictDashboardComponent;
  let fixture: ComponentFixture<DictDashboardComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [DictDashboardComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(DictDashboardComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
