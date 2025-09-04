<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Services\Integrations\PartnerClient; // <— 新增：消费端客户端
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;

class SubjectController extends Controller
{
    /**
     * 列表 + 搜索 + 分页
     */
    public function index(Request $request)
    {
        // 授权：列表视图（需要在 SubjectPolicy 定义 viewAny）
        $this->authorize('viewAny', Subject::class);

        $search = $request->input('search');

        $subjects = Subject::when($search, function ($q) use ($search) {
                $q->where('subject_id', 'like', "%{$search}%")
                  ->orWhere('subject_Name', 'like', "%{$search}%");
            })
            ->orderBy('subject_Name')
            ->paginate(10)
            ->appends(['search' => $search]); // 保留查询参数

        return view('admin.subjects.index', compact('subjects', 'search'));
    }

    /**
     * 详情（演示：消费“队友API/Mock”获取导师资料并显示）
     */
    public function show(Subject $subject, PartnerClient $partner)
    {
        $this->authorize('view', $subject);

        $tutorProfile = null;
        if (!empty($subject->tutor_id)) {
            // PartnerClient 内部可先走 Mock，等队友API就切换到真接口
            try {
                $tutorProfile = $partner->getTutorProfile((string)$subject->tutor_id);
            } catch (\Throwable $e) {
                // 失败也不要中断页面渲染，给前端一个可判空的变量
                $tutorProfile = ['status' => 'error', 'message' => $e->getMessage()];
            }
        }

        return view('admin.subjects.show', compact('subject', 'tutorProfile'));
    }

    public function create()
    {
        $this->authorize('create', Subject::class);
        return view('admin.subjects.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Subject::class);

        $data = $request->validate([
            'subject_Name'        => 'required|string|max:150',
            'subject_Description' => 'nullable|string',
            'duration_Hours'      => 'nullable|integer|min:0',
            'subject_Fee'         => 'nullable|numeric|min:0',
            // 如果有 tutor 关联可加：
            // 'tutor_id'         => 'nullable|integer|exists:users,id',
        ]);

        try {
            Subject::create($data);
        } catch (QueryException $e) {
            return back()->withErrors(['db' => 'Database error: '.$e->getMessage()])
                         ->withInput();
        }

        return redirect()->route('admin.subjects.index')->with('success', 'Subject created.');
    }

    public function edit(Subject $subject)
    {
        $this->authorize('update', $subject);
        return view('admin.subjects.edit', compact('subject'));
    }

    public function update(Request $request, Subject $subject)
    {
        $this->authorize('update', $subject);

        $data = $request->validate([
            'subject_Name'        => 'required|string|max:150',
            'subject_Description' => 'nullable|string',
            'duration_Hours'      => 'nullable|integer|min:0',
            'subject_Fee'         => 'nullable|numeric|min:0',
            // 'tutor_id'         => 'nullable|integer|exists:users,id',
        ]);

        try {
            $subject->update($data);
        } catch (QueryException $e) {
            return back()->withErrors(['db' => 'Database error: '.$e->getMessage()])
                         ->withInput();
        }

        return redirect()->route('admin.subjects.index')->with('success', 'Subject updated.');
    }

    public function destroy(Subject $subject)
    {
        $this->authorize('delete', $subject);

        try {
            $subject->delete();
        } catch (QueryException $e) {
            return back()->withErrors(['db' => 'Database error: '.$e->getMessage()]);
        }

        return back()->with('success', 'Subject deleted.');
    }
}
