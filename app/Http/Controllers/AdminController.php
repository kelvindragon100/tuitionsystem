<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Subject;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;                // 👈 新增：用于消费 Web Service
use App\Mail\UserPasswordResetMail;
use Symfony\Component\HttpFoundation\StreamedResponse;

use App\Services\Users\UserFactory;                // 👈 新增：设计模式 Factory

class AdminController extends Controller
{
    // Admin Dashboard
    public function index()
    {
        $totalStudents = User::where('role', 'student')->count();
        $totalTutors   = User::where('role', 'tutor')->count();

        return view('admin.dashboard', compact('totalStudents', 'totalTutors'));
    }

    // Generic Manage Users (Tutor / Student)
    public function manageUsers(Request $request)
    {
        $type   = $request->input('type', 'tutor'); // 默认 tutor
        $search = $request->input('search');

        $users = User::where('role', $type)
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('name', 'asc')
            ->paginate(10)
            ->appends(['search' => $search, 'type' => $type]);

        return view('admin.users', compact('users', 'search', 'type'));
    }

    // Show form to create user (Tutor/Student)
    // 👇 消费（Consume）我们暴露的 REST API：/api/v1/subjects
    public function createUserForm(Request $request)
    {
        $type = $request->input('type', 'tutor'); // 默认 tutor

        // 先尝试调用本系统暴露的 API（体现 Consume Web Service）
        try {
            $resp = Http::timeout(3)->get(url('/api/v1/subjects'));
            $subjects = collect($resp->json('data', []));
        } catch (\Throwable $e) {
            // 失败则回退到数据库（保证表单可用）
            $subjects = Subject::orderBy('subject_Name', 'asc')->get(['subject_id', 'subject_Name']);
        }

        return view('admin.create_user', compact('type', 'subjects'));
    }

    // Store new user (Tutor/Student) —— 使用 Factory Pattern 统一创建
    public function createUser(Request $request)
    {
        $auto = $request->boolean('auto_generate'); // true=自动生成
        $send = $request->boolean('send_email');

        // 基础校验
        $rules = [
            'role'  => 'required|in:tutor,student,admin',
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
        ];
        if (!$auto) {
            $rules['password'] = 'required|string|min:8|confirmed';
        }
        // 如果是学生，subjects 可以为数组（多选）
        if ($request->input('role') === 'student') {
            $rules['subjects']   = 'nullable|array';
            $rules['subjects.*'] = 'string|exists:subjects,subject_id';
        }

        $validated = $request->validate($rules);

        // 密码：自动或手动
        $plain = $auto ? UserFactory::generateStrongPassword(12) : $request->input('password');

        DB::transaction(function () use ($validated, $plain, $request) {
            // 👉 使用 Factory 统一创建不同角色用户（Design Pattern）
            $factory = new UserFactory();
            $user = $factory->create([
                'role'     => $validated['role'],
                'name'     => $validated['name'],
                'email'    => $validated['email'],
                'password' => $plain,
                'subjects' => (array) $request->input('subjects', []),
            ]);

            // 提供给外层用于 flash 提示
            $request->attributes->set('created_user', $user);
        });

        /** @var \App\Models\User $createdUser */
        $createdUser = $request->attributes->get('created_user');

        // 可选邮件通知
        $mailSent = false;
        if ($send) {
            try {
                Mail::to($createdUser->email)->send(new UserPasswordResetMail($createdUser, $plain));
                $mailSent = true;
            } catch (\Throwable $e) {
                $mailSent = false;
            }
        }

        // 回显提示（含新密码，方便复制）
        $msg = ucfirst($createdUser->role) . " created successfully.";
        if ($auto) {
            $msg .= "\nNew password: {$plain}";
            $msg .= $mailSent ? "\nAn email notification was sent to the user."
                              : ($send ? "\n(Mail could not be sent.)" : '');
        }

        return redirect()
            ->route('admin.users', ['type' => $createdUser->role])
            ->with('success', $msg);
    }

    // Edit User
    public function editUserForm($id)
    {
        $user = User::findOrFail($id);

        // 科目列表 + 学生已选科目
        $subjects = Subject::orderBy('subject_Name', 'asc')->get(['subject_id', 'subject_Name']);
        $selectedSubjectIds = $user->subjects()
                                   ->pluck('subjects.subject_id')
                                   ->toArray();

        return view('admin.edit_user', compact('user', 'subjects', 'selectedSubjectIds'));
    }

