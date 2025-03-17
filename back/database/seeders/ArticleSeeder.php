<?php
// database/seeders/ArticleSeeder.php

namespace Database\Seeders;

use App\Models\Article;
use Illuminate\Database\Seeder;

class ArticleSeeder extends Seeder
{
    /**
     * Seed the articles table with test data.
     */
    public function run(): void
    {
        // Supprimer les données existantes
        Article::truncate();
        
        // Créer des articles de test
        $articles = [
            [
                'title' => 'Introduction au DICT',
                'content' => 'Le DICT (Disponibilité, Intégrité, Confidentialité, Traçabilité) est un framework de sécurité...',
                'author' => 'Admin DICT',
                'published' => true
            ],
            [
                'title' => 'Comprendre la disponibilité dans une application web',
                'content' => 'La disponibilité est l\'un des piliers du DICT. Elle garantit que les services sont accessibles...',
                'author' => 'Expert Système',
                'published' => true
            ],
            [
                'title' => 'Les mécanismes d\'intégrité des données',
                'content' => 'L\'intégrité des données est essentielle pour assurer que les informations ne sont pas altérées...',
                'author' => 'Data Engineer',
                'published' => true
            ],
            [
                'title' => 'Confidentialité et sécurité des données',
                'content' => 'La confidentialité est un aspect critique pour protéger les informations sensibles...',
                'author' => 'Security Officer',
                'published' => true
            ],
            [
                'title' => 'Traçabilité : Suivre les actions dans votre application',
                'content' => 'La traçabilité permet de conserver un historique des actions et événements...',
                'author' => 'Auditeur DICT',
                'published' => true
            ],
            [
                'title' => 'Article non publié',
                'content' => 'Cet article est en cours de rédaction et n\'est pas encore publié.',
                'author' => 'Rédacteur',
                'published' => false
            ],
        ];
        
        // Insérer les articles dans la base de données
        foreach ($articles as $article) {
            Article::create($article);
        }
    }
}