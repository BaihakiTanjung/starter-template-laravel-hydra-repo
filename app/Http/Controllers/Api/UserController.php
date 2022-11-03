<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Repositories\User\UserRepository;
use App\Http\Requests\StoreUserRequest;

class UserController extends Controller
{

    private $UserRepository;

    public function __construct(UserRepository $UserRepository)
    {
        $this->UserRepository = $UserRepository;
    }

    public function index(Request $request)
    {
        return $this->UserRepository->index($request);
    }


    public function store(StoreUserRequest $request)
    {
        return $this->UserRepository->store($request);
    }

    public function show($id)
    {
        return $this->UserRepository->show($id);
    }


    public function update(Request $request, $id)
    {
        return $this->UserRepository->update($request, $id);
    }

    public function destroy($id)
    {
        return $this->UserRepository->destroy($id);
    }
}
