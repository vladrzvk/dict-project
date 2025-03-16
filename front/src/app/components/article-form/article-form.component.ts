import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, FormsModule, ReactiveFormsModule, Validators } from '@angular/forms';
import { ActivatedRoute, Router, RouterModule } from '@angular/router';
import { ArticleService, Article } from '../../services/article.service';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-article-form',
  standalone: true,
  imports: [    
    CommonModule, 
    FormsModule, 
    ReactiveFormsModule,
    RouterModule],
  templateUrl: './article-form.component.html',
  styleUrls: ['./article-form.component.css']
})
export class ArticleFormComponent implements OnInit {
  articleForm: FormGroup;
  isEdit = false;
  articleId!: number;
  loading = false;
  submitted = false;
  error = '';

  constructor(
    private fb: FormBuilder,
    private route: ActivatedRoute,
    private router: Router,
    private articleService: ArticleService
  ) {
    this.articleForm = this.fb.group({
      title: ['', [Validators.required, Validators.maxLength(255)]],
      content: ['', Validators.required],
      author: [''],
      published: [false]
    });
  }

  ngOnInit(): void {
    this.articleId = +this.route.snapshot.params['id'];
    this.isEdit = !!this.articleId;
    
    if (this.isEdit) {
      this.loading = true;
      this.articleService.getArticle(this.articleId)
        .subscribe({
         next: (article) => {
            this.articleForm.patchValue(article);
            this.loading = false;
          },
         error: (error) => {
            this.error = 'Error loading article';
            this.loading = false;
            console.error('Error loading article', error);
          }
        }

        );
    }
  }

  onSubmit(): void {
    this.submitted = true;
    
    if (this.articleForm.invalid) {
      return;
    }
    
    this.loading = true;
    
    const article: Article = this.articleForm.value;
    
    if (this.isEdit) {
      this.articleService.updateArticle(this.articleId, article)
        .subscribe({
          next:() => {
            this.router.navigate(['/articles']);
          },
          error:(error) => {
            this.error = 'Error updating article';
            this.loading = false;
            console.error('Error updating article', error);
          }
        }
        );
    } else {
      this.articleService.createArticle(article)
        .subscribe({
          next:() => {
            this.router.navigate(['/articles']);
          },
          error:(error) => {
            this.error = 'Error creating article';
            this.loading = false;
            console.error('Error creating article', error);
          }
      });
    }
  }
}