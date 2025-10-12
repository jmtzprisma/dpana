<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Controller;
use App\Http\Resources\Api\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = Category::where('status', true)->paginate(30);
        return CategoryResource::collection($categories);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }
    public function topCategory()
    {
        $top_category_ids = getSetting('top_category_ids') != null ? json_decode(getSetting('top_category_ids')) : [];
        $categories = Category::where('status', true)->whereIn('id', $top_category_ids)->paginate(30);

        return CategoryResource::collection($categories);
    }

    public function categoryHome($parent_id)
    {
        $categories = Category::where('status', true)->where('parent_id', $parent_id)->paginate(10);
        return CategoryResource::collection($categories);
    }
}
