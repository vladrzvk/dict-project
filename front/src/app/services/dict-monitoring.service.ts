import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, of, interval } from 'rxjs';
import { catchError, switchMap, shareReplay } from 'rxjs/operators';

export interface HealthStatus {
  status: string;
  timestamp: string;
}

@Injectable({
  providedIn: 'root'
})
export class DictMonitoringService {
  private apiUrl = 'http://localhost:8000/api/dict/health';
  private healthStatus$: Observable<HealthStatus>;
  
  constructor(private http: HttpClient) {
    // Vérifier périodiquement l'état du système
    this.healthStatus$ = interval(60000).pipe(
      switchMap(() => this.checkHealth()),
      shareReplay(1)
    );
    
    // Première vérification
    this.checkHealth().subscribe();
  }
  
  checkHealth(): Observable<HealthStatus> {
    return this.http.get<HealthStatus>(this.apiUrl).pipe(
      catchError(error => {
        console.error('DICT:Disponibilité - Erreur lors de la vérification de santé', error);
        return of({ status: 'DOWN', timestamp: new Date().toISOString() });
      })
    );
  }
  
  getHealthStatus(): Observable<HealthStatus> {
    return this.healthStatus$;
  }
}