<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\NotificationLog;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    /**
     * Get the notifications as blog posts for the mobile app.
     */
    public function index(Request $request)
    {
        $posts = NotificationLog::where('target', 'all')
            ->latest('created_at')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $posts->map(function ($post) {
                return [
                    'id' => $post->id,
                    'title' => $post->title,
                    'body' => $post->body,
                    'image' => $post->image_url,
                    'created_at' => $post->created_at->format('Y-m-d H:i'),
                ];
            }),
            'meta' => [
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'total' => $posts->total(),
            ]
        ]);
    }
}
