import { Injectable } from '@angular/core';
import {
  HttpRequest,
  HttpHandler,
  HttpEvent,
  HttpInterceptor,
  HttpErrorResponse
} from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { catchError, finalize } from 'rxjs/operators';

@Injectable()
export class DictHttpInterceptor implements HttpInterceptor {
  
  constructor() {}
  
  intercept(request: HttpRequest<unknown>, next: HttpHandler): Observable<HttpEvent<unknown>> {
    // Générer un ID unique pour la requête
    const requestId = this.generateUUID();
    
    // Ajouter des en-têtes de sécurité
    const secureRequest = request.clone({
      setHeaders: {
        'X-Request-ID': requestId,
        'X-Requested-With': 'XMLHttpRequest'
      }
    });
    
    // Disponibilité: Mesurer le temps de réponse
    const startTime = Date.now();
    
    // Journaliser la requête (Traçabilité)
    console.log(`DICT:Traçabilité - Requête ${requestId}: ${request.method} ${request.url}`);
    
    return next.handle(secureRequest).pipe(
      catchError((error: HttpErrorResponse) => {
        // Journaliser les erreurs (Traçabilité)
        console.error(`DICT:Traçabilité - Erreur ${requestId}: ${error.status} ${error.message}`);
        
        // Disponibilité: Gérer les erreurs liées à l'indisponibilité du service
        if (error.status === 0 || error.status === 503) {
          console.error('DICT:Disponibilité - Service indisponible');
          // On pourrait afficher une notification à l'utilisateur ici
        }
        
        return throwError(error);
      }),
      finalize(() => {
        // Disponibilité: Calculer le temps de réponse
        const duration = Date.now() - startTime;
        console.log(`DICT:Traçabilité - Requête ${requestId} terminée en ${duration}ms`);
      })
    );
  }
  
  private generateUUID(): string {
    // Implementation simple d'UUID v4
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (c) => {
      const r = Math.random() * 16 | 0;
      const v = c === 'x' ? r : (r & 0x3 | 0x8);
      return v.toString(16);
    });
  }
}