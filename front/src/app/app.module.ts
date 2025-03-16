import { BrowserModule } from '@angular/platform-browser';
import { NgModule } from '@angular/core';
// import { HttpClientModule } from '@angular/common/http';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { RouterModule, Routes } from '@angular/router';

import { AppComponent } from './app.component';
import { ArticleListComponent } from './components/article-list/article-list.component';
import { ArticleFormComponent } from './components/article-form/article-form.component';
import { ArticleDetailsComponent } from './components/article-details/article-details.component';
import { DictHealthIndicatorComponent } from './components/dict-health-indicator/dict-health-indicator.component';

import { provideHttpClient, withInterceptors } from '@angular/common/http';

import { HTTP_INTERCEPTORS } from '@angular/common/http';
import { DictHttpInterceptor } from './interceptors/dict-http.service';

const routes: Routes = [
  { path: '', redirectTo: '/articles', pathMatch: 'full' },
  { path: 'articles', component: ArticleListComponent },
  { path: 'articles/new', component: ArticleFormComponent },
  { path: 'articles/:id', component: ArticleDetailsComponent },
  { path: 'articles/:id/edit', component: ArticleFormComponent }
];

@NgModule({
  declarations: [
    AppComponent,
    ArticleListComponent,
    ArticleFormComponent,
    ArticleDetailsComponent,
    DictHealthIndicatorComponent
  ],
  imports: [
    BrowserModule,
    FormsModule,
    ReactiveFormsModule,
    RouterModule.forRoot(routes)
  ],
  providers: [
    { provide: HTTP_INTERCEPTORS, useClass: DictHttpInterceptor, multi: true }
  ],
  bootstrap: [AppComponent]
})
export class AppModule { }