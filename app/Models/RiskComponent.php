<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class RiskComponent extends Model { protected $fillable=['risk_score_id','component','raw_value','normalized_score','weight','weighted_score','notes']; protected $casts=['raw_value'=>'float','normalized_score'=>'float','weight'=>'float','weighted_score'=>'float']; public function riskScore(){return $this->belongsTo(RiskScore::class);} }
