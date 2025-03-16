import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, of } from 'rxjs'; // Ajoutez of ici

export interface Article {
  id?: number;
  title: string;
  content: string;
  author?: string;
  published: boolean;
  created_at?: string;
  updated_at?: string;
}

@Injectable({
  providedIn: 'root'
})
export class ArticleService {
  private apiUrl = 'http://localhost:8000/api/articles';

  // Données mockées
  private mockArticles: Article[] = [
    { 
      id: 1, 
      title: 'Premier Article', 
      content: 'Ceci est le contenu du premier article. Il parle de quelque chose d\'intéressant.', 
      author: 'John Doe', 
      published: true,
      created_at: new Date().toISOString(),
      updated_at: new Date().toISOString()
    },
    { 
      id: 2, 
      title: 'Article en Brouillon', 
      content: 'Ceci est un article qui n\'est pas encore publié.', 
      author: 'Jane Smith', 
      published: false,
      created_at: new Date().toISOString(),
      updated_at: new Date().toISOString()
    },
    { 
      id: 3, 
      title: 'Dernier Article', 
      content: 'Un troisième article pour compléter notre collection.', 
      author: 'Admin', 
      published: true,
      created_at: new Date().toISOString(),
      updated_at: new Date().toISOString()
    }
  ];

  constructor(private http: HttpClient) { } // Un seul constructeur

  // Méthodes mockées
  getArticles(): Observable<Article[]> {
    return of(this.mockArticles);
  }

  getArticle(id: number): Observable<Article> {
    const article = this.mockArticles.find(a => a.id === id);
    return of(article!);
  }

  createArticle(article: Article): Observable<Article> {
    const newArticle = {
      ...article,
      id: this.mockArticles.length + 1,
      created_at: new Date().toISOString(),
      updated_at: new Date().toISOString()
    };
    this.mockArticles.push(newArticle);
    return of(newArticle);
  }

  updateArticle(id: number, article: Article): Observable<Article> {
    const index = this.mockArticles.findIndex(a => a.id === id);
    if (index !== -1) {
      this.mockArticles[index] = {
        ...article,
        id,
        updated_at: new Date().toISOString()
      };
    }
    return of(this.mockArticles[index]);
  }

  deleteArticle(id: number): Observable<any> {
    const initialLength = this.mockArticles.length;
    this.mockArticles = this.mockArticles.filter(a => a.id !== id);
    
    // Retourne un objet indiquant si la suppression a réussi
    return of({
      success: this.mockArticles.length < initialLength,
      message: this.mockArticles.length < initialLength 
        ? 'Article supprimé avec succès' 
        : 'Article non trouvé'
    });
  }

  // Méthode pour réinitialiser les données mockées (utile pour les tests)
  resetMockData(): void {
    this.mockArticles = [
      { 
        id: 1, 
        title: 'Premier Article', 
        content: 'Ceci est le contenu du premier article. Il parle de quelque chose d\'intéressant.', 
        author: 'John Doe', 
        published: true,
        created_at: new Date().toISOString(),
        updated_at: new Date().toISOString()
      },
      { 
        id: 2, 
        title: 'Article en Brouillon', 
        content: 'Ceci est un article qui n\'est pas encore publié.', 
        author: 'Jane Smith', 
        published: false,
        created_at: new Date().toISOString(),
        updated_at: new Date().toISOString()
      },
      { 
        id: 3, 
        title: 'Dernier Article', 
        content: 'Un troisième article pour compléter notre collection.', 
        author: 'Admin', 
        published: true,
        created_at: new Date().toISOString(),
        updated_at: new Date().toISOString()
      }
    ];
  }

  // Méthodes commentées pour appels HTTP réels (à décommenter plus tard)
  // getArticles(): Observable<Article[]> {
  //   return this.http.get<Article[]>(this.apiUrl);
  // }

  // getArticle(id: number): Observable<Article> {
  //   return this.http.get<Article>(`${this.apiUrl}/${id}`);
  // }

  // createArticle(article: Article): Observable<Article> {
  //   return this.http.post<Article>(this.apiUrl, article);
  // }

  // updateArticle(id: number, article: Article): Observable<Article> {
  //   return this.http.put<Article>(`${this.apiUrl}/${id}`, article);
  // }

  // deleteArticle(id: number): Observable<any> {
  //   return this.http.delete(`${this.apiUrl}/${id}`);
  // }
}