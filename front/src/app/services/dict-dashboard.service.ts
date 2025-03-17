import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { catchError, tap } from 'rxjs/operators';

@Injectable({
  providedIn: 'root'
})
export class DictDashboardService {
  private apiUrl = 'http://localhost:8000/api/dict';
  
  constructor(private http: HttpClient) { }
  
  getDashboard(): Observable<any> {
    return this.http.get(`${this.apiUrl}/dashboard`).pipe(
      catchError(this.handleError('getDashboard', {}))
    );
  }
  
  getHealth(): Observable<any> {
    return this.http.get(`${this.apiUrl}/health`).pipe(
      catchError(this.handleError('getHealth', {}))
    );
  }
  
  getIntegrity(): Observable<any> {
    return this.http.get(`${this.apiUrl}/integrity`).pipe(
      catchError(this.handleError('getIntegrity', {}))
    );
  }
  
  getConfidentiality(): Observable<any> {
    return this.http.get(`${this.apiUrl}/confidentiality`).pipe(
      catchError(this.handleError('getConfidentiality', {}))
    );
  }
  
  getActivity(minutes: number = 60): Observable<any> {
    return this.http.get(`${this.apiUrl}/activity?minutes=${minutes}`).pipe(
      catchError(this.handleError('getActivity', {}))
    );
  }
  
  getStats(): Observable<any> {
    return this.http.get(`${this.apiUrl}/stats`).pipe(
      catchError(this.handleError('getStats', {}))
    );
  }
  
  private handleError<T>(operation = 'operation', result?: T) {
    return (error: any): Observable<T> => {
      console.error(`${operation} failed: ${error.message}`);
      console.error(error);
      return new Observable(subscriber => subscriber.next(result as T));
    };
  }
}