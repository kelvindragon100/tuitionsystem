<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SubjectResource;
use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectApiController extends Controller
{
    /**
     * 列表：支持 q 搜索 subject_id / subject_Name；分页 1..100
     *
     * @OA\Get(
     *   path="/api/v1/subjects",
     *   tags={"Subjects"},
     *   summary="List subjects",
     *   @OA\Parameter(name="q", in="query", description="按 subject_id / subject_Name 模糊搜索", @OA\Schema(type="string")),
     *   @OA\Parameter(name="per_page", in="query", description="分页大小(1..100)，默认10", @OA\Schema(type="integer", default=10, minimum=1, maximum=100)),
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function index(Request $request)
    {
        $q       = $request->string('q')->toString();
        $perPage = (int) $request->input('per_page', 10);
        $perPage = max(1, min($perPage, 100));

        $query = Subject::query()
            ->select(['subject_id','subject_Name','subject_Description','duration_Hours','subject_Fee','created_at'])
            ->when($q, function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('subject_id', 'like', "%{$q}%")
                      ->orWhere('subject_Name', 'like', "%{$q}%");
                });
            })
            ->orderBy('subject_id', 'asc');

        return SubjectResource::collection(
            $query->paginate($perPage)->appends($request->query())
        );
    }

    /**
     * 详情：隐式绑定基于 subject_id（如 SU0001）
     *
     * @OA\Get(
     *   path="/api/v1/subjects/{id}",
     *   tags={"Subjects"},
     *   summary="Get subject detail",
     *   description="使用 subject_id（如 SU0001）作为路径参数；若你的 Server.url 带 /api，这里的 path 改为 /v1/subjects/{id}",
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *   @OA\Response(response=200, description="OK"),
     *   @OA\Response(response=404, description="Not Found")
     * )
     */
    public function show(Subject $subject)
    {
        // $subject 已由隐式绑定注入
        return new SubjectResource($subject);
    }
}
