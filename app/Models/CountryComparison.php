<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CountryComparison extends Model { protected $fillable=['user_id','country_a_id','country_b_id','result']; protected $casts=['result'=>'array']; }
