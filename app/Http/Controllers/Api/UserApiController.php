<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class UserApiController extends Controller
{
    public function index(Request $request)
    {
        $role    = $request->string('role')->toString();     // tutor | student | admin（可空=全部）
        $search  = $request->string('search')->toString();   // 按 name/email 模糊
        $perPage = (int) $request->input('per_page', 10);
        $perPage = max(1, min($perPage, 100));               // 限制每页 1..100

        $query = User::query()
            ->select(['id','name','email','role','created_at'])
            ->when($role, fn($q) => $q->where('role', $role))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($w) use ($search) {
                    $w->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('id', 'asc');

        return UserResource::collection(
            $query->paginate($perPage)->appends($request->query())
        );
    }

    
    // 新增：详情
    public function show($id)
    {
        $user = User::with('subjects:subject_id,subject_Name')
            ->select(['id','name','email','role','created_at'])
            ->findOrFail($id);

        return new UserResource($user);
    }
}
