<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class RiskScore extends Model { protected $fillable=['country_id','weather_score','inflation_score','currency_score','news_score','total_score','risk_level','calculated_at']; protected $casts=['weather_score'=>'float','inflation_score'=>'float','currency_score'=>'float','news_score'=>'float','total_score'=>'float','calculated_at'=>'datetime']; public function country(){return $this->belongsTo(Country::class);} public function components(){return $this->hasMany(RiskComponent::class);} }
