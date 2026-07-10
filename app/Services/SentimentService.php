<?php
namespace App\Services;
use App\Models\NegativeWord;use App\Models\PositiveWord;
class SentimentService
{
 public function analyze(string $text): array
 {
  $clean=strtolower(preg_replace('/[^a-zA-ZÀ-ÿ0-9\s]/u',' ',$text));$words=array_values(array_filter(preg_split('/\s+/u',$clean)));$positive=PositiveWord::pluck('word')->map(fn($w)=>strtolower($w))->all();$negative=NegativeWord::pluck('word')->map(fn($w)=>strtolower($w))->all();$matchedP=array_values(array_intersect($words,$positive));$matchedN=array_values(array_intersect($words,$negative));$p=count($matchedP);$n=count($matchedN);$sentiment=$p>$n?'Positif':($n>$p?'Negatif':'Netral');return ['sentiment'=>$sentiment,'positive'=>$p,'negative'=>$n,'matched_positive'=>array_values(array_unique($matchedP)),'matched_negative'=>array_values(array_unique($matchedN))];
 }
}