    // Update User
    public function updateUser(Request $request, $id)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email,' . $id,
            'password'  => 'nullable|string|min:8|confirmed', // 可留空
            'role'      => 'nullable|in:tutor,student,admin',
            // 当选择/切换到 student 时可提交 subjects
            'subjects'   => 'nullable|array',
            'subjects.*' => 'string|exists:subjects,subject_id',
        ]);

        DB::transaction(function () use ($request, $id) {
            $user = User::findOrFail($id);

            $user->name  = $request->name;
            $user->email = $request->email;

            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }
            // 允许改角色（如需限制请去掉）
            if ($request->filled('role')) {
                $user->role = $request->role;
            }

            $user->save();

            // 角色为 student 时，处理选课；否则清空
            if ($user->role === 'student') {
                $subjectIds = (array) $request->input('subjects', []);
                $user->subjects()->sync($subjectIds);
            } else {
                // 如果从 student 改成了其他角色，清空 pivot
                $user->subjects()->detach();
            }

            $request->attributes->set('updated_user', $user);
        });

        /** @var \App\Models\User $updatedUser */
        $updatedUser = $request->attributes->get('updated_user');

        return redirect()
            ->route('admin.users', ['type' => $updatedUser->role])
            ->with('success', ucfirst($updatedUser->role) . ' updated successfully.');
    }

    // Delete User
    public function deleteUser($id)
    {
        $user = User::findOrFail($id);

        if (in_array($user->role, ['tutor', 'student'])) {
            $role = $user->role;
            // 先解除关联以避免外键问题
            $user->subjects()->detach();
            $user->delete();
            return back()->with('success', ucfirst($role).' deleted.');
        }

        return back()->with('error', 'You can only delete tutor or student accounts.');
    }

    // Reset password：强密码 + 可选邮件 + 前端可复制
    public function resetPassword($id)
    {
        $user = User::findOrFail($id);

        if (!in_array($user->role, ['tutor', 'student'])) {
            return back()->with('error', 'Only tutor or student accounts can be reset.');
        }

        $plain = UserFactory::generateStrongPassword(12);  // 👈 统一用 Factory 的生成器
        $user->password = Hash::make($plain);
        $user->save();

        $mailSent = false;
        try {
            Mail::to($user->email)->send(new UserPasswordResetMail($user, $plain));
            $mailSent = true;
        } catch (\Throwable $e) {
            $mailSent = false;
        }

        $msg = "Password reset successfully.\nNew password: {$plain}";
        $msg .= $mailSent ? "\nAn email notification was sent to the user."
                          : "\n(Mail could not be sent.)";

        return back()->with('success', $msg);
    }

    // Ban（建议用 banned_at 时间戳；若你表里用 is_banned 布尔，见下方注释）
    public function ban(User $user)
    {
        // 只允许封禁 tutor/student（你也可以去掉这个限制）
        if (!in_array($user->role, ['tutor', 'student'])) {
            return back()->with('error', 'Only tutor or student accounts can be banned.');
        }

        // 如果你表里没有 banned_at 而是 is_banned（boolean），改成：$user->is_banned = true;
        if (array_key_exists('banned_at', $user->getAttributes())) {
            $user->banned_at = now();
        } else {
            $user->is_banned = true;
        }
        $user->save();

        return back()->with('success', 'User banned.');
    }

    // Unban
    public function unban(User $user)
    {
        if (!in_array($user->role, ['tutor', 'student'])) {
            return back()->with('error', 'Only tutor or student accounts can be unbanned.');
        }

        // 如果你表里没有 banned_at 而是 is_banned（boolean），改成：$user->is_banned = false;
        if (array_key_exists('banned_at', $user->getAttributes())) {
            $user->banned_at = null;
        } else {
            $user->is_banned = false;
        }
        $user->save();

        return back()->with('success', 'User unbanned.');
    }

    // Export CSV（带 type/search 条件；导出所有匹配结果，非仅当前页）
    public function export(Request $request): StreamedResponse
    {
        $type   = $request->string('type')->toString();    // tutor / student / 也允许为空
        $search = $request->string('search')->toString();  // 可为空

        $query = User::query()
            ->when($type, fn($q) => $q->where('role', $type))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('name', 'like', "%{$search}%")
                       ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('id');

        $filename = 'users_'.($type ?: 'all').'_'.now()->format('Ymd_His').'.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        return response()->stream(function () use ($query) {
            $out = fopen('php://output', 'w');

            // 写入 UTF-8 BOM，避免 Excel 乱码
            echo chr(0xEF).chr(0xBB).chr(0xBF);

            // 表头
            fputcsv($out, ['ID', 'Name', 'Email', 'Role', 'Status', 'Created At']);

            $query->chunk(1000, function ($rows) use ($out) {
                foreach ($rows as $u) {
                    // 兼容两种字段：banned_at 或 is_banned
                    $isBanned = array_key_exists('banned_at', $u->getAttributes())
                        ? !is_null($u->banned_at)
                        : (bool)($u->is_banned ?? false);

                    fputcsv($out, [
                        $u->id,
                        $u->name,
                        $u->email,
                        $u->role,
                        $isBanned ? 'Banned' : 'Active',
                        optional($u->created_at)->format('Y-m-d H:i:s'),
                    ]);
                }
            });

            fclose($out);
        }, 200, $headers);
    }
}
