<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryApiController extends Controller
{
    public function index()
    {
        // Fetch active categories that should be shown on the website
        $categories = Category::where('show_on_website', true)->get()->map(function($category) {
            $category->web_image_url = $category->web_image ? asset($category->web_image) : null;
            return $category;
        });
        
        return response()->json([
            'status' => 'success',
            'data' => $categories
        ]);
    }
}
