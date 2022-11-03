<?php

namespace App\Repositories\User;

use LaravelEasyRepository\Implementations\Eloquent;
use App\Models\User;
use App\Models\Role;
use Laravel\Sanctum\Exceptions\MissingAbilityException;
use Illuminate\Support\Facades\Hash;
use App\Http\Helpers\ResponseHelpers;


class UserRepositoryImplement extends Eloquent implements UserRepository
{

    /**
     * Model class to be used in this repository for the common methods inside Eloquent
     * Don't remove or change $this->model variable name
     * @property Model|mixed $model;
     */
    protected $model;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    public function index($request)
    {
        return ResponseHelpers::sendSuccess('get users data', $this->model->all());
    }

    public function store($request)
    {
        $user = User::where('email', $request['email'])->first();
        if ($user) {
            return response(['error' => 1, 'message' => 'user already exists'], 409);
        }

        $user = User::create([
            'email' => $request['email'],
            'password' => Hash::make($request['password']),
            'name' => $request['name'],
        ]);

        $defaultRoleSlug = config('hydra.default_user_role_slug', 'user');
        $user->roles()->attach(Role::where('slug', $defaultRoleSlug)->first());

        return ResponseHelpers::sendSuccess('user created', $user);
    }

    public function show($id)
    {
        return ResponseHelpers::sendSuccess('get user data', $this->model->find($id));
    }

    public function update($request, $id)
    {
        $this->model = $this->model->find($id);
        $this->model->name = $request->name ?? $this->model->name;
        $this->model->email = $request->email ?? $this->model->email;
        $this->model->password = $request->password ? Hash::make($request->password) : $this->model->password;
        $this->model->email_verified_at = $request->email_verified_at ?? $this->model->email_verified_at;

        try {
            //check if the logged in user is updating it's own record
            $loggedInUser = $request->user();
            if ($loggedInUser->id == $id) {
                $this->model->update();
            } elseif ($loggedInUser->tokenCan('admin') || $loggedInUser->tokenCan('super-admin')) {
                $this->model->update();
            } else {
                throw new MissingAbilityException('Not Authorized');
            }
        } catch (\Throwable $th) {
            throw $th;
        }


        return ResponseHelpers::sendSuccess('user updated', $this->model);
    }


    public function login($request)
    {
        $creds = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $creds['email'])->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response(['error' => 1, 'message' => 'invalid credentials'], 401);
        }

        if (config('hydra.delete_previous_access_tokens_on_login', false)) {
            $user->tokens()->delete();
        }

        $roles = $user->roles->pluck('slug')->all();

        $plainTextToken = $user->createToken('hydra-api-token', $roles)->plainTextToken;

        return ResponseHelpers::sendSuccess('user logged in', ['error' => 0, 'id' => $user->id, 'token' => $plainTextToken]);
    }

    public function destroy($id)
    {
        $this->model = $this->model->find($id);
        $adminRole = Role::where('slug', 'admin')->first();
        $userRoles = $this->model->roles;

        if ($userRoles->contains($adminRole)) {
            //the current user is admin, then if there is only one admin - don't delete
            $numberOfAdmins = Role::where('slug', 'admin')->first()->users()->count();
            if (1 == $numberOfAdmins) {
                return response(['error' => 1, 'message' => 'Create another admin before deleting this only admin user'], 409);
            }
        }

        $this->model->delete();

        return ResponseHelpers::sendSuccess('user deleted', $this->model);
    }

    public function me($request)
    {
        return $request->user();
    }
}
