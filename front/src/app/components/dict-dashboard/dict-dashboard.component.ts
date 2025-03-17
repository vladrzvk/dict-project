import { Component, OnInit, OnDestroy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { DictDashboardService } from '../../services/dict-dashboard.service';
import { interval, Subscription } from 'rxjs';
import { switchMap } from 'rxjs/operators';
import { 
  HealthStatus, 
  IntegrityStatus, 
  ConfidentialityStatus, 
  ActivityStatus, 
  StatsStatus,
  DictDashboard
} from '../../models/dict.model';

@Component({
  selector: 'app-dict-dashboard',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './dict-dashboard.component.html',
  styleUrls: ['./dict-dashboard.component.css']
})
export class DictDashboardComponent implements OnInit, OnDestroy {
  healthData: HealthStatus = { status: '', timestamp: '' };
  integrityData: IntegrityStatus = { status: '', timestamp: '', checks_performed: false, issues: [] };
  confidentialityData: ConfidentialityStatus = { timestamp: '', headers_applied: {}, environment: '', tls_enabled: false };
  activityData: ActivityStatus = { timestamp: '', period: '', request_count: 0, error_count: 0, average_response_time_ms: 0 };
  statsData: StatsStatus = { request_count: 0, average_response_time: 0, error_count: 0, system_health: '' };
  
  loading = true;
  error = '';
  
  private refreshSubscription?: Subscription;
  
  constructor(private dictService: DictDashboardService) { }
  
  ngOnInit(): void {
    // Charger les données initiales
    this.loadAllData();
    
    // Rafraîchir les données toutes les 30 secondes
    this.refreshSubscription = interval(30000)
      .pipe(
        switchMap(() => {
          this.loading = true;
          return this.dictService.getDashboard();
        })
      )
      .subscribe({
        next: (data: DictDashboard) => {
          this.updateDashboardData(data);
          this.loading = false;
        },
        error: (err) => {
          this.error = 'Erreur lors du rafraîchissement des données';
          this.loading = false;
          console.error(err);
        }
      });
  }
  
  ngOnDestroy(): void {
    if (this.refreshSubscription) {
      this.refreshSubscription.unsubscribe();
    }
  }
  
  loadAllData(): void {
    this.loading = true;
    this.dictService.getDashboard().subscribe({
      next: (data: DictDashboard) => {
        this.updateDashboardData(data);
        this.loading = false;
      },
      error: (err) => {
        this.error = 'Erreur lors du chargement des données';
        this.loading = false;
        console.error(err);
      }
    });
  }
  
  updateDashboardData(data: Partial<DictDashboard>): void {
    if (data.health) this.healthData = data.health;
    if (data.integrity) this.integrityData = data.integrity;
    if (data.confidentiality) this.confidentialityData = data.confidentiality;
    if (data.activity) this.activityData = data.activity;
    if (data.stats) this.statsData = data.stats;
  }
  
  getStatusClass(status: string | undefined): string {
    if (!status) return 'bg-gray-100 text-gray-800';
    
    switch (status.toLowerCase()) {
      case 'healthy':
      case 'up':
        return 'bg-green-100 text-green-800';
      case 'degraded':
      case 'warning':
        return 'bg-yellow-100 text-yellow-800';
      case 'down':
      case 'error':
        return 'bg-red-100 text-red-800';
      default:
        return 'bg-gray-100 text-gray-800';
    }
  }
  
  refreshData(): void {
    this.loadAllData();
  }
}