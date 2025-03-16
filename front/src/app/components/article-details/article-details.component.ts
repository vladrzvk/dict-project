import { CommonModule } from '@angular/common';
import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, RouterLink } from '@angular/router';
import { ArticleService, Article } from '../../services/article.service';

@Component({
  selector: 'app-article-details',
  standalone: true,
  imports: [CommonModule, RouterLink],
  templateUrl: './article-details.component.html',
  styleUrl: './article-details.component.css'
})
export class ArticleDetailsComponent implements OnInit {
  article: Article | null = null;
  loading = true;
  error = '';

  constructor(
    private route: ActivatedRoute,
    private articleService: ArticleService
  ) {}

  ngOnInit(): void {
    // Récupérer l'ID de l'article depuis l'URL
    const articleId = Number(this.route.snapshot.paramMap.get('id'));

    if (articleId) {
      this.loadArticleDetails(articleId);
    } else {
      this.error = 'ID d\'article invalide';
      this.loading = false;
    }
  }

  loadArticleDetails(id: number): void {
    this.articleService.getArticle(id).subscribe({
      next: (data) => {
        this.article = data;
        this.loading = false;
      },
      error: (err) => {
        this.error = 'Impossible de charger les détails de l\'article';
        this.loading = false;
        console.error('Erreur de chargement', err);
      }
    });
  }
}