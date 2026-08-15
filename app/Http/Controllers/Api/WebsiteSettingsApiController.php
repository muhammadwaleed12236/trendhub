<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class WebsiteSettingsApiController extends Controller
{
    public function index()
    {
        $settings = Setting::where('group', 'website')->get();

        // Optional: map to return a key-value object if easier for frontend, 
        // but returning models matches the prompt exactly.
        return response()->json([
            'status' => 'success',
            'data' => $settings
        ]);
    }
}
