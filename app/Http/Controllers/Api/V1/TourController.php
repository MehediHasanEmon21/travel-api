<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\TourListRequest;
use App\Http\Resources\TourResource;
use App\Models\Travel;

class TourController extends Controller
{
    public function index(Travel $travel, TourListRequest $request)
    {   
           
        $tours = $travel
                ->tours()
                ->when($request->priceFrom, function($query) use ($request){
                    $query->where('price', '>=', $request->priceFrom * 100);
                })
                ->when($request->priceTo, function($query) use ($request){
                    $query->where('price', '<=', $request->priceTo * 100);
                })
                ->when($request->dateFrom, function($query) use ($request){
                    $query->where('starting_date', '>=', $request->dateFrom);
                })
                ->when($request->dateTo, function($query) use ($request){
                    $query->where('starting_date', '<=', $request->dateTo);
                })
                ->when($request->sortBy && $request->sortByOrder, function($query) use ($request){
                    $query->orderBy($request->sortBy,$request->sortByOrder);
                })
                ->orderBy('starting_date')
                ->paginate();
        
        return TourResource::collection($tours);
    }
}

// /api/v1/travels/{slug}/tours?priceFrom=123&priceTo=456&dateFrom=2023-06-01&dateTo=2023-07-01