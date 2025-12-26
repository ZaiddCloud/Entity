<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\Series;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SeriesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $series = Series::withCount(['books', 'videos', 'audio', 'manuscripts'])->get();

        return response()->json($series);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order_column' => 'integer'
        ]);

        $series = Series::create($request->only(['title', 'description', 'order_column']));

        return response()->json([
            'message' => 'تم إنشاء السلسلة بنجاح',
            'data' => $series
        ], 210);
    }

    /**
     * Display the specified resource.
     */
    public function show(Series $series): JsonResponse
    {
        return response()->json($series->load(['books', 'videos', 'audio', 'manuscripts']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Series $series): JsonResponse
    {
        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'order_column' => 'integer'
        ]);

        $series->update($request->only(['title', 'description', 'order_column']));

        return response()->json([
            'message' => 'تم تحديث السلسلة بنجاح',
            'data' => $series
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Series $series): JsonResponse
    {
        $series->delete();

        return response()->json([
            'message' => 'تم حذف السلسلة بنجاح'
        ]);
    }
}
