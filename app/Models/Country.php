<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Country extends Model
{
 protected $fillable=['name','official_name','code','cca3','region','subregion','capital','currency_code','currency_name','language','flag_url','latitude','longitude','population'];
 protected $casts=['latitude'=>'float','longitude'=>'float','population'=>'integer'];
 public function economics(){ return $this->hasMany(CountryEconomic::class); }
 public function latestEconomic(){ return $this->hasOne(CountryEconomic::class)->latestOfMany('year'); }
 public function risks(){ return $this->hasMany(RiskScore::class); }
 public function latestRisk(){ return $this->hasOne(RiskScore::class)->latestOfMany(); }
 public function ports(){ return $this->hasMany(Port::class); }
 public function weatherHistory(){ return $this->hasMany(WeatherData::class); }
 public function latestWeather(){ return $this->hasOne(WeatherData::class)->latestOfMany('observed_at'); }
}
