<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
class Article extends Model { protected $fillable=['user_id','title','slug','excerpt','content','status','published_at']; protected $casts=['published_at'=>'datetime']; protected static function booted(){ static::creating(function($m){$m->slug=$m->slug?:Str::slug($m->title).'-'.Str::lower(Str::random(5));}); } public function author(){return $this->belongsTo(User::class,'user_id');} }
