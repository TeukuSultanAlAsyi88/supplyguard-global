<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;

class ArticleApiController extends Controller
{
    public function index()
    {
        return response()->json(['success' => true, 'data' => Article::with('author')->where('status', 'published')->latest('published_at')->paginate(20)]);
    }

    public function show(Article $article)
    {
        abort_unless($article->status === 'published', 404);
        return response()->json(['success' => true, 'data' => $article->load('author')]);
    }
}
