<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()
            ->with('roles:id,name')
            ->when($request->search, fn ($q, $s) => $q->where('name', 'LIKE', "%{$s}%")->orWhere('email', 'LIKE', "%{$s}%"))
            ->when($request->role, fn ($q, $v) => $q->whereHas('roles', fn ($rq) => $rq->where('name', $v)))
            ->when($request->active, fn ($q, $v) => $q->where('is_active', $v === '1'))
            ->orderByDesc('id');

        return $this->render('admin/Users/Index', [
            'users' => $this->paginated($query->paginate(15)->withQueryString()),
            'roles' => Role::pluck('name'),
            'filters' => $request->only(['search', 'role', 'active']),
        ]);
    }

    public function store(UserRequest $request)
    {
        DB::transaction(function () use ($request) {
            $user = User::query()->create([
                ...$request->safe()->except(['roles', 'password']),
                'password' => bcrypt($request->password),
                'is_active' => $request->boolean('is_active', true),
            ]);
            $user->syncRoles($request->input('roles', []));
        });

        $this->successFlash('User created.');

        return redirect()->route('admin.users.index');
    }

    public function update(UserRequest $request, User $user)
    {
        DB::transaction(function () use ($request, $user) {
            $data = $request->safe()->except(['roles', 'password']);
            if ($request->filled('password')) {
                $data['password'] = bcrypt($request->password);
            }
            $user->update($data + ['is_active' => $request->boolean('is_active', true)]);
            $user->syncRoles($request->input('roles', []));
        });

        $this->successFlash('User updated.');

        return redirect()->route('admin.users.index');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            $this->errorFlash('You cannot delete your own account.');

            return back();
        }

        $user->delete();
        $this->successFlash('User deleted.');

        return back();
    }
}
