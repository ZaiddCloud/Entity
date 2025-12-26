<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\Audio;
use App\Services\EntityManagerService;
use App\Services\EntityQueryService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AudioController extends Controller
{
    protected $manager;
    protected $query;

    public function __construct(EntityManagerService $manager, EntityQueryService $query)
    {
        $this->manager = $manager;
        $this->query = $query;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);
        $audios = Audio::paginate($perPage);

        return response()->json($audios);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->all();
        $data['type'] = 'audio';

        $audio = $this->manager->create($data);

        return response()->json([
            'message' => 'تم إنشاء الملف الصوتي بنجاح',
            'data' => $audio
        ], 210);
    }

    /**
     * Display the specified resource.
     */
    public function show(Audio $audio): JsonResponse
    {
        return response()->json($audio->load(['tags', 'categories', 'comments']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Audio $audio): JsonResponse
    {
        $this->manager->update($audio, $request->all());

        return response()->json([
            'message' => 'تم تحديث الملف الصوتي بنجاح',
            'data' => $audio
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Audio $audio): JsonResponse
    {
        $this->manager->delete($audio);

        return response()->json([
            'message' => 'تم حذف الملف الصوتي بنجاح'
        ]);
    }
}
