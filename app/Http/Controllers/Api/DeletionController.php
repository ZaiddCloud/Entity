<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\Deletion;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DeletionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $deletions = Deletion::with('user')->latest()->paginate(20);

        return response()->json($deletions);
    }

    /**
     * Display the specified resource.
     */
    public function show(Deletion $deletion): JsonResponse
    {
        return response()->json($deletion->load('user'));
    }
}
