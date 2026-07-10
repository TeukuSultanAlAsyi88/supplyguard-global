<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{NegativeWord, PositiveWord};

class SentimentApiController extends Controller
{
    public function words()
    {
        return response()->json(['success' => true, 'data' => [
            'positive' => PositiveWord::orderBy('word')->pluck('word'),
            'negative' => NegativeWord::orderBy('word')->pluck('word'),
        ]]);
    }
}
