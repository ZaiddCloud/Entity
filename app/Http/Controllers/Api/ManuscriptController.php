<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\Manuscript;
use App\Services\EntityManagerService;
use App\Services\EntityQueryService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ManuscriptController extends Controller
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
        $manuscripts = Manuscript::paginate($perPage);

        return response()->json($manuscripts);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->all();
        $data['type'] = 'manuscript';

        $manuscript = $this->manager->create($data);

        return response()->json([
            'message' => 'تم إنشاء المخطوطة بنجاح',
            'data' => $manuscript
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Manuscript $manuscript): JsonResponse
    {
        return response()->json($manuscript->load(['tags', 'categories', 'comments']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Manuscript $manuscript): JsonResponse
    {
        $this->manager->update($manuscript, $request->all());

        return response()->json([
            'message' => 'تم تحديث المخطوطة بنجاح',
            'data' => $manuscript
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Manuscript $manuscript): JsonResponse
    {
        $this->manager->delete($manuscript);

        return response()->json([
            'message' => 'تم حذف المخطوطة بنجاح'
        ]);
    }
}
