<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ArticleController extends Controller
{
    public function index()
    {
        $articles = Article::all();
        return response()->json($articles);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'author' => 'nullable|string|max:100',
            'published' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $article = Article::create($request->all());
        return response()->json($article, 201);
    }

    public function show($id)
    {
        $article = Article::find($id);
        
        if (!$article) {
            return response()->json(['message' => 'Article not found'], 404);
        }
        
        return response()->json($article);
    }

    public function update(Request $request, $id)
    {
        $article = Article::find($id);
        
        if (!$article) {
            return response()->json(['message' => 'Article not found'], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'author' => 'nullable|string|max:100',
            'published' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        
        $article->update($request->all());
        return response()->json($article);
    }

    public function destroy($id)
    {
        $article = Article::find($id);
        
        if (!$article) {
            return response()->json(['message' => 'Article not found'], 404);
        }
        
        $article->delete();
        return response()->json(['message' => 'Article deleted successfully']);
    }
}