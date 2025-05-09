<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Rules\VideoUrl;

class YoutubeController extends Controller
{
    public function convert(Request $request)
    {
        $request->validate([
            'url' => ['required', 'url', new VideoUrl]
        ]);

    }
} 