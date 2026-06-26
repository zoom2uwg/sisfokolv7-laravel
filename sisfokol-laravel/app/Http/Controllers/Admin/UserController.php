<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use App\Support\Crudlfix\Crudlfix;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    use Crudlfix;

    protected function crudlfix(): array
    {
        return [
            'model'        => User::class,
            'view'         => 'admin.users',
            'route'        => 'admin.users',
            'requestClass' => StoreUserRequest::class,
            'search'       => ['username', 'nama', 'email'],
            'with'         => ['roles'],
            'perPage'      => 20,
            'viewData'     => [
                'roles' => Role::all(),
            ],
        ];
    }

    protected function beforeStore(array $validated, Request $request): array
    {
        $validated['password'] = Hash::make($validated['password']);
        return $validated;
    }

    protected function afterStore($model, Request $request): void
    {
        if (!empty($request->input('role'))) {
            $model->assignRole($request->input('role'));
        }
    }

    protected function beforeUpdate(array $validated, $model, Request $request): array
    {
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }
        return $validated;
    }

    protected function afterUpdate($model, Request $request): void
    {
        if ($request->has('role')) {
            $model->syncRoles($request->input('role'));
        }
    }
}
