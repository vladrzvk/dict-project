import { Component, OnInit } from '@angular/core';
import { DictMonitoringService, HealthStatus } from '../../services/dict-monitoring.service';
import { Observable } from 'rxjs';

@Component({
  selector: 'app-dict-health-indicator',
  standalone: true,
  templateUrl: './components/dict-health-indicator.component.html',
  styleUrls: ['./components/dict-health-indicator.component.css']
})
export class DictHealthIndicatorComponent implements OnInit {
  healthStatus$!: Observable<HealthStatus>;
  
  constructor(private dictMonitoring: DictMonitoringService) { }
  
  ngOnInit(): void {
    this.healthStatus$ = this.dictMonitoring.getHealthStatus();
  }
}