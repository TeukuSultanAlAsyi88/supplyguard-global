<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CountryEconomic extends Model { protected $fillable=['country_id','year','gdp','inflation','exports','imports','population']; protected $casts=['gdp'=>'float','inflation'=>'float','exports'=>'float','imports'=>'float','population'=>'integer']; public function country(){return $this->belongsTo(Country::class);} }
