<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class NewsSentiment extends Model { protected $fillable=['news_cache_id','sentiment','positive_count','negative_count','neutral_count','matched_positive','matched_negative']; protected $casts=['matched_positive'=>'array','matched_negative'=>'array']; public function news(){return $this->belongsTo(NewsCache::class,'news_cache_id');} }
