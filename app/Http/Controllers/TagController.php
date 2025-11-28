<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $tags = Tag::whereHas('todos', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })
            ->orderBy('name')
            ->get();

        return response()->json($tags);
    }
}

