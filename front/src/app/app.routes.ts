import { Routes } from '@angular/router';
import { ArticleListComponent } from './components/article-list/article-list.component';
import { ArticleFormComponent } from './components/article-form/article-form.component';
import { ArticleDetailsComponent } from './components/article-details/article-details.component';
import { DictDashboardComponent } from './components/dict-dashboard/dict-dashboard.component';

export const routes: Routes = [
  { path: '', redirectTo: '/articles', pathMatch: 'full' }, // Route par défaut
  { path: 'articles', component: ArticleListComponent }, // Liste des articles
  { path: 'articles/new', component: ArticleFormComponent }, // Formulaire de création
  { path: 'articles/:id', component: ArticleDetailsComponent }, // Détails d'un article
  { path: 'articles/:id/edit', component: ArticleFormComponent }, // Formulaire d'édition
  { path: 'dict/dashboard', component: DictDashboardComponent }
];