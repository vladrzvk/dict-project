import { Component, OnInit } from '@angular/core';
import { ArticleService, Article } from '../../services/article.service';
import { RouterLink } from '@angular/router';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-article-list',
  standalone: true,
  imports: [CommonModule, RouterLink],
  templateUrl: './article-list.component.html',
  styleUrls: ['./article-list.component.css']
})
export class ArticleListComponent implements OnInit {
  articles: Article[] = [];
  loading = true;
  error = '';

  constructor(private articleService: ArticleService) { }

  ngOnInit(): void {
    this.loadArticles();
  }

  loadArticles(): void {
    this.loading = true;
    this.articleService.getArticles()
    .subscribe({
      next: (data) => {
        this.articles = data;
        this.loading = false;
      },
      error: (error) => {
        this.error = 'Error loading articles';
        this.loading = false;
        console.error('Error loading articles', error);
      }
    });
  }

  deleteArticle(id?: number): void {
    if (!id) {
      console.error('Invalid article ID');
      return;
    }
    
    if (confirm('Are you sure you want to delete this article?')) {
      this.articleService.deleteArticle(id)
        .subscribe({
          next: () => {
            this.articles = this.articles.filter(a => a.id !== id);
          },
          error:(error) => {
            console.error('Error deleting article', error);
          }
    });
    }
  }
}