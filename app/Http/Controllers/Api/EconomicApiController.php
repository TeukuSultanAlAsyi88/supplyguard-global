<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;use App\Models\Country;use App\Services\CountryService;
class EconomicApiController extends Controller { public function show(Country $country,CountryService $s){return response()->json(['success'=>true,'data'=>$s->economic($country)]);} }
