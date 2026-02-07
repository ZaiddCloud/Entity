<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Enums\EntityType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\JsonResponse;

class PresenceController extends Controller
{
    /**
     * تحديث حالة التواجد (Heartbeat) للمستند الحالي.
     * 
     * @param Request $request
     * @param string $type
     * @param string $slug
     * @return JsonResponse
     */
    public function heartbeat(Request $request, string $type, string $slug): JsonResponse
    {
        $entityType = EntityType::tryFrom($type);
        if (!$entityType) {
            return response()->json(['error' => 'Invalid entity type'], 404);
        }

        // استخدام الـ Slug للعثور على الكيان (للتأكد من وجوده)
        $modelClass = $entityType->modelClass();
        $entityId = $modelClass::where('slug', $slug)->value('id');

        if (!$entityId) {
            return response()->json(['error' => 'Entity not found'], 404);
        }

        $user = $request->user();
        $cacheKey = "presence:{$type}:{$entityId}";
        $ttl = 30; // مدة بقاء المستخدم "نشطاً" بالثواني

        // 1. استرجاع القائمة الحالية من الكاش
        $presenceList = Cache::get($cacheKey, []);

        // 2. تحديث بيانات المستخدم الحالي
        $presenceList[$user->id] = [
            'id' => $user->id,
            'name' => $user->name,
            'avatar' => $user->profile_photo_path,
            'last_seen' => now()->timestamp,
        ];

        // 3. تنظيف المستخدمين الذين انتهت صلاحيتهم (Idle)
        $now = now()->timestamp;
        $presenceList = array_filter($presenceList, function ($entry) use ($now, $ttl) {
            return ($now - $entry['last_seen']) < $ttl;
        });

        // 4. حفظ القائمة المحدثة في الكاش
        Cache::put($cacheKey, $presenceList, $ttl + 10);

        // 5. Soft Locking: تتبع القسم النشط (إذا تم تحديده)
        $sectionId = $request->input('section_id');
        if ($sectionId) {
            $sectionKey = "{$cacheKey}:section:{$sectionId}";
            Cache::put($sectionKey, [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'locked_at' => now()->toIso8601String()
            ], $ttl);
        }

        // 6. إرجاع القائمة النهائية
        return response()->json([
            'users' => array_values($presenceList),
            'count' => count($presenceList)
        ]);
    }

    /**
     * الحصول على معلومات القفل لقسم معين (Soft Locking)
     * 
     * @param Request $request
     * @param string $type
     * @param string $slug
     * @return JsonResponse
     */
    public function getSectionLock(Request $request, string $type, string $slug): JsonResponse
    {
        $validated = $request->validate([
            'section_id' => 'required|string',
        ]);

        $entityType = EntityType::tryFrom($type);
        if (!$entityType) {
            return response()->json(['error' => 'Invalid entity type'], 404);
        }

        $modelClass = $entityType->modelClass();
        $entityId = $modelClass::where('slug', $slug)->value('id');

        if (!$entityId) {
            return response()->json(['error' => 'Entity not found'], 404);
        }

        $cacheKey = "presence:{$type}:{$entityId}:section:{$validated['section_id']}";
        $lock = Cache::get($cacheKey);

        $currentUserId = $request->user()->id;

        return response()->json([
            'locked' => $lock !== null,
            'locked_by' => $lock,
            'is_current_user' => $lock && $lock['user_id'] === $currentUserId
        ]);
    }
}
